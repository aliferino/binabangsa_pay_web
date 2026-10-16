<?php

namespace App\Http\Controllers;

use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\TagihanSpp;
use App\Models\TahunAjaran;
use Illuminate\Http\Request;

class LaporanController extends Controller
{
    /**
     * Laporan tagihan, dengan filter lengkap.
     *
     * Catatan: tabel pembayaran belum ada, jadi laporan ini dihitung dari
     * tabel tagihan_spp saja. Tagihan berstatus lunas dianggap sudah
     * dibayar, tagihan berstatus tunggakan dianggap belum dibayar lewat
     * jatuh tempo.
     */
    public function pembayaran(Request $request)
    {
        $kueri = TagihanSpp::with('siswa.kelas.jurusan', 'tarif', 'tahunAjaran');

        if ($request->filled('bulan')) {
            $kueri->where('bulan', $request->input('bulan'));
        }

        if ($request->filled('tahun')) {
            $kueri->where('tahun', $request->input('tahun'));
        }

        if ($request->filled('id_tahun_ajaran')) {
            $kueri->where('id_tahun_ajaran', $request->input('id_tahun_ajaran'));
        }

        if ($request->filled('id_kelas')) {
            $kueri->whereHas('siswa', function ($q) use ($request) {
                $q->where('id_kelas', $request->input('id_kelas'));
            });
        }

        if ($request->filled('id_jurusan')) {
            $kueri->whereHas('siswa.kelas', function ($q) use ($request) {
                $q->where('id_jurusan', $request->input('id_jurusan'));
            });
        }

        if ($request->filled('id_siswa')) {
            $kueri->where('id_siswa', $request->input('id_siswa'));
        }

        if ($request->filled('status')) {
            $kueri->where('status', $request->input('status'));
        }

        $daftar = $kueri->orderByDesc('tahun')->orderByDesc('bulan')->get();

        return $this->tampilkan('laporan.pembayaran', [
            'daftar_tagihan' => $daftar,
            'total_nominal' => (float) $daftar->sum('nominal'),
            'total_lunas' => (float) $daftar->where('status', 'lunas')->sum('nominal'),
            'total_belum_bayar' => (float) $daftar->where('status', 'belum_bayar')->sum('nominal'),
            'total_tunggakan' => (float) $daftar->where('status', 'tunggakan')->sum('nominal'),
            'filter' => $request->only([
                'bulan', 'tahun', 'id_tahun_ajaran', 'id_kelas',
                'id_jurusan', 'id_siswa', 'status',
            ]),
        ] + $this->pilihan());
    }

    /**
     * Laporan tunggakan per siswa.
     *
     * Kolom minimal sesuai brief: NIS, nama, kelas, bulan tunggakan,
     * jumlah tunggakan, dan total nominal.
     */
    public function tunggakan(Request $request)
    {
        $kueri = Siswa::with('kelas.jurusan')
            ->whereHas('tagihan', function ($q) {
                $q->where('status', 'tunggakan');
            });

        if ($request->filled('id_kelas')) {
            $kueri->where('id_kelas', $request->input('id_kelas'));
        }

        if ($request->filled('id_jurusan')) {
            $kueri->whereHas('kelas', function ($q) use ($request) {
                $q->where('id_jurusan', $request->input('id_jurusan'));
            });
        }

        $daftar = $kueri->orderBy('nama')->get()->map(function (Siswa $siswa) {
            $tagihan = $siswa->tagihan()->where('status', 'tunggakan')
                ->orderBy('bulan')
                ->get();

            return [
                'nis' => $siswa->nis,
                'nama' => $siswa->nama,
                'kelas' => $siswa->kelas?->nama_kelas,
                'bulan_tunggakan' => $tagihan->map(fn (TagihanSpp $t) => $t->namaBulan())->implode(', '),
                'jumlah_tunggakan' => $tagihan->count(),
                'total_nominal' => (float) $tagihan->sum('nominal'),
            ];
        });

        return $this->tampilkan('laporan.tunggakan', [
            'daftar_tunggakan' => $daftar,
            'total_semua' => (float) $daftar->sum('total_nominal'),
            'filter' => $request->only(['id_kelas', 'id_jurusan']),
        ] + $this->pilihan());
    }

    /**
     * Data pilihan untuk filter laporan.
     */
    private function pilihan(): array
    {
        return [
            'daftar_kelas' => Kelas::with('jurusan')->orderBy('nama_kelas')->get(),
            'daftar_jurusan' => Jurusan::orderBy('nama_jurusan')->get(),
            'daftar_tahun_ajaran' => TahunAjaran::orderByDesc('tahun_ajaran')->get(),
            'daftar_siswa' => Siswa::orderBy('nama')->get(),
            'daftar_tahun' => TagihanSpp::distinct()->orderByDesc('tahun')->pluck('tahun'),
        ];
    }
}
