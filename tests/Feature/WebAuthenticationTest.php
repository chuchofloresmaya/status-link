<?php

namespace Tests\Feature;

use App\Models\Notary;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WebAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_root_redirects_guests_to_login_and_users_to_dashboard(): void
    {
        $this->get('/')->assertRedirect('/login');

        $user = User::where('email', 'admin@status-link.local')->firstOrFail();
        $this->actingAs($user)->get('/')->assertRedirect('/dashboard');
    }

    public function test_user_can_log_in_and_log_out(): void
    {
        $this->post('/login', [
            'email' => 'admin@status-link.local',
            'password' => 'password',
        ])->assertRedirect('/dashboard');

        $this->assertAuthenticated();
        $this->post('/logout')->assertRedirect('/login');
        $this->assertGuest();
    }

    public function test_invalid_credentials_are_rejected(): void
    {
        $this->from('/login')->post('/login', [
            'email' => 'admin@status-link.local',
            'password' => 'incorrect',
        ])->assertRedirect('/login')->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_public_registration_does_not_exist(): void
    {
        $this->get('/register')->assertNotFound();
        $this->post('/register')->assertNotFound();
    }

    public function test_dashboard_requires_authentication_and_active_entities(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');

        $inactiveUser = User::factory()->create(['is_active' => false]);
        $this->actingAs($inactiveUser)->get('/dashboard')->assertForbidden();

        $notary = Notary::create(['name' => 'Inactive Notary', 'slug' => 'inactive-notary', 'is_active' => false]);
        $user = User::factory()->create(['notary_id' => $notary->id, 'is_active' => true]);
        $this->actingAs($user)->get('/dashboard')->assertForbidden();
    }

    public function test_dashboard_content_depends_on_role(): void
    {
        $superAdmin = User::where('email', 'admin@status-link.local')->firstOrFail();
        $this->actingAs($superAdmin)->get('/dashboard')->assertOk()->assertSee('Resumen global del SaaS');

        $notaryAdmin = User::where('email', 'notaria@status-link.local')->firstOrFail();
        $this->actingAs($notaryAdmin)->get('/dashboard')->assertOk()->assertSee('Notaría Demo')->assertSee('Premium');

        $notaryUser = User::factory()->create(['notary_id' => $notaryAdmin->notary_id, 'is_active' => true]);
        $notaryUser->assignRole('notary_user');
        $this->actingAs($notaryUser)->get('/dashboard')->assertOk()->assertSee('Dashboard de usuario');
    }
}
