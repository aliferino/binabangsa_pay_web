<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('siswa', function (Blueprint $table) {
            $table->id('id_siswa');
            $table->string('nis')->unique();
            $table->string('nisn')->unique();
            $table->string('nama');

            $table->string('jenis_kelamin');
            $table->string('tempat_lahir');
            $table->date('tanggal_lahir');
            $table->text('alamat');
            $table->string('no_hp')->nullable();
            $table->string('nama_orang_tua')->nullable();
            $table->foreignId('id_kelas')->constrained('kelas', 'id_kelas')->cascadeOnDelete();
            $table->foreignId('id_tahun_ajaran')->constrained('tahun_ajaran', 'id_tahun_ajaran')->cascadeOnDelete();

            $table->foreignId('id_user')->nullable()->constrained('users', 'id_user')->nullOnDelete();

            $table->string('status')->default('aktif');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('siswa');
    }
};
