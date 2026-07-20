<?php

namespace App\Policies;

use App\Models\BankAccount;
use App\Models\User;

class BankAccountPolicy
{
    public function before(User $user): ?bool
    {
        return $user->hasRole('super_admin') ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return $user->can('bank_accounts.view');
    }

    public function view(User $user, BankAccount $account): bool
    {
        return $user->can('bank_accounts.view') && $user->notary_id === $account->notary_id;
    }

    public function create(User $user): bool
    {
        return $user->can('bank_accounts.create');
    }

    public function update(User $user, BankAccount $account): bool
    {
        return $user->can('bank_accounts.update') && $user->notary_id === $account->notary_id;
    }
}
