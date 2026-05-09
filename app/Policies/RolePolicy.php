<?php

namespace App\Policies;

use App\Models\Role;
use App\Models\User;
use App\Policies\Concerns\ChecksPermissions;

class RolePolicy
{
    use ChecksPermissions;

    public function viewAny(User $user): bool
    {
        return $this->allows($user, 'roles.view');
    }

    public function view(User $user, Role $role): bool
    {
        return $this->allows($user, 'roles.view');
    }

    public function create(User $user): bool
    {
        return $this->allows($user, 'roles.create');
    }

    public function update(User $user, Role $role): bool
    {
        return ! $role->is_system && $this->allows($user, 'roles.update');
    }

    public function delete(User $user, Role $role): bool
    {
        return ! $role->is_system && $this->allows($user, 'roles.delete');
    }

    public function restore(User $user, Role $role): bool
    {
        return $this->delete($user, $role);
    }

    public function forceDelete(User $user, Role $role): bool
    {
        return $this->delete($user, $role);
    }
}
