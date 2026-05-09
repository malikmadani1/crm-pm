<?php

namespace App\Services;

use App\Models\Project;
use App\Models\Task;
use App\Models\TaskComment;
use App\Models\User;
use App\Notifications\TaskAssignedNotification;
use App\Notifications\TaskCommentAddedNotification;
use App\Notifications\TaskDeadlineReminderNotification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class TaskWorkflowService
{
    public function __construct(private readonly AuditLogService $auditLogService)
    {
    }

    public function syncAssignments(Task $task, array $assigneeIds): void
    {
        $normalizedIds = collect($assigneeIds)->filter()->map(fn ($id) => (int) $id)->unique()->values();
        $existingIds = $task->assignees()->pluck('users.id');

        $syncPayload = $normalizedIds
            ->mapWithKeys(fn (int $id, int $index) => [
                $id => [
                    'is_primary' => $index === 0,
                    'assigned_at' => now(),
                ],
            ])
            ->all();

        $task->assignees()->sync($syncPayload);

        $newlyAssigned = $normalizedIds->diff($existingIds);

        User::query()->whereIn('id', $newlyAssigned)->get()->each(function (User $user) use ($task): void {
            $user->notify(new TaskAssignedNotification($task));
        });
    }

    public function move(Task $task, string $status): Task
    {
        $oldValues = $task->only(['status', 'position', 'completion_percentage']);

        $task->update([
            'status' => $status,
            'completion_percentage' => $status === 'done' ? 100 : $task->completion_percentage,
            'completed_at' => $status === 'done' ? now() : null,
        ]);

        $this->storeTaskLog($task, 'status_changed', $oldValues, $task->only(['status', 'position', 'completion_percentage']));
        $this->syncProjectProgress($task->project);

        return $task->refresh();
    }

    public function addComment(Task $task, string $body, User $author): TaskComment
    {
        return DB::transaction(function () use ($task, $body, $author) {
            $comment = $task->comments()->create([
                'user_id' => $author->id,
                'body' => $body,
            ]);

            $this->storeTaskLog($task, 'comment_added', null, ['comment_id' => $comment->id, 'body' => $body], __('A new comment was added.'));

            $task->assignees()
                ->where('users.id', '!=', $author->id)
                ->get()
                ->each(fn (User $user) => $user->notify(new TaskCommentAddedNotification($task, $comment, $author)));

            return $comment;
        });
    }

    public function syncProjectProgress(Project $project): void
    {
        $totalTasks = $project->tasks()->count();
        $completedTasks = $project->tasks()->where('status', 'done')->count();
        $progress = $totalTasks > 0 ? (int) round(($completedTasks / $totalTasks) * 100) : 0;

        $project->update([
            'progress' => $progress,
            'last_activity_at' => now(),
            'status' => $progress === 100 ? 'completed' : $project->status,
        ]);
    }

    public function notifyUpcomingDeadlines(): void
    {
        Task::query()
            ->with('assignees')
            ->whereIn('status', ['todo', 'in_progress', 'review'])
            ->whereDate('due_date', '<=', now()->addDays(2)->toDateString())
            ->get()
            ->each(function (Task $task): void {
                $task->assignees->each(fn (User $user) => $user->notify(new TaskDeadlineReminderNotification($task)));
            });
    }

    public function storeTaskLog(
        Task $task,
        string $action,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?string $description = null
    ): void {
        $task->logs()->create([
            'user_id' => auth()->id(),
            'action' => $action,
            'description' => $description,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'created_at' => now(),
        ]);

        $this->auditLogService->record(
            module: 'tasks',
            event: $action,
            auditable: $task,
            oldValues: $oldValues,
            newValues: $newValues,
            description: $description,
        );
    }
}
