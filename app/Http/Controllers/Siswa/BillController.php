<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * Tagihan SPP milik siswa yang sedang login.
 *
 * Siswa hanya boleh melihat tagihan miliknya sendiri. Data diambil lewat
 * relasi dari siswa yang login, bukan dari parameter URL, supaya siswa
 * tidak bisa membuka tagihan siswa lain dengan mengubah angka di URL.
 */
class BillController extends Controller
{
    /**
     * Daftar tagihan milik siswa yang login, bisa difilter per bulan
     * dan status.
     */
    public function index(Request $request)
    {
        $siswa = auth()->user()->siswa;

        // Kalau akun belum ditautkan ke data siswa, jangan tampilkan apa pun.
        if ($siswa === null) {
            return $this->render('siswa.bills.index', [
                'siswa' => null,
                'pesan' => 'Akun ini belum ditautkan ke data siswa. Hubungi administrator.',
            ]);
        }

        $kueri = $siswa->tagihan()->with('tarif', 'tahunAjaran');

        if ($request->filled('bulan')) {
            $kueri->where('bulan', $request->input('bulan'));
        }

        if ($request->filled('status')) {
            $kueri->where('status', $request->input('status'));
        }

        return $this->render('siswa.bills.index', [
            'siswa' => $siswa,
            'bills' => $kueri->orderByDesc('tahun')->orderByDesc('bulan')->get(),
            'filter' => $request->only(['bulan', 'status']),
        ]);
    }
}
