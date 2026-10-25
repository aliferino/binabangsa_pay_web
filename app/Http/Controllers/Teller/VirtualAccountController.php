<?php

namespace App\Http\Controllers\Teller;

use App\Http\Controllers\Controller;
use App\Exceptions\XenditException;
use App\Exceptions\MidtransException;
use App\Models\PaymentGatewayTransaction;
use App\Models\Student;
use App\Models\TuitionBill;
use App\Models\VirtualAccount;
use App\Services\MidtransService;
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

        return $this->render('teller.virtual-accounts.index', [
            'virtualAccounts' => $kueri->orderByDesc('id')->get(),
            'filter' => $request->only(['siswa_id', 'status']),
            'students' => Student::orderBy('nama')->get(),
        ]);
    }

    /**
     * Detail satu Virtual Account beserta transaksi gateway-nya.
     */
    public function show(VirtualAccount $virtualAccount)
    {
        return $this->render('teller.virtual-accounts.show', [
            'virtualAccount' => $virtualAccount->load('siswa.kelas', 'transaksi'),
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

        $siswa = Student::findOrFail($data['siswa_id']);

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
    public function deactivate(VirtualAccount $virtualAccount): RedirectResponse
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
    public function activate(VirtualAccount $virtualAccount): RedirectResponse
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
    public function createXendit(Request $request, Student $siswa, XenditService $xendit): RedirectResponse
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
     * Buat Virtual Account Midtrans untuk satu siswa, lalu simpan hasilnya.
     *
     * Aturan satu VA aktif per siswa dipegang di sini. Midtrans diam-diam
     * mengganti nomor kalau nomor yang kita minta sedang dipakai transaksi
     * lain, dan nomor pengganti itu tidak berakhiran NIS, jadi tidak bisa
     * dipakai siswa. Karena itu:
     *
     * 1. Kalau siswa ini masih punya transaksi pending, transaksinya DIPAKAI
     *    ULANG. Tidak ada charge baru, supaya nomornya tidak berubah dan kuota
     *    pembuatan tidak terbuang.
     * 2. Sesudah charge, nomor hasil wajib berakhiran '00' + lima digit NIS.
     *    Kalau tidak, transaksi ditandai gagal dan tidak ditampilkan ke siswa.
     */
    public function createMidtrans(
        Request $request,
        Student $siswa,
        MidtransService $midtrans
    ): RedirectResponse {
        $data = $request->validate([
            'bank' => ['required', 'string', 'max:20'],
            'nominal' => ['nullable', 'numeric', 'gt:0'],
        ], [
            'bank.required' => 'Bank wajib dipilih.',
            'bank.max' => 'Kode bank terlalu panjang.',
            'nominal.numeric' => 'Nominal tagihan harus berupa angka.',
            'nominal.gt' => 'Nominal tagihan harus lebih besar dari nol.',
        ]);

        $va = VirtualAccount::where('siswa_id', $siswa->id_siswa)->first();

        // Aturan satu VA aktif per siswa: pakai ulang transaksi pending.
        $pending = PaymentGatewayTransaction::where('siswa_id', $siswa->id_siswa)
            ->where('status_transaksi', 'pending')
            ->latest('id')
            ->first();

        if ($pending !== null) {
            $nomorPending = $pending->reference_number;

            if ($va !== null && $nomorPending !== null) {
                return redirect()->route('virtual-account.show', $va->id)
                    ->with('sukses', 'Siswa ini masih punya Virtual Account aktif '
                        .$nomorPending.'. Nomor itu dipakai ulang, tidak dibuat yang baru.');
            }
        }

        $nomorKirim = $va?->nomor_va ?? VirtualAccount::generateNomorVa($siswa->nis);

        if ($va === null) {
            $dipakai = VirtualAccount::where('nomor_va', $nomorKirim)->exists();

            if ($dipakai) {
                return back()->withInput()->withErrors([
                    'bank' => 'Nomor VA '.$nomorKirim.' sudah dipakai siswa lain. '
                        .'Lima digit terakhir NIS siswa ini bentrok, jadi nomornya perlu ditentukan manual.',
                ]);
            }
        }

        $tagihan = TuitionBill::where('id_siswa', $siswa->id_siswa)
            ->whereIn('status', ['belum_bayar', 'tunggakan'])
            ->orderBy('tahun')
            ->orderBy('bulan')
            ->first();

        $nominal = (float) ($data['nominal']
            ?? $tagihan?->nominal
            ?? 0);

        if ($nominal <= 0) {
            return back()->withInput()->withErrors([
                'nominal' => 'Tidak ada tagihan yang bisa dijadikan nominal Virtual Account.',
            ]);
        }

        $orderId = 'MT-'.$siswa->nis.'-'.now()->format('YmdHis');

        try {
            $balasan = $midtrans->charge([
                'order_id' => $orderId,
                'gross_amount' => $nominal,
                'bank' => $data['bank'],
                'va_number' => $nomorKirim,
                'first_name' => $siswa->nama,
                'email' => null,
            ]);
        } catch (MidtransException $e) {
            Log::warning('Gagal membuat VA Midtrans', [
                'siswa_id' => $siswa->id_siswa,
                'bank' => $data['bank'],
                'pesan' => $e->getMessage(),
            ]);

            return back()->withInput()->withErrors([
                'bank' => 'Midtrans menolak pembuatan Virtual Account. Tidak ada data yang disimpan. '
                    .'Pesan dari Midtrans: '.$e->getMessage(),
            ]);
        }

        $nomorHasil = $midtrans->ambilNomorVa($balasan);

        // Nomor hasil wajib berakhiran nomor yang kita kirim. Kalau Midtrans
        // menggantinya, transaksi ini tidak berguna bagi siswa.
        if ($nomorHasil === null || ! str_ends_with($nomorHasil, $nomorKirim)) {
            PaymentGatewayTransaction::updateOrCreate(
                ['transaction_id' => $orderId],
                [
                    'virtual_account_id' => $va?->id,
                    'siswa_id' => $siswa->id_siswa,
                    'nominal' => $nominal,
                    'status_transaksi' => 'gagal',
                    'payment_channel' => strtoupper($data['bank']),
                    'reference_number' => $nomorHasil,
                    'raw_response' => $balasan,
                ]
            );

            Log::warning('Midtrans mengganti nomor VA, transaksi dibatalkan', [
                'siswa_id' => $siswa->id_siswa,
                'nomor_kirim' => $nomorKirim,
                'nomor_hasil' => $nomorHasil,
            ]);

            return back()->withInput()->withErrors([
                'bank' => 'Midtrans mengganti nomor Virtual Account, jadi nomor itu tidak berakhiran NIS. '
                    .'Transaksi dibatalkan, coba lagi beberapa saat lagi.',
            ]);
        }

        $va ??= new VirtualAccount([
            'siswa_id' => $siswa->id_siswa,
            'nomor_va' => $nomorKirim,
            'status' => 'aktif',
        ]);

        $va->fill([
            'prefix' => substr($nomorHasil, 0, strlen($nomorHasil) - strlen($nomorKirim)),
            'account_number' => $nomorHasil,
            'bank_code' => strtoupper($data['bank']),
            'external_id' => $orderId,
            'expired_at' => $this->bacaWaktu($balasan['expiry_time'] ?? null),
        ]);

        $va->save();

        PaymentGatewayTransaction::updateOrCreate(
            ['transaction_id' => $orderId],
            [
                'virtual_account_id' => $va->id,
                'siswa_id' => $siswa->id_siswa,
                'nominal' => $nominal,
                'status_transaksi' => $this->mapStatusMidtrans(
                    (string) ($balasan['transaction_status'] ?? 'pending')
                ),
                'payment_channel' => strtoupper($data['bank']),
                'reference_number' => $nomorHasil,
                'raw_response' => $balasan,
            ]
        );

        return redirect()->route('virtual-account.show', $va->id)
            ->with('sukses', 'Virtual Account Midtrans '.$nomorHasil
                .' berhasil dibuat untuk '.$siswa->nama.'.');
    }

    /**
     * Peta status transaksi Midtrans ke status yang dipakai tabel.
     */
    private function mapStatusMidtrans(string $status): string
    {
        return match (strtolower(trim($status))) {
            'settlement', 'capture' => 'berhasil',
            'expire' => 'kedaluwarsa',
            'cancel', 'deny', 'failure' => 'gagal',
            default => 'pending',
        };
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
