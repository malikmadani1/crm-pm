<?php

namespace App\Policies\Concerns;

use App\Models\User;

trait ChecksPermissions
{
    protected function allows(User $user, string $permission): bool
    {
        return $user->hasPermissionTo($permission);
    }
}
