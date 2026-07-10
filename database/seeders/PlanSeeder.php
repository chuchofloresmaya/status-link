<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        $common = ['rpp_query' => true, 'qr_scan' => true, 'isr_calculation' => false, 'declaranot_txt' => false, 'email_notifications' => false, 'whatsapp_notifications' => false, 'urgent_notifications' => false];
        $plans = [
            ['name' => 'Free', 'slug' => 'free', 'price' => 0, 'features' => $common, 'limits' => ['monthly_records' => 50, 'daily_auto_refreshes' => 1, 'users' => 2]],
            ['name' => 'Basic', 'slug' => 'basic', 'price' => 499, 'features' => array_merge($common, ['email_notifications' => true]), 'limits' => ['monthly_records' => 500, 'daily_auto_refreshes' => 3, 'users' => 5]],
            ['name' => 'Premium', 'slug' => 'premium', 'price' => 999, 'features' => array_fill_keys(array_keys($common), true), 'limits' => ['monthly_records' => 5000, 'daily_auto_refreshes' => 12, 'users' => 20]],
        ];
        foreach ($plans as $plan) {
            Plan::updateOrCreate(['slug' => $plan['slug']], array_merge($plan, ['billing_period' => 'monthly', 'is_active' => true]));
        }
    }
}
