<?php

namespace App\Domain\Subscriptions\Services;

use App\Models\Notary;
use App\Models\Plan;
use App\Models\Subscription;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class SubscriptionService
{
    public function activateSubscription(Notary $notary, Plan $plan, array $data = []): Subscription
    {
        return DB::transaction(function () use ($notary, $plan, $data) {
            Subscription::query()->where('notary_id', $notary->id)->where('status', 'active')
                ->lockForUpdate()->update(['status' => 'cancelled', 'cancelled_at' => now()]);

            return Subscription::create(array_merge(
                Arr::only($data, ['starts_at', 'ends_at', 'trial_ends_at', 'metadata']),
                ['notary_id' => $notary->id, 'plan_id' => $plan->id, 'status' => 'active', 'starts_at' => $data['starts_at'] ?? now()]
            ));
        });
    }

    public function cancelSubscription(Subscription $subscription): void
    {
        $subscription->update(['status' => 'cancelled', 'cancelled_at' => now()]);
    }

    public function getActiveSubscription(Notary $notary): ?Subscription
    {
        return $notary->activeSubscription()->with('plan')->first();
    }
}
