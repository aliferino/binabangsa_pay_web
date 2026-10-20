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

    public static function generateNumber(string $nis): string
    {
        $digits = preg_replace('/[^0-9]/', '', $nis);

        $tail = substr($digits, -5);
        $tail = str_pad($tail, 5, '0', STR_PAD_LEFT);

        return '880'.'00'.$tail;
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'siswa_id', 'id_siswa');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(PaymentGatewayTransaction::class, 'virtual_account_id', 'id');
    }

    public function isActive(): bool
    {
        return $this->status === 'aktif';
    }

    public function siswa(): BelongsTo
    {
        return $this->student();
    }

    public function transaksi(): HasMany
    {
        return $this->transactions();
    }

    public function aktif(): bool
    {
        return $this->isActive();
    }

    public static function generateNomorVa(string $nis): string
    {
        return static::generateNumber($nis);
    }
}
