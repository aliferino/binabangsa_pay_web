<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pembayaran', function (Blueprint $table) {
            $table->timestamp('tanggal_pembayaran')->nullable()->after('nominal_pembayaran');
        });

        DB::table('pembayaran')->update([
            'tanggal_pembayaran' => DB::raw('created_at'),
        ]);
    }

    public function down(): void
    {
        Schema::table('pembayaran', function (Blueprint $table) {
            $table->dropColumn('tanggal_pembayaran');
        });
    }
};