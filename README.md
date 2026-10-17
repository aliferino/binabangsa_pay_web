<p align="center">
  <img src="https://img.shields.io/badge/Laravel-13-FF2D20?logo=laravel&logoColor=white" alt="Laravel 13">
  <img src="https://img.shields.io/badge/PHP-8.4-777BB4?logo=php&logoColor=white" alt="PHP 8.4">
  <img src="https://img.shields.io/badge/Tailwind-4-06B6D4?logo=tailwindcss&logoColor=white" alt="Tailwind 4">
  <img src="https://img.shields.io/badge/MySQL-8-4479A1?logo=mysql&logoColor=white" alt="MySQL 8">
  <img src="https://img.shields.io/badge/status-master%20data-blue" alt="Status">
</p>

# BinaBangsa Pay

Sistem pembayaran SPP untuk **SMK Bina Bangsa**. Aplikasi web untuk mengelola data
sekolah dan tagihan SPP siswa, dengan empat peran pengguna yang hak aksesnya
berbeda.

Proyek ini dibuat untuk keperluan LSP. Cakupan yang sudah selesai adalah
**master data**. Bagian pembayaran belum dibuat.

---

## Status pengerjaan

| Bagian | Status |
|---|---|
| Skema database (7 tabel inti) | selesai |
| Model Eloquent | selesai |
| Controller (11 buah) | selesai |
| Route (66 route) | selesai |
| Tampilan Blade (43 berkas) | selesai |
| Test (22 test, 80 assertion) | lolos semua |
| **Pembayaran, virtual account, callback** | **belum dibuat** |

Bagian pembayaran sengaja ditunda sampai skema pembayarannya disetujui guru
pembimbing. Tabel `virtual_account`, `transaksi_payment_gateway`, dan
`pembayaran` belum ada, jadi halaman yang membutuhkannya belum bisa dibuat.

Di dashboard admin ada bagian bertanda **"Belum tersedia"** yang menjelaskan hal
ini secara terbuka, supaya tidak terlihat seperti bug.

---

## Empat peran pengguna

| Peran | Bisa apa |
|---|---|
| `admin` | semua data master, pengguna, laporan, pencarian |
| `kepala_sekolah` | melihat data master dan laporan, **tidak bisa mengubah** |
| `teller` | tagihan, pencarian, laporan |
| `siswa` | hanya dashboard miliknya sendiri |

Hak akses dijaga oleh middleware `PastikanRole`. Siswa tidak bisa membuka data
siswa lain lewat URL, dan ini sudah diuji.

---

## Kebutuhan sistem

| Perangkat | Versi |
|---|---|
| PHP | 8.4 |
| Composer | 2.x |
| Node.js | 20 atau lebih baru |
| MySQL | 8.x |

Di mesin pengembangan dipakai PHP 8.4.16 dan Node 24 dari Laragon.

---

## Cara memasang

### 1. Pasang dependensi

```bash
composer install
npm install
```

### 2. Siapkan berkas lingkungan

```bash
cp .env.example .env
php artisan key:generate
```

Sesuaikan bagian database di `.env`:

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=binabangsa_pay
DB_USERNAME=root
DB_PASSWORD=
```

### 3. Buat database

```sql
CREATE DATABASE binabangsa_pay;
```

### 4. Jalankan migrasi dan isi data contoh

```bash
php artisan migrate --seed
```

### 5. Bangun aset tampilan

```bash
npm run build
```

Langkah ini **wajib**. Tanpa `npm run build`, setiap halaman akan gagal dengan
pesan `Vite manifest not found`, karena layout memakai `@vite`.

### 6. Jalankan

```bash
php artisan serve
```

Buka `http://127.0.0.1:8000`.

Kalau sedang mengembangkan tampilan, jalankan `npm run dev` di terminal lain
supaya perubahan CSS dan JS langsung terlihat tanpa build ulang.

---

## Akun untuk mencoba

Semua akun dibuat oleh seeder dan memakai password yang sama: `password`.

| Username | Peran |
|---|---|
| `admin` | administrator |
| `kepala` | kepala sekolah |
| `teller` | teller |
| `siswa` | siswa |

> Ganti semua password ini sebelum aplikasi dipakai sungguhan.

---

## Struktur database

Tujuh tabel inti, sama dengan versi aplikasi desktop:

