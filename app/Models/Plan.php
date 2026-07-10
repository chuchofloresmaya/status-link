<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'slug', 'description', 'category_label', 'badge_label', 'secondary_label',
        'price', 'monthly_price', 'promotional_price', 'promotional_months',
        'promotional_equivalent_monthly_price', 'billing_period', 'requires_quote',
        'display_order', 'marketing_features', 'features', 'limits', 'is_highlighted', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'monthly_price' => 'decimal:2',
            'promotional_price' => 'decimal:2',
            'promotional_equivalent_monthly_price' => 'decimal:2',
            'marketing_features' => 'array',
            'features' => 'array',
            'limits' => 'array',
            'requires_quote' => 'boolean',
            'is_highlighted' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }
}
