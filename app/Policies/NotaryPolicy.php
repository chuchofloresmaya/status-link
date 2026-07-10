<?php

namespace App\Policies;

use App\Models\Notary;
use App\Models\User;

class NotaryPolicy
{
    public function before(User $user): ?bool
    {
        return $user->hasRole('super_admin') ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return false;
    }

    public function view(User $user, Notary $notary): bool
    {
        return $user->notary_id === $notary->id;
    }

    public function update(User $user, Notary $notary): bool
    {
        return $user->can('settings.manage') && $user->notary_id === $notary->id;
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function delete(User $user, Notary $notary): bool
    {
        return false;
    }
}
