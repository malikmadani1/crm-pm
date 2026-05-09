<?php

namespace App\Services;

use App\Models\Task;
use App\Models\TimeEntry;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class TaskTimerService
{
    public function __construct(private readonly AuditLogService $auditLogService)
    {
    }

    public function activeEntryFor(User $user): ?TimeEntry
    {
        return TimeEntry::query()
            ->with(['task', 'project'])
            ->where('user_id', $user->id)
            ->whereNull('ended_at')
            ->latest('started_at')
            ->first();
    }

    public function start(Task $task, User $user): TimeEntry
    {
        $activeEntry = $this->activeEntryFor($user);

        if ($activeEntry && (int) $activeEntry->task_id !== (int) $task->id) {
            throw ValidationException::withMessages([
                'timer' => __('You already have a running timer on task :task.', [
                    'task' => $activeEntry->task?->title ?? '#'.$activeEntry->task_id,
                ]),
            ]);
        }

        if ($activeEntry) {
            return $activeEntry;
        }

        $entry = TimeEntry::query()->create([
            'project_id' => $task->project_id,
            'task_id' => $task->id,
            'user_id' => $user->id,
            'started_at' => now(),
            'minutes' => 0,
            'billable' => false,
            'description' => __('Timer started from task view.'),
        ]);

        $this->auditLogService->record(
            module: 'tasks',
            event: 'time_entry_created',
            auditable: $task,
            newValues: [
                'task_id' => $task->id,
                'project_id' => $task->project_id,
                'user_id' => $user->id,
                'started_at' => $entry->started_at?->toDateTimeString(),
            ],
            description: __('Task timer started for :task.', ['task' => $task->title]),
            actor: $user,
        );

        app(TaskWorkflowService::class)->storeTaskLog(
            $task,
            'task_updated',
            null,
            ['timer' => __('Started by :name', ['name' => $user->name])],
            __('Task timer started.')
        );

        return $entry;
    }

    public function stop(Task $task, User $user): TimeEntry
    {
        $entry = TimeEntry::query()
            ->where('task_id', $task->id)
            ->where('user_id', $user->id)
            ->whereNull('ended_at')
            ->latest('started_at')
            ->first();

        if (! $entry) {
            throw ValidationException::withMessages([
                'timer' => __('There is no running timer on this task.'),
            ]);
        }

        $entry->update([
            'ended_at' => now(),
            'minutes' => $entry->started_at->diffInMinutes(now()),
        ]);

        $this->syncTaskActualHours($task);

        $this->auditLogService->record(
            module: 'tasks',
            event: 'time_entry_updated',
            auditable: $task,
            oldValues: [
                'started_at' => $entry->started_at?->toDateTimeString(),
                'ended_at' => null,
                'minutes' => 0,
            ],
            newValues: [
                'started_at' => $entry->started_at?->toDateTimeString(),
                'ended_at' => $entry->ended_at?->toDateTimeString(),
                'minutes' => $entry->minutes,
            ],
            description: __('Task timer stopped for :task.', ['task' => $task->title]),
            actor: $user,
        );

        app(TaskWorkflowService::class)->storeTaskLog(
            $task->fresh(),
            'task_updated',
            null,
            ['timer' => __('Stopped by :name after :minutes minutes', ['name' => $user->name, 'minutes' => $entry->minutes])],
            __('Task timer stopped.')
        );

        return $entry;
    }

    public function syncTaskActualHours(Task $task): void
    {
        $minutes = (int) TimeEntry::query()
            ->where('task_id', $task->id)
            ->sum('minutes');

        $task->forceFill([
            'actual_hours' => round($minutes / 60, 2),
        ])->save();
    }
}
