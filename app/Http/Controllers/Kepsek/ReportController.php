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
 * Laporan ini berdiri sendiri, terpisah dari laporan admin, supaya kalau
 * nanti kepala sekolah butuh kolom atau filter sendiri, perubahannya tidak
 * mengganggu laporan admin.
 */
class ReportController extends Controller
{
    /**
     * Laporan pembayaran tagihan SPP.
     *
     * Filter: bulan, tahun, tahun ajaran, kelas, jurusan, siswa, status.
     * Kolom minimal: NIS, nama, kelas, bulan, nominal, status, tanggal bayar.
     */
    public function payments(Request $request)
    {
        $query = TuitionBill::with('siswa.kelas.jurusan', 'tarif', 'tahunAjaran');

        if ($request->filled('bulan')) {
            $query->where('bulan', $request->input('bulan'));
        }

        if ($request->filled('tahun')) {
            $query->where('tahun', $request->input('tahun'));
        }

        if ($request->filled('id_tahun_ajaran')) {
            $query->where('id_tahun_ajaran', $request->input('id_tahun_ajaran'));
        }

        if ($request->filled('id_kelas')) {
            $query->whereHas('siswa', function ($q) use ($request) {
                $q->where('id_kelas', $request->input('id_kelas'));
            });
        }

        if ($request->filled('id_jurusan')) {
            $query->whereHas('siswa.kelas', function ($q) use ($request) {
                $q->where('id_jurusan', $request->input('id_jurusan'));
            });
        }

        if ($request->filled('id_siswa')) {
            $query->where('id_siswa', $request->input('id_siswa'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $rows = $query->orderByDesc('tahun')->orderByDesc('bulan')->get();

        return $this->render('kepsek.reports.payments', [
            'bills' => $rows,
            'totalNominal' => (float) $rows->sum('nominal'),
            'totalPaid' => (float) $rows->where('status', 'lunas')->sum('nominal'),
            'totalUnpaid' => (float) $rows->where('status', 'belum_bayar')->sum('nominal'),
            'totalArrears' => (float) $rows->where('status', 'tunggakan')->sum('nominal'),
            'filter' => $request->only([
                'bulan', 'tahun', 'id_tahun_ajaran', 'id_kelas',
                'id_jurusan', 'id_siswa', 'status',
            ]),
        ] + $this->filterOptions());
    }

    /**
     * Laporan tunggakan per siswa.
     *
     * Filter: bulan, tahun, kelas, jurusan.
     * Kolom minimal: NIS, nama, kelas, bulan tunggakan, jumlah tunggakan,
     * total nominal.
     */
    public function arrears(Request $request)
    {
        $query = Student::with('kelas.jurusan')
            ->whereHas('tagihan', function ($q) {
                $q->where('status', 'tunggakan');
            });

        if ($request->filled('bulan')) {
            $query->whereHas('tagihan', function ($q) use ($request) {
                $q->where('bulan', $request->input('bulan'));
            });
        }

        if ($request->filled('tahun')) {
            $query->whereHas('tagihan', function ($q) use ($request) {
                $q->where('tahun', $request->input('tahun'));
            });
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
            $tagihan = $siswa->tagihan()->where('status', 'tunggakan')
                ->orderBy('tahun')
                ->orderBy('bulan')
                ->get();

            return [
                'nis' => $siswa->nis,
                'nama' => $siswa->nama,
                'kelas' => $siswa->kelas?->nama_kelas,
                'bulan_tunggakan' => $tagihan->map(fn (TuitionBill $t) => $t->namaBulan())->implode(', '),
                'jumlah_tunggakan' => $tagihan->count(),
                'totalNominal' => (float) $tagihan->sum('nominal'),
            ];
        });

        return $this->render('kepsek.reports.arrears', [
            'arrears' => $rows,
            'totalAll' => (float) $rows->sum('totalNominal'),
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
