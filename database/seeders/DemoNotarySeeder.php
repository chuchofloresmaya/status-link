<?php

namespace Database\Seeders;

use App\Domain\BankAccounts\Services\BankAccountService;
use App\Domain\NotarialProfiles\Services\NotarialProfileService;
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
        $profile = $notary->notarialProfiles()->firstOrCreate(['name' => 'Notaría Demo Principal'], ['notary_number' => '1', 'notary_name' => 'Lic. Notario Demo', 'notary_title' => 'Notario Público', 'email' => 'demo@status-link.local', 'is_default' => true, 'is_active' => true]);
        app(NotarialProfileService::class)->setDefault($profile);
        $account = $notary->bankAccounts()->firstOrCreate(['account_number' => '0000000000'], ['notarial_profile_id' => null, 'account_type' => 'general', 'bank_name' => 'Banco Demo', 'account_holder' => 'Notaría Demo', 'clabe' => '000000000000000000', 'currency' => 'MXN', 'is_default' => true, 'is_active' => true]);
        app(BankAccountService::class)->setDefault($account);
        $feesAccount = $notary->bankAccounts()->firstOrCreate(['notarial_profile_id' => $profile->id, 'account_type' => 'honorarios'], ['bank_name' => 'Banco Demo', 'account_holder' => 'Lic. Notario Demo', 'account_number' => '1111111111', 'clabe' => '111111111111111111', 'currency' => 'MXN', 'is_default' => true, 'is_active' => true]);
        app(BankAccountService::class)->setDefault($feesAccount);
        $taxAccount = $notary->bankAccounts()->firstOrCreate(['notarial_profile_id' => $profile->id, 'account_type' => 'impuestos'], ['bank_name' => 'Banco Demo', 'account_holder' => 'Lic. Notario Demo', 'account_number' => '2222222222', 'clabe' => '222222222222222222', 'currency' => 'MXN', 'is_default' => true, 'is_active' => true]);
        app(BankAccountService::class)->setDefault($taxAccount);
    }
}
