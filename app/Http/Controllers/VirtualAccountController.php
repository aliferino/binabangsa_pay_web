<?php

namespace App\Http\Controllers;

use App\Exceptions\XenditException;
use App\Models\Siswa;
use App\Models\VirtualAccount;
use App\Services\XenditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

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

    /**
     * Buat Virtual Account di Xendit untuk satu siswa, lalu simpan hasilnya.
     *
     * Alurnya sengaja dibuat dua tahap yang jelas:
     * 1. Siapkan atau ambil baris VA internal lebih dulu, karena nomor internal
     *    itulah yang dipakai sebagai external_id ke Xendit.
     * 2. Baru panggil Xendit. Kalau panggilan gagal, tidak ada satu kolom pun
     *    yang diubah, sehingga tidak ada data setengah jadi.
     *
     * Ditolak kalau siswa ini sudah punya VA yang terhubung ke Xendit, supaya
     * satu siswa tidak punya dua nomor bank berbeda.
     */
    public function buatXendit(Request $request, Siswa $siswa, XenditService $xendit): RedirectResponse
    {
        $data = $request->validate([
            'bank_code' => ['required', 'string', 'max:20'],
            'expected_amount' => ['nullable', 'numeric', 'gt:0'],
            'berlaku_hari' => ['nullable', 'integer', 'min:1', 'max:365'],
            'description' => ['nullable', 'string', 'max:255'],
        ], [
            'bank_code.required' => 'Kode bank wajib dipilih.',
            'bank_code.max' => 'Kode bank terlalu panjang.',
            'expected_amount.numeric' => 'Nominal tagihan harus berupa angka.',
            'expected_amount.gt' => 'Nominal tagihan harus lebih besar dari nol.',
            'berlaku_hari.integer' => 'Masa berlaku harus berupa angka hari.',
            'berlaku_hari.min' => 'Masa berlaku minimal 1 hari.',
            'berlaku_hari.max' => 'Masa berlaku maksimal 365 hari.',
        ]);

        $va = VirtualAccount::where('siswa_id', $siswa->id_siswa)->first();

        if ($va !== null && $va->xendit_id !== null) {
            return back()->withInput()->withErrors([
                'bank_code' => 'Siswa ini sudah punya Virtual Account Xendit dengan nomor '
                    .$va->account_number.'. Satu siswa hanya boleh punya satu.',
            ]);
        }

        $nomorInternal = $va?->nomor_va ?? VirtualAccount::generateNomorVa($siswa->nis);

        if ($va === null) {
            $dipakai = VirtualAccount::where('nomor_va', $nomorInternal)->exists();

            if ($dipakai) {
                return back()->withInput()->withErrors([
                    'bank_code' => 'Nomor VA internal '.$nomorInternal.' sudah dipakai siswa lain. '
                        .'Lima digit terakhir NIS siswa ini bentrok, jadi nomornya perlu ditentukan manual.',
                ]);
            }
        }

        $hari = (int) ($data['berlaku_hari'] ?? 30);

        $permintaan = [
            'external_id' => $nomorInternal,
            'bank_code' => strtoupper($data['bank_code']),
            'name' => $siswa->nama,
            'is_closed' => true,
            'is_single_use' => true,
            'expiration_date' => Carbon::now()->addDays($hari)->toIso8601String(),
        ];

        if (isset($data['expected_amount'])) {
            $permintaan['expected_amount'] = (float) $data['expected_amount'];
        }

        if (! empty($data['description'])) {
            $permintaan['description'] = $data['description'];
        }

        try {
            $balasan = $xendit->createVirtualAccount($permintaan);
        } catch (XenditException $e) {
            Log::warning('Gagal membuat VA Xendit', [
                'siswa_id' => $siswa->id_siswa,
                'bank_code' => $permintaan['bank_code'],
                'pesan' => $e->getMessage(),
            ]);

            return back()->withInput()->withErrors([
                'bank_code' => 'Xendit menolak pembuatan Virtual Account. Tidak ada data yang disimpan. '
                    .'Pesan dari Xendit: '.$e->getMessage(),
            ]);
        }

        $va ??= new VirtualAccount([
            'siswa_id' => $siswa->id_siswa,
            'nomor_va' => $nomorInternal,
            'prefix' => '880',
            'status' => 'aktif',
        ]);

        $va->fill([
            'xendit_id' => $balasan['id'] ?? null,
            'account_number' => $balasan['account_number'] ?? null,
            'bank_code' => $balasan['bank_code'] ?? $permintaan['bank_code'],
            'external_id' => $balasan['external_id'] ?? $nomorInternal,
            'expired_at' => $this->bacaWaktu($balasan['expiration_date'] ?? null),
        ]);

        $va->save();

        return redirect()->route('virtual-account.show', $va->id)
            ->with('sukses', 'Virtual Account Xendit '.$va->account_number
                .' ('.$va->bank_code.') berhasil dibuat untuk '.$siswa->nama.'.');
    }

    /**
     * Picu pembayaran tiruan ke Xendit. Hanya untuk mode test.
     *
     * Cara memastikan mode test: Xendit menolak sendiri endpoint ini di mode
     * production, dan pesan penolakannya diteruskan apa adanya. Aku tidak
     * menebak dari bentuk kunci API, karena bentuk kunci bisa berubah.
     *
     * Simulasi tidak langsung menandai tagihan lunas. Yang menandai lunas tetap
     * callback resmi dari Xendit, supaya jalur yang diuji sama dengan jalur
     * sungguhan.
     */
    public function simulasi(VirtualAccount $virtualAccount, XenditService $xendit): RedirectResponse
    {
        if ($virtualAccount->xendit_id === null || $virtualAccount->external_id === null) {
            return redirect()->route('virtual-account.show', $virtualAccount->id)
                ->with('gagal', 'Virtual Account ini belum terhubung ke Xendit, jadi tidak bisa disimulasikan.');
        }

        $tagihan = $virtualAccount->siswa
            ?->tagihan()
            ->whereIn('status', ['belum_bayar', 'tunggakan'])
            ->orderBy('tahun')
            ->orderBy('bulan')
            ->first();

        $nominal = $tagihan !== null
            ? (float) $tagihan->nominal
            : (float) ($virtualAccount->siswa?->tagihan()->value('nominal') ?? 0);

        if ($nominal <= 0) {
            return redirect()->route('virtual-account.show', $virtualAccount->id)
                ->with('gagal', 'Tidak ada tagihan yang bisa dijadikan nominal simulasi.');
        }

        try {
            $balasan = $xendit->simulatePayment($virtualAccount->external_id, $nominal);
        } catch (XenditException $e) {
            Log::warning('Simulasi pembayaran Xendit gagal', [
                'virtual_account_id' => $virtualAccount->id,
                'pesan' => $e->getMessage(),
            ]);

            return redirect()->route('virtual-account.show', $virtualAccount->id)
                ->with('gagal', 'Simulasi gagal. Fitur ini hanya tersedia di mode test Xendit. '
                    .'Pesan dari Xendit: '.$e->getMessage());
        }

        return redirect()->route('virtual-account.show', $virtualAccount->id)
            ->with('sukses', 'Simulasi pembayaran Rp'.number_format($nominal, 0, ',', '.')
                .' dikirim ke Xendit. Status tagihan berubah setelah callback masuk. '
                .'Balasan: '.json_encode($balasan));
    }

    /**
     * Baca waktu dari balasan Xendit dengan aman.
     *
     * Kalau bentuknya tidak dikenali, kembalikan null daripada melempar error.
     * Kolomnya boleh kosong, jadi data yang sudah tersimpan tidak perlu batal
     * hanya karena format tanggalnya berbeda.
     */
    private function bacaWaktu(?string $nilai): ?Carbon
    {
        if ($nilai === null || trim($nilai) === '') {
            return null;
        }

        try {
            return Carbon::parse($nilai);
        } catch (\Throwable) {
            return null;
        }
    }
}
