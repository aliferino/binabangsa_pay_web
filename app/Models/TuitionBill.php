<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class TuitionBill extends Model
{
    use HasFactory;

    /**
     * Satu-satunya dua nilai yang sah di kolom status.
     *
     * Tunggakan TIDAK disimpan sebagai status: dihitung dari tanggal jatuh
     * tempo lewat scopeOverdue(). Dulu ada baris berstatus 'belum' dan
     * 'tunggakan' yang tidak pernah terpilih oleh query mana pun, dan itu
     * menyebabkan tagihan termuda yang terbayar, bukan yang tertua.
     */
    public const STATUS_BELUM = 'belum_bayar';

    public const STATUS_LUNAS = 'lunas';

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

    /**
     * Pembayaran yang tercatat untuk tagihan ini. Satu tagihan normalnya
     * hanya punya satu pembayaran berhasil, tetapi relasi dibuat hasMany
     * supaya percobaan yang gagal tetap terlihat.
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'tagihan_spp_id', 'id_tagihan');
    }

    /**
     * Pembayaran berhasil yang dipakai sebagai sumber uang masuk.
     */
    public function successfulPayment(): ?Payment
    {
        return $this->payments
            ->where('status', 'berhasil')
            ->sortBy('id')
            ->first();
    }

    /**
     * Selisih hari antara tanggal bayar dan jatuh tempo.
     *
     * 0 kalau dibayar tepat waktu atau lebih awal, null kalau belum dibayar
     * atau belum lewat jatuh tempo.
     */
    public function daysLate(): ?int
    {
        $paid = $this->successfulPayment()?->paidAt();

        if ($paid === null || $this->tanggal_jatuh_tempo === null) {
            return null;
        }

        $late = (int) $this->tanggal_jatuh_tempo->copy()->startOfDay()
            ->diffInDays($paid->copy()->startOfDay(), false);

        return max($late, 0);
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
        return $this->status !== self::STATUS_LUNAS;
    }

    /**
     * Query tagihan untuk laporan pembayaran, lengkap dengan filter.
     *
     * Dipakai bersama oleh laporan admin dan kepala sekolah supaya keduanya
     * membaca data yang sama persis. Filter tanggal bekerja pada tanggal
     * pembayaran (tanggal_pembayaran, jatuh ke created_at untuk baris lama),
     * bukan tanggal jatuh tempo.
     */
    public static function reportQuery(array $filter)
    {
        $query = static::with('siswa.kelas.jurusan', 'tarif', 'tahunAjaran', 'payments');

        if (($filter['bulan'] ?? null) !== null && $filter['bulan'] !== '') {
            $query->where('bulan', $filter['bulan']);
        }

        if (($filter['tahun'] ?? null) !== null && $filter['tahun'] !== '') {
            $query->where('tahun', $filter['tahun']);
        }

        if (($filter['id_tahun_ajaran'] ?? null) !== null && $filter['id_tahun_ajaran'] !== '') {
            $query->where('id_tahun_ajaran', $filter['id_tahun_ajaran']);
        }

        if (($filter['id_kelas'] ?? null) !== null && $filter['id_kelas'] !== '') {
            $query->whereHas('siswa', fn ($q) => $q->where('id_kelas', $filter['id_kelas']));
        }

        if (($filter['id_jurusan'] ?? null) !== null && $filter['id_jurusan'] !== '') {
            $query->whereHas('siswa.kelas', fn ($q) => $q->where('id_jurusan', $filter['id_jurusan']));
        }

        if (($filter['id_siswa'] ?? null) !== null && $filter['id_siswa'] !== '') {
            $query->where('id_siswa', $filter['id_siswa']);
        }

        if (($filter['status'] ?? null) !== null && $filter['status'] !== '') {
            $status = $filter['status'];

            if ($status === 'tunggakan') {
                $query->overdue();
            } elseif ($status === 'belum_bayar') {
                // Belum lunas dan belum lewat jatuh tempo. Memakai status
                // tersimpan apa adanya membuat baris berstatus aneh ikut
                // hilang dari laporan.
                $query->where('status', '!=', self::STATUS_LUNAS)
                    ->whereDate('tanggal_jatuh_tempo', '>=', today());
            } else {
                $query->where('status', $status);
            }
        }

        if (($filter['dari'] ?? null) !== null && $filter['dari'] !== '') {
            $query->whereHas('payments', function ($q) use ($filter) {
                $q->where('status', 'berhasil')
                    ->whereDate(DB::raw('COALESCE(tanggal_pembayaran, created_at)'), '>=', $filter['dari']);
            });
        }

        if (($filter['sampai'] ?? null) !== null && $filter['sampai'] !== '') {
            $query->whereHas('payments', function ($q) use ($filter) {
                $q->where('status', 'berhasil')
                    ->whereDate(DB::raw('COALESCE(tanggal_pembayaran, created_at)'), '<=', $filter['sampai']);
            });
        }

        return $query->orderByDesc('tahun')->orderByDesc('bulan')->get();
    }

    /**
     * Angka ringkas laporan: kewajiban tagihan dan uang masuk sebenarnya.
     *
     * totalPaid diambil dari tabel pembayaran, bukan dari status tagihan,
     * supaya pembayaran lewat VA atau gateway ikut terhitung.
     */
    public static function reportTotals($rows): array
    {
        $paidRows = $rows->flatMap(fn (TuitionBill $bill) => $bill->payments->where('status', 'berhasil'));

        return [
            'totalNominal' => (float) $rows->sum('nominal'),
            'totalPaid' => (float) $paidRows->sum('nominal_pembayaran'),
            'totalUnpaid' => (float) $rows->where('status', '!=', self::STATUS_LUNAS)->sum('nominal'),
            'totalArrears' => (float) $rows->filter(fn (TuitionBill $b) => $b->status !== self::STATUS_LUNAS && $b->tanggal_jatuh_tempo?->isPast())->sum('nominal'),
        ];
    }

    /**
     * Rekap uang masuk per bulan dan per kelas.
     */
    public static function recap(array $filter): array
    {
        $query = Payment::query()
            ->where('status', 'berhasil')
            ->with('student.kelas');

        if (($filter['dari'] ?? null) !== null && $filter['dari'] !== '') {
            $query->whereDate(DB::raw('COALESCE(tanggal_pembayaran, created_at)'), '>=', $filter['dari']);
        }

        if (($filter['sampai'] ?? null) !== null && $filter['sampai'] !== '') {
            $query->whereDate(DB::raw('COALESCE(tanggal_pembayaran, created_at)'), '<=', $filter['sampai']);
        }

        $payments = $query->get();

        $byMonth = $payments
            ->groupBy(fn (Payment $p) => $p->paidAt()?->format('Y-m') ?? '-')
            ->map(fn ($group, $key) => [
                'periode' => $key,
                'label' => $group->first()->paidAt()?->translatedFormat('F Y') ?? '-',
                'transaksi' => $group->count(),
                'total' => (float) $group->sum('nominal_pembayaran'),
            ])
            ->sortKeysDesc()
            ->values();

        $byClassroom = $payments
            ->groupBy(fn (Payment $p) => $p->student?->kelas?->nama_kelas ?? 'Tanpa kelas')
            ->map(fn ($group, $key) => [
                'kelas' => $key,
                'transaksi' => $group->count(),
                'total' => (float) $group->sum('nominal_pembayaran'),
            ])
            ->sortByDesc('total')
            ->values();

        return [
            'byMonth' => $byMonth,
            'byClassroom' => $byClassroom,
            'grandTotal' => (float) $payments->sum('nominal_pembayaran'),
            'transactionCount' => $payments->count(),
        ];
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
        return $query->where('status', '!=', self::STATUS_LUNAS)
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
