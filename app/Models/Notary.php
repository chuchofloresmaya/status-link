<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Notary extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug', 'rfc', 'email', 'phone', 'address', 'is_active', 'settings'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'settings' => 'array'];
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function notarialProfiles(): HasMany
    {
        return $this->hasMany(NotarialProfile::class);
    }

    public function activeNotarialProfiles(): HasMany
    {
        return $this->notarialProfiles()->where('is_active', true);
    }

    public function defaultNotarialProfile(): HasOne
    {
        return $this->hasOne(NotarialProfile::class)->where('is_default', true);
    }

    public function bankAccounts(): HasMany
    {
        return $this->hasMany(BankAccount::class);
    }

    public function activeBankAccounts(): HasMany
    {
        return $this->bankAccounts()->where('is_active', true);
    }

    public function defaultBankAccount(): HasOne
    {
        return $this->hasOne(BankAccount::class)->whereNull('notarial_profile_id')->where('is_default', true);
    }

    public function activeSubscription(): HasOne
    {
        return $this->hasOne(Subscription::class)->where('status', 'active')->latestOfMany('starts_at');
    }

    public function activePlan(): ?Plan
    {
        return $this->activeSubscription?->plan;
    }
}
