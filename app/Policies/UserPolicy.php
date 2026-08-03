<?php

namespace App\Policies;

use App\Models\User;
use App\Policies\Concerns\ChecksPermissions;

class UserPolicy
{
    use ChecksPermissions;

    public function viewAny(User $user): bool
    {
        return $this->allows($user, 'users.view');
    }

    public function view(User $user, User $model): bool
    {
        return $user->id === $model->id || $this->allows($user, 'users.view');
    }

    public function create(User $user): bool
    {
        return $this->allows($user, 'users.create');
    }

    public function update(User $user, User $model): bool
    {
        return $user->id === $model->id || $this->allows($user, 'users.update');
    }

    public function delete(User $user, User $model): bool
    {
        if ($model->isProtectedSuperAdmin() || $user->is($model)) {
            return false;
        }

        return $this->allows($user, 'users.delete');
    }

    public function restore(User $user, User $model): bool
    {
        return $this->delete($user, $model);
    }

    public function forceDelete(User $user, User $model): bool
    {
        return $this->delete($user, $model);
    }
}
