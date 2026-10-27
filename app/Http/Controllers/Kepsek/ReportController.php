<?php

namespace App\Http\Controllers\Kepsek;

use App\Http\Controllers\Controller;
use App\Models\Classroom;
use App\Models\Major;
use App\Models\Student;
use App\Models\TuitionBill;
use App\Models\AcademicYear;
use Illuminate\Http\Request;

/**
 * Laporan khusus kepala sekolah.
 *
 * Kepala sekolah hanya boleh melihat, tidak boleh mencatat atau mengubah.
 * Query laporan pembayaran dipakai bersama dengan laporan admin lewat
 * TuitionBill::reportQuery, supaya angka kedua laporan tidak bisa berbeda.
 */
class ReportController extends Controller
{
    private const REPORT_FILTERS = [
        'bulan', 'tahun', 'id_tahun_ajaran', 'id_kelas',
        'id_jurusan', 'id_siswa', 'status', 'dari', 'sampai',
    ];

    /**
     * Laporan pembayaran tagihan SPP.
     *
     * Uang masuk dibaca dari tabel pembayaran. Satu baris mewakili satu
     * tagihan, lengkap dengan tanggal bayar dan selisih hari keterlambatan.
     */
    public function payments(Request $request)
    {
        $filter = $request->only(self::REPORT_FILTERS);
        $rows = TuitionBill::reportQuery($filter);

        return $this->render('kepsek.reports.payments', [
            'bills' => $rows,
            'filter' => $filter,
        ] + TuitionBill::reportTotals($rows) + $this->filterOptions());
    }

    /**
     * Rekap uang masuk per bulan dan per kelas.
     */
    public function recap(Request $request)
    {
        $filter = $request->only(['dari', 'sampai']);

        return $this->render('kepsek.reports.recap', [
            'filter' => $filter,
        ] + TuitionBill::recap($filter));
    }

    /**
     * Laporan tunggakan per siswa.
     *
     * Tunggakan dihitung dari tanggal jatuh tempo, bukan dari nilai kolom
     * status, karena kolom itu hanya berisi belum_bayar atau lunas.
     */
    public function arrears(Request $request)
    {
        $query = Student::with('kelas.jurusan')
            ->whereHas('tagihan', fn ($q) => $q->overdue());

        if ($request->filled('bulan')) {
            $query->whereHas('tagihan', fn ($q) => $q->where('bulan', $request->input('bulan'))->overdue());
        }

        if ($request->filled('tahun')) {
            $query->whereHas('tagihan', fn ($q) => $q->where('tahun', $request->input('tahun'))->overdue());
        }

        if ($request->filled('id_kelas')) {
            $query->where('id_kelas', $request->input('id_kelas'));
        }

        if ($request->filled('id_jurusan')) {
            $query->whereHas('kelas', function ($q) use ($request) {
                $q->where('id_jurusan', $request->input('id_jurusan'));
            });
        }

        $rows = $query->orderBy('nama')->get()->map(function (Student $siswa) {
            $tagihan = $siswa->tuitionBills()->overdue()
                ->orderBy('tahun')
                ->orderBy('bulan')
                ->get();

            return [
                'nis' => $siswa->nis,
                'nama' => $siswa->nama,
                'kelas' => $siswa->kelas?->nama_kelas,
                'bulan_tunggakan' => $tagihan->map(fn (TuitionBill $t) => $t->namaBulan())->implode(', '),
                'jumlah_tunggakan' => $tagihan->count(),
                'total_nominal' => (float) $tagihan->sum('nominal'),
                'hari_terlambat' => $tagihan->max(fn (TuitionBill $t) => $t->tanggal_jatuh_tempo?->diffInDays(today()) ?? 0),
            ];
        });

        return $this->render('kepsek.reports.arrears', [
            'arrears' => $rows,
            'totalAll' => (float) $rows->sum('total_nominal'),
            'filter' => $request->only(['bulan', 'tahun', 'id_kelas', 'id_jurusan']),
        ] + $this->filterOptions());
    }

    /**
     * Data pilihan untuk filter laporan.
     */
    private function filterOptions(): array
    {
        return [
            'classrooms' => Classroom::with('jurusan')->orderBy('nama_kelas')->get(),
            'majors' => Major::orderBy('nama_jurusan')->get(),
            'academicYears' => AcademicYear::orderByDesc('tahun_ajaran')->get(),
            'students' => Student::orderBy('nama')->get(),
            'years' => TuitionBill::distinct()->orderByDesc('tahun')->pluck('tahun'),
        ];
    }
}