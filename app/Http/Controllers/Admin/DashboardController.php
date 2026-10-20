<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
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
        $data = [
            'totalStudents' => Student::count(),
            'totalBills' => TuitionBill::count(),
            'totalArrears' => TuitionBill::where('status', 'tunggakan')->count(),
            'totalUnpaidStudents' => TuitionBill::where('status', 'belum_bayar')
                ->distinct('id_siswa')
                ->count('id_siswa'),
            'arrearsAmount' => (float) TuitionBill::where('status', 'tunggakan')->sum('nominal'),
        ];

        return $this->render('admin.dashboard.index', $data);
    }
}
