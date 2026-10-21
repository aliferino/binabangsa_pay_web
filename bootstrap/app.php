<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        then: function () {
            Route::middleware('web')
                ->group(base_path('routes/kepsek.php'));
            Route::middleware('web')
                ->group(base_path('routes/teller.php'));
            Route::middleware('web')
                ->group(base_path('routes/siswa.php'));
        },
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => \App\Http\Middleware\CheckRole::class,
        ]);

        // Callback payment gateway datang dari server luar, bukan dari browser
        // pengguna, jadi tidak mungkin membawa token CSRF milik aplikasi ini.
        // Tanpa pengecualian ini setiap callback akan ditolak 419.
        //
        // Pengaman penggantinya ada di CallbackController: transaction_id wajib
        // unik dan pencatatan pembayaran bersifat idempoten.
        $middleware->validateCsrfTokens(except: [
            'callback/payment',
            'callback/xendit',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
