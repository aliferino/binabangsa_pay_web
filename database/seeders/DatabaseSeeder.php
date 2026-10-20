<?php

namespace Database\Seeders;

use App\Models\Major;
use App\Models\Classroom;
use App\Models\Student;
use App\Models\TuitionBill;
use App\Models\AcademicYear;
use App\Models\TuitionRate;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::create([
            'nama' => 'Administrator', 'username' => 'admin',
            'password' => Hash::make('password'), 'role' => 'admin', 'status' => 'aktif',
        ]);
        User::create([
            'nama' => 'Kepala Sekolah', 'username' => 'kepala',
            'password' => Hash::make('password'), 'role' => 'kepala_sekolah', 'status' => 'aktif',
        ]);
        User::create([
            'nama' => 'Teller Sekolah', 'username' => 'teller',
            'password' => Hash::make('password'), 'role' => 'teller', 'status' => 'aktif',
        ]);
        $akunSiswa = User::create([
            'nama' => 'Ahmad Fauzan', 'username' => 'siswa',
            'password' => Hash::make('password'), 'role' => 'siswa', 'status' => 'aktif',
        ]);

        $ta = AcademicYear::create([
            'tahun_ajaran' => '2026/2027',
            'tanggal_mulai' => '2026-07-01',
            'tanggal_selesai' => '2027-06-30',
            'status' => 'aktif',
        ]);
        $tarif = TuitionRate::create([
            'id_tahun_ajaran' => $ta->id_tahun_ajaran,
            'nominal' => 350000,
            'keterangan' => 'SPP bulanan tahun ajaran 2026/2027',
            'status' => 'aktif',
        ]);

        $rpl = Major::create([
            'kode_jurusan' => 'RPL',
            'nama_jurusan' => 'Rekayasa Perangkat Lunak',
            'keterangan' => 'Pemrograman dan pengembangan aplikasi',
        ]);
        $kelas = Classroom::create([
            'nama_kelas' => 'XI RPL 1', 'tingkat' => 'XI',
            'id_jurusan' => $rpl->id_jurusan, 'id_tahun_ajaran' => $ta->id_tahun_ajaran,
        ]);

        $siswa = Student::create([
            'nis' => '12345', 'nisn' => '0071234567', 'nama' => 'Ahmad Fauzan',
            'jenis_kelamin' => 'L', 'tempat_lahir' => 'Sidoarjo',
            'tanggal_lahir' => '2009-05-12', 'alamat' => 'Desa Sumber Makmur',
            'no_hp' => '081234567890', 'nama_orang_tua' => 'Bapak Fauzan',
            'id_kelas' => $kelas->id_kelas, 'id_tahun_ajaran' => $ta->id_tahun_ajaran,
            'id_user' => $akunSiswa->id_user, 'status' => 'aktif',
        ]);

        foreach ([7, 8, 9, 10] as $bulan) {
            TuitionBill::create([
                'id_siswa' => $siswa->id_siswa,
                'id_tarif' => $tarif->id_tarif,
                'id_tahun_ajaran' => $ta->id_tahun_ajaran,
                'bulan' => $bulan, 'tahun' => 2026,
                'nominal' => $tarif->nominal,
                'tanggal_jatuh_tempo' => sprintf('2026-%02d-%02d', $bulan, 30),
                'status' => 'belum_bayar',
            ]);
        }

        $this->command->info('Data contoh selesai dibuat.');
        $this->command->info('Akun: admin, kepala, teller, siswa. Semua password: password');
    }
}
