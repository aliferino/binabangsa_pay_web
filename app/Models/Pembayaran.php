<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pembayaran extends Model
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

    public function tagihanSpp(): BelongsTo
    {
        return $this->belongsTo(TagihanSpp::class, 'tagihan_spp_id', 'id_tagihan');
    }

    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class, 'siswa_id', 'id_siswa');
    }

    public function transaksi(): BelongsTo
    {
        return $this->belongsTo(TransaksiPaymentGateway::class, 'transaksi_id', 'id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id_user');
    }

    /**
     * Susun nomor transaksi pembayaran yang unik.
     *
     * Bentuknya: PAY, tanggal hari ini, lalu 6 digit urutan. Contoh
     * PAY20261025000001. Dipakai untuk pembayaran yang tidak lewat gateway,
     * yaitu tunai dan QRIS.
     */
    public static function buatNomorTransaksi(): string
    {
        $awalan = 'PAY'.now()->format('Ymd');

        $terakhir = static::where('nomor_transaksi', 'like', $awalan.'%')
            ->orderByDesc('nomor_transaksi')
            ->value('nomor_transaksi');

        $urutan = $terakhir === null ? 1 : ((int) substr($terakhir, -6)) + 1;

        return $awalan.str_pad((string) $urutan, 6, '0', STR_PAD_LEFT);
    }

    public function berhasil(): bool
    {
        return $this->status === 'berhasil';
    }
}
