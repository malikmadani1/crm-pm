<?php

namespace App\Policies;

use App\Models\Deal;
use App\Models\User;
use App\Policies\Concerns\ChecksPermissions;

class DealPolicy
{
    use ChecksPermissions;

    public function viewAny(User $user): bool
    {
        return $this->allows($user, 'deals.view');
    }

    public function view(User $user, Deal $deal): bool
    {
        return $this->allows($user, 'deals.view')
            && ($user->hasRole('manager') || $deal->owner_id === $user->id);
    }

    public function create(User $user): bool
    {
        return $this->allows($user, 'deals.create');
    }

    public function update(User $user, Deal $deal): bool
    {
        return $this->allows($user, 'deals.update')
            && ($user->hasRole('manager') || $deal->owner_id === $user->id);
    }

    public function delete(User $user, Deal $deal): bool
    {
        return $this->allows($user, 'deals.delete')
            && ($user->hasRole('manager') || $deal->owner_id === $user->id);
    }

    public function restore(User $user, Deal $deal): bool
    {
        return $this->delete($user, $deal);
    }

    public function forceDelete(User $user, Deal $deal): bool
    {
        return $this->delete($user, $deal);
    }
}
