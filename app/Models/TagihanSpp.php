<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TagihanSpp extends Model
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

    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'id_siswa', 'id_siswa');
    }

    public function tarif()
    {
        return $this->belongsTo(TarifSpp::class, 'id_tarif', 'id_tarif');
    }

    public function tahunAjaran()
    {
        return $this->belongsTo(TahunAjaran::class, 'id_tahun_ajaran', 'id_tahun_ajaran');
    }

    public function namaBulan(): string
    {
        $daftar = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];

        return $daftar[$this->bulan] ?? '-';
    }

    public function bolehDibayar(): bool
    {
        return $this->status !== 'lunas';
    }
}
