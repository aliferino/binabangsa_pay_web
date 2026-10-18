<?php

namespace App\Http\Controllers;

use App\Models\Siswa;
use App\Models\VirtualAccount;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class VirtualAccountController extends Controller
{
    /**
     * Daftar Virtual Account, bisa difilter per siswa dan per status.
     */
    public function index(Request $request)
    {
        $kueri = VirtualAccount::with('siswa.kelas');

        if ($request->filled('siswa_id')) {
            $kueri->where('siswa_id', $request->input('siswa_id'));
        }

        if ($request->filled('status')) {
            $kueri->where('status', $request->input('status'));
        }

        return $this->tampilkan('virtual-account.index', [
            'daftar_va' => $kueri->orderByDesc('id')->get(),
            'filter' => $request->only(['siswa_id', 'status']),
            'daftar_siswa' => Siswa::orderBy('nama')->get(),
        ]);
    }

    /**
     * Detail satu Virtual Account beserta transaksi gateway-nya.
     */
    public function show(VirtualAccount $virtualAccount)
    {
        return $this->tampilkan('virtual-account.show', [
            'va' => $virtualAccount->load('siswa.kelas', 'transaksi'),
        ]);
    }

    /**
     * Buat Virtual Account untuk satu siswa.
     *
     * Ditolak kalau siswa sudah punya VA, karena aturannya satu siswa hanya
     * satu VA. Ditolak juga kalau nomor hasil susunan sudah dipakai siswa
     * lain, karena nomor VA wajib unik. Kasus kedua bisa terjadi kalau dua
     * NIS berbeda punya 5 digit terakhir yang sama.
     */
    public function generate(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'siswa_id' => ['required', 'exists:siswa,id_siswa'],
        ], [
            'siswa_id.required' => 'Siswa wajib dipilih.',
            'siswa_id.exists' => 'Siswa yang dipilih tidak ditemukan.',
        ]);

        $siswa = Siswa::findOrFail($data['siswa_id']);

        $sudahAda = VirtualAccount::where('siswa_id', $siswa->id_siswa)->exists();

        if ($sudahAda) {
            return back()
                ->withInput()
                ->withErrors(['siswa_id' => 'Siswa ini sudah punya Virtual Account.']);
        }

        $nomor = VirtualAccount::generateNomorVa($siswa->nis);

        $dipakai = VirtualAccount::where('nomor_va', $nomor)->exists();

        if ($dipakai) {
            return back()
                ->withInput()
                ->withErrors([
                    'siswa_id' => 'Nomor VA '.$nomor.' sudah dipakai siswa lain. '
                        .'Lima digit terakhir NIS siswa ini bentrok, jadi nomornya perlu ditentukan manual.',
                ]);
        }

        VirtualAccount::create([
            'siswa_id' => $siswa->id_siswa,
            'nomor_va' => $nomor,
            'prefix' => '880',
            'status' => 'aktif',
        ]);

        return redirect()->route('virtual-account.index')
            ->with('sukses', 'Virtual Account '.$nomor.' berhasil dibuat untuk '.$siswa->nama.'.');
    }

    /**
     * Nonaktifkan satu Virtual Account.
     *
     * Barisnya tidak dihapus, hanya statusnya diubah, supaya riwayat
     * transaksi yang menunjuk ke VA ini tetap utuh.
     */
    public function nonaktifkan(VirtualAccount $virtualAccount): RedirectResponse
    {
        if (! $virtualAccount->aktif()) {
            return redirect()->route('virtual-account.index')
                ->with('gagal', 'Virtual Account ini sudah nonaktif.');
        }

        $virtualAccount->update(['status' => 'nonaktif']);

        return redirect()->route('virtual-account.index')
            ->with('sukses', 'Virtual Account '.$virtualAccount->nomor_va.' dinonaktifkan.');
    }

    /**
     * Aktifkan kembali Virtual Account yang nonaktif.
     *
     * Tanpa ini, menonaktifkan VA jadi jalan buntu: nomornya tidak bisa
     * dibuat ulang karena satu siswa hanya boleh punya satu baris VA.
     */
    public function aktifkan(VirtualAccount $virtualAccount): RedirectResponse
    {
        if ($virtualAccount->aktif()) {
            return redirect()->route('virtual-account.index')
                ->with('gagal', 'Virtual Account ini sudah aktif.');
        }

        $virtualAccount->update(['status' => 'aktif']);

        return redirect()->route('virtual-account.index')
            ->with('sukses', 'Virtual Account '.$virtualAccount->nomor_va.' diaktifkan kembali.');
    }
}
