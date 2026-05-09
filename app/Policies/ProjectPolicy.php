<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;
use App\Policies\Concerns\ChecksPermissions;

class ProjectPolicy
{
    use ChecksPermissions;

    public function viewAny(User $user): bool
    {
        return $this->allows($user, 'projects.view');
    }

    public function view(User $user, Project $project): bool
    {
        return $this->allows($user, 'projects.view')
            && (
                $user->hasRole('manager')
                || $project->manager_id === $user->id
                || $project->members->contains('id', $user->id)
            );
    }

    public function create(User $user): bool
    {
        return $this->allows($user, 'projects.create');
    }

    public function update(User $user, Project $project): bool
    {
        return $this->allows($user, 'projects.update')
            && ($user->hasRole('manager') || $project->manager_id === $user->id);
    }

    public function delete(User $user, Project $project): bool
    {
        return $this->allows($user, 'projects.delete')
            && ($user->hasRole('manager') || $project->manager_id === $user->id);
    }

    public function restore(User $user, Project $project): bool
    {
        return $this->delete($user, $project);
    }

    public function forceDelete(User $user, Project $project): bool
    {
        return $this->delete($user, $project);
    }
}
