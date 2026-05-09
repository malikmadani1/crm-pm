<?php

namespace App\Http\Controllers\PM;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTimeEntryRequest;
use App\Http\Requests\UpdateTimeEntryRequest;
use App\Models\Project;
use App\Models\Task;
use App\Models\TimeEntry;
use App\Models\User;
use App\Services\AuditLogService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class TimeEntryController extends Controller
{
    public function index(Request $request)
    {
        abort_unless($request->user()->hasPermissionTo('time_entries.view'), 403);

        $projects = Project::query()->orderBy('name')->get(['id', 'name']);
        $users = User::query()->active()->orderBy('name')->get(['id', 'name']);

        $timeEntries = TimeEntry::query()
            ->with(['project', 'task', 'user'])
            ->when($request->filled('project_id'), fn ($query) => $query->where('project_id', $request->integer('project_id')))
            ->when($request->filled('user_id'), fn ($query) => $query->where('user_id', $request->integer('user_id')))
            ->when(! $request->user()->isAdmin() && ! $request->user()->hasRole('manager'), fn ($query) => $query->where('user_id', $request->user()->id))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('pm.time-entries.index', compact('timeEntries', 'projects', 'users'));
    }

    public function create()
    {
        abort_unless(auth()->user()->hasPermissionTo('time_entries.create'), 403);

        return view('pm.time-entries.create', [
            'projects' => Project::query()->orderBy('name')->get(['id', 'name']),
            'tasks' => Task::query()->orderBy('title')->get(['id', 'title']),
            'users' => User::query()->active()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(StoreTimeEntryRequest $request, AuditLogService $auditLogService)
    {
        $data = $request->validated();
        $data['user_id'] ??= $request->user()->id;

        if (! isset($data['minutes']) && ! empty($data['ended_at'])) {
            $data['minutes'] = Carbon::parse($data['started_at'])->diffInMinutes(Carbon::parse($data['ended_at']));
        }

        $timeEntry = TimeEntry::query()->create($data);

        $auditLogService->record(
            module: 'tasks',
            event: 'time_entry_created',
            auditable: $timeEntry,
            newValues: $timeEntry->toArray(),
            description: __('Time entry created.'),
        );

        return redirect()->route('time-entries.index')->with('success', __('Time entry created successfully.'));
    }

    public function show(TimeEntry $timeEntry)
    {
        abort_unless(auth()->user()->hasPermissionTo('time_entries.view'), 403);

        $timeEntry->load(['project', 'task', 'user']);

        return view('pm.time-entries.show', compact('timeEntry'));
    }

    public function edit(TimeEntry $timeEntry)
    {
        abort_unless(auth()->user()->hasPermissionTo('time_entries.update'), 403);

        return view('pm.time-entries.edit', [
            'timeEntry' => $timeEntry,
            'projects' => Project::query()->orderBy('name')->get(['id', 'name']),
            'tasks' => Task::query()->orderBy('title')->get(['id', 'title']),
            'users' => User::query()->active()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function update(UpdateTimeEntryRequest $request, TimeEntry $timeEntry, AuditLogService $auditLogService)
    {
        abort_unless($request->user()->hasPermissionTo('time_entries.update'), 403);

        $oldValues = $timeEntry->toArray();
        $data = $request->validated();

        if (! isset($data['minutes']) && ! empty($data['ended_at'])) {
            $data['minutes'] = Carbon::parse($data['started_at'])->diffInMinutes(Carbon::parse($data['ended_at']));
        }

        $timeEntry->update($data);

        $auditLogService->record(
            module: 'tasks',
            event: 'time_entry_updated',
            auditable: $timeEntry,
            oldValues: $oldValues,
            newValues: $timeEntry->fresh()->toArray(),
            description: __('Time entry updated.'),
        );

        return redirect()->route('time-entries.show', $timeEntry)->with('success', __('Time entry updated successfully.'));
    }

    public function destroy(Request $request, TimeEntry $timeEntry, AuditLogService $auditLogService)
    {
        abort_unless($request->user()->hasPermissionTo('time_entries.delete'), 403);

        $auditLogService->record(
            module: 'tasks',
            event: 'time_entry_deleted',
            auditable: $timeEntry,
            oldValues: $timeEntry->toArray(),
            description: __('Time entry deleted.'),
        );

        $timeEntry->delete();

        return redirect()->route('time-entries.index')->with('success', __('Time entry deleted successfully.'));
    }
}
