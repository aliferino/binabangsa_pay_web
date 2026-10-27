<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\PaymentGatewayTransaction;
use App\Models\TuitionBill;
use App\Models\VirtualAccount;
use App\Services\MockGatewayService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

/**
 * Simulasi transfer Virtual Account untuk siswa, tanpa login.
 *
 * Halaman ini pengganti langkah "siswa transfer di mobile banking" saat
 * didemokan. Uangnya tidak benar-benar berpindah: nomor VA dicari di database,
 * lalu notifikasi mock gateway lokal yang sah dikirim ke callback kita sendiri.
 * Jadi jalur yang diuji tetap jalur produksi, bukan jalan pintas, dan tidak
 * butuh kunci Midtrans maupun Xendit.
 *
 * Halaman ini bisa dimatikan lewat MOCK_GATEWAY_SIMULASI_AKTIF. Penjaganya
 * sengaja tidak lagi membaca pengaturan Midtrans, supaya jalur simulasi benar
 * benar berdiri sendiri.
 */
class SimulationController extends Controller
{
    public function __construct()
    {
        // Penjaga sandbox dipasang sekali di sini supaya tidak ada satu pun
        // method yang bisa lolos kalau nanti ada method baru ditambahkan.
        //
        // Halaman galat 403 milik proyek memakai pesan tetap yang tidak
        // menyebut soal sandbox, jadi kerangka yang sama dirender ulang dengan
        // pesan yang jelas. Tidak ada berkas view yang diubah.
        if (! (bool) config('mock_gateway.simulasi_aktif', true)) {
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
        // Tidak ada daftar nomor: halaman ini murni untuk mengetik manual.
        return $this->render('public.simulation.index');
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
            // Tahap berjalan ditampilkan satu per satu di view. Karena satu
            // tekan menjalankan tiga tahap sekaligus, halaman detail langsung
            // menandai tahap 1 sampai 3 sudah dilalui dan tahap 4 yang aktif.
            // Kalau semua tagihan sudah lunas, semuanya dianggap selesai.
            'tahap' => $tagihan === null ? 'berhasil' : MockGatewayService::TAHAP_VIRTUAL_ACCOUNT,
            'tahapDaftar' => [
                MockGatewayService::TAHAP_GENERATE,
                MockGatewayService::TAHAP_GATEWAY,
                MockGatewayService::TAHAP_VIRTUAL_ACCOUNT,
                MockGatewayService::TAHAP_SIMULASI,
            ],
        ]);
    }

    /**
     * Jalankan pembayaran tiruan.
     *
     * Satu tekan menjalankan tiga tahap sekaligus: transaksi dibuat (Generate
     * Payment), dikirim ke mock gateway lokal, lalu notifikasi sahnya
     * dikirim ke callback kita sendiri (Simulasi Pembayaran). Urutan itu tetap
     * nyata di kode dan di tabel, yang dihemat hanya satu klik.
     *
     * Status tagihan TIDAK disentuh di sini. Yang menandai lunas tetap callback
     * resmi, jadi jalur yang diuji tetap jalur produksi.
     */
    public function bayar(string $nomorVa, Request $request): RedirectResponse
    {
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

        $transaksi = $this->transaksiTerakhir($va);

        if ($transaksi === null) {
            // Transaksi ini TIDAK pernah dikirim ke gateway sungguhan. Nomor
            // berawalan SIM- dan channel SIMULASI jadi penandanya, supaya tidak
            // tertukar dengan transaksi asli kalau nanti ada rekonsiliasi.
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

        $payload = MockGatewayService::payload($transaksi, $nominal);
        $hasil = MockGatewayService::kirim($payload);

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
            ? 'Simulasi gagal. Mock gateway lokal membalas status '.$hasil['status'].'.'
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
     * Bukti transfer sebagai PNG, supaya bisa disimpan atau dicetak.
     *
     * Digambar sendiri pakai GD: tidak butuh paket tambahan dan tidak membuka
     * peramban. Yang mengunduh tetap data pembayaran asli dari database, jadi
     * nomor transaksi di gambar sama dengan yang tercatat.
     */
    public function bukti(string $nomorTransaksi): Response
    {
        $pembayaran = Payment::with('student.kelas')->where('nomor_transaksi', $nomorTransaksi)->first();

        if ($pembayaran === null) {
            abort(404, 'Pembayaran tidak ditemukan.');
        }

        $lebar = 720;
        $tinggi = 520;
        $gambar = imagecreatetruecolor($lebar, $tinggi);

        $putih = imagecolorallocate($gambar, 255, 255, 255);
        $tinta = imagecolorallocate($gambar, 17, 17, 17);
        $redup = imagecolorallocate($gambar, 110, 116, 126);
        $garis = imagecolorallocate($gambar, 222, 226, 214);
        $hijau = imagecolorallocate($gambar, 74, 122, 10);
        $neon = imagecolorallocate($gambar, 184, 240, 72);

        imagefilledrectangle($gambar, 0, 0, $lebar, $tinggi, $putih);
        imagefilledrectangle($gambar, 0, 0, $lebar, 96, $neon);

        $tebal = 'C:/Windows/Fonts/arialbd.ttf';
        $biasa = 'C:/Windows/Fonts/arial.ttf';
        $tebal = is_file($tebal) ? $tebal : null;
        $biasa = is_file($biasa) ? $biasa : null;

        $tulis = function (int $ukuran, int $x, int $y, $warna, string $teks, bool $bold = false) use ($gambar, $tebal, $biasa) {
            $font = $bold ? ($tebal ?: $biasa) : ($biasa ?: $tebal);
            if ($font !== null) {
                imagettftext($gambar, $ukuran, 0, $x, $y, $warna, $font, $teks);

                return;
            }
            // Cadangan kalau font bawaan Windows tidak ada: 5 adalah ukuran
            // terbesar pada font baku GD.
            imagestring($gambar, 5, $x, $y - 14, $teks, $warna);
        };

        $tulis(15, 32, 45, $tinta, 'BinaBangsa Pay', true);
        $tulis(10, 32, 72, $tinta, 'SMK Bina Bangsa Sidoarjo');

        $tulis(11, 32, 140, $redup, 'BUKTI TRANSFER');
        $tulis(11, 32, 162, $hijau, 'BERHASIL', true);

        $baris = [
            ['Nomor transaksi', (string) $pembayaran->nomor_transaksi],
            ['Nama siswa', (string) ($pembayaran->student?->nama ?? 'Tanpa nama')],
            ['NIS', (string) ($pembayaran->student?->nis ?? '-')],
            ['Kelas', (string) ($pembayaran->student?->kelas?->nama_kelas ?? '-')],
            ['Tanggal bayar', $pembayaran->paidAt()?->format('d M Y H:i') ?? '-'],
            ['Metode', strtoupper((string) $pembayaran->metode_pembayaran)],
            ['Status', strtoupper((string) $pembayaran->status)],
        ];

        $y = 208;
        foreach ($baris as $pasangan) {
            $tulis(10, 32, $y, $redup, $pasangan[0]);
            $tulis(11, 260, $y, $tinta, $pasangan[1], true);
            $y += 32;
            imageline($gambar, 32, $y - 16, $lebar - 32, $y - 16, $garis);
        }

        $tulis(10, 32, $y + 22, $redup, 'Nominal');
        $tulis(22, 260, $y + 28, $hijau, 'Rp'.number_format((float) $pembayaran->nominal_pembayaran, 0, ',', '.'), true);

        $tulis(8, 32, $tinggi - 30, $redup, 'Bukti simulasi. Uang tidak benar benar berpindah.');

        ob_start();
        imagepng($gambar);
        $isi = (string) ob_get_clean();
        imagedestroy($gambar);

        return response($isi, 200, [
            'Content-Type' => 'image/png',
            'Content-Disposition' => 'attachment; filename="bukti-'.$nomorTransaksi.'.png"',
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
     * Transaksi pending terakhir untuk VA ini, kalau ada.
     */
    private function transaksiTerakhir(VirtualAccount $va): ?PaymentGatewayTransaction
    {
        return PaymentGatewayTransaction::where('virtual_account_id', $va->id)
            ->where('status_transaksi', 'pending')
            ->latest('id')
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
