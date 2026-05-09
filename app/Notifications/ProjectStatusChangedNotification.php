<?php

namespace App\Notifications;

use App\Models\Project;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ProjectStatusChangedNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly Project $project, private readonly string $oldStatus, private readonly string $newStatus)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'project_status_changed',
            'title' => __('Project status updated'),
            'message' => __('Project :project moved from :old to :new.', [
                'project' => $this->project->name,
                'old' => __(str($this->oldStatus)->replace('_', ' ')->title()->toString()),
                'new' => __(str($this->newStatus)->replace('_', ' ')->title()->toString()),
            ]),
            'url' => route('projects.show', $this->project),
            'project_id' => $this->project->id,
        ];
    }
}
