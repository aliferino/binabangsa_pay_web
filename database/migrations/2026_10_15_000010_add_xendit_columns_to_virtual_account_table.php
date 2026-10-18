<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('virtual_account', function (Blueprint $table) {
            $table->string('xendit_id')->nullable()->unique();
            $table->string('account_number')->nullable()->unique();
            $table->string('bank_code')->nullable();
            $table->string('external_id')->nullable()->unique();
            $table->timestamp('expired_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('virtual_account', function (Blueprint $table) {
            $table->dropUnique(['xendit_id']);
            $table->dropUnique(['account_number']);
            $table->dropUnique(['external_id']);
            $table->dropColumn(['xendit_id', 'account_number', 'bank_code', 'external_id', 'expired_at']);
        });
    }
};
