<?php

use App\Http\Controllers\Admin\AcademicYearController;
use App\Http\Controllers\Admin\ClassroomController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\MajorController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\SearchController;
use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\Admin\TuitionRateController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Kepsek\DashboardController as KepsekDashboardController;
use App\Http\Controllers\Public\CallbackController;
use App\Http\Controllers\Siswa\DashboardController as SiswaDashboardController;
use App\Http\Controllers\Teller\DashboardController as TellerDashboardController;
use App\Http\Controllers\Teller\PaymentController;
use App\Http\Controllers\Teller\TuitionBillController;
use App\Http\Controllers\Teller\VirtualAccountController;
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
| Callback payment gateway
|--------------------------------------------------------------------------
| Tanpa middleware auth, karena yang memanggil adalah server payment gateway,
| bukan pengguna yang punya sesi login. Tetap di dalam grup web, jadi
| perlindungan CSRF tetap berlaku untuk route lain di berkas ini.
|
| Route ini dikecualikan dari CSRF di bootstrap/app.php, sebab payment gateway
| tidak mungkin mengirim token CSRF milik aplikasi ini. Penggantinya adalah
| pencocokan transaction_id yang wajib unik, ditambah pemeriksaan idempoten di
| dalam CallbackController.
*/

Route::post('/callback/payment', [CallbackController::class, 'handle'])
    ->name('callback.payment');

/*
|--------------------------------------------------------------------------
| Callback Xendit
|--------------------------------------------------------------------------
| Sama seperti callback di atas: tanpa auth dan tanpa CSRF, karena pemanggilnya
| server Xendit. Pengaman sebenarnya adalah header x-callback-token yang
| diperiksa lebih dulu di dalam CallbackController.
*/

