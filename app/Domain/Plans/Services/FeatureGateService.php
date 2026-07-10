<?php

namespace App\Domain\Plans\Services;

use App\Models\Notary;
use Illuminate\Auth\Access\AuthorizationException;

class FeatureGateService
{
    public function allows(Notary $notary, string $feature): bool
    {
        return (bool) ($notary->activePlan()?->features[$feature] ?? false);
    }

    public function limit(Notary $notary, string $limitKey): mixed
    {
        return $notary->activePlan()?->limits[$limitKey] ?? null;
    }

    public function assertAllowed(Notary $notary, string $feature): void
    {
        if (! $this->allows($notary, $feature)) {
            throw new AuthorizationException("The feature [{$feature}] is not enabled.");
        }
    }
}
