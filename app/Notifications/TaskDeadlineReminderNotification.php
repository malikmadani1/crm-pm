<?php

namespace App\Notifications;

use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class TaskDeadlineReminderNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly Task $task)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'task_deadline',
            'title' => __('Task deadline approaching'),
            'message' => __('The deadline for :task is :date.', ['task' => $this->task->title, 'date' => $this->task->due_date?->format('Y-m-d')]),
            'url' => route('tasks.show', $this->task),
            'task_id' => $this->task->id,
        ];
    }
}
