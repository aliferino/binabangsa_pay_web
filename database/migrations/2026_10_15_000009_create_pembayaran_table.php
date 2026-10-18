<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pembayaran', function (Blueprint $table) {
            $table->id();

            $table->string('nomor_transaksi')->unique();

            $table->foreignId('transaksi_id')
                ->nullable()
                ->constrained('transaksi_payment_gateway')
                ->nullOnDelete();

            $table->foreignId('tagihan_spp_id')->constrained('tagihan_spp', 'id_tagihan')->cascadeOnDelete();
            $table->foreignId('siswa_id')->constrained('siswa', 'id_siswa')->cascadeOnDelete();

            $table->decimal('nominal_pembayaran', 12, 2);

            $table->enum('metode_pembayaran', ['va', 'tunai', 'qris']);
            $table->enum('status', ['berhasil', 'gagal', 'dibatalkan'])->default('berhasil');

            $table->foreignId('user_id')->nullable()->constrained('users', 'id_user')->nullOnDelete();

            $table->text('keterangan')->nullable();
            $table->string('nomor_referensi')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pembayaran');
    }
};
