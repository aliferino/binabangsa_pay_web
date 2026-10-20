<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Tampilkan form login.
     */
    public function showLogin()
    {
        return $this->render('auth.login');
    }

    /**
     * Proses login memakai username dan password.
     *
     * Selain memeriksa kecocokan kredensial, status user juga diperiksa.
     * Akun yang statusnya bukan "aktif" ditolak walau passwordnya benar.
     */
    public function login(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ], [
            'username.required' => 'Username wajib diisi.',
            'password.required' => 'Password wajib diisi.',
        ]);

        if (! Auth::attempt($data)) {
            throw ValidationException::withMessages([
                'username' => 'Username atau password salah.',
            ]);
        }

        if (! Auth::user()->punyaRole('admin', 'kepala_sekolah', 'teller', 'siswa')) {
            Auth::logout();

            throw ValidationException::withMessages([
                'username' => 'Role akun ini tidak dikenali.',
            ]);
        }

        if (Auth::user()->status !== 'aktif') {
            Auth::logout();

            throw ValidationException::withMessages([
                'username' => 'Akun ini sedang tidak aktif. Hubungi administrator.',
            ]);
        }

        $request->session()->regenerate();

        return redirect()->route('dashboard');
    }

    /**
     * Keluar dari sesi yang sedang berjalan.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
