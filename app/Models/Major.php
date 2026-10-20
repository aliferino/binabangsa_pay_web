<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Major extends Model
{
    use HasFactory;

    protected $table = 'jurusan';

    protected $primaryKey = 'id_jurusan';

    protected $fillable = ['kode_jurusan', 'nama_jurusan', 'keterangan'];

    public function classrooms(): HasMany
    {
        return $this->hasMany(Classroom::class, 'id_jurusan', 'id_jurusan');
    }

    public function kelas(): HasMany
    {
        return $this->classrooms();
    }
}
