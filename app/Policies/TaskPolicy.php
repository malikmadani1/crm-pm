<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;
use App\Policies\Concerns\ChecksPermissions;

class TaskPolicy
{
    use ChecksPermissions;

    public function viewAny(User $user): bool
    {
        return $this->allows($user, 'tasks.view');
    }

    public function view(User $user, Task $task): bool
    {
        return $this->allows($user, 'tasks.view') && $this->belongsToTask($user, $task);
    }

    public function create(User $user): bool
    {
        return $this->allows($user, 'tasks.create');
    }

    public function update(User $user, Task $task): bool
    {
        return $this->allows($user, 'tasks.update') && $this->belongsToTask($user, $task);
    }

    public function delete(User $user, Task $task): bool
    {
        return $this->allows($user, 'tasks.delete') && $this->belongsToTask($user, $task);
    }

    public function restore(User $user, Task $task): bool
    {
        return $this->delete($user, $task);
    }

    public function forceDelete(User $user, Task $task): bool
    {
        return $this->delete($user, $task);
    }

    public function move(User $user, Task $task): bool
    {
        return $this->allows($user, 'tasks.move') && $this->belongsToTask($user, $task);
    }

    public function assign(User $user, Task $task): bool
    {
        return $this->allows($user, 'tasks.assign')
            && ($user->hasRole('manager') || $task->project?->manager_id === $user->id);
    }

    public function comment(User $user, Task $task): bool
    {
        return $this->allows($user, 'tasks.comment') && $this->belongsToTask($user, $task);
    }

    private function belongsToTask(User $user, Task $task): bool
    {
        return $user->hasRole('manager')
            || $task->project?->manager_id === $user->id
            || $task->created_by === $user->id
            || $task->assignees->contains('id', $user->id);
    }
}
