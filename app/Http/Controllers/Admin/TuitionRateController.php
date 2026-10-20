<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TuitionBill;
use App\Models\AcademicYear;
use App\Models\TuitionRate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TuitionRateController extends Controller
{
    /**
     * Daftar tarif SPP.
     */
    public function index()
    {
        return $this->render('admin.tuition-rates.index', [
            'tuitionRates' => TuitionRate::with('tahunAjaran')
                ->orderByDesc('id_tahun_ajaran')
                ->get(),
        ] + $this->pilihan());
    }


    /**
     * Simpan tarif baru.
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate($this->aturan(), $this->pesan());

        TuitionRate::create($data);

        return redirect()->route('tarif-spp.index')->with('sukses', 'Tarif SPP berhasil ditambahkan.');
    }

    /**
     * Tampilkan detail satu tarif.
     */
    public function show(TuitionRate $tarifSpp)
    {
        return $this->render('admin.tuition-rates.show', [
            'tarif' => $tarifSpp->load('tahunAjaran'),
        ]);
    }

    /**
     * Form ubah sekarang berupa modal di halaman daftar. Route lama
     * tetap ada supaya tautan Ubah di halaman detail tidak rusak, tetapi
     * sekarang hanya mengalihkan ke daftar dengan penanda modal terbuka.
     */
    public function edit(TuitionRate $tarifSpp): RedirectResponse
    {
        return redirect()
            ->route('tarif-spp.index', ['ubah' => $tarifSpp->id_tarif])
            ->with('buka_modal', 'ubah');
    }

    /**
     * Perbarui data tarif.
     */
    public function update(Request $request, TuitionRate $tarifSpp): RedirectResponse
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
    public function destroy(TuitionRate $tarifSpp): RedirectResponse
    {
        $dipakai = TuitionBill::where('id_tarif', $tarifSpp->id_tarif)->exists();

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
            'academicYears' => AcademicYear::orderByDesc('tahun_ajaran')->get(),
        ];
    }
}
