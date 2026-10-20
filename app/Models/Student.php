<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Student extends Model
{
    use HasFactory;

    protected $table = 'siswa';

    protected $primaryKey = 'id_siswa';

    protected $fillable = [
        'nis', 'nisn', 'nama', 'jenis_kelamin', 'tempat_lahir', 'tanggal_lahir',
        'alamat', 'no_hp', 'nama_orang_tua', 'id_kelas', 'id_tahun_ajaran',
        'id_user', 'status',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_lahir' => 'date',
        ];
    }

    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class, 'id_kelas', 'id_kelas');
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class, 'id_tahun_ajaran', 'id_tahun_ajaran');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }

    public function tuitionBills(): HasMany
    {
        return $this->hasMany(TuitionBill::class, 'id_siswa', 'id_siswa');
    }

    public function virtualAccount(): HasOne
    {
        return $this->hasOne(VirtualAccount::class, 'siswa_id', 'id_siswa');
    }

    public function paymentGatewayTransactions(): HasMany
    {
        return $this->hasMany(PaymentGatewayTransaction::class, 'siswa_id', 'id_siswa');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'siswa_id', 'id_siswa');
    }

    public function totalArrears(): float
    {
        return (float) $this->tuitionBills()->where('status', 'tunggakan')->sum('nominal');
    }

    public function kelas(): BelongsTo
    {
        return $this->classroom();
    }

    public function tahunAjaran(): BelongsTo
    {
        return $this->academicYear();
    }

    public function tagihan(): HasMany
    {
        return $this->tuitionBills();
    }

    public function transaksi(): HasMany
    {
        return $this->paymentGatewayTransactions();
    }

    public function pembayaran(): HasMany
    {
        return $this->payments();
    }

    public function totalTunggakan(): float
    {
        return $this->totalArrears();
    }
}
