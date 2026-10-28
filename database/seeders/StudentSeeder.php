<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Major;
use App\Models\Student;
use App\Models\TuitionBill;
use App\Models\TuitionRate;
use App\Models\User;
use App\Models\VirtualAccount;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Data siswa contoh: 2 jurusan, 3 kelas per jurusan, 5 siswa per kelas.
 *
 * NIS lima digit: dua digit depan nomor tingkat (10, 11, 12) lalu tiga digit
 * nomor urut siswa di kelasnya. Jadi kelas X memakai 10001 sampai 10005, kelas
 * XI memakai 11001 sampai 11005, dan seterusnya.
 *
 * Username dan password akun siswa sama dengan NIS, supaya mudah didemokan.
 *
 * Seeder ini aman dijalankan berulang: jurusan, kelas, siswa, dan VA dicari
 * dulu sebelum dibuat, jadi data yang sudah ada tidak terduplikasi.
 */
class StudentSeeder extends Seeder
{
    /** Tiga tingkat, masing-masing jadi satu kelas per jurusan. */
    private const TINGKAT = [
        'X' => '10',
        'XI' => '11',
        'XII' => '12',
    ];

    private const JURUSAN = [
        'RPL' => 'Rekayasa Perangkat Lunak',
        'DKV' => 'Desain Komunikasi Visual',
    ];

    private const PER_KELAS = 5;

    /** Nama siswa contoh, dipakai berurutan. */
    private const NAMA = [
        'Ahmad Fauzan', 'Bunga Lestari', 'Candra Wijaya', 'Dewi Anggraini', 'Eko Prasetyo',
        'Fitri Handayani', 'Galih Ramadhan', 'Hana Safira', 'Irfan Maulana', 'Jihan Aulia',
        'Krisna Bayu', 'Laras Wati', 'Muhammad Rizky', 'Nadia Puspita', 'Oka Saputra',
        'Putri Amelia', 'Qori Hidayat', 'Rani Oktaviani', 'Satria Nugraha', 'Tiara Kusuma',
        'Umar Hakim', 'Vina Marlina', 'Wahyu Setiawan', 'Xenia Anindita', 'Yoga Pratama',
        'Zahra Nabila', 'Arif Kurniawan', 'Bella Permata', 'Cahyo Wibowo', 'Dinda Ayu',
    ];

    public function run(): void
    {
        $tahunAjaran = AcademicYear::where('status', 'aktif')->first()
            ?? AcademicYear::first();

        if ($tahunAjaran === null) {
            $this->command->warn('Tahun ajaran belum ada. Jalankan DatabaseSeeder dulu.');

            return;
        }

        $tarif = TuitionRate::where('status', 'aktif')->first()
            ?? TuitionRate::first();

        if ($tarif === null) {
            $this->command->warn('Tarif SPP belum ada. Jalankan DatabaseSeeder dulu.');

            return;
        }

        $nomor = 0;
        $jumlahSiswa = 0;

        // Nomor urut NIS dihitung per tingkat, bukan per kelas, karena NIS
        // harus unik di seluruh sekolah. Kelas RPL memakai 001 sampai 005, lalu
        // kelas DKV di tingkat yang sama melanjutkan dari 006.
        $urutPerTingkat = [];

        foreach (self::JURUSAN as $kode => $namaJurusan) {

            $jurusan = Major::firstOrCreate(
                ['kode_jurusan' => $kode],
                ['nama_jurusan' => $namaJurusan]
            );

            foreach (self::TINGKAT as $tingkat => $awalanNis) {

                $kelas = Classroom::firstOrCreate(
                    ['nama_kelas' => "$tingkat $kode 1"],
                    [
                        'tingkat' => $tingkat,
                        'id_jurusan' => $jurusan->id_jurusan,
                        'id_tahun_ajaran' => $tahunAjaran->id_tahun_ajaran,
                    ]
                );

                for ($i = 0; $i < self::PER_KELAS; $i++) {

                    $urutPerTingkat[$awalanNis] = ($urutPerTingkat[$awalanNis] ?? 0) + 1;

                    $nis = $awalanNis.str_pad((string) $urutPerTingkat[$awalanNis], 3, '0', STR_PAD_LEFT);

                    $siswa = $this->buatSiswa(
                        $nis,
                        self::NAMA[$nomor % count(self::NAMA)],
                        $nomor,
                        $kelas,
                        $tahunAjaran
                    );

                    $this->buatTagihan($siswa, $tarif, $tahunAjaran);
                    $this->buatVirtualAccount($siswa);

                    $nomor++;
                    $jumlahSiswa++;
                }

                $this->command->info("Kelas {$kelas->nama_kelas}: ".self::PER_KELAS.' siswa.');
            }
        }

        $this->command->info("Selesai. Total {$jumlahSiswa} siswa ditambahkan atau sudah ada.");
        $this->command->info('Username dan password tiap siswa sama dengan NIS, contoh 10001 / 10001.');
    }

