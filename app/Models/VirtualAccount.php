<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VirtualAccount extends Model
{
    use HasFactory;

    protected $table = 'virtual_account';

    protected $fillable = [
        'siswa_id', 'nomor_va', 'prefix', 'status',
        'xendit_id', 'account_number', 'bank_code', 'external_id', 'expired_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => 'string',
            'expired_at' => 'datetime',
        ];
    }

    /**
     * Susun nomor Virtual Account dari NIS.
     *
     * Aturannya: prefix 880, lalu 00, lalu 5 digit terakhir NIS. Total 10
     * digit. Kalau NIS kurang dari 5 digit, bagian depannya diisi nol supaya
     * panjangnya tetap 10 digit.
     */
    public static function generateNomorVa(string $nis): string
    {
        $bersih = preg_replace('/[^0-9]/', '', $nis);

        $ekor = substr($bersih, -5);
        $ekor = str_pad($ekor, 5, '0', STR_PAD_LEFT);

        return '880'.'00'.$ekor;
    }

    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class, 'siswa_id', 'id_siswa');
    }

    public function transaksi(): HasMany
    {
        return $this->hasMany(TransaksiPaymentGateway::class, 'virtual_account_id', 'id');
    }

    public function aktif(): bool
    {
        return $this->status === 'aktif';
    }
}
