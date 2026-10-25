<?php

namespace App\Providers;

use App\Services\IntegrationSettings;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Kunci API bisa diubah dari halaman admin dan disimpan di berkas
        // terpisah, bukan di .env. Nilainya ditimpa ke config di sini supaya
        // SEMUA kode yang sudah ada (MidtransService, callback, controller)
        // langsung memakai nilai dari berkas tanpa perlu diubah.
        //
        // Pembacaan berkas TIDAK boleh ditaruh di config/midtrans.php: config
        // bisa di-cache, dan berkas tidak boleh disentuh saat config:cache.
        $pengaturan = app(IntegrationSettings::class)->semua();

        config([
            'midtrans.server_key' => $pengaturan['midtrans_server_key'],
            'midtrans.client_key' => $pengaturan['midtrans_client_key'],
            'midtrans.is_production' => $pengaturan['midtrans_is_production'],
        ]);
    }
}
