<?php

namespace App\Http\Controllers;

use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\TahunAjaran;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class KelasController extends Controller
{
    /**
     * Daftar kelas.
     */
    public function index()
    {
        return $this->tampilkan('kelas.index', [
            'daftar_kelas' => Kelas::with('jurusan', 'tahunAjaran')
                ->withCount('siswa')
                ->orderBy('nama_kelas')
                ->get(),
        ]);
    }

    /**
     * Tampilkan form tambah kelas.
     */
    public function create()
    {
        return $this->tampilkan('kelas.create', $this->pilihan());
    }

    /**
     * Simpan kelas baru.
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nama_kelas' => ['required', 'string', 'max:255'],
            'tingkat' => ['required', 'string', 'max:255'],
            'id_jurusan' => ['required', 'exists:jurusan,id_jurusan'],
            'id_tahun_ajaran' => ['required', 'exists:tahun_ajaran,id_tahun_ajaran'],
        ], [
            'nama_kelas.required' => 'Nama kelas wajib diisi.',
            'tingkat.required' => 'Tingkat wajib diisi.',
            'id_jurusan.required' => 'Jurusan wajib dipilih.',
            'id_jurusan.exists' => 'Jurusan yang dipilih tidak ditemukan.',
            'id_tahun_ajaran.required' => 'Tahun ajaran wajib dipilih.',
            'id_tahun_ajaran.exists' => 'Tahun ajaran yang dipilih tidak ditemukan.',
        ]);

        Kelas::create($data);

        return redirect()->route('kelas.index')->with('sukses', 'Kelas berhasil ditambahkan.');
    }

    /**
     * Tampilkan detail satu kelas.
     */
    public function show(Kelas $kelas)
    {
        return $this->tampilkan('kelas.show', [
            'kelas' => $kelas->load('jurusan', 'tahunAjaran', 'siswa'),
        ]);
    }

    /**
     * Tampilkan form ubah kelas.
     */
    public function edit(Kelas $kelas)
    {
        return $this->tampilkan('kelas.edit', array_merge(
            ['kelas' => $kelas],
            $this->pilihan(),
        ));
    }

    /**
     * Perbarui data kelas.
     */
    public function update(Request $request, Kelas $kelas): RedirectResponse
    {
        $data = $request->validate([
            'nama_kelas' => ['required', 'string', 'max:255'],
            'tingkat' => ['required', 'string', 'max:255'],
            'id_jurusan' => ['required', 'exists:jurusan,id_jurusan'],
            'id_tahun_ajaran' => ['required', 'exists:tahun_ajaran,id_tahun_ajaran'],
        ], [
            'nama_kelas.required' => 'Nama kelas wajib diisi.',
            'tingkat.required' => 'Tingkat wajib diisi.',
            'id_jurusan.exists' => 'Jurusan yang dipilih tidak ditemukan.',
            'id_tahun_ajaran.exists' => 'Tahun ajaran yang dipilih tidak ditemukan.',
        ]);

        $kelas->update($data);

        return redirect()->route('kelas.index')->with('sukses', 'Kelas berhasil diperbarui.');
    }

    /**
     * Hapus kelas.
     */
    public function destroy(Kelas $kelas): RedirectResponse
    {
        // Kelas yang masih berisi siswa tidak boleh dihapus.
        if ($kelas->siswa()->exists()) {
            return redirect()->route('kelas.index')
                ->with('gagal', 'Kelas ini masih berisi siswa, jadi belum bisa dihapus.');
        }

        $kelas->delete();

        return redirect()->route('kelas.index')->with('sukses', 'Kelas berhasil dihapus.');
    }

    /**
     * Data pilihan jurusan dan tahun ajaran untuk form.
     */
    private function pilihan(): array
    {
        return [
            'daftar_jurusan' => Jurusan::orderBy('nama_jurusan')->get(),
            'daftar_tahun_ajaran' => TahunAjaran::orderByDesc('tahun_ajaran')->get(),
        ];
    }
}
