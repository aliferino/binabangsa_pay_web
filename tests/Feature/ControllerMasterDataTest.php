<?php

namespace Tests\Feature;

use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\TagihanSpp;
use App\Models\TahunAjaran;
use App\Models\TarifSpp;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ControllerMasterDataTest extends TestCase
{
    use RefreshDatabase;

    private TahunAjaran $tahunAjaran;

    private Kelas $kelas;

    private TarifSpp $tarif;

    protected function setUp(): void
    {
        parent::setUp();

        $jurusan = Jurusan::create([
            'kode_jurusan' => 'RPL',
            'nama_jurusan' => 'Rekayasa Perangkat Lunak',
        ]);

        $this->tahunAjaran = TahunAjaran::create([
            'tahun_ajaran' => '2026/2027',
            'tanggal_mulai' => '2026-07-01',
            'tanggal_selesai' => '2027-06-30',
            'status' => 'aktif',
        ]);

        $this->kelas = Kelas::create([
            'nama_kelas' => 'XI RPL 1',
            'tingkat' => 'XI',
            'id_jurusan' => $jurusan->id_jurusan,
            'id_tahun_ajaran' => $this->tahunAjaran->id_tahun_ajaran,
        ]);

        $this->tarif = TarifSpp::create([
            'id_tahun_ajaran' => $this->tahunAjaran->id_tahun_ajaran,
            'nominal' => 350000,
            'status' => 'aktif',
        ]);
    }

    private function buatSiswa(string $nis, string $nama, ?User $akun = null): Siswa
    {
        return Siswa::create([
            'nis' => $nis,
            'nisn' => '00'.$nis,
            'nama' => $nama,
            'jenis_kelamin' => 'L',
            'tempat_lahir' => 'Sidoarjo',
            'tanggal_lahir' => '2009-05-12',
            'alamat' => 'Desa Sumber Makmur',
            'id_kelas' => $this->kelas->id_kelas,
            'id_tahun_ajaran' => $this->tahunAjaran->id_tahun_ajaran,
            'id_user' => $akun?->id_user,
            'status' => 'aktif',
        ]);
    }

    public function test_tamu_diarahkan_ke_login(): void
    {
        $this->get(route('siswa.index'))->assertRedirect(route('login'));
        $this->get(route('dashboard'))->assertRedirect(route('login'));
        $this->get(route('user.index'))->assertRedirect(route('login'));
    }

    public function test_halaman_login_bisa_dibuka(): void
    {
        $this->get(route('login'))->assertStatus(200);
    }

    public function test_login_berhasil_dan_diarahkan_ke_dashboard(): void
    {
        User::factory()->create([
            'username' => 'admin',
            'password' => 'password',
            'role' => 'admin',
            'status' => 'aktif',
        ]);

        $response = $this->post(route('login.proses'), [
            'username' => 'admin',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticated();
    }

    public function test_login_ditolak_kalau_status_tidak_aktif(): void
    {
        User::factory()->nonaktif()->create([
            'username' => 'admin',
            'password' => 'password',
            'role' => 'admin',
        ]);

        $response = $this->post(route('login.proses'), [
            'username' => 'admin',
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors('username');
        $this->assertGuest();
    }

    public function test_login_ditolak_kalau_password_salah(): void
    {
        User::factory()->create([
            'username' => 'admin',
            'password' => 'password',
            'role' => 'admin',
        ]);

        $response = $this->post(route('login.proses'), [
            'username' => 'admin',
            'password' => 'salah',
        ]);

        $response->assertSessionHasErrors('username');
        $this->assertGuest();
    }

    public function test_tiap_role_bisa_membuka_dashboard_sendiri(): void
    {
        $this->actingAs(User::factory()->role('admin')->create())
            ->get(route('dashboard.admin'))->assertStatus(200);

        $this->actingAs(User::factory()->role('kepala_sekolah')->create())
            ->get(route('dashboard.kepala-sekolah'))->assertStatus(200);

        $this->actingAs(User::factory()->role('teller')->create())
            ->get(route('dashboard.teller'))->assertStatus(200);

        $this->actingAs(User::factory()->role('siswa')->create())
            ->get(route('dashboard.siswa'))->assertStatus(200);
    }

    public function test_role_tidak_bisa_membuka_dashboard_role_lain(): void
    {
        $this->actingAs(User::factory()->role('teller')->create())
            ->get(route('dashboard.admin'))->assertStatus(403);

        $this->actingAs(User::factory()->role('admin')->create())
            ->get(route('dashboard.siswa'))->assertStatus(403);

        $this->actingAs(User::factory()->role('siswa')->create())
            ->get(route('dashboard.teller'))->assertStatus(403);
    }

    public function test_siswa_tidak_bisa_masuk_area_admin(): void
    {
        $siswa = User::factory()->role('siswa')->create();

        $this->actingAs($siswa)->get(route('user.index'))->assertStatus(403);
        $this->actingAs($siswa)->get(route('siswa.index'))->assertStatus(403);
        $this->actingAs($siswa)->get(route('jurusan.index'))->assertStatus(403);
        $this->actingAs($siswa)->get(route('tagihan-spp.index'))->assertStatus(403);
        $this->actingAs($siswa)->get(route('laporan.pembayaran'))->assertStatus(403);
        $this->actingAs($siswa)->get(route('pencarian.index'))->assertStatus(403);
    }

    public function test_kepala_sekolah_bisa_melihat_tapi_tidak_bisa_mengubah(): void
    {
        $kepala = User::factory()->role('kepala_sekolah')->create();

        // Boleh melihat.
        $this->actingAs($kepala)->get(route('siswa.index'))->assertStatus(200);
        $this->actingAs($kepala)->get(route('jurusan.index'))->assertStatus(200);
        $this->actingAs($kepala)->get(route('kelas.index'))->assertStatus(200);
        $this->actingAs($kepala)->get(route('tarif-spp.index'))->assertStatus(200);
        $this->actingAs($kepala)->get(route('laporan.tunggakan'))->assertStatus(200);

        // Tidak boleh mengubah atau menghapus.
        $this->actingAs($kepala)->get(route('siswa.create'))->assertStatus(403);
        $this->actingAs($kepala)->post(route('jurusan.store'), [])->assertStatus(403);
        $this->actingAs($kepala)->delete(route('kelas.destroy', $this->kelas))->assertStatus(403);
        $this->actingAs($kepala)->get(route('user.index'))->assertStatus(403);
    }

    public function test_teller_bisa_lihat_tagihan_dan_laporan_tapi_tidak_bisa_mengubah(): void
    {
        $teller = User::factory()->role('teller')->create();

        $this->actingAs($teller)->get(route('tagihan-spp.lihat'))->assertStatus(200);
        $this->actingAs($teller)->get(route('laporan.pembayaran'))->assertStatus(200);
        $this->actingAs($teller)->get(route('pencarian.index'))->assertStatus(200);

        $this->actingAs($teller)->get(route('tagihan-spp.create'))->assertStatus(403);
        $this->actingAs($teller)->post(route('siswa.store'), [])->assertStatus(403);
        $this->actingAs($teller)->get(route('user.index'))->assertStatus(403);
    }

    public function test_siswa_hanya_melihat_data_miliknya_sendiri(): void
    {
        $akunSaya = User::factory()->role('siswa')->create(['nama' => 'Siswa Satu']);
        $akunLain = User::factory()->role('siswa')->create(['nama' => 'Siswa Dua']);

        $this->buatSiswa('11111', 'Siswa Satu', $akunSaya);
        $this->buatSiswa('22222', 'Siswa Dua', $akunLain);

        $response = $this->actingAs($akunSaya)->get(route('dashboard.siswa'));

        $response->assertStatus(200);
        $response->assertSee('Siswa Satu');
        $response->assertDontSee('Siswa Dua');
        $response->assertDontSee('22222');
    }

    public function test_siswa_tidak_bisa_membuka_data_siswa_lain_lewat_url(): void
    {
        $akunSaya = User::factory()->role('siswa')->create();
        $siswaLain = $this->buatSiswa('22222', 'Siswa Dua');

        // Route detail siswa dijaga middleware role, jadi siswa tetap ditolak.
        $this->actingAs($akunSaya)
            ->get(route('siswa.show', $siswaLain))
            ->assertStatus(403);
    }

    public function test_tahun_ajaran_hanya_satu_yang_aktif(): void
    {
        $admin = User::factory()->role('admin')->create();

        $this->actingAs($admin)->post(route('tahun-ajaran.store'), [
            'tahun_ajaran' => '2027/2028',
            'tanggal_mulai' => '2027-07-01',
            'tanggal_selesai' => '2028-06-30',
            'status' => 'aktif',
        ])->assertRedirect(route('tahun-ajaran.index'));

        $this->assertSame(1, TahunAjaran::where('status', 'aktif')->count());
        $this->assertSame(
            '2027/2028',
            TahunAjaran::where('status', 'aktif')->value('tahun_ajaran'),
        );
    }

    public function test_tagihan_ganda_bulan_sama_ditolak_dengan_pesan_ramah(): void
    {
        $admin = User::factory()->role('admin')->create();
        $siswa = $this->buatSiswa('12345', 'Ahmad Fauzan');

        $isi = [
            'id_siswa' => $siswa->id_siswa,
            'id_tarif' => $this->tarif->id_tarif,
            'id_tahun_ajaran' => $this->tahunAjaran->id_tahun_ajaran,
            'bulan' => 7,
            'tahun' => 2026,
            'nominal' => 350000,
            'tanggal_jatuh_tempo' => '2026-07-31',
            'status' => 'belum_bayar',
        ];

        $this->actingAs($admin)->post(route('tagihan-spp.store'), $isi)
            ->assertRedirect(route('tagihan-spp.index'));

        $this->actingAs($admin)->post(route('tagihan-spp.store'), $isi)
            ->assertSessionHasErrors('bulan');

        $this->assertSame(1, TagihanSpp::count());
    }

    public function test_tandai_tunggakan_hanya_menyentuh_yang_lewat_jatuh_tempo(): void
    {
        $admin = User::factory()->role('admin')->create();
        $siswa = $this->buatSiswa('12345', 'Ahmad Fauzan');

        // Bulan Januari 2026 sudah lewat jauh, harus jadi tunggakan.
        $lama = TagihanSpp::create([
            'id_siswa' => $siswa->id_siswa,
            'id_tarif' => $this->tarif->id_tarif,
            'id_tahun_ajaran' => $this->tahunAjaran->id_tahun_ajaran,
            'bulan' => 1,
            'tahun' => 2026,
            'nominal' => 350000,
            'tanggal_jatuh_tempo' => '2026-01-31',
            'status' => 'belum_bayar',
        ]);

        // Bulan Desember 2026 belum lewat, harus tetap belum_bayar.
        $baru = TagihanSpp::create([
            'id_siswa' => $siswa->id_siswa,
            'id_tarif' => $this->tarif->id_tarif,
            'id_tahun_ajaran' => $this->tahunAjaran->id_tahun_ajaran,
            'bulan' => 12,
            'tahun' => 2026,
            'nominal' => 350000,
            'tanggal_jatuh_tempo' => '2026-12-31',
            'status' => 'belum_bayar',
        ]);

        $this->actingAs($admin)
            ->post(route('tagihan-spp.tandai-tunggakan'))
            ->assertRedirect(route('tagihan-spp.index'));

        $this->assertSame('tunggakan', $lama->fresh()->status);
        $this->assertSame('belum_bayar', $baru->fresh()->status);
    }

    public function test_tagihan_lunas_tidak_ikut_jadi_tunggakan(): void
    {
        $admin = User::factory()->role('admin')->create();
        $siswa = $this->buatSiswa('12345', 'Ahmad Fauzan');

        $lunas = TagihanSpp::create([
            'id_siswa' => $siswa->id_siswa,
            'id_tarif' => $this->tarif->id_tarif,
            'id_tahun_ajaran' => $this->tahunAjaran->id_tahun_ajaran,
            'bulan' => 1,
            'tahun' => 2026,
            'nominal' => 350000,
            'tanggal_jatuh_tempo' => '2026-01-31',
            'status' => 'lunas',
        ]);

        $this->actingAs($admin)->post(route('tagihan-spp.tandai-tunggakan'));

        $this->assertSame('lunas', $lunas->fresh()->status);
    }

    public function test_validasi_siswa_menolak_nis_dan_nisn_ganda(): void
    {
        $admin = User::factory()->role('admin')->create();
        $this->buatSiswa('12345', 'Ahmad Fauzan');

        $this->actingAs($admin)->post(route('siswa.store'), [
            'nis' => '12345',
            'nisn' => '0012345',
            'nama' => 'Siswa Baru',
            'jenis_kelamin' => 'P',
            'tempat_lahir' => 'Sidoarjo',
            'tanggal_lahir' => '2010-01-01',
            'alamat' => 'Jalan Baru',
            'id_kelas' => $this->kelas->id_kelas,
            'id_tahun_ajaran' => $this->tahunAjaran->id_tahun_ajaran,
            'status' => 'aktif',
        ])->assertSessionHasErrors('nis');

        $this->assertSame(1, Siswa::count());
    }

    public function test_validasi_siswa_menolak_jenis_kelamin_di_luar_l_dan_p(): void
    {
        $admin = User::factory()->role('admin')->create();

        $this->actingAs($admin)->post(route('siswa.store'), [
            'nis' => '99999',
            'nisn' => '0099999',
            'nama' => 'Siswa Baru',
            'jenis_kelamin' => 'X',
            'tempat_lahir' => 'Sidoarjo',
            'tanggal_lahir' => '2010-01-01',
            'alamat' => 'Jalan Baru',
            'id_kelas' => $this->kelas->id_kelas,
            'id_tahun_ajaran' => $this->tahunAjaran->id_tahun_ajaran,
            'status' => 'aktif',
        ])->assertSessionHasErrors('jenis_kelamin');
    }

    public function test_admin_tidak_bisa_menghapus_akun_sendiri(): void
    {
        $admin = User::factory()->role('admin')->create();

        $this->actingAs($admin)
            ->delete(route('user.destroy', $admin))
            ->assertRedirect(route('user.index'));

        $this->assertDatabaseHas('users', ['id_user' => $admin->id_user]);
    }

    public function test_pencarian_menemukan_siswa_lewat_nis_dan_nama(): void
    {
        $admin = User::factory()->role('admin')->create();
        $this->buatSiswa('12345', 'Ahmad Fauzan');

        $this->actingAs($admin)->get(route('pencarian.index', ['q' => '12345']))
            ->assertStatus(200)
            ->assertSee('Ahmad Fauzan');

        $this->actingAs($admin)->get(route('pencarian.index', ['q' => 'Fauzan']))
            ->assertStatus(200)
            ->assertSee('12345');

        $this->actingAs($admin)->get(route('pencarian.index', ['q' => 'Tidak Ada']))
            ->assertStatus(200)
            ->assertDontSee('Ahmad Fauzan');
    }
}
