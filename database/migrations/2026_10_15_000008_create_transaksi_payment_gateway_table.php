<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transaksi_payment_gateway', function (Blueprint $table) {
            $table->id();

            $table->string('transaction_id')->unique();

            $table->foreignId('virtual_account_id')
                ->nullable()
                ->constrained('virtual_account')
                ->nullOnDelete();

            $table->foreignId('siswa_id')->constrained('siswa', 'id_siswa')->cascadeOnDelete();

            $table->decimal('nominal', 12, 2);

            $table->enum('status_transaksi', ['pending', 'berhasil', 'gagal', 'kedaluwarsa'])
                ->default('pending');

            $table->string('payment_channel')->nullable();
            $table->string('reference_number')->nullable();

            $table->json('raw_response')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transaksi_payment_gateway');
    }
};
