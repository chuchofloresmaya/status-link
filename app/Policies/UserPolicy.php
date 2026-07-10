<?php

namespace App\Policies;

use App\Domain\Users\Services\UserTenantService;
use App\Models\User;

class UserPolicy
{
    public function __construct(private UserTenantService $tenants) {}

    public function before(User $user): ?bool
    {
        return $user->hasRole('super_admin') ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return $user->can('users.view');
    }

    public function view(User $user, User $target): bool
    {
        return $user->can('users.view') && $this->tenants->canManageUser($user, $target);
    }

    public function create(User $user): bool
    {
        return $user->can('users.create');
    }

    public function update(User $user, User $target): bool
    {
        return $user->can('users.update') && $this->tenants->canManageUser($user, $target);
    }

    public function delete(User $user, User $target): bool
    {
        return $user->can('users.delete') && $this->tenants->canManageUser($user, $target);
    }
}
