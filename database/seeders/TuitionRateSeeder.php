<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\TuitionRate;
use Illuminate\Database\Seeder;

/**
 * Tarif SPP per tingkatan.
 *
 * Setiap tingkatan punya biaya sendiri, jadi satu tahun ajaran berisi tiga
 * tarif: tingkat 10, 11, dan 12. Nominalnya beda supaya perbedaan itu terlihat
 * saat demo. Tarif lama yang tidak punya tingkat tetap dibiarkan, dan dipakai
 * sebagai cadangan kalau ada siswa tanpa kelas.
 */
class TuitionRateSeeder extends Seeder
{
    /** Nominal SPP per tingkatan. Kelas 10 dan 11 sama, kelas 12 lebih tinggi. */
    private const TARIF = [
        '10' => 350000,
        '11' => 350000,
        '12' => 500000,
    ];

    public function run(): void
    {
        $tahunAjaran = AcademicYear::where('status', 'aktif')->first() ?? AcademicYear::first();

        if ($tahunAjaran === null) {
            $this->command?->warn('Belum ada tahun ajaran. Jalankan DatabaseSeeder dulu.');

            return;
        }

        foreach (self::TARIF as $tingkat => $nominal) {
            TuitionRate::updateOrCreate(
                [
                    'id_tahun_ajaran' => $tahunAjaran->id_tahun_ajaran,
                    'tingkat' => $tingkat,
                ],
                [
                    'nominal' => $nominal,
                    'keterangan' => 'SPP bulanan tingkat '.$tingkat.' tahun ajaran '.$tahunAjaran->tahun_ajaran,
                    'status' => 'aktif',
                ]
            );
        }

        // Tarif tanpa tingkat dari data lama dijadikan nonaktif, supaya tidak
        // berebut saat pemilihan tarif otomatis.
        TuitionRate::whereNull('tingkat')->update(['status' => 'nonaktif']);

        $this->command?->info('Tarif SPP per tingkatan siap: '.implode(', ', array_map(
            fn ($t, $n) => $t.' = Rp'.number_format($n, 0, ',', '.'),
            array_keys(self::TARIF),
            self::TARIF
        )));
    }
}