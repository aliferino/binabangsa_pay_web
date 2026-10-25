<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\PaymentGatewayTransaction;
use App\Models\TuitionBill;
use App\Models\VirtualAccount;
use App\Services\MidtransService;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MidtransCallbackController extends Controller
{
    /**
     * Terima notifikasi Midtrans.
     *
     * Route ini tanpa middleware auth dan tanpa CSRF, karena yang memanggil
     * server Midtrans. Pengamannya bukan sesi login, tetapi signature_key yang
     * diperiksa lebih dulu. Tanpa itu siapa pun bisa mengirim notifikasi palsu
     * dan melunasi tagihan tanpa membayar.
     *
     * Idempoten, tiga lapis seperti jalur Xendit:
     * 1. Transaksi ini sudah punya pembayaran, balas 200 tanpa menulis apa pun.
     * 2. lockForUpdate di dalam transaksi database, supaya notifikasi kembar
     *    diproses bergantian.
     * 3. Kunci unik nomor_transaksi di tabel pembayaran, lapis terakhir.
     */
    public function handle(Request $request, MidtransService $midtrans): JsonResponse
    {
        if (! $midtrans->verifySignature($request->all(), (string) config('midtrans.server_key'))) {
            Log::warning('Notifikasi Midtrans ditolak, signature tidak cocok', [
                'ip' => $request->ip(),
                'order_id' => $request->input('order_id'),
            ]);

            return response()->json([
                'sukses' => false,
                'pesan' => 'Signature tidak sah.',
            ], 403);
        }

        $orderId = trim((string) $request->input('order_id', ''));

        if ($orderId === '') {
            return response()->json([
                'sukses' => false,
                'pesan' => 'Notifikasi tidak memuat order_id.',
            ], 422);
        }

        $transaksi = PaymentGatewayTransaction::where('transaction_id', $orderId)->first();

        if ($transaksi === null) {
            return response()->json([
                'sukses' => false,
                'pesan' => 'Transaksi dengan order_id '.$orderId.' tidak dikenal.',
            ], 404);
        }

        $status = strtolower(trim((string) $request->input('transaction_status', '')));
        $fraud = strtolower(trim((string) $request->input('fraud_status', '')));
        $statusTransaksi = $this->mapStatus($status, $fraud);

        // Jejak audit disimpan dulu, walaupun statusnya belum berhasil.
        $transaksi->update([
            'status_transaksi' => $statusTransaksi,
            'raw_response' => $request->all(),
            'reference_number' => (string) $request->input('transaction_id', $transaksi->reference_number),
        ]);

        if ($statusTransaksi !== 'berhasil') {
            return response()->json([
                'sukses' => true,
                'pesan' => 'Notifikasi diterima dengan status '.$status.'. Tagihan tidak diubah.',
                'order_id' => $transaksi->transaction_id,
                'dibuat' => false,
            ], 200);
        }

        // Lapis 1: sudah pernah diproses.
        if (Payment::where('transaksi_id', $transaksi->id)->exists()) {
            return response()->json([
                'sukses' => true,
                'pesan' => 'Notifikasi ini sudah pernah diproses sebelumnya.',
                'order_id' => $transaksi->transaction_id,
                'dibuat' => false,
            ], 200);
        }

        $paidAt = $this->bacaWaktu($request->input('settlement_time'))
            ?? $this->bacaWaktu($request->input('transaction_time'));

        try {
            $hasil = DB::transaction(function () use ($transaksi, $request, $paidAt) {
                /** @var PaymentGatewayTransaction $terkunci */
                $terkunci = PaymentGatewayTransaction::whereKey($transaksi->id)->lockForUpdate()->firstOrFail();

                if (Payment::where('transaksi_id', $terkunci->id)->exists()) {
                    return null;
                }

                $tagihan = $this->selectBill($terkunci);

                if ($tagihan === null) {
                    return 'tanpa_tagihan';
                }

                $nominal = (float) $request->input('gross_amount', $terkunci->nominal);

                $pembayaran = Payment::create([
                    'nomor_transaksi' => 'MT-'.$terkunci->transaction_id,
                    'transaksi_id' => $terkunci->id,
                    'tagihan_spp_id' => $tagihan->id_tagihan,
                    'siswa_id' => $terkunci->siswa_id,
                    'nominal_pembayaran' => $nominal,
                    'tanggal_pembayaran' => $paidAt ?? now(),
                    'metode_pembayaran' => 'va',
                    'status' => 'berhasil',
                    'user_id' => null,
                    'keterangan' => 'Pembayaran Virtual Account Midtrans.',
                    'nomor_referensi' => $terkunci->reference_number,
                ]);

                $tagihan->update(['status' => 'lunas']);

                return $pembayaran;
            });
        } catch (QueryException $e) {
            if ($this->karenaDuplikat($e)) {
                Log::warning('Notifikasi Midtrans kembar ditolak kunci unik', [
                    'order_id' => $transaksi->transaction_id,
                ]);

                return response()->json([
                    'sukses' => true,
                    'pesan' => 'Notifikasi ini sudah pernah diproses sebelumnya.',
                    'order_id' => $transaksi->transaction_id,
                    'dibuat' => false,
                ], 200);
            }

            throw $e;
        }

        if ($hasil === null) {
            return response()->json([
                'sukses' => true,
                'pesan' => 'Notifikasi ini sudah pernah diproses sebelumnya.',
                'order_id' => $transaksi->transaction_id,
                'dibuat' => false,
            ], 200);
        }

        if ($hasil === 'tanpa_tagihan') {
            return response()->json([
                'sukses' => false,
                'pesan' => 'Transaksi ditemukan, tetapi tidak ada tagihan yang belum dibayar untuk siswa ini.',
                'order_id' => $transaksi->transaction_id,
                'dibuat' => false,
            ], 422);
        }

        return response()->json([
            'sukses' => true,
            'pesan' => 'Pembayaran berhasil dicatat.',
            'order_id' => $transaksi->transaction_id,
            'nomor_transaksi' => $hasil->nomor_transaksi,
            'tagihan_spp_id' => $hasil->tagihan_spp_id,
            'dibuat' => true,
        ], 201);
    }

    /**
     * Peta status Midtrans ke status yang dipakai aplikasi.
     *
     * capture hanya dianggap berhasil kalau fraud_status accept. Tanpa
     * syarat itu, transaksi yang ditandai challenge akan terlanjur melunasi
     * tagihan padahal uangnya belum tentu masuk.
     */
    private function mapStatus(string $status, string $fraud): string
    {
        return match ($status) {
            'settlement' => 'berhasil',
            'capture' => $fraud === 'accept' ? 'berhasil' : 'pending',
            'expire' => 'kedaluwarsa',
            'cancel', 'deny', 'failure' => 'gagal',
            default => 'pending',
        };
    }

    /**
     * Tentukan tagihan yang dibayar oleh transaksi ini.
     *
     * Midtrans tidak mengirim id tagihan, jadi dipilih tagihan tertua yang
     * belum lunas milik siswa tersebut. Tagihan yang sudah lunas tidak pernah
     * dipilih, supaya satu tagihan tidak tercatat lunas dua kali dengan dua
     * metode berbeda.
     */
    private function selectBill(PaymentGatewayTransaction $transaksi): ?TuitionBill
    {
        return TuitionBill::where('id_siswa', $transaksi->siswa_id)
            ->whereIn('status', ['belum_bayar', 'tunggakan'])
            ->orderBy('tahun')
            ->orderBy('bulan')
            ->lockForUpdate()
            ->first();
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
     * Apakah kegagalan ini karena kunci unik menolak baris kembar.
     */
    private function karenaDuplikat(QueryException $e): bool
    {
        return (int) ($e->errorInfo[1] ?? 0) === 1062;
    }
}