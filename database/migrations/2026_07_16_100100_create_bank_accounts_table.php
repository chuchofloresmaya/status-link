<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bank_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('notary_id')->constrained()->cascadeOnDelete();
            $table->foreignId('notarial_profile_id')->nullable()->constrained()->nullOnDelete();
            $table->string('bank_name');
            $table->string('account_holder');
            $table->string('account_number')->nullable();
            $table->string('clabe')->nullable();
            $table->string('card_number')->nullable();
            $table->string('currency')->default('MXN');
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['notary_id', 'is_active']);
            $table->index(['notary_id', 'is_default']);
            $table->index(['notarial_profile_id', 'is_default']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bank_accounts');
    }
};
