<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::updateOrCreate(['email' => 'admin@status-link.local'], ['name' => 'Super Admin', 'password' => 'password', 'notary_id' => null, 'is_active' => true]);
        $user->syncRoles('super_admin');
    }
}
