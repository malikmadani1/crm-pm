<?php

namespace App\Notifications;

use App\Models\Project;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ProjectAssignedNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly Project $project)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'project_assigned',
            'title' => __('تمت إضافتك إلى مشروع'),
            'message' => __('تمت إضافتك إلى المشروع :project.', ['project' => $this->project->name]),
            'url' => route('projects.show', $this->project),
            'project_id' => $this->project->id,
        ];
    }
}
