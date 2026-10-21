<?php

use App\Http\Controllers\Kepsek\DashboardController;
use App\Http\Controllers\Kepsek\ReportController;
use App\Http\Controllers\Kepsek\StudentController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Route kepala sekolah
|--------------------------------------------------------------------------
| Semua route di sini hanya untuk role kepala_sekolah. Kepala sekolah hanya
| boleh melihat, tidak boleh menambah, mengubah, atau menghapus.
|
| Route lama di routes/web.php (dashboard.kepala-sekolah, laporan.pembayaran,
| laporan.tunggakan) tetap dipertahankan, sebab masih dipakai. Berkas ini
| menambah route baru dengan prefix /kepsek, tidak menggantikan yang lama.
*/

Route::middleware(['auth', 'role:kepala_sekolah'])->prefix('kepsek')->name('kepsek.')->group(function () {

    // Dashboard.
    Route::get('/dashboard', [DashboardController::class, 'principal'])
        ->name('dashboard');

    // Siswa. Read-only: hanya index dan show.
    Route::get('/siswa', [StudentController::class, 'index'])
        ->name('students.index');
    Route::get('/siswa/{siswa}', [StudentController::class, 'show'])
        ->name('students.show');

    // Laporan.
    Route::get('/laporan/pembayaran', [ReportController::class, 'payments'])
        ->name('reports.payments');
    Route::get('/laporan/tunggakan', [ReportController::class, 'arrears'])
        ->name('reports.arrears');
});
