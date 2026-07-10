<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->string('category_label')->nullable()->after('description');
            $table->string('badge_label')->nullable()->after('category_label');
            $table->string('secondary_label')->nullable()->after('badge_label');
            $table->decimal('monthly_price', 10, 2)->nullable()->after('price');
            $table->decimal('promotional_price', 10, 2)->nullable()->after('monthly_price');
            $table->unsignedInteger('promotional_months')->nullable()->after('promotional_price');
            $table->decimal('promotional_equivalent_monthly_price', 10, 2)->nullable()->after('promotional_months');
            $table->boolean('requires_quote')->default(false)->after('billing_period');
            $table->unsignedInteger('display_order')->default(0)->after('requires_quote');
            $table->json('marketing_features')->nullable()->after('display_order');
            $table->boolean('is_highlighted')->default(false)->after('limits');
        });
    }

    public function down(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->dropColumn([
                'category_label', 'badge_label', 'secondary_label', 'monthly_price',
                'promotional_price', 'promotional_months', 'promotional_equivalent_monthly_price',
                'requires_quote', 'display_order', 'marketing_features', 'is_highlighted',
            ]);
        });
    }
};
