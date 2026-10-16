<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\JurusanController;
use App\Http\Controllers\KelasController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\PencarianController;
use App\Http\Controllers\SiswaController;
use App\Http\Controllers\TagihanSppController;
use App\Http\Controllers\TahunAjaranController;
use App\Http\Controllers\TarifSppController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Route tamu
|--------------------------------------------------------------------------
| Hanya halaman login yang boleh dibuka tanpa masuk dulu.
*/

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.proses');
});

/*
|--------------------------------------------------------------------------
| Route terproteksi
|--------------------------------------------------------------------------
| Semua route di bawah ini wajib login.
|
| PENTING soal urutan: route dengan segmen tetap seperti /siswa/create dan
| /tagihan-spp/lihat WAJIB didaftarkan SEBELUM route berparameter seperti
| /siswa/{siswa}. Kalau terbalik, kata "create" akan dibaca sebagai id dan
| permintaannya berakhir 404, bukan ditolak rapi oleh middleware role.
*/

Route::middleware('auth')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Dashboard. Satu pintu masuk, lalu diarahkan sesuai role.
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/admin', [DashboardController::class, 'admin'])
        ->middleware('role:admin')->name('dashboard.admin');
    Route::get('/dashboard/kepala-sekolah', [DashboardController::class, 'kepalaSekolah'])
        ->middleware('role:kepala_sekolah')->name('dashboard.kepala-sekolah');
    Route::get('/dashboard/teller', [DashboardController::class, 'teller'])
        ->middleware('role:teller')->name('dashboard.teller');
    Route::get('/dashboard/siswa', [DashboardController::class, 'siswa'])
        ->middleware('role:siswa')->name('dashboard.siswa');

    /*
    |----------------------------------------------------------------------
    | BAGIAN 1: route dengan segmen tetap
    |----------------------------------------------------------------------
    | Semua route di sini punya kata tetap di akhir, jadi harus didaftarkan
    | lebih dulu supaya tidak tertangkap route berparameter di bagian 2.
    */

    Route::middleware('role:admin')->group(function () {
        // Form tambah. Wajib di atas route {param}.
        Route::get('/user/create', [UserController::class, 'create'])->name('user.create');
        Route::get('/jurusan/create', [JurusanController::class, 'create'])->name('jurusan.create');
        Route::get('/tahun-ajaran/create', [TahunAjaranController::class, 'create'])->name('tahun-ajaran.create');
        Route::get('/kelas/create', [KelasController::class, 'create'])->name('kelas.create');
        Route::get('/siswa/create', [SiswaController::class, 'create'])->name('siswa.create');
        Route::get('/tarif-spp/create', [TarifSppController::class, 'create'])->name('tarif-spp.create');
        Route::get('/tagihan-spp/create', [TagihanSppController::class, 'create'])->name('tagihan-spp.create');

        // Aksi khusus. Juga wajib di atas route {param}.
        Route::post('/tagihan-spp/tandai-tunggakan', [TagihanSppController::class, 'tandaiTunggakan'])
            ->name('tagihan-spp.tandai-tunggakan');
    });

    // Tagihan untuk teller. Teller boleh melihat, tidak boleh mengubah.
    Route::middleware('role:admin,teller')->group(function () {
        Route::get('/tagihan-spp/lihat', [TagihanSppController::class, 'index'])
            ->name('tagihan-spp.lihat');
    });

    /*
    |----------------------------------------------------------------------
    | BAGIAN 2: daftar dan detail
    |----------------------------------------------------------------------
    | Admin dan kepala sekolah boleh melihat data master. Kepala sekolah
    | hanya boleh melihat, jadi route tambah, ubah, dan hapus dipisah ke
    | bagian 3 dengan middleware admin saja.
    */

    Route::middleware('role:admin,kepala_sekolah')->group(function () {
        Route::get('/jurusan', [JurusanController::class, 'index'])->name('jurusan.index');
        Route::get('/tahun-ajaran', [TahunAjaranController::class, 'index'])->name('tahun-ajaran.index');
        Route::get('/kelas', [KelasController::class, 'index'])->name('kelas.index');
        Route::get('/siswa', [SiswaController::class, 'index'])->name('siswa.index');
        Route::get('/tarif-spp', [TarifSppController::class, 'index'])->name('tarif-spp.index');
    });

    /*
    |----------------------------------------------------------------------
    | BAGIAN 3: aksi tulis, admin saja
    |----------------------------------------------------------------------
    | Termasuk route berparameter dan route ubah. Semua wajib admin.
    */

    Route::middleware('role:admin')->group(function () {
        // Pengguna. Hanya admin, karena ini menyangkut hak akses.
        Route::get('/user', [UserController::class, 'index'])->name('user.index');
        Route::post('/user', [UserController::class, 'store'])->name('user.store');
        Route::get('/user/{user}', [UserController::class, 'show'])->name('user.show');
        Route::get('/user/{user}/edit', [UserController::class, 'edit'])->name('user.edit');
        Route::put('/user/{user}', [UserController::class, 'update'])->name('user.update');
        Route::delete('/user/{user}', [UserController::class, 'destroy'])->name('user.destroy');

        // Jurusan.
        Route::post('/jurusan', [JurusanController::class, 'store'])->name('jurusan.store');
        Route::get('/jurusan/{jurusan}/edit', [JurusanController::class, 'edit'])->name('jurusan.edit');
        Route::put('/jurusan/{jurusan}', [JurusanController::class, 'update'])->name('jurusan.update');
        Route::delete('/jurusan/{jurusan}', [JurusanController::class, 'destroy'])->name('jurusan.destroy');

        // Tahun ajaran.
        Route::post('/tahun-ajaran', [TahunAjaranController::class, 'store'])->name('tahun-ajaran.store');
        Route::get('/tahun-ajaran/{tahunAjaran}/edit', [TahunAjaranController::class, 'edit'])->name('tahun-ajaran.edit');
        Route::put('/tahun-ajaran/{tahunAjaran}', [TahunAjaranController::class, 'update'])->name('tahun-ajaran.update');
        Route::delete('/tahun-ajaran/{tahunAjaran}', [TahunAjaranController::class, 'destroy'])->name('tahun-ajaran.destroy');

        // Kelas.
        Route::post('/kelas', [KelasController::class, 'store'])->name('kelas.store');
        Route::get('/kelas/{kelas}/edit', [KelasController::class, 'edit'])->name('kelas.edit');
        Route::put('/kelas/{kelas}', [KelasController::class, 'update'])->name('kelas.update');
        Route::delete('/kelas/{kelas}', [KelasController::class, 'destroy'])->name('kelas.destroy');

        // Siswa.
        Route::post('/siswa', [SiswaController::class, 'store'])->name('siswa.store');
        Route::get('/siswa/{siswa}/edit', [SiswaController::class, 'edit'])->name('siswa.edit');
        Route::put('/siswa/{siswa}', [SiswaController::class, 'update'])->name('siswa.update');
        Route::delete('/siswa/{siswa}', [SiswaController::class, 'destroy'])->name('siswa.destroy');

        // Tarif SPP.
        Route::post('/tarif-spp', [TarifSppController::class, 'store'])->name('tarif-spp.store');
        Route::get('/tarif-spp/{tarifSpp}/edit', [TarifSppController::class, 'edit'])->name('tarif-spp.edit');
        Route::put('/tarif-spp/{tarifSpp}', [TarifSppController::class, 'update'])->name('tarif-spp.update');
        Route::delete('/tarif-spp/{tarifSpp}', [TarifSppController::class, 'destroy'])->name('tarif-spp.destroy');

        // Tagihan SPP.
        Route::get('/tagihan-spp', [TagihanSppController::class, 'index'])->name('tagihan-spp.index');
        Route::post('/tagihan-spp', [TagihanSppController::class, 'store'])->name('tagihan-spp.store');
        Route::get('/tagihan-spp/{tagihanSpp}', [TagihanSppController::class, 'show'])->name('tagihan-spp.show');
        Route::get('/tagihan-spp/{tagihanSpp}/edit', [TagihanSppController::class, 'edit'])->name('tagihan-spp.edit');
        Route::put('/tagihan-spp/{tagihanSpp}', [TagihanSppController::class, 'update'])->name('tagihan-spp.update');
        Route::delete('/tagihan-spp/{tagihanSpp}', [TagihanSppController::class, 'destroy'])->name('tagihan-spp.destroy');
    });

    /*
    |----------------------------------------------------------------------
    | BAGIAN 4: detail untuk admin dan kepala sekolah
    |----------------------------------------------------------------------
    | Route berparameter yang boleh dibaca kepala sekolah. Didaftarkan
    | paling akhir supaya tidak menutupi route tetap di bagian 1.
    */

    Route::middleware('role:admin,kepala_sekolah')->group(function () {
        Route::get('/jurusan/{jurusan}', [JurusanController::class, 'show'])->name('jurusan.show');
        Route::get('/tahun-ajaran/{tahunAjaran}', [TahunAjaranController::class, 'show'])->name('tahun-ajaran.show');
        Route::get('/kelas/{kelas}', [KelasController::class, 'show'])->name('kelas.show');
        Route::get('/siswa/{siswa}', [SiswaController::class, 'show'])->name('siswa.show');
        Route::get('/tarif-spp/{tarifSpp}', [TarifSppController::class, 'show'])->name('tarif-spp.show');
    });

    /*
    |----------------------------------------------------------------------
    | Laporan
    |----------------------------------------------------------------------
    | Admin, kepala sekolah, dan teller boleh mencetak laporan. Siswa tidak.
    */

    Route::middleware('role:admin,kepala_sekolah,teller')->group(function () {
        Route::get('/laporan/pembayaran', [LaporanController::class, 'pembayaran'])
            ->name('laporan.pembayaran');
        Route::get('/laporan/tunggakan', [LaporanController::class, 'tunggakan'])
            ->name('laporan.tunggakan');
    });

    /*
    |----------------------------------------------------------------------
    | Pencarian
    |----------------------------------------------------------------------
    | Hanya admin dan teller. Siswa tidak perlu, karena data miliknya
    | sudah tampil di dashboard-nya sendiri.
    */

    Route::middleware('role:admin,teller')->group(function () {
        Route::get('/pencarian', [PencarianController::class, 'index'])->name('pencarian.index');
    });
});
