<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\Request;

/**
 * Riwayat pembayaran milik siswa yang sedang login.
 *
 * Siswa hanya boleh melihat pembayaran miliknya sendiri. Method show
 * wajib memeriksa kepemilikan, supaya siswa tidak bisa membuka data
 * pembayaran siswa lain dengan mengubah angka di URL.
 */
class PaymentController extends Controller
{
    /**
     * Riwayat pembayaran milik siswa yang login.
     */
    public function index(Request $request)
    {
        $siswa = auth()->user()->siswa;

        // Kalau akun belum ditautkan ke data siswa, jangan tampilkan apa pun.
        if ($siswa === null) {
            return $this->render('siswa.payments.index', [
                'siswa' => null,
                'pesan' => 'Akun ini belum ditautkan ke data siswa. Hubungi administrator.',
            ]);
        }

        $kueri = Payment::with('tagihanSpp', 'user', 'transaksi')
            ->where('siswa_id', $siswa->id_siswa);

        if ($request->filled('status')) {
            $kueri->where('status', $request->input('status'));
        }

        if ($request->filled('metode_pembayaran')) {
            $kueri->where('metode_pembayaran', $request->input('metode_pembayaran'));
        }

        return $this->render('siswa.payments.index', [
            'siswa' => $siswa,
            'payments' => $kueri->orderByDesc('id')->get(),
            'filter' => $request->only(['status', 'metode_pembayaran']),
        ]);
    }

    /**
     * Detail satu pembayaran.
     *
     * Wajib periksa kepemilikan: kalau pembayaran ini bukan milik siswa
     * yang login, tolak dengan 403. Tanpa pemeriksaan ini, siswa bisa
     * membuka data siswa lain dengan mengubah angka di URL.
     */
    public function show(Payment $pembayaran)
    {
        $siswa = auth()->user()->siswa;

        if ($siswa === null || $pembayaran->siswa_id !== $siswa->id_siswa) {
            abort(403);
        }

        return $this->render('siswa.payments.show', [
            'siswa' => $siswa,
            'pembayaran' => $pembayaran->load('tagihanSpp', 'user', 'transaksi'),
        ]);
    }
}
