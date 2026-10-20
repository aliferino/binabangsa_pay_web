<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Classroom extends Model
{
    use HasFactory;

    protected $table = 'kelas';

    protected $primaryKey = 'id_kelas';

    protected $fillable = ['nama_kelas', 'tingkat', 'id_jurusan', 'id_tahun_ajaran'];

    public function major(): BelongsTo
    {
        return $this->belongsTo(Major::class, 'id_jurusan', 'id_jurusan');
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class, 'id_tahun_ajaran', 'id_tahun_ajaran');
    }

    public function students(): HasMany
    {
        return $this->hasMany(Student::class, 'id_kelas', 'id_kelas');
    }

    public function jurusan(): BelongsTo
    {
        return $this->major();
    }

    public function tahunAjaran(): BelongsTo
    {
        return $this->academicYear();
    }

    public function siswa(): HasMany
    {
        return $this->students();
    }
}
