<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    /**
     * Dashboard siswa. Hanya menampilkan data milik siswa yang sedang login.
     */
    public function student()
    {
        $student = auth()->user()->siswa;

        // Kalau akun siswa belum ditautkan ke data siswa, jangan tampilkan apa pun.
        if ($student === null) {
            return $this->render('dashboard.siswa', [
                'siswa' => null,
                'pesan' => 'Akun ini belum ditautkan ke data siswa. Hubungi administrator.',
            ]);
        }

        $data = [
            'siswa' => $student->load('kelas.jurusan', 'tahunAjaran'),
            'bills' => $student->tagihan()->orderByDesc('tahun')->orderByDesc('bulan')->get(),
            'totalArrears' => $student->totalTunggakan(),
        ];

        return $this->render('dashboard.siswa', $data);
    }
}
