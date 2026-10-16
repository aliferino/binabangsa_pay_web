<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PastikanRole
{
    /**
     * Batasi akses route hanya untuk role tertentu.
     *
     * Dipakai di route seperti: middleware('role:admin,kepala_sekolah').
     * Kalau belum login, arahkan ke halaman login. Kalau sudah login tetapi
     * role-nya tidak cocok, tolak dengan kode 403.
     */
    public function handle(Request $request, Closure $next, string ...$role): Response
    {
        $user = $request->user();

        if ($user === null) {
            return redirect()->route('login');
        }

        if (! $user->punyaRole(...$role)) {
            abort(403, 'Anda tidak punya akses ke halaman ini.');
        }

        return $next($request);
    }
}
