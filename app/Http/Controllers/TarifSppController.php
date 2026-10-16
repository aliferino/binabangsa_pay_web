<?php

namespace App\Http\Controllers;

use App\Models\TagihanSpp;
use App\Models\TahunAjaran;
use App\Models\TarifSpp;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TarifSppController extends Controller
{
    /**
     * Daftar tarif SPP.
     */
    public function index()
    {
        return $this->tampilkan('tarif-spp.index', [
            'daftar_tarif' => TarifSpp::with('tahunAjaran')
                ->orderByDesc('id_tahun_ajaran')
                ->get(),
        ]);
    }

    /**
     * Tampilkan form tambah tarif.
     */
    public function create()
    {
        return $this->tampilkan('tarif-spp.create', $this->pilihan());
    }

    /**
     * Simpan tarif baru.
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate($this->aturan(), $this->pesan());

        TarifSpp::create($data);

        return redirect()->route('tarif-spp.index')->with('sukses', 'Tarif SPP berhasil ditambahkan.');
    }

    /**
     * Tampilkan detail satu tarif.
     */
    public function show(TarifSpp $tarifSpp)
    {
        return $this->tampilkan('tarif-spp.show', [
            'tarif' => $tarifSpp->load('tahunAjaran'),
        ]);
    }

    /**
     * Tampilkan form ubah tarif.
     */
    public function edit(TarifSpp $tarifSpp)
    {
        return $this->tampilkan('tarif-spp.edit', array_merge(
            ['tarif' => $tarifSpp],
            $this->pilihan(),
        ));
    }

    /**
     * Perbarui data tarif.
     */
    public function update(Request $request, TarifSpp $tarifSpp): RedirectResponse
    {
        $data = $request->validate($this->aturan(), $this->pesan());

        $tarifSpp->update($data);

        return redirect()->route('tarif-spp.index')->with('sukses', 'Tarif SPP berhasil diperbarui.');
    }

    /**
     * Hapus tarif.
     *
     * Tarif yang sudah dipakai tagihan tidak dihapus, supaya tagihan lama
     * tetap punya acuan nominalnya.
     */
    public function destroy(TarifSpp $tarifSpp): RedirectResponse
    {
        $dipakai = TagihanSpp::where('id_tarif', $tarifSpp->id_tarif)->exists();

        if ($dipakai) {
            return redirect()->route('tarif-spp.index')
                ->with('gagal', 'Tarif ini sudah dipakai tagihan, jadi belum bisa dihapus.');
        }

        $tarifSpp->delete();

        return redirect()->route('tarif-spp.index')->with('sukses', 'Tarif SPP berhasil dihapus.');
    }

    /**
     * Aturan validasi tarif.
     */
    private function aturan(): array
    {
        return [
            'id_tahun_ajaran' => ['required', 'exists:tahun_ajaran,id_tahun_ajaran'],
            'nominal' => ['required', 'numeric', 'gt:0'],
            'keterangan' => ['nullable', 'string'],
            'status' => ['required', Rule::in(['aktif', 'nonaktif'])],
        ];
    }

    /**
     * Pesan validasi dalam bahasa Indonesia.
     */
    private function pesan(): array
    {
        return [
            'id_tahun_ajaran.required' => 'Tahun ajaran wajib dipilih.',
            'id_tahun_ajaran.exists' => 'Tahun ajaran yang dipilih tidak ditemukan.',
            'nominal.required' => 'Nominal wajib diisi.',
            'nominal.numeric' => 'Nominal harus berupa angka.',
            'nominal.gt' => 'Nominal harus lebih besar dari nol.',
            'status.in' => 'Status harus aktif atau nonaktif.',
        ];
    }

    /**
     * Data pilihan tahun ajaran untuk form.
     */
    private function pilihan(): array
    {
        return [
            'daftar_tahun_ajaran' => TahunAjaran::orderByDesc('tahun_ajaran')->get(),
        ];
    }
}
