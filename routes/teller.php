<?php

use App\Http\Controllers\Teller\DashboardController;
use App\Http\Controllers\Teller\PaymentController;
use App\Http\Controllers\Teller\SearchController;
use App\Http\Controllers\Teller\TuitionBillController;
use App\Http\Controllers\Teller\VirtualAccountController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Route teller
|--------------------------------------------------------------------------
| Semua route di sini hanya untuk role teller. Teller boleh melihat dan
| mencatat pembayaran, tetapi tidak boleh mengelola data master.
|
| Route lama di routes/web.php (pembayaran.index, tagihan-spp.index,
| virtual-account.index, pencarian.index, dan lain-lain) tetap
| dipertahankan, sebab masih dipakai. Berkas ini menambah route baru
| dengan prefix /teller, tidak menggantikan yang lama.
|
| Urutan: segmen tetap (/teller/pencarian) didaftarkan SEBELUM route
| berparameter (/teller/tagihan/{tagihanSpp}) supaya kata "pencarian"
| tidak tertangkap sebagai parameter.
*/

Route::middleware(['auth', 'role:teller'])->prefix('teller')->name('teller.')->group(function () {

    // Dashboard.
    Route::get('/dashboard', [DashboardController::class, 'teller'])
        ->name('dashboard');

    // Pencarian. Segmen tetap, wajib di atas route berparameter.
    Route::get('/pencarian', [SearchController::class, 'index'])
        ->name('search.index');

    // Tagihan.
    Route::get('/tagihan', [TuitionBillController::class, 'index'])
        ->name('bills.index');
    Route::get('/tagihan/{tagihanSpp}', [TuitionBillController::class, 'show'])
        ->name('bills.show');

    // Pembayaran.
    Route::get('/pembayaran', [PaymentController::class, 'index'])
        ->name('payments.index');
    Route::get('/pembayaran/{pembayaran}', [PaymentController::class, 'show'])
        ->name('payments.show');

    // Virtual Account.
    Route::get('/virtual-account', [VirtualAccountController::class, 'index'])
        ->name('virtual-accounts.index');
    Route::get('/virtual-account/{virtualAccount}', [VirtualAccountController::class, 'show'])
        ->name('virtual-accounts.show');
});
