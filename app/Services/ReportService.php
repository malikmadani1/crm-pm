<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Deal;
use App\Models\Project;
use App\Models\Task;
use App\Models\TimeEntry;
use App\Models\AttendanceRecord;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportService
{
    public function sales(Request $request): array
    {
        $query = Deal::query()->with(['customer', 'owner', 'stage']);
        $this->applyDateFilters($query, $request);
        $this->applyIdFilter($query, $request, 'customer_id');

        $deals = $query->latest()->paginate(15)->withQueryString();

        return [
            'items' => $deals,
            'summary' => [
                'total_value' => (float) $query->sum('value'),
                'won_value' => (float) (clone $query)->where('status', 'won')->sum('value'),
                'open_value' => (float) (clone $query)->where('status', 'open')->sum('value'),
                'avg_probability' => round((float) (clone $query)->avg('probability'), 1),
            ],
        ];
    }

    public function crm(Request $request): array
    {
        $customersQuery = Customer::query();
        $leadsQuery = \App\Models\Lead::query();

        $this->applyDateFilters($customersQuery, $request);
        $this->applyDateFilters($leadsQuery, $request);

        return [
            'customers_count' => $customersQuery->count(),
            'potential_customers' => (clone $customersQuery)->where('status', 'potential')->count(),
            'active_customers' => (clone $customersQuery)->where('status', 'active')->count(),
            'lead_open_count' => (clone $leadsQuery)->where('status', 'open')->count(),
            'lead_converted_count' => (clone $leadsQuery)->where('status', 'converted')->count(),
        ];
    }

    public function projects(Request $request): array
    {
        $query = Project::query()->with(['customer', 'manager']);
        $this->applyDateFilters($query, $request);
        $this->applyIdFilter($query, $request, 'customer_id');

        return [
            'items' => $query->latest()->paginate(15)->withQueryString(),
            'summary' => [
                'budget_total' => (float) $query->sum('budget'),
                'completed_count' => (clone $query)->where('status', 'completed')->count(),
                'late_count' => (clone $query)->whereDate('due_date', '<', now())->where('status', '!=', 'completed')->count(),
                'avg_progress' => round((float) (clone $query)->avg('progress'), 1),
            ],
        ];
    }

    public function tasks(Request $request): array
    {
        $query = Task::query()->with(['project', 'assignees']);
        $this->applyDateFilters($query, $request);
        $this->applyIdFilter($query, $request, 'project_id');
        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        $items = $query->latest()->paginate(15)->withQueryString();
        $items->getCollection()->loadSum('timeEntries as tracked_minutes_sum', 'minutes');

        return [
            'items' => $items,
            'summary' => [
                'done_count' => (clone $query)->where('status', 'done')->count(),
                'overdue_count' => (clone $query)->whereDate('due_date', '<', now())->where('status', '!=', 'done')->count(),
                'avg_completion' => round((float) (clone $query)->avg('completion_percentage'), 1),
                'tracked_hours_total' => round(((clone TimeEntry::query())
                    ->when($request->filled('project_id'), function ($timeQuery) use ($request) {
                        $timeQuery->whereHas('task', fn ($taskQuery) => $taskQuery->where('project_id', $request->integer('project_id')));
                    })
                    ->when($request->filled('from'), fn ($timeQuery) => $timeQuery->whereDate('started_at', '>=', $request->string('from')))
                    ->when($request->filled('to'), fn ($timeQuery) => $timeQuery->whereDate('started_at', '<=', $request->string('to')))
                    ->sum('minutes')) / 60, 1),
            ],
        ];
    }

    public function teamPerformance(Request $request): array
    {
        $query = User::query()
            ->withCount([
                'tasks as completed_tasks_count' => fn ($builder) => $builder->where('status', 'done'),
                'attendanceRecords as attendance_days_count',
            ])
            ->withSum('timeEntries as tracked_minutes_sum', 'minutes')
            ->withSum('attendanceRecords as attendance_minutes_sum', 'worked_minutes');

        $items = $query->paginate(15)->withQueryString();

        $summaryAttendance = AttendanceRecord::query();
        $summaryTracked = TimeEntry::query();

        if ($request->filled('from')) {
            $summaryAttendance->whereDate('work_date', '>=', $request->string('from'));
            $summaryTracked->whereDate('started_at', '>=', $request->string('from'));
        }

        if ($request->filled('to')) {
            $summaryAttendance->whereDate('work_date', '<=', $request->string('to'));
            $summaryTracked->whereDate('started_at', '<=', $request->string('to'));
        }

        $attendanceMinutes = (int) $summaryAttendance->sum('worked_minutes');
        $trackedMinutes = (int) $summaryTracked->sum('minutes');

        return [
            'items' => $items,
            'summary' => [
                'attendance_hours' => round($attendanceMinutes / 60, 1),
                'tracked_hours' => round($trackedMinutes / 60, 1),
                'utilization' => $attendanceMinutes > 0 ? round(($trackedMinutes / $attendanceMinutes) * 100, 1) : 0,
            ],
        ];
    }

    public function streamCsv(string $filename, array $headers, iterable $rows): StreamedResponse
    {
        return response()->streamDownload(function () use ($headers, $rows): void {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $headers);

            foreach ($rows as $row) {
                fputcsv($handle, $row);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    private function applyDateFilters(Builder $query, Request $request): void
    {
        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->string('from'));
        }

        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->string('to'));
        }
    }

    private function applyIdFilter(Builder $query, Request $request, string $column): void
    {
        if ($request->filled($column)) {
            $query->where($column, $request->integer($column));
        }
    }
}
