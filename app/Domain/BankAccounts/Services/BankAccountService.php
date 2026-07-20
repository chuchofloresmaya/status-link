<?php

namespace App\Domain\BankAccounts\Services;

use App\Models\BankAccount;
use App\Models\Notary;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BankAccountService
{
    public function setDefault(BankAccount $account): void
    {
        DB::transaction(function () use ($account) {
            $accountType = $account->account_type ?? 'general';
            $query = $this->scope($account->notary, $account->notarial_profile_id, $accountType)->whereKeyNot($account->id);
            $query->update(['is_default' => false]);
            $account->update(['account_type' => $accountType, 'is_default' => true, 'is_active' => true]);
        });
    }

    public function toggleActive(BankAccount $account): void
    {
        DB::transaction(function () use ($account) {
            $account->update(['is_active' => ! $account->is_active]);
            if (! $account->is_active && $account->is_default) {
                $account->update(['is_default' => false]);
                $query = $this->scope($account->notary, $account->notarial_profile_id, $account->account_type ?? 'general')->where('is_active', true)->whereKeyNot($account->id);
                if ($replacement = $query->first()) {
                    $this->setDefault($replacement);
                }
            }
        });
    }

    public function storeForNotary(Notary $notary, array $data): BankAccount
    {
        return DB::transaction(function () use ($notary, $data) {
            $this->assertProfile($notary, $data['notarial_profile_id'] ?? null);
            $data['account_type'] = $data['account_type'] ?? 'general';
            $scope = $this->scope($notary, $data['notarial_profile_id'] ?? null, $data['account_type']);
            $makeDefault = ($data['is_default'] ?? false) || (($data['is_active'] ?? true) && ! $scope->where('is_default', true)->exists());
            $account = $notary->bankAccounts()->create($data + ['is_default' => $makeDefault]);
            if ($makeDefault) {
                $this->setDefault($account);
            }

            return $account;
        });
    }

    public function updateBankAccount(BankAccount $account, array $data): BankAccount
    {
        return DB::transaction(function () use ($account, $data) {
            $this->assertProfile($account->notary, $data['notarial_profile_id'] ?? null);
            $oldProfileId = $account->notarial_profile_id;
            $oldType = $account->account_type ?? 'general';
            $wasDefault = $account->is_default;
            $account->update($data);
            if ($data['is_default'] ?? false) {
                $this->setDefault($account);
            }
            if ($wasDefault && ($oldProfileId !== $account->notarial_profile_id || $oldType !== $account->account_type)) {
                $replacement = $this->scope($account->notary, $oldProfileId, $oldType)->where('is_active', true)->whereKeyNot($account->id)->first();
                if ($replacement) {
                    $this->setDefault($replacement);
                }
            }

            return $account->refresh();
        });
    }

    private function assertProfile(Notary $notary, ?int $profileId): void
    {
        if ($profileId && ! $notary->notarialProfiles()->whereKey($profileId)->exists()) {
            throw ValidationException::withMessages(['notarial_profile_id' => 'El perfil notarial no pertenece a esta notaría.']);
        }
    }

    private function scope(Notary $notary, ?int $profileId, ?string $accountType)
    {
        $query = $notary->bankAccounts()->where('account_type', $accountType ?? 'general');
        $profileId ? $query->where('notarial_profile_id', $profileId) : $query->whereNull('notarial_profile_id');

        return $query;
    }
}
