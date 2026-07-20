<?php

namespace Tests\Feature;

use App\Domain\BankAccounts\Services\BankAccountService;
use App\Domain\NotarialProfiles\Services\NotarialProfileService;
use App\Models\BankAccount;
use App\Models\NotarialProfile;
use App\Models\Notary;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class NotarialConfigurationTest extends TestCase
{
    use RefreshDatabase;

    private User $super;

    private User $admin;

    private Notary $notary;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        $this->super = User::whereEmail('admin@status-link.local')->firstOrFail();
        $this->admin = User::whereEmail('notaria@status-link.local')->firstOrFail();
        $this->notary = $this->admin->notary;
    }

    public function test_models_create_profiles_and_accounts_with_same_tenant_relationships(): void
    {
        $profile = $this->notary->notarialProfiles()->create(['name' => 'Second Profile']);
        $account = $this->notary->bankAccounts()->create(['notarial_profile_id' => $profile->id, 'bank_name' => 'Bank', 'account_holder' => 'Holder']);
        $this->assertTrue($profile->notary->is($this->notary));
        $this->assertTrue($account->notary->is($this->notary));
        $this->assertTrue($account->notarialProfile->is($profile));
    }

    public function test_bank_account_service_rejects_profile_from_another_notary(): void
    {
        $other = Notary::create(['name' => 'Other', 'slug' => 'other-config']);
        $profile = $other->notarialProfiles()->create(['name' => 'Foreign Profile']);
        $this->expectException(ValidationException::class);
        app(BankAccountService::class)->storeForNotary($this->notary, ['notarial_profile_id' => $profile->id, 'bank_name' => 'Bank', 'account_holder' => 'Holder']);
    }

    public function test_setting_profile_default_clears_previous_default(): void
    {
        $first = $this->notary->defaultNotarialProfile;
        $second = app(NotarialProfileService::class)->storeForNotary($this->notary, ['name' => 'Second', 'is_active' => true]);
        app(NotarialProfileService::class)->setDefault($second);
        $this->assertFalse($first->fresh()->is_default);
        $this->assertTrue($second->fresh()->is_default);
        $this->assertSame(1, $this->notary->notarialProfiles()->where('is_default', true)->count());
    }

    public function test_general_and_profile_bank_defaults_are_scoped_independently(): void
    {
        $service = app(BankAccountService::class);
        $general = $this->notary->defaultBankAccount;
        $secondGeneral = $service->storeForNotary($this->notary, ['bank_name' => 'General Two', 'account_holder' => 'Holder', 'is_default' => true, 'is_active' => true]);
        $profile = $this->notary->defaultNotarialProfile;
        $profileOne = $service->storeForNotary($this->notary, ['notarial_profile_id' => $profile->id, 'bank_name' => 'Profile One', 'account_holder' => 'Holder', 'is_active' => true]);
        $profileTwo = $service->storeForNotary($this->notary, ['notarial_profile_id' => $profile->id, 'bank_name' => 'Profile Two', 'account_holder' => 'Holder', 'is_default' => true, 'is_active' => true]);
        $this->assertFalse($general->fresh()->is_default);
        $this->assertTrue($secondGeneral->fresh()->is_default);
        $this->assertFalse($profileOne->fresh()->is_default);
        $this->assertTrue($profileTwo->fresh()->is_default);
        $this->assertSame(1, $this->notary->bankAccounts()->whereNull('notarial_profile_id')->where('is_default', true)->count());
        $this->assertSame(1, $profile->bankAccounts()->where('account_type', 'general')->where('is_default', true)->count());
        $this->assertSame(3, $profile->bankAccounts()->where('is_default', true)->count());
    }

    public function test_deactivating_defaults_assigns_active_replacements(): void
    {
        $profileService = app(NotarialProfileService::class);
        $oldProfile = $this->notary->defaultNotarialProfile;
        $replacement = $profileService->storeForNotary($this->notary, ['name' => 'Replacement', 'is_active' => true]);
        $profileService->toggleActive($oldProfile);
        $this->assertTrue($replacement->fresh()->is_default);
        $accountService = app(BankAccountService::class);
        $oldAccount = $this->notary->defaultBankAccount;
        $newAccount = $accountService->storeForNotary($this->notary, ['bank_name' => 'Replacement Bank', 'account_holder' => 'Holder', 'is_active' => true]);
        $accountService->toggleActive($oldAccount);
        $this->assertTrue($newAccount->fresh()->is_default);
    }

    public function test_super_admin_can_view_any_notary_profiles_and_accounts(): void
    {
        $this->actingAs($this->super)->get("/admin/notaries/{$this->notary->id}/profiles")->assertOk()->assertSee('Notaría Demo Principal');
        $this->get("/admin/notaries/{$this->notary->id}/bank-accounts")->assertOk()->assertSee('Banco Demo');
    }

    public function test_notary_admin_views_only_own_configuration(): void
    {
        $this->actingAs($this->admin)->get('/app/notarial-profiles')->assertOk()->assertSee('Notaría Demo Principal');
        $this->get('/app/bank-accounts')->assertOk()->assertSee('Banco Demo');
        $other = Notary::create(['name' => 'Foreign', 'slug' => 'foreign-config']);
        $foreignProfile = $other->notarialProfiles()->create(['name' => 'Secret Profile']);
        $foreignAccount = $other->bankAccounts()->create(['bank_name' => 'Secret Bank', 'account_holder' => 'Secret']);
        $this->get("/app/notarial-profiles/{$foreignProfile->id}/edit")->assertForbidden();
        $this->get("/app/bank-accounts/{$foreignAccount->id}/edit")->assertForbidden();
    }

    public function test_notary_user_cannot_access_profiles_or_accounts(): void
    {
        $user = User::factory()->create(['notary_id' => $this->notary->id, 'is_active' => true]);
        $user->assignRole('notary_user');
        $this->actingAs($user)->get('/app/notarial-profiles')->assertForbidden();
        $this->get('/app/bank-accounts')->assertForbidden();
    }

    public function test_notary_admin_creates_and_updates_profile_only_for_own_notary(): void
    {
        $this->actingAs($this->admin)->post('/app/notarial-profiles', ['name' => 'Created Profile', 'is_active' => 1, 'notary_id' => 999])->assertRedirect('/app/notarial-profiles');
        $profile = NotarialProfile::where('name', 'Created Profile')->firstOrFail();
        $this->assertSame($this->notary->id, $profile->notary_id);
        $this->put("/app/notarial-profiles/{$profile->id}", ['name' => 'Updated Profile', 'is_active' => 1])->assertRedirect('/app/notarial-profiles');
        $this->assertDatabaseHas('notarial_profiles', ['id' => $profile->id, 'name' => 'Updated Profile']);
    }

    public function test_notary_admin_cannot_edit_or_toggle_foreign_profile(): void
    {
        $other = Notary::create(['name' => 'Other', 'slug' => 'other-profile-actions']);
        $profile = $other->notarialProfiles()->create(['name' => 'Foreign', 'is_active' => true]);
        $this->actingAs($this->admin)->put("/app/notarial-profiles/{$profile->id}", ['name' => 'Hacked', 'is_active' => 1])->assertForbidden();
        $this->patch("/app/notarial-profiles/{$profile->id}/toggle-active")->assertForbidden();
        $this->patch("/app/notarial-profiles/{$profile->id}/set-default")->assertForbidden();
    }

    public function test_notary_admin_creates_account_only_for_own_notary_and_profile(): void
    {
        $profile = $this->notary->defaultNotarialProfile;
        $this->actingAs($this->admin)->post('/app/bank-accounts', ['notarial_profile_id' => $profile->id, 'account_type' => 'general', 'bank_name' => 'New Bank', 'account_holder' => 'Holder', 'currency' => 'MXN', 'is_active' => 1, 'notary_id' => 999])->assertRedirect('/app/bank-accounts');
        $account = BankAccount::where('bank_name', 'New Bank')->firstOrFail();
        $this->assertSame($this->notary->id, $account->notary_id);
        $other = Notary::create(['name' => 'Other', 'slug' => 'other-account-create']);
        $foreign = $other->notarialProfiles()->create(['name' => 'Foreign']);
        $this->post('/app/bank-accounts', ['notarial_profile_id' => $foreign->id, 'bank_name' => 'Bad', 'account_holder' => 'Holder', 'currency' => 'MXN', 'is_active' => 1])->assertSessionHasErrors('notarial_profile_id');
    }

    public function test_notary_admin_cannot_edit_toggle_or_default_foreign_account(): void
    {
        $other = Notary::create(['name' => 'Other', 'slug' => 'other-account-actions']);
        $account = $other->bankAccounts()->create(['bank_name' => 'Foreign', 'account_holder' => 'Holder', 'is_active' => true]);
        $this->actingAs($this->admin)->put("/app/bank-accounts/{$account->id}", ['bank_name' => 'Hacked', 'account_holder' => 'X', 'currency' => 'MXN', 'is_active' => 1])->assertForbidden();
        $this->patch("/app/bank-accounts/{$account->id}/toggle-active")->assertForbidden();
        $this->patch("/app/bank-accounts/{$account->id}/set-default")->assertForbidden();
    }

    public function test_valid_logo_is_stored_on_public_disk(): void
    {
        Storage::fake('public');
        $logo = UploadedFile::fake()->image('logo.png', 100, 100);
        $this->actingAs($this->admin)->post('/app/notarial-profiles', ['name' => 'Logo Profile', 'logo' => $logo, 'is_active' => 1])->assertRedirect('/app/notarial-profiles');
        $profile = NotarialProfile::where('name', 'Logo Profile')->firstOrFail();
        $this->assertNotNull($profile->logo_path);
        Storage::disk('public')->assertExists($profile->logo_path);
    }

    public function test_profile_has_independent_default_accounts_for_fees_and_taxes(): void
    {
        $profile = $this->notary->defaultNotarialProfile;
        $fees = $profile->bankAccounts()->where('account_type', 'honorarios')->firstOrFail();
        $taxes = $profile->bankAccounts()->where('account_type', 'impuestos')->firstOrFail();
        $this->assertTrue($fees->is_default);
        $this->assertTrue($taxes->is_default);
        $this->assertGreaterThanOrEqual(2, $profile->bankAccounts()->count());
    }

    public function test_changing_default_for_one_type_does_not_affect_another_type(): void
    {
        $profile = $this->notary->defaultNotarialProfile;
        $oldFees = $profile->bankAccounts()->where('account_type', 'honorarios')->firstOrFail();
        $taxes = $profile->bankAccounts()->where('account_type', 'impuestos')->firstOrFail();
        $newFees = app(BankAccountService::class)->storeForNotary($this->notary, [
            'notarial_profile_id' => $profile->id,
            'account_type' => 'honorarios',
            'bank_name' => 'Fees Bank Two',
            'account_holder' => 'Holder',
            'is_default' => true,
            'is_active' => true,
        ]);
        $this->assertFalse($oldFees->fresh()->is_default);
        $this->assertTrue($newFees->fresh()->is_default);
        $this->assertTrue($taxes->fresh()->is_default);
    }

    public function test_deactivating_default_assigns_replacement_of_same_type_only(): void
    {
        $profile = $this->notary->defaultNotarialProfile;
        $service = app(BankAccountService::class);
        $oldFees = $profile->bankAccounts()->where('account_type', 'honorarios')->firstOrFail();
        $taxes = $profile->bankAccounts()->where('account_type', 'impuestos')->firstOrFail();
        $replacement = $service->storeForNotary($this->notary, ['notarial_profile_id' => $profile->id, 'account_type' => 'honorarios', 'bank_name' => 'Replacement Fees', 'account_holder' => 'Holder', 'is_active' => true]);
        $service->toggleActive($oldFees);
        $this->assertTrue($replacement->fresh()->is_default);
        $this->assertTrue($taxes->fresh()->is_default);
    }

    public function test_custom_account_type_can_be_created_from_form(): void
    {
        $profile = $this->notary->defaultNotarialProfile;
        $this->actingAs($this->admin)->post('/app/bank-accounts', [
            'notarial_profile_id' => $profile->id,
            'account_type' => 'other',
            'custom_account_type' => 'Gastos especiales',
            'bank_name' => 'Custom Bank',
            'account_holder' => 'Holder',
            'currency' => 'MXN',
            'is_active' => 1,
        ])->assertRedirect('/app/bank-accounts');
        $this->assertDatabaseHas('bank_accounts', ['notary_id' => $this->notary->id, 'account_type' => 'gastos_especiales', 'bank_name' => 'Custom Bank']);
    }

    public function test_future_operational_routes_do_not_exist(): void
    {
        foreach (['/quotes', '/quotations', '/volantes', '/rpp', '/qr', '/isr', '/declaranot', '/whatsapp'] as $path) {
            $this->get($path)->assertNotFound();
        }
    }
}
