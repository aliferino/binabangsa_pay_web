<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tarif_spp', function (Blueprint $table) {
            $table->id('id_tarif');
            $table->foreignId('id_tahun_ajaran')->constrained('tahun_ajaran', 'id_tahun_ajaran')->cascadeOnDelete();
            $table->decimal('nominal', 12, 2);
            $table->text('keterangan')->nullable();

            $table->string('status')->default('aktif');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tarif_spp');
    }
};
