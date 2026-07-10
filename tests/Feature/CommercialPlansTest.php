<?php

namespace Tests\Feature;

use App\Models\Plan;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommercialPlansTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_five_commercial_plans_exist_in_display_order(): void
    {
        $this->assertSame(['free', 'basic', 'professional', 'premium', 'corporate'], Plan::orderBy('display_order')->pluck('slug')->all());
    }

    public function test_professional_is_highlighted_and_has_promotional_equivalent(): void
    {
        $plan = Plan::whereSlug('professional')->firstOrFail();
        $this->assertTrue($plan->is_highlighted);
        $this->assertSame('430.00', $plan->promotional_equivalent_monthly_price);
    }

    public function test_corporate_requires_quote_and_has_unlimited_records(): void
    {
        $plan = Plan::whereSlug('corporate')->firstOrFail();
        $this->assertTrue($plan->requires_quote);
        $this->assertNull($plan->limits['active_records']);
        $this->assertTrue($plan->features['custom_frequency']);
    }

    public function test_premium_enables_whatsapp_and_allows_one_thousand_records(): void
    {
        $plan = Plan::whereSlug('premium')->firstOrFail();
        $this->assertTrue($plan->features['whatsapp_alerts']);
        $this->assertSame(1000, $plan->limits['active_records']);
    }

    public function test_free_and_basic_have_expected_commercial_values(): void
    {
        $free = Plan::whereSlug('free')->firstOrFail();
        $basic = Plan::whereSlug('basic')->firstOrFail();
        $this->assertSame(20, $free->limits['active_records']);
        $this->assertSame('600.00', $basic->promotional_price);
        $this->assertSame(3, $basic->promotional_months);
    }
}
