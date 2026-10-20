<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AcademicYear extends Model
{
    use HasFactory;

    protected $table = 'tahun_ajaran';

    protected $primaryKey = 'id_tahun_ajaran';

    protected $fillable = ['tahun_ajaran', 'tanggal_mulai', 'tanggal_selesai', 'status'];

    protected function casts(): array
    {
        return [
            'tanggal_mulai' => 'date',
            'tanggal_selesai' => 'date',
        ];
    }

    public function classrooms(): HasMany
    {
        return $this->hasMany(Classroom::class, 'id_tahun_ajaran', 'id_tahun_ajaran');
    }

    public function tuitionRates(): HasMany
    {
        return $this->hasMany(TuitionRate::class, 'id_tahun_ajaran', 'id_tahun_ajaran');
    }

    public function kelas(): HasMany
    {
        return $this->classrooms();
    }

    public function tarifSpp(): HasMany
    {
        return $this->tuitionRates();
    }
}
