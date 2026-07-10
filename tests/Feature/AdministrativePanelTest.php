<?php

namespace Tests\Feature;

use App\Models\Notary;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdministrativePanelTest extends TestCase
{
    use RefreshDatabase;

    private User $super;

    private User $notaryAdmin;

    private Notary $notary;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        $this->super = User::whereEmail('admin@status-link.local')->firstOrFail();
        $this->notaryAdmin = User::whereEmail('notaria@status-link.local')->firstOrFail();
        $this->notary = $this->notaryAdmin->notary;
    }

    public function test_super_admin_can_manage_notaries(): void
    {
        $this->actingAs($this->super)->get('/admin/notaries')->assertOk()->assertSee('Notaría Demo');
        $this->post('/admin/notaries', ['name' => 'Nueva Notaría', 'slug' => 'nueva-notaria', 'email' => 'legal@example.test', 'is_active' => 1, 'settings' => ['users_can_view_all_records' => 1]])->assertRedirect('/admin/notaries');
        $notary = Notary::whereSlug('nueva-notaria')->firstOrFail();
        $this->put("/admin/notaries/{$notary->id}", ['name' => 'Notaría Editada', 'slug' => 'nueva-notaria', 'is_active' => 1])->assertRedirect('/admin/notaries');
        $this->patch("/admin/notaries/{$notary->id}/toggle-active")->assertRedirect();
        $this->assertDatabaseHas('notaries', ['id' => $notary->id, 'name' => 'Notaría Editada', 'is_active' => false]);
    }

    public function test_super_admin_can_manage_plans(): void
    {
        $this->actingAs($this->super)->get('/admin/plans')->assertOk()->assertSee('Premium');
        $payload = ['name' => 'Enterprise', 'slug' => 'enterprise', 'monthly_price' => 1999, 'billing_period' => 'monthly', 'display_order' => 10, 'marketing_features' => '["Soporte"]', 'features' => '{"support":true}', 'limits' => '{"users":100}', 'is_active' => 1];
        $this->post('/admin/plans', $payload)->assertRedirect('/admin/plans');
        $plan = Plan::whereSlug('enterprise')->firstOrFail();
        $this->put("/admin/plans/{$plan->id}", array_merge($payload, ['name' => 'Enterprise Plus']))->assertRedirect('/admin/plans');
        $this->assertDatabaseHas('plans', ['id' => $plan->id, 'name' => 'Enterprise Plus']);
    }

    public function test_super_admin_can_create_subscription_and_manual_payment(): void
    {
        $plan = Plan::whereSlug('basic')->firstOrFail();
        $this->actingAs($this->super)->post('/admin/subscriptions', ['notary_id' => $this->notary->id, 'plan_id' => $plan->id, 'status' => 'active', 'starts_at' => now()->format('Y-m-d H:i:s')])->assertRedirect('/admin/subscriptions');
        $subscription = Subscription::where('notary_id', $this->notary->id)->where('plan_id', $plan->id)->firstOrFail();
        $this->post('/admin/payments', ['notary_id' => $this->notary->id, 'subscription_id' => $subscription->id, 'amount' => 499, 'currency' => 'MXN', 'payment_method' => 'transfer', 'reference' => 'TEST-1', 'status' => 'paid', 'paid_at' => now()->format('Y-m-d H:i:s')])->assertRedirect('/admin/payments');
        $this->assertDatabaseHas('payments', ['subscription_id' => $subscription->id, 'reference' => 'TEST-1', 'created_by' => $this->super->id]);
    }

    public function test_super_admin_can_create_global_and_notary_admin_users(): void
    {
        $this->actingAs($this->super)->post('/admin/users', ['name' => 'Global Admin', 'email' => 'global@example.test', 'password' => 'password', 'role' => 'super_admin', 'notary_id' => $this->notary->id, 'is_active' => 1])->assertRedirect('/admin/users');
        $global = User::whereEmail('global@example.test')->firstOrFail();
        $this->assertNull($global->notary_id);
        $this->assertTrue($global->hasRole('super_admin'));
        $this->post('/admin/users', ['name' => 'Admin Two', 'email' => 'admin2@example.test', 'password' => 'password', 'role' => 'notary_admin', 'notary_id' => $this->notary->id, 'is_active' => 1])->assertRedirect('/admin/users');
        $this->assertTrue(User::whereEmail('admin2@example.test')->firstOrFail()->hasRole('notary_admin'));
    }

    public function test_notary_admin_is_blocked_from_admin_and_sees_only_own_users(): void
    {
        $other = Notary::create(['name' => 'Other', 'slug' => 'other-panel']);
        $foreign = User::factory()->create(['notary_id' => $other->id]);
        $foreign->assignRole('notary_user');
        $this->actingAs($this->notaryAdmin)->get('/admin/notaries')->assertForbidden();
        $this->get('/app/users')->assertOk()->assertSee($this->notaryAdmin->email)->assertDontSee($foreign->email);
    }

    public function test_notary_admin_creates_only_notary_user_in_own_notary(): void
    {
        $this->actingAs($this->notaryAdmin)->post('/app/users', ['name' => 'Local User', 'email' => 'local@example.test', 'password' => 'password', 'role' => 'super_admin', 'notary_id' => 999, 'is_active' => 1])->assertRedirect('/app/users');
        $user = User::whereEmail('local@example.test')->firstOrFail();
        $this->assertSame($this->notary->id, $user->notary_id);
        $this->assertTrue($user->hasRole('notary_user'));
        $this->assertFalse($user->hasRole('super_admin'));
    }

    public function test_notary_admin_can_toggle_local_but_not_foreign_user(): void
    {
        $local = User::factory()->create(['notary_id' => $this->notary->id, 'is_active' => true]);
        $local->assignRole('notary_user');
        $other = Notary::create(['name' => 'Foreign', 'slug' => 'foreign']);
        $foreign = User::factory()->create(['notary_id' => $other->id, 'is_active' => true]);
        $foreign->assignRole('notary_user');
        $this->actingAs($this->notaryAdmin)->patch("/app/users/{$local->id}/toggle-active")->assertRedirect();
        $this->patch("/app/users/{$foreign->id}/toggle-active")->assertForbidden();
        $this->assertFalse($local->fresh()->is_active);
        $this->assertTrue($foreign->fresh()->is_active);
    }

    public function test_notary_admin_updates_only_own_settings(): void
    {
        $other = Notary::create(['name' => 'Untouched', 'slug' => 'untouched', 'settings' => ['users_can_view_all_records' => false]]);
        $this->actingAs($this->notaryAdmin)->put('/app/settings', ['users_can_view_all_records' => 1, 'notary_id' => $other->id, 'name' => 'Forged'])->assertRedirect();
        $this->assertTrue($this->notary->fresh()->settings['users_can_view_all_records']);
        $this->assertArrayNotHasKey('name', $this->notary->fresh()->settings);
        $this->assertFalse($other->fresh()->settings['users_can_view_all_records']);
    }

    public function test_notary_admin_can_create_users_when_plan_has_no_user_limit(): void
    {
        $this->assertNull($this->notary->activePlan()->limits['users']);
        $this->actingAs($this->notaryAdmin)->post('/app/users', ['name' => 'Unlimited User', 'email' => 'unlimited@example.test', 'password' => 'password', 'is_active' => 1])->assertSessionDoesntHaveErrors('users');
        $this->assertDatabaseHas('users', ['email' => 'unlimited@example.test', 'notary_id' => $this->notary->id]);
    }

    public function test_plan_crud_renders_new_commercial_fields(): void
    {
        $this->actingAs($this->super)->get('/admin/plans')->assertOk()->assertSee('Precio mensual')->assertSee('Profesional');
        $this->get('/admin/plans/create')->assertOk()->assertSee('Categoría visual')->assertSee('Características visibles')->assertSee('Requiere cotización');
    }

    public function test_notary_user_cannot_access_cruds_but_sees_dashboard(): void
    {
        $user = User::factory()->create(['notary_id' => $this->notary->id, 'is_active' => true]);
        $user->assignRole('notary_user');
        $this->actingAs($user)->get('/admin/notaries')->assertForbidden();
        $this->get('/app/users')->assertForbidden();
        $this->get('/dashboard')->assertOk()->assertSee('Dashboard de usuario');
    }

    public function test_layout_and_sidebar_are_role_aware(): void
    {
        $this->actingAs($this->super)->get('/dashboard')->assertOk()->assertSee('Status Link')->assertSee('Notarías')->assertSee('Suscripciones');
        $this->actingAs($this->notaryAdmin)->get('/dashboard')->assertOk()->assertSee('Configuración')->assertDontSee('Suscripciones');
    }

    public function test_login_renders_tailwind_interface(): void
    {
        $this->get('/login')->assertOk()->assertSee('Bienvenido a Status Link')->assertSee('Iniciar sesión');
    }
}
