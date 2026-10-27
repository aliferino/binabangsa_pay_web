<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\PaymentGatewayTransaction;
use App\Models\TuitionBill;
use App\Services\MockGatewayService;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MockCallbackController extends Controller
{
    /**
     * Terima notifikasi gateway tiruan lokal.
     *
     * Endpoint ini ada supaya alur pembayaran bisa diuji tanpa menyentuh
     * Midtrans maupun Xendit. Seperti dua callback lain, route ini tanpa
     * middleware auth dan tanpa CSRF, karena yang memanggil bukan browser
     * pengguna. Pengamannya tanda tangan HMAC, bukan sesi login.
     *
     * Tanda tangan mengikat empat nilai: order_id, status_code, gross_amount,
     * dan transaction_status. Kalau salah satu diubah di jalan, tanda tangan
     * lama tidak lagi cocok.
     *
     * Idempoten, tiga lapis seperti jalur Midtrans dan Xendit:
     * 1. Transaksi ini sudah punya pembayaran, balas 200 tanpa menulis apa pun.
     * 2. lockForUpdate di dalam transaksi database, supaya notifikasi kembar
     *    diproses bergantian.
     * 3. Kunci unik nomor_transaksi di tabel pembayaran, lapis terakhir.
     */
    public function handle(Request $request): JsonResponse
    {
        $orderId = (string) $request->input('order_id', '');

        if (! $this->verifySignature($request)) {
            Log::warning('Notifikasi mock gateway ditolak, tanda tangan tidak cocok', [
                'ip' => $request->ip(),
                'order_id' => $orderId,
            ]);

            return response()->json([
                'sukses' => false,
                'pesan' => 'Tanda tangan tidak sah.',
            ], 403);
        }

        $transaksi = PaymentGatewayTransaction::where('transaction_id', $orderId)->first();

        if ($transaksi === null) {
            return response()->json([
                'sukses' => false,
                'pesan' => 'Transaksi tidak ditemukan.',
            ], 404);
        }

        // Lapis 1: sudah pernah diproses.
        if (Payment::where('transaksi_id', $transaksi->id)->exists()) {
            return response()->json([
                'sukses' => true,
                'pesan' => 'Notifikasi ini sudah pernah diproses sebelumnya.',
                'order_id' => $orderId,
                'dibuat' => false,
            ], 200);
        }

        $grossAmount = (float) $request->input('gross_amount', 0);

        // Tagihan tertua yang belum lunas milik siswa ini. Tagihan yang sudah
        // lunas tidak pernah dipilih, supaya satu tagihan tidak tercatat lunas
        // dua kali.
        $tagihan = TuitionBill::where('id_siswa', $transaksi->siswa_id)
            ->where('status', '!=', TuitionBill::STATUS_LUNAS)
            ->orderBy('tahun')
            ->orderBy('bulan')
            ->first();

        if ($tagihan === null) {
            return response()->json([
                'sukses' => false,
                'pesan' => 'Tidak ada tagihan yang bisa dibayar untuk siswa ini.',
            ], 422);
        }

        // Nominal wajib cocok dengan tagihan. Kalau tidak, tidak ada status yang
        // diubah, supaya notifikasi bernominal salah tidak melunasi tagihan.
        if ((float) $tagihan->nominal !== $grossAmount) {
            return response()->json([
                'sukses' => false,
                'pesan' => 'Nominal tidak cocok dengan tagihan. Tagihan Rp'
                    .number_format((float) $tagihan->nominal, 0, ',', '.')
                    .', diterima Rp'.number_format($grossAmount, 0, ',', '.').'.',
            ], 422);
        }

        $paidAt = $this->bacaWaktu($request->input('settlement_time')) ?? now();

        try {
            $hasil = DB::transaction(function () use ($transaksi, $request, $tagihan, $grossAmount, $paidAt) {
                /** @var PaymentGatewayTransaction $terkunci */
                $terkunci = PaymentGatewayTransaction::whereKey($transaksi->id)->lockForUpdate()->firstOrFail();

                // Lapis 2: rekan notifikasi kembar sudah menulis lebih dulu.
                if (Payment::where('transaksi_id', $terkunci->id)->exists()) {
                    return null;
                }

                $pembayaran = Payment::create([
                    // transaction_id dari halaman simulasi sudah berawalan
                    // MOCK-, jadi tidak ditambah lagi. Kalau ditambah, nomornya
                    // jadi MOCK-MOCK-... dan menyulitkan pembacaan.
                    'nomor_transaksi' => $this->nomorTransaksi((string) $terkunci->transaction_id),
                    'transaksi_id' => $terkunci->id,
                    'tagihan_spp_id' => $tagihan->id_tagihan,
                    'siswa_id' => $terkunci->siswa_id,
                    'nominal_pembayaran' => $grossAmount,
                    'tanggal_pembayaran' => $paidAt,
                    'metode_pembayaran' => 'va',
                    'status' => 'berhasil',
                    'user_id' => null,
                    'keterangan' => (string) $request->input(
                        'keterangan_mock',
                        'Mock gateway lokal, tidak melewati Midtrans maupun Xendit.'
                    ),
                    'nomor_referensi' => $terkunci->reference_number,
                ]);

                $terkunci->update([
                    'status_transaksi' => 'berhasil',
                    'paid_at' => $paidAt,
                ]);

                $tagihan->update(['status' => TuitionBill::STATUS_LUNAS]);

                return $pembayaran;
            });
        } catch (QueryException $e) {
            // Lapis 3: kunci unik nomor_transaksi menolak baris kembar.
            if ($this->karenaDuplikat($e)) {
                Log::warning('Notifikasi mock gateway kembar ditolak kunci unik', [
                    'order_id' => $orderId,
                ]);

                return response()->json([
                    'sukses' => true,
                    'pesan' => 'Notifikasi ini sudah pernah diproses sebelumnya.',
                    'order_id' => $orderId,
                    'dibuat' => false,
                ], 200);
            }

            throw $e;
        }

        if ($hasil === null) {
            return response()->json([
                'sukses' => true,
                'pesan' => 'Notifikasi ini sudah pernah diproses sebelumnya.',
                'order_id' => $orderId,
                'dibuat' => false,
            ], 200);
        }

        return response()->json([
            'sukses' => true,
            'pesan' => 'Pembayaran berhasil dicatat.',
            'order_id' => $orderId,
            'nomor_transaksi' => $this->nomorTransaksi($orderId),
            'dibuat' => true,
        ], 201);
    }

    /**
     * Nomor transaksi untuk pembayaran. Awalan MOCK- ditambahkan sekali saja.
     */
    private function nomorTransaksi(string $orderId): string
    {
        return str_starts_with($orderId, 'MOCK-') ? $orderId : 'MOCK-'.$orderId;
    }

    /**
     * Hitung ulang tanda tangan HMAC dan bandingkan dengan yang dikirim.
     *
     * Bentuknya dibekukan: sha256 dari
     * order_id|status_code|gross_amount|transaction_status, dengan kunci dari
     * config. Perbandingan memakai hash_equals supaya tidak bocor lewat waktu
     * eksekusi.
     */
    private function verifySignature(Request $request): bool
    {
        // Rumus tanda tangan tinggal di satu tempat saja, yaitu
        // MockGatewayService. Kalau rumusnya diubah di sana, pengirim dan
        // pemeriksa tetap sinkron. Dua salinan rumus yang terpisah berisiko
        // berbeda diam diam, dan akibatnya semua callback sah ditolak.
        return MockGatewayService::verifikasi([
            'order_id' => (string) $request->input('order_id', ''),
            'status_code' => (string) $request->input('status_code', ''),
            'gross_amount' => (string) $request->input('gross_amount', ''),
            'transaction_status' => (string) $request->input('transaction_status', ''),
            'signature_key' => (string) $request->input('signature_key', ''),
        ]);
    }

    /**
     * Baca waktu dari notifikasi dengan aman, kembalikan null kalau tidak
     * dikenali, supaya satu format tanggal aneh tidak membatalkan pencatatan.
     */
    private function bacaWaktu(mixed $nilai): ?Carbon
    {
        if (! is_string($nilai) || trim($nilai) === '') {
            return null;
        }

        try {
            return Carbon::parse($nilai);
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Apakah kegagalan ini karena kunci unik menolak baris kembar. Pesan
     * MySQL, PostgreSQL, dan SQLite diperiksa karena nama driver berbeda.
     */
    private function karenaDuplikat(QueryException $e): bool
    {
        if ((int) ($e->errorInfo[1] ?? 0) === 1062) {
            return true;
        }

        $pesan = strtolower($e->getMessage());

        return str_contains($pesan, 'duplicate') || str_contains($pesan, 'unique');
    }
}
