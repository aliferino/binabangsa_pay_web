<?php

namespace App\Http\Controllers\Kepsek;

use App\Http\Controllers\Controller;
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
            'paidStudents' => Student::whereHas('tagihan', function ($q) {
                $q->where('status', 'lunas');
            })->count(),
            'unpaidStudents' => Student::whereHas('tagihan', function ($q) {
                $q->where('status', 'belum_bayar');
            })->count(),
            'overdueStudents' => Student::whereHas('tagihan', function ($q) {
                $q->where('status', 'tunggakan');
            })->count(),
            'totalArrears' => (float) TuitionBill::where('status', 'tunggakan')->sum('nominal'),
        ];

        return $this->render('kepsek.dashboard.index', $data);
    }
}
