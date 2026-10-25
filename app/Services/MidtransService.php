<?php

namespace App\Services;

use App\Exceptions\MidtransException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class MidtransService
{
    private string $serverKey;

    private string $baseUrl;

    private int $timeout = 30;

    public function __construct()
    {
        $this->serverKey = (string) config('midtrans.server_key');
        $this->baseUrl = rtrim((string) config('midtrans.base_url'), '/');
    }

    /**
     * Buat transaksi Virtual Account di Midtrans.
     *
     * Nomor VA yang kita kirim ditaruh Midtrans di belakang prefix merchant,
     * dan nomor hasilnya yang dipakai siswa untuk transfer. Karena itu nomor
     * hasil dibaca dari va_numbers, bukan disusun sendiri.
     *
     * Balasan mentah dikembalikan apa adanya, supaya pemanggil bisa menyimpan
     * seluruhnya ke kolom raw_response.
     */
    public function charge(array $data): array
    {
        $this->pastikanTerkonfigurasi();

        $wajib = ['order_id', 'gross_amount', 'va_number', 'bank'];

        foreach ($wajib as $kunci) {
            if (empty($data[$kunci])) {
                throw new MidtransException('Parameter '.$kunci.' wajib diisi untuk membuat transaksi Midtrans.');
            }
        }

        $nominal = (int) round((float) $data['gross_amount']);

        if ($nominal <= 0) {
            throw new MidtransException('Nominal transaksi Midtrans harus lebih besar dari nol.');
        }

        $body = [
            'payment_type' => 'bank_transfer',
            'transaction_details' => [
                'order_id' => (string) $data['order_id'],
                'gross_amount' => $nominal,
            ],
            'bank_transfer' => [
                'bank' => strtolower((string) $data['bank']),
                'va_number' => (string) $data['va_number'],
            ],
        ];

        if (! empty($data['first_name']) || ! empty($data['email'])) {
            $body['customer_details'] = array_filter([
                'first_name' => $data['first_name'] ?? null,
                'email' => $data['email'] ?? null,
            ]);
        }

        $response = $this->kirim('post', '/v2/charge', $body);

        $hasil = $response->json();

        if (! is_array($hasil) || empty($hasil['transaction_id'])) {
            throw new MidtransException(
                'Midtrans membalas tanpa transaction_id. Balasan: '.$response->body()
            );
        }

        return $hasil;
    }

    /**
     * Ambil status transaksi berdasarkan order_id.
     */
    public function getStatus(string $orderId): array
    {
        $this->pastikanTerkonfigurasi();

        if (trim($orderId) === '') {
            throw new MidtransException('order_id wajib diisi untuk memeriksa status Midtrans.');
        }

        $response = $this->kirim('get', '/v2/'.rawurlencode($orderId).'/status');

        $hasil = $response->json();

        if (! is_array($hasil)) {
            throw new MidtransException('Balasan status Midtrans tidak bisa dibaca sebagai JSON.');
        }

        return $hasil;
    }

    /**
     * Cocokkan signature_key dari notifikasi Midtrans.
     *
     * Rumusnya SHA512 dari order_id + status_code + gross_amount + server key.
     * Perbandingan memakai hash_equals supaya waktu pembandingannya tetap,
     * sehingga signature tidak bisa ditebak lewat pengukuran waktu balasan.
     */
    public function verifySignature(array $payload, string $serverKey): bool
    {
        if (trim($serverKey) === '') {
            return false;
        }

        $orderId = (string) ($payload['order_id'] ?? '');
        $statusCode = (string) ($payload['status_code'] ?? '');
        $grossAmount = (string) ($payload['gross_amount'] ?? '');
        $signature = (string) ($payload['signature_key'] ?? '');

        if ($orderId === '' || $statusCode === '' || $grossAmount === '' || $signature === '') {
            return false;
        }

        $harapan = hash('sha512', $orderId.$statusCode.$grossAmount.$serverKey);

        return hash_equals($harapan, $signature);
    }

    /**
     * Ambil nomor Virtual Account hasil Midtrans.
     *
     * Bentuk balasan Midtrans bisa va_numbers (bank_transfer) atau
     * permata_va_number. Keduanya dibaca supaya satu bentuk baru tidak
     * membuat transaksi yang sebenarnya berhasil terlihat gagal.
     */
    public function ambilNomorVa(array $balasan): ?string
    {
        $nomor = $balasan['va_numbers'][0]['va_number']
            ?? $balasan['permata_va_number']
            ?? null;

        return $nomor === null || trim((string) $nomor) === '' ? null : (string) $nomor;
    }

    /**
     * Apakah kunci server sudah diisi di .env.
     */
    public function terkonfigurasi(): bool
    {
        return $this->serverKey !== '';
    }

    /**
     * Susun permintaan HTTP dengan Basic Auth dan batas waktu.
     *
     * Format Basic Auth Midtrans: server key sebagai username, password kosong.
     * Karena itu ada titik dua di akhir, dan itu bukan salah tulis.
     */
    private function klien(): PendingRequest
    {
        return Http::withBasicAuth($this->serverKey, '')
            ->timeout($this->timeout)
            ->acceptJson()
            ->asJson();
    }

    /**
     * Kirim permintaan dan ubah kegagalan jadi exception yang jelas.
     */
    private function kirim(string $method, string $path, array $body = []): Response
    {
        $url = $this->baseUrl.$path;

        try {
            $response = $method === 'get'
                ? $this->klien()->get($url)
                : $this->klien()->post($url, $body);
        } catch (ConnectionException $e) {
            throw new MidtransException(
                'Tidak bisa menghubungi Midtrans di '.$this->baseUrl.'. Periksa koneksi internet. Pesan asli: '.$e->getMessage(),
                0,
                $e
            );
        }

        if ($response->successful()) {
            return $response;
        }

        throw new MidtransException(
            'Midtrans menolak permintaan ke '.$path.' dengan kode '.$response->status().'. Balasan: '.$response->body()
        );
    }

    /**
     * Hentikan lebih awal kalau kunci belum diisi, supaya pesan errornya
     * menunjuk ke penyebab yang benar, bukan ke kegagalan HTTP yang membingungkan.
     */
    private function pastikanTerkonfigurasi(): void
    {
        if ($this->serverKey === '') {
            throw new MidtransException(
                'MIDTRANS_SERVER_KEY belum diisi di .env. Isi dulu sebelum memakai fitur Midtrans.'
            );
        }
    }
}