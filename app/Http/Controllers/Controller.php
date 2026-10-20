<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;

abstract class Controller
{
    /**
     * Padanan key lama, dipakai sementara supaya view lama tetap jalan.
     *
     * Controller mengirim key baru berbahasa Inggris. Selama view belum
     * dipindah, render menambahkan key lama yang menunjuk nilai yang sama.
     * Setelah tugas view selesai, daftar ini boleh dikosongkan.
     */
    private const LEGACY_KEYS = [
        'majors' => 'daftar_jurusan',
        'classrooms' => 'daftar_kelas',
        'students' => 'daftar_siswa',
        'academicYears' => 'daftar_tahun_ajaran',
        'tuitionRates' => 'daftar_tarif',
        'accounts' => 'daftar_akun',
        'users' => 'daftar_user',
        'bills' => 'daftar_tagihan',
        'payments' => 'daftar_pembayaran',
        'virtualAccounts' => 'daftar_va',
        'arrears' => 'daftar_tunggakan',
        'years' => 'daftar_tahun',
        'results' => 'hasil',
        'keyword' => 'kata_kunci',
        'details' => 'rincian',
        'options' => 'pilihan',
        'message' => 'pesan',
        'student' => 'siswa',
        'classroom' => 'kelas',
        'major' => 'jurusan',
        'academicYear' => 'tahun_ajaran',
        'tuitionRate' => 'tarif',
        'totalStudents' => 'total_siswa',
        'totalBills' => 'total_tagihan',
        'totalArrears' => 'total_tunggakan',
        'totalUnpaidStudents' => 'total_siswa_belum_bayar',
        'arrearsAmount' => 'nominal_tunggakan',
        'activeStudents' => 'siswa_aktif',
        'paidStudents' => 'siswa_lunas',
        'unpaidStudents' => 'siswa_belum_bayar',
        'overdueStudents' => 'siswa_menunggak',
        'billsDueToday' => 'tagihan_hari_ini',
        'totalNominal' => 'total_nominal',
        'totalPaid' => 'total_lunas',
        'totalUnpaid' => 'total_belum_bayar',
        'totalAll' => 'total_semua',
    ];

    /**
     * Kembalikan tampilan kalau berkas Blade-nya sudah ada.
     *
     * Data dikirim sebagai JSON selama tampilan belum tersedia. Begitu Blade
     * ditambahkan nanti, method ini otomatis memakai tampilan itu tanpa perlu
     * diubah.
     */
    protected function render(string $view, array $data = [], int $status = 200): View|JsonResponse
    {
        $data = $this->withLegacyKeys($data);

        if (view()->exists($view)) {
            return view($view, $data);
        }

        return response()->json([
            'pesan' => 'Tampilan belum tersedia, ini data mentahnya.',
            'tampilan' => $view,
            'data' => $data,
        ], $status);
    }

    /**
     * Tambahkan key lama yang belum ada, tanpa menimpa key yang sudah diisi.
     */
    private function withLegacyKeys(array $data): array
    {
        foreach (self::LEGACY_KEYS as $new => $legacy) {
            if (array_key_exists($new, $data) && ! array_key_exists($legacy, $data)) {
                $data[$legacy] = $data[$new];
            }
        }

        return $data;
    }
}
