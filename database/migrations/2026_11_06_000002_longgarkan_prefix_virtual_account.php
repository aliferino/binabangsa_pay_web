<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Kolom prefix pada virtual_account menahan sisa era Xendit.
 *
 * Default '880' membuat setiap baris baru otomatis berisi angka karangan
 * kalau kolomnya tidak diisi, dan panjang 8 karakter tidak cukup untuk prefix
 * merchant Midtrans (77701) ditambah nomor kirim. Sekarang prefix hanya bagian
 * depan nomor hasil, jadi lebarnya disamakan dengan nomor_va.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE virtual_account MODIFY prefix VARCHAR(16) NULL DEFAULT NULL');

        // Prefix lama yang tidak mungkin berasal dari nomor hasil dikosongkan.
        DB::table('virtual_account')
            ->where('prefix', '880')
            ->update(['prefix' => null]);
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE virtual_account MODIFY prefix VARCHAR(8) NOT NULL DEFAULT '880'");
    }
};