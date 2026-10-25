<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\PaymentGatewayTransaction;
use App\Models\TuitionBill;
use App\Models\VirtualAccount;
use App\Services\MidtransNotifier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;

/**
 * Simulasi transfer Virtual Account untuk siswa, tanpa login.
 *
 * Halaman ini pengganti langkah "siswa transfer di mobile banking" saat
 * didemokan. Uangnya tidak benar-benar berpindah: nomor VA dicari di database,
 * lalu notifikasi Midtrans yang sah dikirim ke callback kita sendiri. Jadi
 * jalur yang diuji tetap jalur produksi, bukan jalan pintas.
 *
 * Halaman ini hanya boleh hidup di mode sandbox. Kalau MIDTRANS_IS_PRODUCTION
 * bernilai true, semua akses ditolak 403.
 */
class SimulationController extends Controller
{
    /**
     * Penanda jujur untuk pembayaran hasil simulasi. Midtrans Sandbox tidak
     * punya endpoint resmi untuk menandai transaksi settlement tanpa transfer
     * nyata (yang ada hanya cancel, expire, refund, approve challenge, dan
     * status), jadi simulasi ini TIDAK pernah sampai ke dashboard Midtrans.
     * Keterangan ini yang membuatnya terlihat di data.
     */
    public const KETERANGAN_SIMULASI = 'Simulasi lokal, tidak melewati Midtrans Sandbox.';

    public function __construct()
    {
        // Penjaga sandbox dipasang sekali di sini supaya tidak ada satu pun
        // method yang bisa lolos kalau nanti ada method baru ditambahkan.
        //
        // Halaman galat 403 milik proyek memakai pesan tetap yang tidak
        // menyebut soal sandbox, jadi kerangka yang sama dirender ulang dengan
        // pesan yang jelas. Tidak ada berkas view yang diubah.
        if ((bool) config('midtrans.is_production')) {
            abort(response()->view('errors._layout', [
                'kode' => '403',
                'judul' => 'Simulasi dinonaktifkan',
                'pesan' => 'Simulasi hanya tersedia di mode sandbox.',
                'saran' => 'Hubungi admin sekolah kalau simulasi perlu dihidupkan.',
            ], 403));
        }
    }

    /**
     * Halaman awal: daftar Virtual Account aktif yang bisa dipilih.
     */
    public function index(): View|JsonResponse
    {
        $daftarVa = VirtualAccount::with('student.kelas')
            ->where('status', 'aktif')
            ->orderBy('nomor_va')
            ->limit(10)
            ->get();

        return $this->render('public.simulation.index', [
            'daftarVa' => $daftarVa,
        ]);
    }

