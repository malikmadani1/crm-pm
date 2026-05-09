<?php

namespace App\Policies;

use App\Models\Lead;
use App\Models\User;
use App\Policies\Concerns\ChecksPermissions;

class LeadPolicy
{
    use ChecksPermissions;

    public function viewAny(User $user): bool
    {
        return $this->allows($user, 'leads.view');
    }

    public function view(User $user, Lead $lead): bool
    {
        return $this->allows($user, 'leads.view')
            && ($user->hasRole('manager') || $lead->owner_id === $user->id);
    }

    public function create(User $user): bool
    {
        return $this->allows($user, 'leads.create');
    }

    public function update(User $user, Lead $lead): bool
    {
        return $this->allows($user, 'leads.update')
            && ($user->hasRole('manager') || $lead->owner_id === $user->id);
    }

    public function delete(User $user, Lead $lead): bool
    {
        return $this->allows($user, 'leads.delete')
            && ($user->hasRole('manager') || $lead->owner_id === $user->id);
    }

    public function restore(User $user, Lead $lead): bool
    {
        return $this->delete($user, $lead);
    }

    public function forceDelete(User $user, Lead $lead): bool
    {
        return $this->delete($user, $lead);
    }
}
