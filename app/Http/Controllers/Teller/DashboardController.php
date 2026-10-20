<?php

namespace App\Http\Controllers\Teller;

use App\Http\Controllers\Controller;
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
            'unpaidStudents' => TuitionBill::where('status', 'belum_bayar')
                ->distinct('id_siswa')
                ->count('id_siswa'),
        ];

        return $this->render('teller.dashboard.index', $data);
    }
}
