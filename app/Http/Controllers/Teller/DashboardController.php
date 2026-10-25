<?php

namespace App\Http\Controllers\Teller;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\PaymentGatewayTransaction;
use App\Models\TuitionBill;

class DashboardController extends Controller
{
    /**
     * Dashboard teller. Fokus pada tagihan yang jatuh tempo hari ini.
     */
    public function teller()
    {
        $today = now()->toDateString();

        $data = [
            'billsDueToday' => TuitionBill::whereDate('tanggal_jatuh_tempo', $today)->count(),
            'unpaidStudents' => TuitionBill::where('status', '!=', TuitionBill::STATUS_LUNAS)
                ->distinct('id_siswa')
                ->count('id_siswa'),
            'overdueBills' => TuitionBill::overdue()->count(),
            'todayPayments' => Payment::where('status', 'berhasil')->whereDate('created_at', today())->count(),
            'todayAmount' => (float) Payment::where('status', 'berhasil')->whereDate('created_at', today())->sum('nominal_pembayaran'),
            'todayTransactions' => PaymentGatewayTransaction::whereDate('created_at', today())->count(),

            // Tagihan terdekat jatuh tempo yang belum dibayar, untuk ditindaklanjuti.
            'dueSoon' => TuitionBill::with('student')
                ->where('status', '!=', 'lunas')
                ->orderBy('tanggal_jatuh_tempo')
                ->limit(6)
                ->get(),

            'recentPayments' => Payment::with('student')->where('status', 'berhasil')->latest('id')->limit(5)->get(),
        ];

        return $this->render('teller.dashboard.index', $data);
    }
}