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
        $all = ['notaries.manage', 'plans.manage', 'subscriptions.manage', 'payments.manage', 'users.manage', 'users.view', 'users.create', 'users.update', 'users.delete', 'users.activate', 'users.deactivate', 'settings.manage'];
        foreach ($all as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }
        Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web'])->syncPermissions($all);
        Role::firstOrCreate(['name' => 'notary_admin', 'guard_name' => 'web'])->syncPermissions(['users.manage', 'users.view', 'users.create', 'users.update', 'users.activate', 'users.deactivate', 'settings.manage']);
        Role::firstOrCreate(['name' => 'notary_user', 'guard_name' => 'web'])->syncPermissions([]);
    }
}
