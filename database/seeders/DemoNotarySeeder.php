<?php

namespace Database\Seeders;

use App\Domain\Subscriptions\Services\SubscriptionService;
use App\Models\Notary;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoNotarySeeder extends Seeder
{
    public function run(): void
    {
        $notary = Notary::updateOrCreate(['slug' => 'notaria-demo'], ['name' => 'Notaría Demo', 'email' => 'demo@status-link.local', 'is_active' => true]);
        $user = User::updateOrCreate(['email' => 'notaria@status-link.local'], ['name' => 'Admin Notaría Demo', 'password' => 'password', 'notary_id' => $notary->id, 'is_active' => true]);
        $user->syncRoles('notary_admin');
        if (! $notary->activeSubscription()->exists()) {
            app(SubscriptionService::class)->activateSubscription($notary, Plan::where('slug', 'premium')->firstOrFail(), ['ends_at' => now()->addMonth()]);
        }
    }
}
