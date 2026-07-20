<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class NotarialProfile extends Model
{
    use HasFactory;

    protected $fillable = ['notary_id', 'name', 'notary_number', 'notary_name', 'notary_title', 'rfc', 'email', 'phone', 'address', 'logo_path', 'is_default', 'is_active', 'settings'];

    protected function casts(): array
    {
        return ['settings' => 'array', 'is_default' => 'boolean', 'is_active' => 'boolean'];
    }

    public function notary(): BelongsTo
    {
        return $this->belongsTo(Notary::class);
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
        return $this->hasOne(BankAccount::class)->where('is_default', true);
    }
}