| Tabel | Isi |
|---|---|
| `users` | akun dan peran pengguna |
| `jurusan` | data jurusan |
| `tahun_ajaran` | tahun ajaran, hanya satu yang aktif |
| `kelas` | kelas, terhubung ke jurusan dan tahun ajaran |
| `siswa` | data siswa, terhubung ke kelas dan akun |
| `tarif_spp` | nominal SPP per tahun ajaran |
| `tagihan_spp` | tagihan per siswa per bulan |

Tabel `sessions` dipakai karena `SESSION_DRIVER=database`.

---

## Daftar halaman

### Dashboard
`/dashboard` mengarahkan otomatis sesuai peran. Ada empat dashboard terpisah
untuk admin, kepala sekolah, teller, dan siswa.

### Data master
- `/jurusan` - daftar, tambah, detail, ubah, hapus
- `/kelas` - daftar, tambah, detail, ubah, hapus
- `/tahun-ajaran` - daftar, tambah, detail, ubah, hapus
- `/siswa` - daftar dengan filter, tambah, detail, ubah, hapus
- `/tarif-spp` - daftar, tambah, detail, ubah, hapus
- `/tagihan-spp` - daftar dengan filter, tambah, detail, ubah, hapus
- `/user` - daftar, tambah, detail, ubah, hapus

### Laporan dan pencarian
- `/laporan/pembayaran` - rekap tagihan dengan filter
- `/laporan/tunggakan` - daftar tunggakan per siswa
- `/pencarian` - cari siswa lewat NIS, NISN, atau nama

---

## Aturan bisnis yang sudah berjalan

Ini yang dijaga di kode dan diuji oleh test suite:

- Tahun ajaran hanya boleh **satu** yang berstatus aktif
- Tagihan ganda pada bulan dan tahun yang sama ditolak, dengan pesan yang ramah
- Tandai tunggakan hanya menyentuh tagihan yang sudah lewat jatuh tempo
- Tagihan yang sudah lunas tidak ikut jadi tunggakan
- Jurusan yang masih dipakai kelas tidak bisa dihapus
- Admin tidak bisa menghapus akunnya sendiri
- NIS dan NISN harus unik
- Jenis kelamin hanya boleh `L` atau `P`
- Siswa hanya melihat tagihan miliknya sendiri

---

## Menjalankan test

```bash
php artisan test
```

Test memakai database terpisah, jadi data pengembangan tidak tersentuh. Siapkan
dulu database `binabangsa_pay_testing`, karena `phpunit.xml` diarahkan ke situ.

Hasil terakhir: **22 test lolos, 80 assertion**.

---

## Struktur folder penting

```
app/
  Http/
    Controllers/      11 controller
    Middleware/       PastikanRole.php
  Models/             7 model
resources/
  views/
    layouts/          layout induk
    partials/         navigasi, pesan, error, lencana status
    auth/             halaman login
    dashboard/        4 dashboard sesuai peran
    jurusan/ kelas/ tahun-ajaran/ siswa/ tarif-spp/ tagihan-spp/ user/
    laporan/ pencarian/
routes/
  web.php             semua route
database/
  migrations/         7 migrasi
  seeders/            data contoh
tests/
  Feature/            test controller dan hak akses
```

---

## Catatan teknis

**Controller mengembalikan JSON kalau tampilan belum ada.** Ada helper
`tampilkan()` di `app/Http/Controllers/Controller.php` yang mengecek
`view()->exists()`. Kalau berkas Blade-nya ada, tampilan dipakai. Kalau belum,
data dikirim sebagai JSON supaya route tetap bisa dipanggil tanpa error.

**Antrian dan cache tidak memakai database.** `QUEUE_CONNECTION=sync` dan
`CACHE_STORE=file`, jadi tabel `jobs` dan `cache` tidak diperlukan. Hanya
`sessions` yang disimpan di database.

**Urutan route penting.** Route dengan segmen tetap seperti `/siswa/create`
didaftarkan sebelum route berparameter `/siswa/{siswa}`. Kalau terbalik, kata
"create" akan dibaca sebagai id dan permintaannya berakhir 404.

---

## Yang belum dikerjakan

1. Tabel dan model untuk virtual account, transaksi payment gateway, dan pembayaran
2. Halaman pembayaran dan integrasi payment gateway
3. Callback dari payment gateway
4. Laporan pembayaran yang memakai data transaksi sungguhan

Bagian ini menunggu keputusan guru pembimbing soal skema pembayarannya.

---

## Lisensi

Proyek tugas sekolah. Dibuat untuk SMK Bina Bangsa.
