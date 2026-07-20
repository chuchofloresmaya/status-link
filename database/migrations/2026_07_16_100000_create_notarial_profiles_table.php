<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notarial_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('notary_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('notary_number')->nullable();
            $table->string('notary_name')->nullable();
            $table->string('notary_title')->nullable();
            $table->string('rfc')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->string('logo_path')->nullable();
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->json('settings')->nullable();
            $table->timestamps();
            $table->index(['notary_id', 'is_active']);
            $table->index(['notary_id', 'is_default']);
            $table->index(['notary_id', 'notary_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notarial_profiles');
    }
};
