<?php

namespace App\Services;

use App\Exceptions\XenditException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class XenditService
{
    private string $secretKey;

    private string $callbackToken;

    private string $baseUrl;

    private int $timeout = 30;

    public function __construct()
    {
        $this->secretKey = (string) config('services.xendit.secret_key');
        $this->callbackToken = (string) config('services.xendit.callback_token');
        $this->baseUrl = rtrim((string) config('services.xendit.base_url'), '/');
    }

    /**
     * Buat Virtual Account di Xendit.
     *
     * Parameter yang wajib dikirim: external_id, bank_code, dan name.
     * external_id sebaiknya memakai nomor VA internal kita, karena dari situ
     * callback nanti dicocokkan.
     *
     * Balasan mentah dikembalikan apa adanya, supaya pemanggil bisa menyimpan
     * seluruhnya ke kolom raw_response.
     */
    public function createVirtualAccount(array $data): array
    {
        $this->pastikanTerkonfigurasi();

        $wajib = ['external_id', 'bank_code', 'name'];

        foreach ($wajib as $kunci) {
            if (empty($data[$kunci])) {
                throw new XenditException('Parameter '.$kunci.' wajib diisi untuk membuat Virtual Account Xendit.');
            }
        }

        $body = [
            'external_id' => (string) $data['external_id'],
            'bank_code' => (string) $data['bank_code'],
            'name' => (string) $data['name'],
            'is_closed' => (bool) ($data['is_closed'] ?? true),
            'is_single_use' => (bool) ($data['is_single_use'] ?? true),
        ];

        if (isset($data['expected_amount'])) {
            $body['expected_amount'] = (int) round((float) $data['expected_amount']);
        }

        if (! empty($data['expiration_date'])) {
            $body['expiration_date'] = (string) $data['expiration_date'];
        }

        if (! empty($data['description'])) {
            $body['description'] = (string) $data['description'];
        }

        if (! empty($data['customer'])) {
            $body['customer'] = $data['customer'];
        }

        $response = $this->kirim('post', '/callback_virtual_accounts', $body);

        $hasil = $response->json();

        if (! is_array($hasil) || empty($hasil['id']) || empty($hasil['account_number'])) {
            throw new XenditException(
                'Xendit membalas tanpa id atau account_number. Balasan: '.$response->body()
            );
        }

        return $hasil;
    }

    /**
     * Ambil detail Virtual Account berdasarkan id Xendit.
     */
    public function getVirtualAccount(string $id): array
    {
        $this->pastikanTerkonfigurasi();

        if (trim($id) === '') {
            throw new XenditException('Id Virtual Account Xendit wajib diisi.');
        }

        $response = $this->kirim('get', '/callback_virtual_accounts/'.rawurlencode($id));

        $hasil = $response->json();

        if (! is_array($hasil)) {
            throw new XenditException('Balasan Xendit tidak bisa dibaca sebagai JSON.');
        }

        return $hasil;
    }

    /**
     * Picu pembayaran tiruan untuk sebuah Virtual Account.
     *
     * Endpoint ini hanya tersedia di mode test. Kalau dipanggil di mode
     * production, Xendit akan menolak, dan pesan errornya diteruskan apa
     * adanya supaya jelas penyebabnya.
     */
    public function simulatePayment(string $externalId, float $amount): array
    {
        $this->pastikanTerkonfigurasi();

        if (trim($externalId) === '') {
            throw new XenditException('external_id wajib diisi untuk simulasi pembayaran.');
        }

        if ($amount <= 0) {
            throw new XenditException('Nominal simulasi pembayaran harus lebih besar dari nol.');
        }

        $response = $this->kirim(
            'post',
            '/callback_virtual_accounts/'.rawurlencode($externalId).'/simulate_payment',
            ['amount' => (int) round($amount)]
        );

        $hasil = $response->json();

        if (! is_array($hasil)) {
            throw new XenditException('Balasan simulasi Xendit tidak bisa dibaca sebagai JSON.');
        }

        return $hasil;
    }

    /**
     * Cocokkan token callback dari header x-callback-token.
     *
     * Perbandingan memakai hash_equals supaya waktu pembandingannya tetap,
     * sehingga token tidak bisa ditebak lewat pengukuran waktu balasan.
     */
    public function verifyCallbackToken(?string $token): bool
    {
        if ($token === null || trim($token) === '') {
            return false;
        }

        if ($this->callbackToken === '') {
            return false;
        }

        return hash_equals($this->callbackToken, trim($token));
    }

    /**
     * Apakah kunci API dan token callback sudah diisi di .env.
     */
    public function terkonfigurasi(): bool
    {
        return $this->secretKey !== '' && $this->callbackToken !== '';
    }

    /**
     * Susun permintaan HTTP dengan Basic Auth dan batas waktu.
     *
     * Format Basic Auth Xendit: secret key sebagai username, password kosong.
     * Karena itu ada titik dua di akhir, dan itu bukan salah tulis.
     */
    private function klien(): PendingRequest
    {
        return Http::withBasicAuth($this->secretKey, '')
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
            throw new XenditException(
                'Tidak bisa menghubungi Xendit di '.$this->baseUrl.'. Periksa koneksi internet. Pesan asli: '.$e->getMessage(),
                0,
                $e
            );
        }

        if ($response->successful()) {
            return $response;
        }

        throw new XenditException(
            'Xendit menolak permintaan ke '.$path.' dengan kode '.$response->status().'. Balasan: '.$response->body()
        );
    }

    /**
     * Hentikan lebih awal kalau kunci belum diisi, supaya pesan errornya
     * menunjuk ke penyebab yang benar, bukan ke kegagalan HTTP yang membingungkan.
     */
    private function pastikanTerkonfigurasi(): void
    {
        if ($this->secretKey === '') {
            throw new XenditException(
                'XENDIT_SECRET_KEY belum diisi di .env. Isi dulu sebelum memakai fitur Xendit.'
            );
        }
    }
}
