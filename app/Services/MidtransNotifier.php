<?php

namespace App\Services;

use App\Http\Controllers\Public\MidtransCallbackController;
use App\Models\PaymentGatewayTransaction;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

/**
 * Pengirim notifikasi Midtrans tiruan untuk halaman uji dan halaman simulasi.
 *
 * Dua tempat membutuhkannya (pengaturan integrasi dan simulasi publik), dan
 * rumus payload-nya harus identik, jadi disatukan di sini.
 *
 * Soal cara kirim. Jalur utamanya HTTP ke route callback sendiri, supaya yang
 * diuji benar-benar jalur produksi. Tetapi server dev `php artisan serve`
 * secara bawaan hanya punya SATU worker, sehingga permintaan HTTP ke diri
 * sendiri buntu sampai timeout: worker tunggal itu sedang sibuk melayani
 * permintaan yang sedang berjalan. Karena itu, kalau koneksi gagal, payload
 * yang sama dijalankan langsung di proses yang sama. Hasilnya sama, yang
 * berbeda hanya jalurnya. Di produksi, yang dipakai selalu jalur HTTP.
 */
class MidtransNotifier
{
    /**
     * Batas tunggu jalur HTTP. Sengaja pendek supaya server dev tidak
     * menggantung lama sebelum jatuh ke jalur dalam proses.
     */
    private const BATAS_HTTP = 5;

    /**
     * Susun payload notifikasi Midtrans yang sah.
     *
     * Rumus signature-nya sama dengan yang dipakai Midtrans sungguhan:
     * sha512 dari order_id, status_code, gross_amount, dan server key.
     *
     * @return array<string, string>
     */
    public static function payload(
        PaymentGatewayTransaction $transaksi,
        ?float $nominal,
        string $serverKey,
        ?string $keterangan = null
    ): array {
        $orderId = (string) $transaksi->transaction_id;
        $statusCode = '200';
        $grossAmount = number_format($nominal ?? (float) $transaksi->nominal, 2, '.', '');

        $payload = [
            'order_id' => $orderId,
            'status_code' => $statusCode,
            'gross_amount' => $grossAmount,
            'transaction_status' => 'settlement',
            'fraud_status' => 'accept',
            'transaction_id' => (string) Str::uuid(),
            'settlement_time' => now()->format('Y-m-d H:i:s'),
            'signature_key' => hash('sha512', $orderId.$statusCode.$grossAmount.$serverKey),
        ];

        // Label ini tidak dikirim Midtrans dan tidak ikut signature. Callback
        // memakainya untuk menulis keterangan pembayaran, supaya jelas transaksi
        // mana yang hanya simulasi lokal.
        if ($keterangan !== null) {
            $payload['keterangan_simulasi'] = $keterangan;
        }

        return $payload;
    }

    /**
     * Kirim payload ke callback Midtrans dan kembalikan balasannya.
     *
     * @return array{status:int,body:array<string,mixed>,lewat:string}
     */
    public static function kirim(array $payload): array
    {
        try {
            $balasan = Http::acceptJson()
                ->timeout(self::BATAS_HTTP)
                ->post(url('/callback/midtrans'), $payload);

            return [
                'status' => $balasan->status(),
                'body' => (array) $balasan->json(),
                'lewat' => 'http',
            ];
        } catch (ConnectionException) {
            // Server dev satu worker. Jalan yang sama, dijalankan di proses ini.
        }

        $permintaan = Request::create('/callback/midtrans', 'POST', $payload);
        $jawaban = app()->call(
            [new MidtransCallbackController, 'handle'],
            ['request' => $permintaan, 'midtrans' => app(MidtransService::class)]
        );

        return [
            'status' => $jawaban->status(),
            'body' => (array) $jawaban->getData(true),
            'lewat' => 'dalam-proses',
        ];
    }
}
