<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Customer;
use App\Models\Deal;
use App\Models\Lead;
use App\Models\Project;
use App\Models\Task;
use App\Models\TimeEntry;
use App\Models\AttendanceRecord;
use App\Models\User;
use Illuminate\Support\Collection;

class DashboardService
{
    public function overview(User $user): array
    {
        $baseTasks = Task::query();
        $baseProjects = Project::query();
        $baseDeals = Deal::query();
        $baseCustomers = Customer::query();
        $baseLeads = Lead::query();

        if (! $user->isAdmin() && ! $user->hasRole('manager')) {
            $baseTasks->whereHas('assignees', fn ($query) => $query->where('users.id', $user->id));
            $baseProjects->where(function ($query) use ($user) {
                $query->where('manager_id', $user->id)
                    ->orWhereHas('members', fn ($memberQuery) => $memberQuery->where('users.id', $user->id));
            });
            $baseDeals->where('owner_id', $user->id);
            $baseCustomers->where('owner_id', $user->id);
            $baseLeads->where('owner_id', $user->id);
        }

        $activeAttendanceQuery = AttendanceRecord::query()->with('user')->whereNull('checked_out_at');
        $activeTimersQuery = TimeEntry::query()->with(['user', 'task', 'project'])->whereNull('ended_at');

        if (! $user->isAdmin() && ! $user->hasRole('manager')) {
            $activeAttendanceQuery->where('user_id', $user->id);
            $activeTimersQuery->where('user_id', $user->id);
        }

        return [
            'stats' => [
                'customers' => (clone $baseCustomers)->count(),
                'leads' => (clone $baseLeads)->count(),
                'open_deals' => (clone $baseDeals)->where('status', 'open')->count(),
                'won_deals' => (clone $baseDeals)->where('status', 'won')->count(),
                'sales_total' => (float) (clone $baseDeals)->where('status', 'won')->sum('value'),
                'projects' => (clone $baseProjects)->count(),
                'late_projects' => (clone $baseProjects)->whereDate('due_date', '<', now())->where('status', '!=', 'completed')->count(),
                'tasks' => (clone $baseTasks)->count(),
                'overdue_tasks' => (clone $baseTasks)->whereDate('due_date', '<', now())->where('status', '!=', 'done')->count(),
            ],
            'sales_by_stage' => Deal::query()
                ->with('stage')
                ->get()
                ->groupBy(fn (Deal $deal) => $deal->stage?->name ?? 'Unknown')
                ->map(fn (Collection $group) => (float) $group->sum('value'))
                ->all(),
            'project_status_counts' => Project::query()
                ->selectRaw('status, COUNT(*) as total')
                ->groupBy('status')
                ->pluck('total', 'status')
                ->all(),
            'recent_activities' => AuditLog::query()
                ->with('user')
                ->latest('created_at')
                ->limit(10)
                ->get(),
            'recent_leads' => Lead::query()->latest()->limit(6)->get(),
            'overdue_tasks' => Task::query()
                ->with(['project', 'assignees'])
                ->whereDate('due_date', '<', now())
                ->where('status', '!=', 'done')
                ->latest('due_date')
                ->limit(8)
                ->get(),
            'upcoming_deadlines' => Task::query()
                ->with('project')
                ->whereBetween('due_date', [now()->toDateString(), now()->addDays(7)->toDateString()])
                ->where('status', '!=', 'done')
                ->orderBy('due_date')
                ->limit(8)
                ->get(),
            'team_performance' => User::query()
                ->withCount([
                    'tasks as completed_tasks_count' => fn ($query) => $query->where('status', 'done'),
                    'projects as active_projects_count',
                ])
                ->withSum('timeEntries as tracked_minutes_sum', 'minutes')
                ->withSum('attendanceRecords as attendance_minutes_sum', 'worked_minutes')
                ->limit(8)
                ->get(),
            'active_attendance' => $activeAttendanceQuery
                ->latest('checked_in_at')
                ->limit(8)
                ->get(),
            'active_task_timers' => $activeTimersQuery
                ->latest('started_at')
                ->limit(8)
                ->get(),
            'risk_signals' => [
                'many_in_progress' => Task::query()->whereIn('status', ['in_progress', 'review'])->count(),
                'completed_today' => Task::query()->where('status', 'done')->whereDate('updated_at', now()->toDateString())->count(),
                'attendance_minutes_today' => (int) AttendanceRecord::query()->whereDate('work_date', now()->toDateString())->sum('worked_minutes'),
                'tracked_minutes_today' => (int) TimeEntry::query()->whereDate('started_at', now()->toDateString())->sum('minutes'),
            ],
        ];
    }
}
