<?php

namespace Tests\Feature;

use App\Domain\Plans\Services\FeatureGateService;
use App\Domain\Users\Services\UserTenantService;
use App\Models\Notary;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdministrativeFoundationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_required_roles_and_permissions_exist(): void
    {
        foreach (['super_admin', 'notary_admin', 'notary_user'] as $role) {
            $this->assertTrue(Role::where('name', $role)->exists());
        }
        foreach (['notaries.manage', 'plans.manage', 'subscriptions.manage', 'payments.manage', 'users.manage', 'users.view', 'users.create', 'users.update', 'users.delete', 'users.activate', 'users.deactivate', 'settings.manage'] as $permission) {
            $this->assertTrue(Permission::where('name', $permission)->exists());
        }
    }

    public function test_seeded_super_admin_is_global(): void
    {
        $u = User::whereEmail('admin@status-link.local')->firstOrFail();
        $this->assertTrue($u->hasRole('super_admin'));
        $this->assertNull($u->notary_id);
    }

    public function test_demo_notary_admin_and_subscription_exist(): void
    {
        $n = Notary::whereSlug('notaria-demo')->firstOrFail();
        $u = User::whereEmail('notaria@status-link.local')->firstOrFail();
        $this->assertSame($n->id, $u->notary_id);
        $this->assertTrue($u->hasRole('notary_admin'));
        $this->assertNotNull($n->activeSubscription);
    }

    public function test_feature_gate_reads_active_plan(): void
    {
        $n = Notary::whereSlug('notaria-demo')->firstOrFail();
        $s = app(FeatureGateService::class);
        $this->assertTrue($s->allows($n, 'rpp_query'));
        $this->assertFalse($s->allows($n, 'missing'));
        $this->assertSame(5000, $s->limit($n, 'monthly_records'));
    }

    public function test_tenant_service_enforces_boundaries(): void
    {
        $super = User::whereEmail('admin@status-link.local')->firstOrFail();
        $admin = User::whereEmail('notaria@status-link.local')->firstOrFail();
        $local = User::factory()->create(['notary_id' => $admin->notary_id]);
        $other = Notary::create(['name' => 'Other', 'slug' => 'other']);
        $foreign = User::factory()->create(['notary_id' => $other->id]);
        $s = app(UserTenantService::class);
        $this->assertTrue($s->canManageUser($super, $foreign));
        $this->assertTrue($s->canManageUser($admin, $local));
        $this->assertFalse($s->canManageUser($admin, $foreign));
    }

    public function test_me_requires_sanctum_authentication(): void
    {
        $this->getJson('/api/v1/me')->assertUnauthorized();
    }
}
