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
            return $this->render('siswa.dashboard.index', [
                'siswa' => null,
                'pesan' => 'Akun ini belum ditautkan ke data siswa. Hubungi administrator.',
            ]);
        }

        $tagihan = $student->tagihan()->orderByDesc('tahun')->orderByDesc('bulan')->get();
        $virtualAccount = $student->virtualAccount;

        // Tagihan terdekat yang belum dibayar, supaya siswa tahu apa yang harus dilunasi dulu.
        // Yang paling mendesak: yang sudah lewat jatuh tempo dulu, baru yang belum.
        $berikutnya = $tagihan->first(fn ($t) => $t->status !== TuitionBill::STATUS_LUNAS && $t->tanggal_jatuh_tempo?->isPast())
            ?? $tagihan->first(fn ($t) => $t->status !== TuitionBill::STATUS_LUNAS);

        $belumLunas = $tagihan->where('status', '!=', 'lunas');
        $totalTagihan = (float) $tagihan->sum('nominal');
        $sudahDibayar = (float) $tagihan->where('status', 'lunas')->sum('nominal');

        // Berapa hari lewat jatuh tempo, dipakai untuk menegaskan mana yang paling mendesak.
        $telat = 0;
        if ($berikutnya?->tanggal_jatuh_tempo?->isPast()) {
            $telat = (int) $berikutnya->tanggal_jatuh_tempo->diffInDays(now());
        }

        $data = [
            'siswa' => $student->load('kelas.jurusan', 'tahunAjaran'),
            'bills' => $tagihan,
            'totalArrears' => $student->totalTunggakan(),
            'totalOutstanding' => (float) $belumLunas->sum('nominal'),
            'totalBilled' => $totalTagihan,
            'totalPaid' => $sudahDibayar,
            'percentPaid' => $totalTagihan > 0 ? (int) round($sudahDibayar / $totalTagihan * 100) : 0,
            'totalBills' => $tagihan->count(),
            'unpaidCount' => $belumLunas->count(),
            'paidCount' => $tagihan->where('status', 'lunas')->count(),
            'nextBill' => $berikutnya,
            'daysLate' => $telat,
            'virtualAccount' => $virtualAccount,
        ];

        return $this->render('siswa.dashboard.index', $data);
    }
}