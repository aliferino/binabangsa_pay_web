<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;

/**
 * Virtual Account milik siswa yang sedang login.
 *
 * Siswa hanya boleh melihat VA miliknya sendiri. Data diambil lewat
 * relasi dari siswa yang login, bukan dari parameter URL.
 */
class VirtualAccountController extends Controller
{
    /**
     * Daftar Virtual Account milik siswa yang login.
     */
    public function index()
    {
        $siswa = auth()->user()->siswa;

        // Kalau akun belum ditautkan ke data siswa, jangan tampilkan apa pun.
        if ($siswa === null) {
            return $this->render('siswa.virtual-accounts.index', [
                'siswa' => null,
                'pesan' => 'Akun ini belum ditautkan ke data siswa. Hubungi administrator.',
            ]);
        }

        $va = $siswa->virtualAccount;

        return $this->render('siswa.virtual-accounts.index', [
            'siswa' => $siswa,
            'virtualAccounts' => $va === null ? collect() : collect([$va]),
        ]);
    }
}
