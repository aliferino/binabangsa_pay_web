<?php

namespace App\Services;

use App\Http\Controllers\Public\MockCallbackController;
use App\Models\Payment;
use App\Models\PaymentGatewayTransaction;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

/**
 * Gateway pembayaran tiruan untuk halaman uji dan halaman simulasi lokal.
 *
 * Pengganti Midtrans dan Xendit saat pengembangan. Semua tahap disimpan di
 * tabel transaksi yang sudah ada, tanpa kolom baru, supaya skema tidak ikut
 * berubah hanya karena alat uji. Tahap dibaca kembali dari data:
 *
 * generate -> gateway -> virtual_account -> simulasi -> berhasil
 *
 * Soal cara kirim. Jalur utamanya HTTP ke route callback sendiri, supaya yang
 * diuji benar-benar jalur produksi. Tetapi server dev `php artisan serve`
 * secara bawaan hanya punya SATU worker, sehingga permintaan HTTP ke diri
 * sendiri buntu sampai timeout. Karena itu, kalau koneksi gagal, payload yang
 * sama dijalankan langsung di proses yang sama. Verifikasi tanda tangan tetap
 * dijalankan, jadi yang diuji tidak dilewati begitu saja.
 */
class MockGatewayService
{
    /** Permintaan pembayaran baru dibuat, belum dikirim ke gateway. */
    public const TAHAP_GENERATE = 'generate';

    /** Gateway tiruan menerima permintaan dan mengembalikan referensi. */
    public const TAHAP_GATEWAY = 'gateway';

    /** Nomor virtual account sudah terbit dan menunggu pembayaran. */
    public const TAHAP_VIRTUAL_ACCOUNT = 'virtual_account';

    /** Pembayaran ditandai sudah dibayar, menunggu callback. */
    public const TAHAP_SIMULASI = 'simulasi';

    /** Callback sudah masuk dan pembayaran tercatat. */
    public const TAHAP_BERHASIL = 'berhasil';

    /** Nilai kolom payment_channel untuk transaksi tiruan ini. */
    public const CHANNEL = 'MOCK';

    /** Keterangan yang ikut dikirim di payload, penanda simulasi lokal. */
    public const KETERANGAN = 'Mock gateway lokal, tidak melewati Midtrans maupun Xendit.';

    /**
     * Kunci tanda tangan gateway tiruan, dibaca dari config, bukan dari kode.
     */
    public static function kunciTandaTangan(): string
    {
        return (string) config('mock_gateway.secret', 'mock-gateway-lokal-untuk-ukk');
    }

    /**
     * Hitung tanda tangan payload memakai HMAC SHA256 dari empat nilai inti.
     *
     * Empat nilai ini sama dengan yang dikirim di payload, jadi pihak penerima
     * bisa menghitung ulang dan mencocokkannya.
     */
    public static function tandaTangan(
        string $orderId,
        string $statusCode,
        string $grossAmount,
        string $status
    ): string {
        return hash_hmac(
            'sha256',
            $orderId.'|'.$statusCode.'|'.$grossAmount.'|'.$status,
            self::kunciTandaTangan()
        );
    }

    /**
     * Susun payload callback gateway tiruan yang sah.
     *
     * @return array<string, string>
     */
    public static function payload(PaymentGatewayTransaction $transaksi, float $nominal): array
    {
        $orderId = (string) $transaksi->transaction_id;
        $statusCode = '200';
        $grossAmount = number_format($nominal, 2, '.', '');

        return [
            'order_id' => $orderId,
            'status_code' => $statusCode,
            'gross_amount' => $grossAmount,
            'transaction_status' => 'settlement',
            'transaction_id' => (string) Str::uuid(),
            'settlement_time' => now()->format('Y-m-d H:i:s'),
            'keterangan_mock' => self::KETERANGAN,
            'signature_key' => self::tandaTangan($orderId, $statusCode, $grossAmount, 'settlement'),
        ];
    }

    /**
     * Verifikasi tanda tangan payload yang masuk.
     *
     * Dipakai controller callback sebelum menulis pembayaran, supaya payload
     * palsu tidak pernah dianggap sah. Kalau ada kunci yang hilang, dianggap
     * tidak sah.
     */
    public static function verifikasi(array $payload): bool
    {
        $orderId = (string) ($payload['order_id'] ?? '');
        $statusCode = (string) ($payload['status_code'] ?? '');
        $grossAmount = (string) ($payload['gross_amount'] ?? '');
        $status = (string) ($payload['transaction_status'] ?? '');
        $signature = (string) ($payload['signature_key'] ?? '');

        if ($signature === '') {
            return false;
        }

        $harusnya = self::tandaTangan($orderId, $statusCode, $grossAmount, $status);

        return hash_equals($harusnya, $signature);
    }

    /**
     * Kirim payload ke callback gateway tiruan dan kembalikan balasannya.
     *
     * @return array{status:int,body:array<string,mixed>,lewat:string}
     */
    public static function kirim(array $payload): array
    {
        $url = rtrim((string) config('mock_gateway.base_url', 'http://127.0.0.1:8000'), '/').'/callback/mock';

        try {
            $balasan = Http::acceptJson()
                ->timeout((int) config('mock_gateway.timeout', 5))
                ->post($url, $payload);

            return [
                'status' => $balasan->status(),
                'body' => (array) $balasan->json(),
                'lewat' => 'http',
            ];
        } catch (ConnectionException) {
            // Server dev satu worker tidak bisa melayani HTTP ke dirinya sendiri,
            // jadi payload yang sama dijalankan di proses ini. Jalur dan
            // verifikasi tetap sama.
        }

        $permintaan = Request::create('/callback/mock', 'POST', $payload);
        $jawaban = app()->call(
            [new MockCallbackController, 'handle'],
            ['request' => $permintaan]
        );

        return [
            'status' => $jawaban->status(),
            'body' => (array) $jawaban->getData(true),
            'lewat' => 'dalam-proses',
        ];
    }

    /**
     * Baca tahap transaksi dari data yang sudah ada, tanpa kolom baru.
     */
    public static function tahap(?PaymentGatewayTransaction $transaksi): string
    {
        if ($transaksi === null) {
            return self::TAHAP_GENERATE;
        }

        if ($transaksi->succeeded() && Payment::where('transaksi_id', $transaksi->id)->exists()) {
            return self::TAHAP_BERHASIL;
        }

        if (($transaksi->raw_response['tahap'] ?? null) === self::TAHAP_GATEWAY) {
            return self::TAHAP_VIRTUAL_ACCOUNT;
        }

        return self::TAHAP_GENERATE;
    }

    /**
     * Catat tahap gateway pada transaksi.
     *
     * Ini mewakili tahap 2, gateway tiruan menerima permintaan dan
     * mengembalikan referensi. Tabel tagihan tidak disentuh dan status
     * transaksi tetap pending.
     */
    public static function catatGateway(PaymentGatewayTransaction $transaksi): PaymentGatewayTransaction
    {
        $transaksi->raw_response = array_merge((array) $transaksi->raw_response, [
            'tahap' => self::TAHAP_GATEWAY,
            'channel' => self::CHANNEL,
            'referensi' => (string) $transaksi->transaction_id,
            'diterima_pada' => now()->format('Y-m-d H:i:s'),
        ]);
        $transaksi->save();

        return $transaksi;
    }
}
