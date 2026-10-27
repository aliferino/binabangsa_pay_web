<?php

use App\Http\Controllers\Admin\AcademicYearController;
use App\Http\Controllers\Admin\ClassroomController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\IntegrationController;
use App\Http\Controllers\Admin\GatewayTransactionController;
use App\Http\Controllers\Admin\MajorController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\SearchController;
use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\Admin\TuitionRateController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Kepsek\DashboardController as KepsekDashboardController;
use App\Http\Controllers\Public\CallbackController;
use App\Http\Controllers\Public\SimulationController;
use App\Http\Controllers\Public\MidtransCallbackController;
use App\Http\Controllers\Public\MockCallbackController;
use App\Http\Controllers\Siswa\DashboardController as SiswaDashboardController;
use App\Http\Controllers\Teller\DashboardController as TellerDashboardController;
use App\Http\Controllers\Teller\PaymentController;
use App\Http\Controllers\Teller\TuitionBillController;
use App\Http\Controllers\Teller\VirtualAccountController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Kepsek\DashboardController as KepsekDashboard;
use App\Http\Controllers\Kepsek\ReportController as KepsekReport;
use App\Http\Controllers\Kepsek\StudentController as KepsekStudent;
use App\Http\Controllers\Teller\DashboardController as TellerDashboard;
use App\Http\Controllers\Teller\SearchController as TellerSearch;
use App\Http\Controllers\Siswa\BillController as SiswaBill;
use App\Http\Controllers\Siswa\DashboardController as SiswaDashboard;
use App\Http\Controllers\Siswa\PaymentController as SiswaPayment;
use App\Http\Controllers\Siswa\VirtualAccountController as SiswaVirtualAccount;

/*
|--------------------------------------------------------------------------
| Route tamu
|--------------------------------------------------------------------------
| Halaman depan boleh dibuka tanpa masuk. Yang sudah punya sesi langsung
| dilempar ke dashboard supaya tidak mengetik ulang alamat.
*/

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : view('beranda');
})->name('beranda');

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
| Callback Midtrans
|--------------------------------------------------------------------------
| Tanpa auth dan tanpa CSRF, karena pemanggilnya server Midtrans. Pengamannya
| adalah signature_key SHA512 yang diperiksa lebih dulu di
| MidtransCallbackController. Xendit tetap ada sebagai jalur cadangan.
*/

Route::post('/callback/midtrans', [MidtransCallbackController::class, 'handle'])
    ->name('callback.midtrans');

/*
|--------------------------------------------------------------------------
| Callback mock gateway
|--------------------------------------------------------------------------
| Tanpa auth dan tanpa CSRF, karena pemanggilnya mock gateway lokal, bukan
| pengguna yang punya sesi login. Pengaman sebenarnya adalah signature_key
| yang diperiksa lebih dulu di dalam MockCallbackController.
*/

