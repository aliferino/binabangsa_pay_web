<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TarifSpp extends Model
{
    use HasFactory;

    protected $table = 'tarif_spp';

    protected $primaryKey = 'id_tarif';

    protected $fillable = ['id_tahun_ajaran', 'nominal', 'keterangan', 'status'];

    protected function casts(): array
    {
        return [
            'nominal' => 'decimal:2',
        ];
    }

    public function tahunAjaran()
    {
        return $this->belongsTo(TahunAjaran::class, 'id_tahun_ajaran', 'id_tahun_ajaran');
    }
}
