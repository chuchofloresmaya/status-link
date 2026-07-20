<?php

namespace App\Policies;

use App\Models\NotarialProfile;
use App\Models\User;

class NotarialProfilePolicy
{
    public function before(User $user): ?bool
    {
        return $user->hasRole('super_admin') ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return $user->can('notarial_profiles.view');
    }

    public function view(User $user, NotarialProfile $profile): bool
    {
        return $user->can('notarial_profiles.view') && $user->notary_id === $profile->notary_id;
    }

    public function create(User $user): bool
    {
        return $user->can('notarial_profiles.create');
    }

    public function update(User $user, NotarialProfile $profile): bool
    {
        return $user->can('notarial_profiles.update') && $user->notary_id === $profile->notary_id;
    }
}