    /**
     * Cari Virtual Account dari nomor yang diketik.
     */
    public function cari(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nomor_va' => ['required', 'string', 'max:40'],
        ], [
            'nomor_va.required' => 'Nomor Virtual Account wajib diisi.',
            'nomor_va.max' => 'Nomor Virtual Account terlalu panjang.',
        ]);

        $va = $this->cariVa(trim($data['nomor_va']));

        if ($va === null) {
            return back()->withInput()->with('gagal', 'Nomor Virtual Account tidak ditemukan.');
        }

        return redirect()->route('simulasi.detail', $this->nomorTampil($va));
    }

    /**
     * Halaman transfer: tagihan tertua yang belum lunas plus riwayat singkat.
     */
    public function detail(string $nomorVa): View|JsonResponse
    {
        $va = $this->cariVa($nomorVa);

        if ($va === null) {
            abort(404, 'Virtual Account tidak ditemukan.');
        }

        $tagihan = $this->tagihanTertua($va);
        $siswa = $va->student;

        return $this->render('public.simulation.detail', [
            'va' => (object) [
                'nomor' => $this->nomorTampil($va),
                'nama' => $siswa?->nama ?? 'Tanpa nama',
                'nis' => $siswa?->nis ?? '-',
                'kelas' => $siswa?->kelas?->nama_kelas ?? '-',
            ],
            'tagihan' => $tagihan,
            'nominal' => $tagihan === null ? 0 : (float) $tagihan->nominal,
            'riwayat' => $this->riwayat($va),
        ]);
    }

    /**
     * Jalankan pembayaran tiruan.
     *
     * Langkahnya: siapkan transaksi pending, susun notifikasi Midtrans yang
     * sah, kirim ke callback sendiri, lalu baca hasilnya.
     */
    public function bayar(string $nomorVa, Request $request): RedirectResponse
    {
        $serverKey = (string) config('midtrans.server_key');

        if ($serverKey === '') {
            return back()->with('gagal', 'Server key Midtrans belum diisi, simulasi tidak bisa dijalankan.');
        }

        $va = $this->cariVa($nomorVa);

        if ($va === null) {
            abort(404, 'Virtual Account tidak ditemukan.');
        }

        $data = $request->validate([
            'nominal' => ['required', 'numeric', 'min:1'],
        ], [
            'nominal.required' => 'Nominal transfer wajib diisi.',
            'nominal.numeric' => 'Nominal transfer harus berupa angka.',
            'nominal.min' => 'Nominal transfer minimal 1 rupiah.',
        ]);

        $tagihan = $this->tagihanTertua($va);

        if ($tagihan === null) {
            return back()->with('gagal', 'Semua tagihan siswa ini sudah lunas.');
        }

        $nominal = (float) $data['nominal'];

        $transaksi = PaymentGatewayTransaction::where('virtual_account_id', $va->id)
            ->where('status_transaksi', 'pending')
            ->latest('id')
            ->first();

        if ($transaksi === null) {
            // Transaksi ini TIDAK pernah dikirim ke Midtrans. Nomor berawalan
            // SIM- dan channel SIMULASI jadi penandanya, supaya tidak tertukar
            // dengan transaksi asli kalau nanti ada rekonsiliasi.
            $transaksi = PaymentGatewayTransaction::create([
                'transaction_id' => 'SIM-'.($va->account_number ?: $va->nomor_va).'-'.now()->format('YmdHis'),
                'virtual_account_id' => $va->id,
                'siswa_id' => $va->siswa_id,
                'nominal' => $nominal,
                'status_transaksi' => 'pending',
                'payment_channel' => 'SIMULASI',
                'reference_number' => $va->account_number ?: $va->nomor_va,
            ]);
        }

        $payload = MidtransNotifier::payload($transaksi, $nominal, $serverKey, self::KETERANGAN_SIMULASI);
        $hasil = MidtransNotifier::kirim($payload);

        $pesan = (string) ($hasil['body']['pesan'] ?? '');

        if (($hasil['body']['dibuat'] ?? false) === true) {
            return redirect()->route('simulasi.sukses', (string) $hasil['body']['nomor_transaksi']);
        }

        // Notifikasi kembar tetap berarti tagihan sudah lunas, jadi tetap
        // diantar ke halaman bukti memakai pembayaran yang sudah tercatat.
        if (str_contains($pesan, 'sudah pernah diproses')) {
            $sudah = Payment::where('transaksi_id', $transaksi->id)->first();

            if ($sudah !== null) {
                return redirect()->route('simulasi.sukses', $sudah->nomor_transaksi);
            }
        }

        return back()->with('gagal', $pesan === ''
            ? 'Simulasi gagal. Callback membalas status '.$hasil['status'].'.'
            : 'Simulasi gagal. '.$pesan);
    }

    /**
     * Bukti pembayaran setelah simulasi berhasil.
     */
    public function sukses(string $nomorTransaksi): View|JsonResponse
    {
        $pembayaran = Payment::with('student')->where('nomor_transaksi', $nomorTransaksi)->first();

        if ($pembayaran === null) {
            abort(404, 'Pembayaran tidak ditemukan.');
        }

        return $this->render('public.simulation.success', [
            'simulasiLokal' => true,
            'pembayaran' => (object) [
                'nomor_transaksi' => $pembayaran->nomor_transaksi,
                'nama' => $pembayaran->student?->nama ?? 'Tanpa nama',
                'nominal' => (float) $pembayaran->nominal_pembayaran,
                'waktu' => $pembayaran->paidAt(),
                'status' => $pembayaran->status,
            ],
        ]);
    }

    /**
     * Nomor yang ditampilkan ke siswa.
     *
     * account_number bisa ada tapi kosong (VA lama belum pernah diurus ke
     * Midtrans), jadi kosong diperlakukan sama seperti tidak ada.
     */
    private function nomorTampil(VirtualAccount $va): string
    {
        return $va->nomorTampil();
    }

    /**
     * Cari Virtual Account dari nomor tampil atau nomor internal.
     *
     * Dua kolom dicari supaya siswa bisa mengetik nomor apa pun yang tertera
     * di kartu VA, tanpa perlu tahu mana yang internal.
     */
    private function cariVa(string $nomor): ?VirtualAccount
    {
        // Siswa sering mengetik nomor sesuai yang tercetak di kartu VA,
        // lengkap dengan spasinya: "7770 1001 2345". Spasi dibuang dulu.
        $nomor = preg_replace('/[^0-9]/', '', $nomor);

        if ($nomor === '') {
            return null;
        }

        return VirtualAccount::with('student.kelas')
            ->where('account_number', $nomor)
            ->orWhere('nomor_va', $nomor)
            ->first();
    }

    /**
     * Tagihan belum lunas paling tua milik siswa pemilik VA ini.
     */
    private function tagihanTertua(VirtualAccount $va): ?TuitionBill
    {
        return TuitionBill::where('id_siswa', $va->siswa_id)
            ->where('status', '!=', TuitionBill::STATUS_LUNAS)
            ->orderBy('tahun')
            ->orderBy('bulan')
            ->first();
    }

    /**
     * Tiga pembayaran terakhir siswa pemilik VA ini.
     *
     * @return \Illuminate\Support\Collection<int, object>
     */
    private function riwayat(VirtualAccount $va)
    {
        return Payment::where('siswa_id', $va->siswa_id)
            ->latest('id')
            ->limit(3)
            ->get()
            ->map(fn (Payment $p) => (object) [
                'tanggal' => $p->paidAt(),
                'nominal' => (float) $p->nominal_pembayaran,
                'status' => $p->status,
            ])
            ->values();
    }

}
