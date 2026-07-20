<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $profilePermissions = ['notarial_profiles.manage', 'notarial_profiles.view', 'notarial_profiles.create', 'notarial_profiles.update', 'notarial_profiles.toggle_active'];
        $bankPermissions = ['bank_accounts.manage', 'bank_accounts.view', 'bank_accounts.create', 'bank_accounts.update', 'bank_accounts.toggle_active'];
        $all = array_merge(['notaries.manage', 'plans.manage', 'subscriptions.manage', 'payments.manage', 'users.manage', 'users.view', 'users.create', 'users.update', 'users.delete', 'users.activate', 'users.deactivate', 'settings.manage'], $profilePermissions, $bankPermissions);
        foreach ($all as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }
        Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web'])->syncPermissions($all);
        Role::firstOrCreate(['name' => 'notary_admin', 'guard_name' => 'web'])->syncPermissions(array_merge(['users.manage', 'users.view', 'users.create', 'users.update', 'users.activate', 'users.deactivate', 'settings.manage'], $profilePermissions, $bankPermissions));
        Role::firstOrCreate(['name' => 'notary_user', 'guard_name' => 'web'])->syncPermissions([]);
    }
}
