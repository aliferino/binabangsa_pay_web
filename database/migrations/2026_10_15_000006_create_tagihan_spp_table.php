<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tagihan_spp', function (Blueprint $table) {
            $table->id('id_tagihan');
            $table->foreignId('id_siswa')->constrained('siswa', 'id_siswa')->cascadeOnDelete();
            $table->foreignId('id_tarif')->constrained('tarif_spp', 'id_tarif')->cascadeOnDelete();
            $table->foreignId('id_tahun_ajaran')->constrained('tahun_ajaran', 'id_tahun_ajaran')->cascadeOnDelete();
            $table->unsignedTinyInteger('bulan');
            $table->unsignedSmallInteger('tahun');
            $table->decimal('nominal', 12, 2);
            $table->date('tanggal_jatuh_tempo');
            $table->string('status')->default('belum_bayar');
            $table->timestamps();

            $table->unique(['id_siswa', 'bulan', 'tahun'], 'tagihan_siswa_bulan_unik');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tagihan_spp');
    }
};
