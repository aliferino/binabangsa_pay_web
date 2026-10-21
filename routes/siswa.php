<?php

use App\Http\Controllers\Siswa\BillController;
use App\Http\Controllers\Siswa\DashboardController;
use App\Http\Controllers\Siswa\PaymentController;
use App\Http\Controllers\Siswa\VirtualAccountController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Route siswa
|--------------------------------------------------------------------------
| Semua route di sini hanya untuk role siswa. Siswa hanya boleh melihat
| data miliknya sendiri, tidak boleh melihat data siswa lain.
|
| Prefix /akun-saya dipakai, bukan /siswa, sebab /siswa dan /siswa/{siswa}
| sudah dipakai route admin di routes/web.php. Kalau dipaksakan pakai
| /siswa, route admin yang berparameter akan menangkap /siswa/tagihan,
| /siswa/pembayaran, dan /siswa/virtual-account sebagai {siswa} sebelum
| middleware role:siswa sempat memeriksa. Dengan /akun-saya, tidak ada
| bentrok sama sekali.
|
| Nama route tetap memakai prefix "siswa." (bukan "akun-saya."), supaya
| kode yang memanggil route('siswa.bills.index') dan sejenisnya tidak
| perlu diubah.
|
| Route lama di routes/web.php (dashboard.siswa) tetap dipertahankan,
| sebab masih dipakai. Berkas ini menambah route baru, tidak
| menggantikan yang lama.
*/

Route::middleware(['auth', 'role:siswa'])->prefix('akun-saya')->name('siswa.')->group(function () {

    // Dashboard.
    Route::get('/dashboard', [DashboardController::class, 'student'])
        ->name('dashboard');

    // Tagihan.
    Route::get('/tagihan', [BillController::class, 'index'])
        ->name('bills.index');

    // Pembayaran.
    Route::get('/pembayaran', [PaymentController::class, 'index'])
        ->name('payments.index');
    Route::get('/pembayaran/{pembayaran}', [PaymentController::class, 'show'])
        ->name('payments.show');

    // Virtual Account.
    Route::get('/virtual-account', [VirtualAccountController::class, 'index'])
        ->name('virtual-accounts.index');
});
