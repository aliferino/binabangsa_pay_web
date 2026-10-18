<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class TransaksiPaymentGateway extends Model
{
    use HasFactory;

    protected $table = 'transaksi_payment_gateway';

    protected $fillable = [
        'transaction_id', 'virtual_account_id', 'siswa_id', 'nominal',
        'status_transaksi', 'payment_channel', 'reference_number', 'raw_response',
        'xendit_payment_id', 'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'raw_response' => 'array',
            'nominal' => 'decimal:2',
            'paid_at' => 'datetime',
        ];
    }

    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class, 'siswa_id', 'id_siswa');
    }

    public function virtualAccount(): BelongsTo
    {
        return $this->belongsTo(VirtualAccount::class, 'virtual_account_id', 'id');
    }

    public function pembayaran(): HasOne
    {
        return $this->hasOne(Pembayaran::class, 'transaksi_id', 'id');
    }

    public function berhasil(): bool
    {
        return $this->status_transaksi === 'berhasil';
    }
}
