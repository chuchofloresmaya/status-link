<?php

namespace App\Domain\Users\Services;

use App\Models\Notary;
use App\Models\User;

class UserTenantService
{
    public function canManageUser(User $actor, User $target): bool
    {
        if ($actor->hasRole('super_admin')) {
            return true;
        }

        return $actor->hasRole('notary_admin')
            && $actor->notary_id !== null
            && $actor->notary_id === $target->notary_id;
    }

    public function canCreateUserForNotary(User $actor, Notary $notary): bool
    {
        return $actor->hasRole('super_admin')
            || ($actor->hasRole('notary_admin') && $actor->notary_id === $notary->id);
    }
}
