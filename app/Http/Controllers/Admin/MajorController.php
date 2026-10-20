<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Major;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MajorController extends Controller
{
    /**
     * Daftar jurusan.
     */
    public function index()
    {
        return $this->render('jurusan.index', [
            'majors' => Major::withCount('kelas')->orderBy('nama_jurusan')->get(),
        ]);
    }

    /**
     * Tampilkan form tambah jurusan.
     */
    public function create()
    {
        return $this->render('jurusan.create');
    }

    /**
     * Simpan jurusan baru.
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'kode_jurusan' => ['required', 'string', 'max:255', 'unique:jurusan,kode_jurusan'],
            'nama_jurusan' => ['required', 'string', 'max:255'],
            'keterangan' => ['nullable', 'string'],
        ], [
            'kode_jurusan.required' => 'Kode jurusan wajib diisi.',
            'kode_jurusan.unique' => 'Kode jurusan ini sudah dipakai.',
            'nama_jurusan.required' => 'Nama jurusan wajib diisi.',
        ]);

        Major::create($data);

        return redirect()->route('jurusan.index')->with('sukses', 'Jurusan berhasil ditambahkan.');
    }

    /**
     * Tampilkan detail satu jurusan.
     */
    public function show(Major $jurusan)
    {
        return $this->render('jurusan.show', [
            'jurusan' => $jurusan->load('kelas'),
        ]);
    }

    /**
     * Tampilkan form ubah jurusan.
     */
    public function edit(Major $jurusan)
    {
        return $this->render('jurusan.edit', ['jurusan' => $jurusan]);
    }

    /**
     * Perbarui data jurusan.
     */
    public function update(Request $request, Major $jurusan): RedirectResponse
    {
        $data = $request->validate([
            'kode_jurusan' => ['required', 'string', 'max:255',
                Rule::unique('jurusan', 'kode_jurusan')->ignore($jurusan->id_jurusan, 'id_jurusan')],
            'nama_jurusan' => ['required', 'string', 'max:255'],
            'keterangan' => ['nullable', 'string'],
        ], [
            'kode_jurusan.required' => 'Kode jurusan wajib diisi.',
            'kode_jurusan.unique' => 'Kode jurusan ini sudah dipakai.',
            'nama_jurusan.required' => 'Nama jurusan wajib diisi.',
        ]);

        $jurusan->update($data);

        return redirect()->route('jurusan.index')->with('sukses', 'Jurusan berhasil diperbarui.');
    }

    /**
     * Hapus jurusan.
     *
     * Jurusan yang masih dipakai kelas tidak boleh dihapus, supaya data
     * kelas tidak ikut terhapus lewat cascade.
     */
    public function destroy(Major $jurusan): RedirectResponse
    {
        if ($jurusan->kelas()->exists()) {
            return redirect()->route('jurusan.index')
                ->with('gagal', 'Jurusan ini masih dipakai oleh kelas, jadi belum bisa dihapus.');
        }

        $jurusan->delete();

        return redirect()->route('jurusan.index')->with('sukses', 'Jurusan berhasil dihapus.');
    }
}