Route::post('/callback/xendit', [CallbackController::class, 'xendit'])
    ->name('callback.xendit');

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
    Route::get('/dashboard/kepala-sekolah', [KepsekDashboardController::class, 'principal'])
        ->middleware('role:kepala_sekolah')->name('dashboard.kepala-sekolah');
    Route::get('/dashboard/teller', [TellerDashboardController::class, 'teller'])
        ->middleware('role:teller')->name('dashboard.teller');
    Route::get('/dashboard/siswa', [SiswaDashboardController::class, 'student'])
        ->middleware('role:siswa')->name('dashboard.siswa');

    /*
    |----------------------------------------------------------------------
    | BAGIAN 1: route dengan segmen tetap
    |----------------------------------------------------------------------
    | Semua route di sini punya kata tetap di akhir, jadi harus didaftarkan
    | lebih dulu supaya tidak tertangkap route berparameter di bagian 2.
    */

    Route::middleware('role:admin')->group(function () {
        // Aksi khusus. Juga wajib di atas route {param}.
        Route::post('/tagihan-spp/tandai-tunggakan', [TuitionBillController::class, 'markAsArrears'])
            ->name('tagihan-spp.tandai-tunggakan');
    });

    // Tagihan untuk teller. Teller boleh melihat, tidak boleh mengubah.
    Route::middleware('role:admin,teller')->group(function () {
        Route::get('/tagihan-spp/lihat', [TuitionBillController::class, 'index'])
            ->name('tagihan-spp.lihat');
    });

    /*
    |----------------------------------------------------------------------
    | Pembayaran dan Virtual Account: route dengan segmen tetap
    |----------------------------------------------------------------------
    | Sama seperti bagian 1 di atas, route di sini wajib didaftarkan SEBELUM
    | route berparameter /pembayaran/{pembayaran} dan
    | /virtual-account/{virtualAccount}. Kalau terbalik, kata "tunai",
    | "qris", "generate", atau "nonaktifkan" akan dibaca sebagai id dan
    | permintaannya berakhir 404.
    |
    | Siswa sengaja tidak diberi akses ke seluruh blok ini, karena mencatat
    | dan mengubah pembayaran bukan haknya.
    */

    Route::middleware('role:admin,teller')->group(function () {
        Route::post('/pembayaran/tunai', [PaymentController::class, 'tunai'])
            ->name('pembayaran.tunai');
        Route::post('/pembayaran/qris', [PaymentController::class, 'qris'])
            ->name('pembayaran.qris');
        Route::get('/pembayaran/ringkasan', [PaymentController::class, 'ringkasan'])
            ->name('pembayaran.ringkasan');

        Route::post('/virtual-account/generate', [VirtualAccountController::class, 'generate'])
            ->name('virtual-account.generate');
        Route::post('/virtual-account/{virtualAccount}/nonaktifkan', [VirtualAccountController::class, 'deactivate'])
            ->name('virtual-account.nonaktifkan');
        Route::post('/virtual-account/{virtualAccount}/aktifkan', [VirtualAccountController::class, 'activate'])
            ->name('virtual-account.aktifkan');

        // Xendit. Segmen terakhirnya kata tetap, jadi tidak tertukar dengan
        // route berparameter di atas.
        Route::post('/virtual-account/{siswa}/buat-xendit', [VirtualAccountController::class, 'createXendit'])
            ->name('virtual-account.buat-xendit');
        Route::post('/virtual-account/{virtualAccount}/simulasi', [VirtualAccountController::class, 'simulasi'])
            ->name('virtual-account.simulasi');
    });

    /*
    |----------------------------------------------------------------------
    | Pembayaran dan Virtual Account: daftar dan detail
    |----------------------------------------------------------------------
    | Didaftarkan setelah segmen tetap di atas. Kepala sekolah boleh melihat
    | sebagai bahan laporan, tetapi tidak boleh mencatat pembayaran.
    */

    Route::middleware('role:admin,teller,kepala_sekolah')->group(function () {
        Route::get('/pembayaran', [PaymentController::class, 'index'])->name('pembayaran.index');
        Route::get('/pembayaran/{pembayaran}', [PaymentController::class, 'show'])->name('pembayaran.show');
    });

    Route::middleware('role:admin,teller')->group(function () {
        Route::get('/virtual-account', [VirtualAccountController::class, 'index'])->name('virtual-account.index');
        Route::get('/virtual-account/{virtualAccount}', [VirtualAccountController::class, 'show'])->name('virtual-account.show');
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
        Route::get('/jurusan', [MajorController::class, 'index'])->name('jurusan.index');
        Route::get('/tahun-ajaran', [AcademicYearController::class, 'index'])->name('tahun-ajaran.index');
        Route::get('/kelas', [ClassroomController::class, 'index'])->name('kelas.index');
        Route::get('/siswa', [StudentController::class, 'index'])->name('siswa.index');
        Route::get('/tarif-spp', [TuitionRateController::class, 'index'])->name('tarif-spp.index');
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
        Route::post('/jurusan', [MajorController::class, 'store'])->name('jurusan.store');
        Route::get('/jurusan/{jurusan}/edit', [MajorController::class, 'edit'])->name('jurusan.edit');
        Route::put('/jurusan/{jurusan}', [MajorController::class, 'update'])->name('jurusan.update');
        Route::delete('/jurusan/{jurusan}', [MajorController::class, 'destroy'])->name('jurusan.destroy');

        // Tahun ajaran.
        Route::post('/tahun-ajaran', [AcademicYearController::class, 'store'])->name('tahun-ajaran.store');
        Route::get('/tahun-ajaran/{tahunAjaran}/edit', [AcademicYearController::class, 'edit'])->name('tahun-ajaran.edit');
        Route::put('/tahun-ajaran/{tahunAjaran}', [AcademicYearController::class, 'update'])->name('tahun-ajaran.update');
        Route::delete('/tahun-ajaran/{tahunAjaran}', [AcademicYearController::class, 'destroy'])->name('tahun-ajaran.destroy');

        // Kelas.
        Route::post('/kelas', [ClassroomController::class, 'store'])->name('kelas.store');
        Route::get('/kelas/{kelas}/edit', [ClassroomController::class, 'edit'])->name('kelas.edit');
        Route::put('/kelas/{kelas}', [ClassroomController::class, 'update'])->name('kelas.update');
        Route::delete('/kelas/{kelas}', [ClassroomController::class, 'destroy'])->name('kelas.destroy');

        // Siswa.
        Route::post('/siswa', [StudentController::class, 'store'])->name('siswa.store');
        Route::get('/siswa/{siswa}/edit', [StudentController::class, 'edit'])->name('siswa.edit');
        Route::put('/siswa/{siswa}', [StudentController::class, 'update'])->name('siswa.update');
        Route::delete('/siswa/{siswa}', [StudentController::class, 'destroy'])->name('siswa.destroy');

        // Tarif SPP.
        Route::post('/tarif-spp', [TuitionRateController::class, 'store'])->name('tarif-spp.store');
        Route::get('/tarif-spp/{tarifSpp}/edit', [TuitionRateController::class, 'edit'])->name('tarif-spp.edit');
        Route::put('/tarif-spp/{tarifSpp}', [TuitionRateController::class, 'update'])->name('tarif-spp.update');
        Route::delete('/tarif-spp/{tarifSpp}', [TuitionRateController::class, 'destroy'])->name('tarif-spp.destroy');

        // Tagihan SPP.
        Route::get('/tagihan-spp', [TuitionBillController::class, 'index'])->name('tagihan-spp.index');
        Route::post('/tagihan-spp', [TuitionBillController::class, 'store'])->name('tagihan-spp.store');
        Route::get('/tagihan-spp/{tagihanSpp}', [TuitionBillController::class, 'show'])->name('tagihan-spp.show');
        Route::get('/tagihan-spp/{tagihanSpp}/edit', [TuitionBillController::class, 'edit'])->name('tagihan-spp.edit');
        Route::put('/tagihan-spp/{tagihanSpp}', [TuitionBillController::class, 'update'])->name('tagihan-spp.update');
        Route::delete('/tagihan-spp/{tagihanSpp}', [TuitionBillController::class, 'destroy'])->name('tagihan-spp.destroy');
    });

    /*
    |----------------------------------------------------------------------
    | BAGIAN 4: detail untuk admin dan kepala sekolah
    |----------------------------------------------------------------------
    | Route berparameter yang boleh dibaca kepala sekolah. Didaftarkan
    | paling akhir supaya tidak menutupi route tetap di bagian 1.
    */

    Route::middleware('role:admin,kepala_sekolah')->group(function () {
        Route::get('/jurusan/{jurusan}', [MajorController::class, 'show'])->name('jurusan.show');
        Route::get('/tahun-ajaran/{tahunAjaran}', [AcademicYearController::class, 'show'])->name('tahun-ajaran.show');
        Route::get('/kelas/{kelas}', [ClassroomController::class, 'show'])->name('kelas.show');
        Route::get('/siswa/{siswa}', [StudentController::class, 'show'])->name('siswa.show');
        Route::get('/tarif-spp/{tarifSpp}', [TuitionRateController::class, 'show'])->name('tarif-spp.show');
    });

    /*
    |----------------------------------------------------------------------
    | Laporan
    |----------------------------------------------------------------------
    | Admin, kepala sekolah, dan teller boleh mencetak laporan. Siswa tidak.
    */

    Route::middleware('role:admin,kepala_sekolah,teller')->group(function () {
        Route::get('/laporan/pembayaran', [ReportController::class, 'payments'])
            ->name('laporan.pembayaran');
        Route::get('/laporan/tunggakan', [ReportController::class, 'arrears'])
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
        Route::get('/pencarian', [SearchController::class, 'index'])->name('pencarian.index');
    });
});
