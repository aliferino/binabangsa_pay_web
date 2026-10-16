<?php

namespace App\Http\Controllers;

use App\Models\Jurusan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class JurusanController extends Controller
{
    /**
     * Daftar jurusan.
     */
    public function index()
    {
        return $this->tampilkan('jurusan.index', [
            'daftar_jurusan' => Jurusan::withCount('kelas')->orderBy('nama_jurusan')->get(),
        ]);
    }

    /**
     * Tampilkan form tambah jurusan.
     */
    public function create()
    {
        return $this->tampilkan('jurusan.create');
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

        Jurusan::create($data);

        return redirect()->route('jurusan.index')->with('sukses', 'Jurusan berhasil ditambahkan.');
    }

    /**
     * Tampilkan detail satu jurusan.
     */
    public function show(Jurusan $jurusan)
    {
        return $this->tampilkan('jurusan.show', [
            'jurusan' => $jurusan->load('kelas'),
        ]);
    }

    /**
     * Tampilkan form ubah jurusan.
     */
    public function edit(Jurusan $jurusan)
    {
        return $this->tampilkan('jurusan.edit', ['jurusan' => $jurusan]);
    }

    /**
     * Perbarui data jurusan.
     */
    public function update(Request $request, Jurusan $jurusan): RedirectResponse
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
    public function destroy(Jurusan $jurusan): RedirectResponse
    {
        if ($jurusan->kelas()->exists()) {
            return redirect()->route('jurusan.index')
                ->with('gagal', 'Jurusan ini masih dipakai oleh kelas, jadi belum bisa dihapus.');
        }

        $jurusan->delete();

        return redirect()->route('jurusan.index')->with('sukses', 'Jurusan berhasil dihapus.');
    }
}
