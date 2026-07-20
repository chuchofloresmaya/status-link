<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BankAccount extends Model
{
    use HasFactory;

    protected $fillable = ['notary_id', 'notarial_profile_id', 'account_type', 'bank_name', 'account_holder', 'account_number', 'clabe', 'card_number', 'currency', 'is_default', 'is_active', 'notes'];

    protected function casts(): array
    {
        return ['is_default' => 'boolean', 'is_active' => 'boolean'];
    }

    public function notary(): BelongsTo
    {
        return $this->belongsTo(Notary::class);
    }

    public function notarialProfile(): BelongsTo
    {
        return $this->belongsTo(NotarialProfile::class);
    }
}
