<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('virtual_account', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')->constrained('siswa', 'id_siswa')->cascadeOnDelete();
            $table->string('nomor_va', 16)->unique();
            $table->string('prefix', 8)->default('880');
            $table->enum('status', ['aktif', 'nonaktif'])->default('aktif');
            $table->timestamps();

            $table->unique('siswa_id', 'virtual_account_siswa_unik');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('virtual_account');
    }
};
