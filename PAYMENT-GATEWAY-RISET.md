# Midtrans vs Xendit untuk binabangsa_pay_web

Riset 10 Okt 2026. Semua klaim dari dokumentasi resmi, sudah diverifikasi sendiri.

## Ringkasan cepat

| | Midtrans | Xendit |
|---|---|---|
| Sandbox gratis | ya | ya |
| Perlu badan usaha untuk sandbox | tidak | tidak |
| Perlu NPWP untuk sandbox | tidak | tidak |
| Sandbox otomatis saat daftar | ya | ya (Test Mode) |
| Production sebagai perorangan | bisa | wajib badan hukum |
| SDK resmi PHP | midtrans/midtrans-php 2.6.2 | xendit/xendit-php 7.0.0 |
| Kompatibel PHP 8.3 | ya | ya |
| Simulator | web terpusat | endpoint per produk |
| Verifikasi production | tidak dipublikasikan | 14 hari kerja |

## Midtrans

Sandbox dibuat otomatis saat signup dan gratis. Bisa daftar sebagai
perorangan (pilihan "my business has no business entity"). NPWP dan KTP
baru diminta untuk naik ke production.

Langkah daftar:
1. dashboard.midtrans.com/register
2. Isi form (email dan nomor HP yang belum pernah dipakai Midtrans)
3. Verifikasi email
4. Login, langsung di mode Sandbox (sidebar oranye)
5. Settings > Access Key untuk ambil Server Key dan Client Key

Metode di sandbox: Virtual Account (BCA, BRI, BNI, Mandiri, Permata, CIMB,
BSI, dan lain lain), QRIS, GoPay, ShopeePay, DANA, kartu kredit, Alfamart,
Indomaret, internet banking.

Simulator webhook: https://simulator.sandbox.midtrans.com (sudah dicek hidup,
HTTP 200). Ada tab per metode pembayaran.

## Xendit

Test Mode langsung tersedia setelah signup, tanpa verifikasi bisnis.
Production wajib badan hukum (minimal Perseroan Perorangan).

Langkah daftar:
1. dashboard.xendit.co/register/
2. Verifikasi email
3. Isi pertanyaan dasar bisnis
4. Langsung masuk Test Mode
5. Buat Secret Key SENDIRI di Settings > API Keys

PENTING: Xendit tidak memberi Secret Key otomatis. Dokumentasinya:
"You'll have zero secret key when you start."

Simulasi webhook lewat endpoint khusus, contoh:
POST https://api.xendit.co/callback_virtual_accounts/{external_id}/simulate_payment

## Rekomendasi

Pakai Midtrans sebagai jalur utama, karena:
1. Dokumentasinya menyebut Laravel secara eksplisit
2. Ada Snap: satu snap_token di backend, snap.pay() di frontend
3. Simulator terpusat, mudah untuk demo

Kalau mau nilai tambah (dosen menyebut keduanya), integrasikan keduanya
dengan satu abstraksi:

```php
interface PaymentGateway {
    public function createTransaction(Order $order): array;
    public function handleWebhook(Request $request): void;
}
```

Kedua paket tidak konflik. Keduanya pakai Guzzle versi kompatibel.

## Jebakan yang sering bikin gagal

1. Webhook tidak bisa masuk localhost. Midtrans: "cannot send notifications
   to localhost, URL protected with authorization, URL behind VPN".
   Wajib pakai ngrok.
2. URL ngrok gratis berubah tiap restart. Kalau lupa update di dashboard,
   webhook mati diam diam.
3. Signature Midtrans: SHA512 dari order_id + status_code + gross_amount +
   ServerKey. gross_amount harus persis string yang dikirim, misal "10000.00".
4. Server Key sandbox beda dengan production. Salah pakai = error 401.
5. Server Key hanya boleh di backend. Frontend cuma butuh Client Key.
6. Lupa php artisan config:clear setelah ubah .env.
7. Webhook handler harus balas HTTP 200, kalau tidak Midtrans retry terus.
8. Jangan pakai xendit/xendit-php-clients (sudah ABANDONED, digantikan
   xendit/xendit-php).
9. Xendit: callback URL harus lolos tombol "test and save" (balas 2XX) baru
   tersimpan.
10. Jangan percaya mentah isi webhook. Verifikasi signature.

## Sumber resmi

Midtrans:
- https://docs.midtrans.com/docs/midtrans-account
- https://docs.midtrans.com/docs/dashboard-basics
- https://docs.midtrans.com/docs/testing-payment-on-sandbox
- https://simulator.sandbox.midtrans.com
- https://docs.midtrans.com/docs/https-notification-webhooks
- https://docs.midtrans.com/docs/snap-snap-integration-guide
- https://github.com/Midtrans/midtrans-php

Xendit:
- https://docs.xendit.co/docs/create-account
- https://docs.xendit.co/docs/available-payment-channels
- https://docs.xendit.co/apidocs/simulate-payment-test-mode
- https://github.com/xendit/xendit-php
- https://help.xendit.co/hc/id/articles/360035083911 (badan hukum)

## Status project binabangsa_pay_web

- Model: cuma User
- Migration: cuma tabel bawaan Laravel (users, cache, jobs)
- Belum ada tabel transaksi
- Belum ada MIDTRANS atau XENDIT di .env

Perlu dibuat dari nol: tabel transactions dengan kolom order_id, amount,
status, gateway, gateway_reference_id.
