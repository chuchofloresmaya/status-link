<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subscription extends Model
{
    use HasFactory;

    protected $fillable = ['notary_id', 'plan_id', 'status', 'starts_at', 'ends_at', 'trial_ends_at', 'cancelled_at', 'metadata'];

    protected function casts(): array
    {
        return ['starts_at' => 'datetime', 'ends_at' => 'datetime', 'trial_ends_at' => 'datetime', 'cancelled_at' => 'datetime', 'metadata' => 'array'];
    }

    public function notary(): BelongsTo
    {
        return $this->belongsTo(Notary::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }
}
