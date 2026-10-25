<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Menormalkan kolom status pada tagihan_spp.
 *
 * Hanya dua nilai yang sah: belum_bayar dan lunas. Tunggakan tidak pernah
 * disimpan, dihitung dari tanggal_jatuh_tempo. Nilai lain yang terlanjur ada
 * ('belum', 'tunggakan', dan variasi lain) membuat baris tidak pernah terpilih
 * oleh query, sehingga tagihan termuda yang terbayar, bukan yang tertua.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('tagihan_spp')
            ->whereNotIn('status', ['belum_bayar', 'lunas'])
            ->update(['status' => 'belum_bayar']);

        DB::statement("ALTER TABLE tagihan_spp MODIFY status VARCHAR(255) NOT NULL DEFAULT 'belum_bayar'");
    }

    public function down(): void
    {
        // Tidak dikembalikan: nilai lama tidak bisa dipulihkan dan memang salah.
    }
};