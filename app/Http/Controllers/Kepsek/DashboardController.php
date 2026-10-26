<?php

namespace App\Http\Controllers\Kepsek;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Student;
use App\Models\TuitionBill;

class DashboardController extends Controller
{
    /**
     * Dashboard kepala sekolah. Fokus pada rekap, bukan operasional harian.
     */
    public function principal()
    {
        // Tunggakan per kelas dan per jurusan dalam satu query. Kolom status dan
        // tanggal_jatuh_tempo harus diberi prefix tabel karena siswa juga punya
        // kolom status. Syaratnya sama dengan scopeOverdue().
        $arrears = TuitionBill::query()
            ->join('siswa', 'siswa.id_siswa', '=', 'tagihan_spp.id_siswa')
            ->leftJoin('kelas', 'kelas.id_kelas', '=', 'siswa.id_kelas')
            ->leftJoin('jurusan', 'jurusan.id_jurusan', '=', 'kelas.id_jurusan')
            ->where('tagihan_spp.status', '!=', TuitionBill::STATUS_LUNAS)
            ->whereDate('tagihan_spp.tanggal_jatuh_tempo', '<', today())
            ->get([
                'kelas.nama_kelas as kelas',
                'jurusan.nama_jurusan as jurusan',
                'tagihan_spp.nominal',
            ]);

        $data = [
            'activeStudents' => Student::where('status', 'aktif')->count(),
            'totalStudents' => Student::count(),
            'paidStudents' => Student::whereHas('tagihan', function ($q) {
                $q->where('status', 'lunas');
            })->count(),
            'unpaidStudents' => Student::whereHas('tagihan', function ($q) {
                $q->where('status', '!=', 'lunas');
            })->count(),
            'overdueStudents' => Student::whereHas('tagihan', function ($q) {
                $q->overdue();
            })->count(),
            'totalBills' => TuitionBill::count(),
            'totalPaid' => (float) Payment::where('status', 'berhasil')->sum('nominal_pembayaran'),
            'totalArrears' => (float) TuitionBill::overdue()->sum('nominal'),
            'totalOutstanding' => (float) TuitionBill::where('status', '!=', 'lunas')->sum('nominal'),

            // Tunggakan per kelas dan per jurusan. Satu query, dua pengelompokan.
            // Tunggakan = tagihan belum lunas yang sudah lewat jatuh tempo,
            // sama dengan scopeOverdue() yang dipakai angka totalArrears.
            'arrearsByClass' => $arrears->groupBy(fn ($baris) => $baris->kelas ?? 'Tanpa kelas')
                ->map(fn ($grup, $nama) => ['nama' => $nama, 'nominal' => (float) $grup->sum('nominal')])
                ->sortByDesc('nominal')
                ->values(),
            'arrearsByMajor' => $arrears->groupBy(fn ($baris) => $baris->jurusan ?? 'Tanpa jurusan')
                ->map(fn ($grup, $nama) => ['nama' => $nama, 'nominal' => (float) $grup->sum('nominal')])
                ->sortByDesc('nominal')
                ->values(),

            // Penerimaan per bulan, untuk melihat arus uang masuk sepanjang tahun.
            'monthlyIncome' => Payment::where('status', 'berhasil')
                ->selectRaw('YEAR(created_at) as tahun, MONTH(created_at) as bulan, sum(nominal_pembayaran) as nominal')
                ->groupBy('tahun', 'bulan')
                ->orderBy('tahun')
                ->orderBy('bulan')
                ->get()
                ->map(fn ($baris) => [
                    'bulan' => (int) $baris->bulan,
                    'tahun' => (int) $baris->tahun,
                    'nama' => TuitionBill::namaBulanDari((int) $baris->bulan),
                    'nominal' => (float) $baris->nominal,
                ]),
        ];

        return $this->render('kepsek.dashboard.index', $data);
    }
}