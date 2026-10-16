<?php

namespace App\Http\Controllers;

use App\Models\TahunAjaran;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class TahunAjaranController extends Controller
{
    /**
     * Daftar tahun ajaran.
     */
    public function index()
    {
        return $this->tampilkan('tahun-ajaran.index', [
            'daftar_tahun_ajaran' => TahunAjaran::withCount(['kelas', 'tarifSpp'])
                ->orderByDesc('tahun_ajaran')
                ->get(),
        ]);
    }

    /**
     * Tampilkan form tambah tahun ajaran.
     */
    public function create()
    {
        return $this->tampilkan('tahun-ajaran.create');
    }

    /**
     * Simpan tahun ajaran baru.
     *
     * Kalau tahun ajaran baru ini berstatus aktif, tahun ajaran lain
     * otomatis dinonaktifkan supaya hanya ada satu yang aktif.
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $this->validasi($request);

        DB::transaction(function () use ($data) {
            if ($data['status'] === 'aktif') {
                TahunAjaran::where('status', 'aktif')->update(['status' => 'nonaktif']);
            }

            TahunAjaran::create($data);
        });

        return redirect()->route('tahun-ajaran.index')
            ->with('sukses', 'Tahun ajaran berhasil ditambahkan.');
    }

    /**
     * Tampilkan detail satu tahun ajaran.
     */
    public function show(TahunAjaran $tahunAjaran)
    {
        return $this->tampilkan('tahun-ajaran.show', [
            'tahun_ajaran' => $tahunAjaran->load('kelas', 'tarifSpp'),
        ]);
    }

    /**
     * Tampilkan form ubah tahun ajaran.
     */
    public function edit(TahunAjaran $tahunAjaran)
    {
        return $this->tampilkan('tahun-ajaran.edit', ['tahun_ajaran' => $tahunAjaran]);
    }

    /**
     * Perbarui data tahun ajaran.
     */
    public function update(Request $request, TahunAjaran $tahunAjaran): RedirectResponse
    {
        $data = $this->validasi($request, $tahunAjaran);

        DB::transaction(function () use ($data, $tahunAjaran) {
            if ($data['status'] === 'aktif') {
                TahunAjaran::where('id_tahun_ajaran', '!=', $tahunAjaran->id_tahun_ajaran)
                    ->where('status', 'aktif')
                    ->update(['status' => 'nonaktif']);
            }

            $tahunAjaran->update($data);
        });

        return redirect()->route('tahun-ajaran.index')
            ->with('sukses', 'Tahun ajaran berhasil diperbarui.');
    }

    /**
     * Hapus tahun ajaran.
     */
    public function destroy(TahunAjaran $tahunAjaran): RedirectResponse
    {
        // Tahun ajaran yang masih dipakai kelas atau tarif tidak boleh dihapus.
        if ($tahunAjaran->kelas()->exists() || $tahunAjaran->tarifSpp()->exists()) {
            return redirect()->route('tahun-ajaran.index')
                ->with('gagal', 'Tahun ajaran ini masih dipakai kelas atau tarif, jadi belum bisa dihapus.');
        }

        $tahunAjaran->delete();

        return redirect()->route('tahun-ajaran.index')
            ->with('sukses', 'Tahun ajaran berhasil dihapus.');
    }

    /**
     * Aturan validasi yang dipakai store dan update.
     *
     * Nama tahun ajaran wajib unik. Saat update, baris yang sedang diubah
     * dikecualikan supaya tidak dianggap bentrok dengan dirinya sendiri.
     */
    private function validasi(Request $request, ?TahunAjaran $tahunAjaran = null): array
    {
        $aturanUnik = Rule::unique('tahun_ajaran', 'tahun_ajaran');

        if ($tahunAjaran !== null) {
            $aturanUnik = $aturanUnik->ignore($tahunAjaran->id_tahun_ajaran, 'id_tahun_ajaran');
        }

        return $request->validate([
            'tahun_ajaran' => ['required', 'string', 'max:255', $aturanUnik],
            'tanggal_mulai' => ['required', 'date'],
            'tanggal_selesai' => ['required', 'date', 'after_or_equal:tanggal_mulai'],
            'status' => ['required', Rule::in(['aktif', 'nonaktif'])],
        ], [
            'tahun_ajaran.required' => 'Tahun ajaran wajib diisi.',
            'tahun_ajaran.unique' => 'Tahun ajaran ini sudah ada.',
            'tanggal_mulai.required' => 'Tanggal mulai wajib diisi.',
            'tanggal_mulai.date' => 'Tanggal mulai harus berupa tanggal yang sah.',
            'tanggal_selesai.required' => 'Tanggal selesai wajib diisi.',
            'tanggal_selesai.after_or_equal' => 'Tanggal selesai tidak boleh lebih awal dari tanggal mulai.',
            'status.in' => 'Status harus aktif atau nonaktif.',
        ]);
    }
}
