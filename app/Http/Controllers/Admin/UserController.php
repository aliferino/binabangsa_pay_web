<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Daftar pengguna.
     */
    public function index()
    {
        return $this->render('admin.users.index', [
            'users' => User::orderBy('nama')->get(),
        ]);
    }


    /**
     * Simpan pengguna baru.
     *
     * Password wajib saat menambah, karena akun tanpa password tidak bisa
     * dipakai login.
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'unique:users,username'],
            'password' => ['required', 'string', 'min:6'],
            'role' => ['required', Rule::in(['admin', 'kepala_sekolah', 'teller', 'siswa'])],
            'status' => ['required', Rule::in(['aktif', 'nonaktif'])],
        ], [
            'nama.required' => 'Nama wajib diisi.',
            'username.required' => 'Username wajib diisi.',
            'username.unique' => 'Username ini sudah dipakai.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal 6 karakter.',
            'role.required' => 'Role wajib dipilih.',
            'role.in' => 'Role harus salah satu dari: admin, kepala_sekolah, teller, siswa.',
        ]);

        User::create($data);

        return redirect()->route('user.index')->with('sukses', 'Pengguna berhasil ditambahkan.');
    }

    /**
     * Tampilkan detail satu pengguna.
     */
    public function show(User $user)
    {
        return $this->render('admin.users.show', [
            'user' => $user->load('siswa'),
        ]);
    }

    /**
     * Form ubah sekarang berupa modal di halaman daftar. Route lama
     * tetap ada supaya tautan Ubah di halaman detail tidak rusak, tetapi
     * sekarang hanya mengalihkan ke daftar dengan penanda modal terbuka.
     */
    public function edit(User $user): RedirectResponse
    {
        return redirect()
            ->route('user.index', ['ubah' => $user->id_user])
            ->with('buka_modal', 'ubah');
    }

    /**
     * Perbarui data pengguna.
     *
     * Password boleh dikosongkan. Kalau kosong, password lama dipertahankan.
     */
    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255',
                Rule::unique('users', 'username')->ignore($user->id_user, 'id_user')],
            'password' => ['nullable', 'string', 'min:6'],
            'role' => ['required', Rule::in(['admin', 'kepala_sekolah', 'teller', 'siswa'])],
            'status' => ['required', Rule::in(['aktif', 'nonaktif'])],
        ], [
            'nama.required' => 'Nama wajib diisi.',
            'username.required' => 'Username wajib diisi.',
            'username.unique' => 'Username ini sudah dipakai.',
            'password.min' => 'Password minimal 6 karakter.',
            'role.in' => 'Role harus salah satu dari: admin, kepala_sekolah, teller, siswa.',
        ]);

        if (empty($data['password'])) {
            unset($data['password']);
        }

        $user->update($data);

        return redirect()->route('user.index')->with('sukses', 'Pengguna berhasil diperbarui.');
    }

    /**
     * Hapus pengguna.
     */
    public function destroy(User $user): RedirectResponse
    {
        // Cegah user menghapus akunnya sendiri supaya tidak terkunci keluar.
        if ($user->id_user === auth()->id()) {
            return redirect()->route('user.index')
                ->with('gagal', 'Anda tidak bisa menghapus akun sendiri.');
        }

        $user->delete();

        return redirect()->route('user.index')->with('sukses', 'Pengguna berhasil dihapus.');
    }
}
