<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    protected $table = 'pembayaran';

    protected $fillable = [
        'nomor_transaksi', 'transaksi_id', 'tagihan_spp_id', 'siswa_id',
        'nominal_pembayaran', 'metode_pembayaran', 'status', 'user_id',
        'keterangan', 'nomor_referensi',
    ];

    protected function casts(): array
    {
        return [
            'nominal_pembayaran' => 'decimal:2',
        ];
    }

    public function tuitionBill(): BelongsTo
    {
        return $this->belongsTo(TuitionBill::class, 'tagihan_spp_id', 'id_tagihan');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'siswa_id', 'id_siswa');
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(PaymentGatewayTransaction::class, 'transaksi_id', 'id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id_user');
    }

    public static function generateTransactionNumber(): string
    {
        $prefix = 'PAY'.now()->format('Ymd');

        $latest = static::where('nomor_transaksi', 'like', $prefix.'%')
            ->orderByDesc('nomor_transaksi')
            ->value('nomor_transaksi');

        $sequence = $latest === null ? 1 : ((int) substr($latest, -6)) + 1;

        return $prefix.str_pad((string) $sequence, 6, '0', STR_PAD_LEFT);
    }

    public function succeeded(): bool
    {
        return $this->status === 'berhasil';
    }

    public function tagihanSpp(): BelongsTo
    {
        return $this->tuitionBill();
    }

    public function siswa(): BelongsTo
    {
        return $this->student();
    }

    public function transaksi(): BelongsTo
    {
        return $this->transaction();
    }

    public static function buatNomorTransaksi(): string
    {
        return static::generateTransactionNumber();
    }

    public function berhasil(): bool
    {
        return $this->succeeded();
    }
}
