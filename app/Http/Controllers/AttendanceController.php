<?php

namespace App\Http\Controllers;

use App\Models\AttendanceRecord;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        abort_unless($request->user()->hasPermissionTo('reports.view'), 403);

        $recordsQuery = AttendanceRecord::query()
            ->with('user')
            ->when($request->filled('user_id'), fn ($query) => $query->where('user_id', $request->integer('user_id')))
            ->when($request->filled('from'), fn ($query) => $query->whereDate('work_date', '>=', $request->string('from')))
            ->when($request->filled('to'), fn ($query) => $query->whereDate('work_date', '<=', $request->string('to')));

        if (! $request->user()->isAdmin() && ! $request->user()->hasRole('manager')) {
            $recordsQuery->where('user_id', $request->user()->id);
        }

        $records = $recordsQuery
            ->latest('work_date')
            ->latest('checked_in_at')
            ->paginate(20)
            ->withQueryString();

        $users = (! $request->user()->isAdmin() && ! $request->user()->hasRole('manager'))
            ? User::query()->whereKey($request->user()->id)->get(['id', 'name'])
            : User::query()->active()->orderBy('name')->get(['id', 'name']);

        $summaryQuery = AttendanceRecord::query()
            ->when($request->filled('user_id'), fn ($query) => $query->where('user_id', $request->integer('user_id')))
            ->when($request->filled('from'), fn ($query) => $query->whereDate('work_date', '>=', $request->string('from')))
            ->when($request->filled('to'), fn ($query) => $query->whereDate('work_date', '<=', $request->string('to')));

        if (! $request->user()->isAdmin() && ! $request->user()->hasRole('manager')) {
            $summaryQuery->where('user_id', $request->user()->id);
        }

        $summary = [
            'days_count' => (clone $summaryQuery)->count(),
            'open_days_count' => (clone $summaryQuery)->whereNull('checked_out_at')->count(),
            'worked_hours' => round(((clone $summaryQuery)->sum('worked_minutes')) / 60, 1),
            'worked_duration' => \App\Support\Duration::fromMinutes((clone $summaryQuery)->sum('worked_minutes')),
        ];

        return view('attendance.index', compact('records', 'users', 'summary'));
    }

    public function checkIn(Request $request, AuditLogService $auditLogService)
    {
        $user = $request->user();
        $today = now()->toDateString();

        $record = AttendanceRecord::query()->firstOrNew([
            'user_id' => $user->id,
            'work_date' => $today,
        ]);

        if ($record->checked_in_at && ! $record->checked_out_at) {
            return back()->with('info', __('You are already checked in for today.'));
        }

        if (! $record->exists) {
            $record->fill([
                'checked_in_at' => now(),
                'worked_minutes' => 0,
            ])->save();
        } else {
            $record->update([
                'checked_in_at' => $record->checked_in_at ?? now(),
                'checked_out_at' => null,
            ]);
        }

        $auditLogService->record(
            module: 'team',
            event: 'attendance_checked_in',
            auditable: $record,
            newValues: $record->toArray(),
            description: __('Attendance check-in recorded.'),
            actor: $user,
        );

        return back()->with('success', __('Work day started successfully.'));
    }

    public function checkOut(Request $request, AuditLogService $auditLogService)
    {
        $user = $request->user();
        $record = AttendanceRecord::query()
            ->where('user_id', $user->id)
            ->whereDate('work_date', now()->toDateString())
            ->latest('checked_in_at')
            ->first();

        if (! $record || ! $record->checked_in_at) {
            return back()->with('error', __('You have not checked in yet today.'));
        }

        if ($record->checked_out_at) {
            return back()->with('info', __('You have already checked out today.'));
        }

        $record->update([
            'checked_out_at' => now(),
            'worked_minutes' => $record->checked_in_at->diffInMinutes(now()),
        ]);

        $auditLogService->record(
            module: 'team',
            event: 'attendance_checked_out',
            auditable: $record,
            oldValues: ['checked_out_at' => null, 'worked_minutes' => 0],
            newValues: ['checked_out_at' => $record->checked_out_at?->toDateTimeString(), 'worked_minutes' => $record->worked_minutes],
            description: __('Attendance check-out recorded.'),
            actor: $user,
        );

        return back()->with('success', __('Work day ended successfully.'));
    }
}