Route::post('/callback/mock', [MockCallbackController::class, 'handle'])
    ->name('callback.mock');

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
    |
    | Pencatatan tunai dan QRIS dipegang admin saja. Teller tidak menerima
    | uang tunai dan tidak boleh melunasi tagihan secara manual, jadi kedua
    | route itu dipisah ke grup admin di bawah.
    */

    Route::middleware('role:admin')->group(function () {
        Route::post('/pembayaran/tunai', [PaymentController::class, 'tunai'])
            ->name('pembayaran.tunai');
        Route::post('/pembayaran/qris', [PaymentController::class, 'qris'])
            ->name('pembayaran.qris');
    });

    Route::middleware('role:admin,teller')->group(function () {
        Route::get('/pembayaran/ringkasan', [PaymentController::class, 'ringkasan'])
            ->name('pembayaran.ringkasan');

        Route::post('/virtual-account/generate', [VirtualAccountController::class, 'generate'])
            ->name('virtual-account.generate');
        Route::post('/virtual-account/{virtualAccount}/nonaktifkan', [VirtualAccountController::class, 'deactivate'])
            ->name('virtual-account.nonaktifkan');
        Route::post('/virtual-account/{virtualAccount}/aktifkan', [VirtualAccountController::class, 'activate'])
            ->name('virtual-account.aktifkan');

        // Midtrans. Segmen terakhirnya kata tetap, jadi tidak tertukar dengan
        // route berparameter di atas.
        Route::post('/virtual-account/{siswa}/buat-midtrans', [VirtualAccountController::class, 'createMidtrans'])
            ->name('virtual-account.buat-midtrans');

        // Xendit. Tetap ada sebagai jalur cadangan.
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
    | Transaksi payment gateway
    |----------------------------------------------------------------------
    | Read-only, dibuat oleh callback gateway, bukan oleh operator. Admin,
    | kepala sekolah, dan teller boleh melihat, karena ini yang ditanyakan
    | siswa saat transfernya belum terbaca. Tidak ada route tulis.
    |
    | Dua pintu masuk ke tabel yang sama:
    |   /transaksi-gateway  seluruh baris gateway
    |   /transaksi-va       hanya baris yang menempel pada virtual account
    |
    | Segmen tetap didaftarkan lebih dulu, sebelum route berparameter.
    */
    Route::middleware('role:admin,kepala_sekolah,teller')->group(function () {
        Route::get('/transaksi-gateway', [GatewayTransactionController::class, 'index'])
            ->name('gateway-transactions.index');
        Route::get('/transaksi-va', [GatewayTransactionController::class, 'va'])
            ->name('va-transactions.index');
        Route::get('/transaksi-gateway/{transaction}', [GatewayTransactionController::class, 'show'])
            ->name('gateway-transactions.show');
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
        Route::get('/laporan/rekap', [ReportController::class, 'recap'])
            ->name('laporan.rekap');
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

    /*
    |----------------------------------------------------------------------
    | Pengaturan integrasi
    |----------------------------------------------------------------------
    | Admin saja. Menyalakan tunnel dan mengirim notifikasi uji menyentuh
    | konfigurasi pembayaran, jadi tidak dibuka untuk role lain.
    */

    Route::middleware('role:admin')->group(function () {
        Route::get('/pengaturan/integrasi', [IntegrationController::class, 'index'])->name('integrasi.index');
        Route::post('/pengaturan/integrasi/kunci', [IntegrationController::class, 'simpanKunci'])->name('integrasi.kunci.simpan');
        Route::post('/pengaturan/integrasi/kunci/reset', [IntegrationController::class, 'resetKunci'])->name('integrasi.kunci.reset');
        Route::post('/pengaturan/integrasi/tunnel', [IntegrationController::class, 'nyalakanTunnel'])->name('integrasi.tunnel.nyalakan');
        Route::delete('/pengaturan/integrasi/tunnel', [IntegrationController::class, 'matikanTunnel'])->name('integrasi.tunnel.matikan');
        Route::post('/pengaturan/integrasi/uji', [IntegrationController::class, 'ujiNotifikasi'])->name('integrasi.uji');
    });
});

/*
|----------------------------------------------------------------------
| Simulasi transfer publik
|----------------------------------------------------------------------
| Sengaja di luar grup auth, karena siswa tidak punya akun di sistem ini.
| Tetap di dalam grup web, jadi CSRF aktif untuk route tulisnya. Isinya
| hanya data simulasi, dan seluruh halaman ditolak 403 kalau konfigurasi
| Midtrans ada di mode produksi.
|
| Route sukses didaftarkan SEBELUM route berparameter supaya tidak
| tertukar.
*/

Route::get('/simulasi', [SimulationController::class, 'index'])->name('simulasi.index');
Route::post('/simulasi', [SimulationController::class, 'cari'])->name('simulasi.cari');
Route::get('/simulasi/sukses/{nomorTransaksi}/bukti', [SimulationController::class, 'bukti'])->name('simulasi.bukti');
Route::get('/simulasi/sukses/{nomorTransaksi}', [SimulationController::class, 'sukses'])->name('simulasi.sukses');
Route::get('/simulasi/{nomorVa}', [SimulationController::class, 'detail'])->name('simulasi.detail');
Route::post('/simulasi/{nomorVa}/bayar', [SimulationController::class, 'bayar'])->name('simulasi.bayar');

/*
|--------------------------------------------------------------------------
| Route kepala sekolah
|--------------------------------------------------------------------------
| Hanya untuk role kepala_sekolah. Kepala sekolah hanya boleh melihat.
| Digabung ke berkas ini supaya tidak ada pendaftaran tambahan di
| bootstrap/app.php; seluruh route aplikasi cukup di satu tempat.
*/

Route::middleware(['auth', 'role:kepala_sekolah'])->prefix('kepsek')->name('kepsek.')->group(function () {

    // Dashboard.
    Route::get('/dashboard', [KepsekDashboard::class, 'principal'])
        ->name('dashboard');

    // Siswa. Read-only: hanya index dan show.
    Route::get('/siswa', [KepsekStudent::class, 'index'])
        ->name('students.index');
    Route::get('/siswa/{siswa}', [KepsekStudent::class, 'show'])
        ->name('students.show');

    // Laporan.
    Route::get('/laporan/pembayaran', [KepsekReport::class, 'payments'])
        ->name('reports.payments');
    Route::get('/laporan/tunggakan', [KepsekReport::class, 'arrears'])
        ->name('reports.arrears');
    Route::get('/laporan/rekap', [KepsekReport::class, 'recap'])
        ->name('reports.recap');
});

/*
|--------------------------------------------------------------------------
| Route teller
|--------------------------------------------------------------------------
| Hanya untuk role teller. Boleh melihat dan mencatat pembayaran, tidak
| boleh mengelola data master. Segmen tetap (/teller/pencarian)
| didaftarkan SEBELUM route berparameter supaya kata "pencarian" tidak
| tertangkap sebagai parameter.
*/

Route::middleware(['auth', 'role:teller'])->prefix('teller')->name('teller.')->group(function () {

    // Dashboard.
    Route::get('/dashboard', [TellerDashboard::class, 'teller'])
        ->name('dashboard');

    // Pencarian. Segmen tetap, wajib di atas route berparameter.
    Route::get('/pencarian', [TellerSearch::class, 'index'])
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

/*
|--------------------------------------------------------------------------
| Route siswa
|--------------------------------------------------------------------------
| Hanya untuk role siswa, dan hanya data miliknya sendiri. Prefix
| /akun-saya dipakai, bukan /siswa, sebab /siswa/{siswa} milik admin akan
| menangkap /siswa/tagihan sebagai {siswa} sebelum middleware sempat
| memeriksa. Nama route tetap bersisip "siswa." supaya pemanggil lama
| tidak perlu diubah.
*/

Route::middleware(['auth', 'role:siswa'])->prefix('akun-saya')->name('siswa.')->group(function () {

    // Dashboard.
    Route::get('/dashboard', [SiswaDashboard::class, 'student'])
        ->name('dashboard');

    // Tagihan.
    Route::get('/tagihan', [SiswaBill::class, 'index'])
        ->name('bills.index');

    // Pembayaran.
    Route::get('/pembayaran', [SiswaPayment::class, 'index'])
        ->name('payments.index');
    Route::get('/pembayaran/{pembayaran}', [SiswaPayment::class, 'show'])
        ->name('payments.show');

    // Virtual Account.
    Route::get('/virtual-account', [SiswaVirtualAccount::class, 'index'])
        ->name('virtual-accounts.index');
});