    /**
     * Buat siswa beserta akun loginnya. Kalau NIS sudah terdaftar, dipakai
     * ulang supaya seeder tidak menggandakan data.
     */
    private function buatSiswa(
        string $nis,
        string $nama,
        int $nomor,
        Classroom $kelas,
        AcademicYear $tahunAjaran
    ): Student {
        if ($siswa = Student::where('nis', $nis)->first()) {
            return $siswa;
        }

        $akun = User::firstOrCreate(
            ['username' => $nis],
            [
                'nama' => $nama,
                'password' => Hash::make($nis),
                'role' => 'siswa',
                'status' => 'aktif',
            ]
        );

        return Student::create([
            'nis' => $nis,
            'nisn' => '00'.str_pad((string) (10000000 + $nomor), 8, '0', STR_PAD_LEFT),
            'nama' => $nama,
            'jenis_kelamin' => $nomor % 2 === 0 ? 'P' : 'L',
            'tempat_lahir' => 'Sidoarjo',
            'tanggal_lahir' => sprintf('200%s-%02d-%02d', $kelas->tingkat === 'X' ? 9 : 8, ($nomor % 12) + 1, ($nomor % 27) + 1),
            'alamat' => 'Desa Sumber Makmur',
            'no_hp' => '0812345'.str_pad((string) $nomor, 5, '0', STR_PAD_LEFT),
            'nama_orang_tua' => 'Bapak '.explode(' ', $nama)[0],
            'id_kelas' => $kelas->id_kelas,
            'id_tahun_ajaran' => $tahunAjaran->id_tahun_ajaran,
            'id_user' => $akun->id_user,
            'status' => 'aktif',
        ]);
    }

    /** Empat tagihan contoh, Juli sampai Oktober, semuanya belum bayar. */
    private function buatTagihan(Student $siswa, TuitionRate $tarif, AcademicYear $tahunAjaran): void
    {
        foreach ([7, 8, 9, 10] as $bulan) {

            TuitionBill::firstOrCreate(
                [
                    'id_siswa' => $siswa->id_siswa,
                    'bulan' => $bulan,
                    'tahun' => 2026,
                ],
                [
                    'id_tarif' => $tarif->id_tarif,
                    'id_tahun_ajaran' => $tahunAjaran->id_tahun_ajaran,
                    'nominal' => $tarif->nominal,
                    'tanggal_jatuh_tempo' => sprintf('2026-%02d-%02d', $bulan, 30),
                    'status' => TuitionBill::STATUS_BELUM,
                ]
            );
        }
    }

    /** Satu Virtual Account aktif per siswa, akhiran NIS. */
    private function buatVirtualAccount(Student $siswa): void
    {
        if (VirtualAccount::where('siswa_id', $siswa->id_siswa)->exists()) {
            return;
        }

        $nomorVa = VirtualAccount::PREFIX_MIDTRANS.VirtualAccount::generateNumber($siswa->nis);

        VirtualAccount::create([
            'siswa_id' => $siswa->id_siswa,
            'nomor_va' => $nomorVa,
            'prefix' => VirtualAccount::PREFIX_MIDTRANS,
            'account_number' => $nomorVa,
            'status' => 'aktif',
        ]);
    }
}
