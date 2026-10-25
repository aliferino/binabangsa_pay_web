<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VirtualAccount extends Model
{
    use HasFactory;

    /**
     * Prefix merchant Midtrans untuk nomor VA. Dipakai supaya angka ini tidak
     * tersebar sebagai literal di beberapa berkas.
     */
    public const PREFIX_MIDTRANS = '77701';

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

        // Nomor yang kita kirim ke Midtrans: dua nol di depan, lima digit
        // terakhir NIS di belakang. Midtrans menempelkan prefix merchant di
        // depannya, jadi nomor final di rekening siswa tetap berakhiran NIS.
        return '00'.$tail;
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

    /**
     * Nomor yang benar-benar dipakai siswa: prefix merchant Midtrans di depan,
     * nomor kirim di belakang. Hasilnya 12 digit, misal 77701 + 0012345.
     *
     * Nomor inilah yang ditampilkan, dicari, dan disimpan di account_number
     * dan nomor_va. Jangan simpan dua nomor berbeda untuk satu VA.
     */
    public static function nomorLengkap(string $nis, string $prefix = self::PREFIX_MIDTRANS): string
    {
        return $prefix.static::generateNumber($nis);
    }

    public static function generateNomorVa(string $nis): string
    {
        return static::generateNumber($nis);
    }

    /**
     * Nomor untuk ditampilkan, dikelompokkan 4-4-4 supaya mudah dibaca.
     */
    public function nomorTampil(): string
    {
        $angka = preg_replace('/[^0-9]/', '', (string) ($this->account_number ?: $this->nomor_va));

        return trim(chunk_split($angka, 4, ' '));
    }
}
