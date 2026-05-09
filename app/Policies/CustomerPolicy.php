<?php

namespace App\Policies;

use App\Models\Customer;
use App\Models\User;
use App\Policies\Concerns\ChecksPermissions;

class CustomerPolicy
{
    use ChecksPermissions;

    public function viewAny(User $user): bool
    {
        return $this->allows($user, 'customers.view');
    }

    public function view(User $user, Customer $customer): bool
    {
        return $this->allows($user, 'customers.view')
            && ($user->hasRole('manager') || $customer->owner_id === $user->id);
    }

    public function create(User $user): bool
    {
        return $this->allows($user, 'customers.create');
    }

    public function update(User $user, Customer $customer): bool
    {
        return $this->allows($user, 'customers.update')
            && ($user->hasRole('manager') || $customer->owner_id === $user->id);
    }

    public function delete(User $user, Customer $customer): bool
    {
        return $this->allows($user, 'customers.delete')
            && ($user->hasRole('manager') || $customer->owner_id === $user->id);
    }

    public function restore(User $user, Customer $customer): bool
    {
        return $this->delete($user, $customer);
    }

    public function forceDelete(User $user, Customer $customer): bool
    {
        return $this->delete($user, $customer);
    }
}
