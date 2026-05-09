<?php

namespace App\Notifications;

use App\Models\Task;
use App\Models\TaskComment;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class TaskCommentAddedNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly Task $task,
        private readonly TaskComment $comment,
        private readonly User $author
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'task_comment_added',
            'title' => __('New task comment'),
            'message' => __(':author commented on :task.', ['author' => $this->author->name, 'task' => $this->task->title]),
            'url' => route('tasks.show', $this->task) . '#comment-' . $this->comment->id,
            'task_id' => $this->task->id,
            'comment_id' => $this->comment->id,
        ];
    }
}
