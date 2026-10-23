<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TuitionBill extends Model
{
    use HasFactory;

    protected $table = 'tagihan_spp';

    protected $primaryKey = 'id_tagihan';

    protected $fillable = [
        'id_siswa', 'id_tarif', 'id_tahun_ajaran', 'bulan', 'tahun',
        'nominal', 'tanggal_jatuh_tempo', 'status',
    ];

    protected function casts(): array
    {
        return [
            'nominal' => 'decimal:2',
            'tanggal_jatuh_tempo' => 'date',
            'bulan' => 'integer',
            'tahun' => 'integer',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'id_siswa', 'id_siswa');
    }

    public function tuitionRate(): BelongsTo
    {
        return $this->belongsTo(TuitionRate::class, 'id_tarif', 'id_tarif');
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class, 'id_tahun_ajaran', 'id_tahun_ajaran');
    }

    public function monthName(): string
    {
        return self::namaBulanDari($this->bulan);
    }

    /**
     * Nama bulan Indonesia dari angka 1-12. Dipakai juga oleh dashboard
     * yang mengelompokkan tagihan per bulan tanpa memuat modelnya.
     */
    public static function namaBulanDari(int $bulan): string
    {
        $months = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];

        return $months[$bulan] ?? '-';
    }

    public function isPayable(): bool
    {
        return $this->status !== 'lunas';
    }

    /**
     * Tagihan yang sudah lewat jatuh tempo tapi belum lunas.
     *
     * Status tunggakan TIDAK disimpan di kolom status (isinya hanya belum_bayar
     * atau lunas), jadi dihitung dari tanggal. Dengan begitu tidak perlu proses
     * penjadwalan yang mengubah status tiap tengah malam.
     */
    public function scopeOverdue($query)
    {
        return $query->where('status', '!=', 'lunas')
            ->whereDate('tanggal_jatuh_tempo', '<', today());
    }

    public function siswa(): BelongsTo
    {
        return $this->student();
    }

    public function tarif(): BelongsTo
    {
        return $this->tuitionRate();
    }

    public function tahunAjaran(): BelongsTo
    {
        return $this->academicYear();
    }

    public function namaBulan(): string
    {
        return $this->monthName();
    }

    public function bolehDibayar(): bool
    {
        return $this->isPayable();
    }
}
