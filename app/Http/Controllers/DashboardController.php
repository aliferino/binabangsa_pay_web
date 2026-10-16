<?php

namespace App\Http\Controllers;

use App\Models\Siswa;
use App\Models\TagihanSpp;
use Illuminate\Http\RedirectResponse;

class DashboardController extends Controller
{
    /**
     * Arahkan user ke dashboard sesuai role-nya.
     */
    public function index(): RedirectResponse
    {
        return match (auth()->user()->role) {
            'admin' => redirect()->route('dashboard.admin'),
            'kepala_sekolah' => redirect()->route('dashboard.kepala-sekolah'),
            'teller' => redirect()->route('dashboard.teller'),
            'siswa' => redirect()->route('dashboard.siswa'),
            default => redirect()->route('login'),
        };
    }

    /**
     * Dashboard admin. Menampilkan ringkasan seluruh sekolah.
     */
    public function admin()
    {
        $data = [
            'total_siswa' => Siswa::count(),
            'total_tagihan' => TagihanSpp::count(),
            'total_tunggakan' => TagihanSpp::where('status', 'tunggakan')->count(),
            'total_siswa_belum_bayar' => TagihanSpp::where('status', 'belum_bayar')
                ->distinct('id_siswa')
                ->count('id_siswa'),
            'nominal_tunggakan' => (float) TagihanSpp::where('status', 'tunggakan')->sum('nominal'),
        ];

        return $this->tampilkan('dashboard.admin', $data);
    }

    /**
     * Dashboard kepala sekolah. Fokus pada rekap, bukan operasional harian.
     */
    public function kepalaSekolah()
    {
        $data = [
            'siswa_aktif' => Siswa::where('status', 'aktif')->count(),
            'siswa_lunas' => Siswa::whereHas('tagihan', function ($q) {
                $q->where('status', 'lunas');
            })->count(),
            'siswa_belum_bayar' => Siswa::whereHas('tagihan', function ($q) {
                $q->where('status', 'belum_bayar');
            })->count(),
            'siswa_menunggak' => Siswa::whereHas('tagihan', function ($q) {
                $q->where('status', 'tunggakan');
            })->count(),
            'total_tunggakan' => (float) TagihanSpp::where('status', 'tunggakan')->sum('nominal'),
        ];

        return $this->tampilkan('dashboard.kepala-sekolah', $data);
    }

    /**
     * Dashboard teller. Fokus pada tagihan yang jatuh tempo hari ini.
     */
    public function teller()
    {
        $hariIni = now()->toDateString();

        $data = [
            'tagihan_hari_ini' => TagihanSpp::whereDate('tanggal_jatuh_tempo', $hariIni)->count(),
            'siswa_belum_bayar' => TagihanSpp::where('status', 'belum_bayar')
                ->distinct('id_siswa')
                ->count('id_siswa'),
        ];

        return $this->tampilkan('dashboard.teller', $data);
    }

    /**
     * Dashboard siswa. Hanya menampilkan data milik siswa yang sedang login.
     */
    public function siswa()
    {
        $siswa = auth()->user()->siswa;

        // Kalau akun siswa belum ditautkan ke data siswa, jangan tampilkan apa pun.
        if ($siswa === null) {
            return $this->tampilkan('dashboard.siswa', [
                'siswa' => null,
                'pesan' => 'Akun ini belum ditautkan ke data siswa. Hubungi administrator.',
            ]);
        }

        $data = [
            'siswa' => $siswa->load('kelas.jurusan', 'tahunAjaran'),
            'daftar_tagihan' => $siswa->tagihan()->orderByDesc('tahun')->orderByDesc('bulan')->get(),
            'total_tunggakan' => $siswa->totalTunggakan(),
        ];

        return $this->tampilkan('dashboard.siswa', $data);
    }
}
