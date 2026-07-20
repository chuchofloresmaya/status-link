<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bank_accounts', function (Blueprint $table) {
            $table->string('account_type')->default('general')->after('notarial_profile_id');
            $table->index(['notary_id', 'notarial_profile_id', 'account_type', 'is_default'], 'bank_accounts_default_scope_index');
        });
    }

    public function down(): void
    {
        Schema::table('bank_accounts', function (Blueprint $table) {
            $table->dropIndex('bank_accounts_default_scope_index');
            $table->dropColumn('account_type');
        });
    }
};
