<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\PaymentGatewayTransaction;
use App\Models\Student;
use App\Models\TuitionBill;
use Illuminate\Http\RedirectResponse;

class DashboardController extends Controller
{
    /**
     * Arahkan user ke dashboard sesuai role-nya.
     */
    public function index(): RedirectResponse
    {
        return match (auth()->user()->role) {
            'admin' => redirect()->route('dashboard.admin'),
            'kepala_sekolah' => redirect()->route('dashboard.kepala-sekolah'),
            'teller' => redirect()->route('dashboard.teller'),
            'siswa' => redirect()->route('dashboard.siswa'),
            default => redirect()->route('login'),
        };
    }

    /**
     * Dashboard admin. Menampilkan ringkasan seluruh sekolah.
     */
    public function admin()
    {
        // Grafik tagihan per bulan. Dikelompokkan di query, bukan di view,
        // supaya view tinggal menggambar.
        $perBulan = TuitionBill::selectRaw('bulan, tahun, status, count(*) as jumlah, sum(nominal) as nominal')
            ->groupBy('bulan', 'tahun', 'status')
            ->orderBy('tahun')
            ->orderBy('bulan')
            ->get()
            ->groupBy(fn ($baris) => $baris->tahun.'-'.str_pad($baris->bulan, 2, '0', STR_PAD_LEFT));

        $grafik = $perBulan->map(function ($grup, $kunci) {
            [$tahun, $bulan] = explode('-', $kunci);

            return [
                'bulan' => (int) $bulan,
                'tahun' => (int) $tahun,
                'nama' => TuitionBill::namaBulanDari((int) $bulan),
                'lunas' => (float) $grup->where('status', 'lunas')->sum('nominal'),
                'belum' => (float) $grup->where('status', '!=', 'lunas')->sum('nominal'),
            ];
        })->values();

        $data = [
            'totalStudents' => Student::count(),
            'totalBills' => TuitionBill::count(),
            'totalArrears' => TuitionBill::overdue()->count(),
            'totalUnpaidStudents' => TuitionBill::where('status', 'belum_bayar')
                ->distinct('id_siswa')
                ->count('id_siswa'),
            'arrearsAmount' => (float) TuitionBill::overdue()->sum('nominal'),
            'totalOutstanding' => (float) TuitionBill::where('status', '!=', 'lunas')->sum('nominal'),
            'totalPaid' => (float) Payment::where('status', 'berhasil')->sum('nominal_pembayaran'),
            'paymentCount' => Payment::where('status', 'berhasil')->count(),
            'todayTransactions' => PaymentGatewayTransaction::whereDate('created_at', today())->count(),
            'monthlyChart' => $grafik,
            'recentPayments' => Payment::with('student')->latest('id')->limit(5)->get(),
        ];

        return $this->render('admin.dashboard.index', $data);
    }
}
