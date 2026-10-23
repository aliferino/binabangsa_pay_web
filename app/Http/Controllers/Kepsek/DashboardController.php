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