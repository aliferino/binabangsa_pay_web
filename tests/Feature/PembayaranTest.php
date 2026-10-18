<?php

namespace Tests\Feature;

use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\Pembayaran;
use App\Models\Siswa;
use App\Models\TagihanSpp;
use App\Models\TahunAjaran;
use App\Models\TarifSpp;
use App\Models\TransaksiPaymentGateway;
use App\Models\User;
use App\Models\VirtualAccount;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PembayaranTest extends TestCase
{
    use RefreshDatabase;

    private TahunAjaran $tahunAjaran;

    private Kelas $kelas;

    private TarifSpp $tarif;

    private Siswa $siswa;

    private TagihanSpp $tagihan;

    private User $admin;

    private User $teller;

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

        $this->siswa = $this->buatSiswa('2612345', 'Budi Santoso');

        $this->tagihan = TagihanSpp::create([
            'id_siswa' => $this->siswa->id_siswa,
            'id_tarif' => $this->tarif->id_tarif,
            'id_tahun_ajaran' => $this->tahunAjaran->id_tahun_ajaran,
            'bulan' => 8,
            'tahun' => 2026,
            'nominal' => 350000,
            'tanggal_jatuh_tempo' => '2026-08-31',
            'status' => 'belum_bayar',
        ]);

        $this->admin = User::factory()->create([
            'username' => 'admin',
            'role' => 'admin',
            'status' => 'aktif',
        ]);

        $this->teller = User::factory()->create([
            'username' => 'teller',
            'role' => 'teller',
            'status' => 'aktif',
        ]);
    }

    private function buatSiswa(string $nis, string $nama): Siswa
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
            'status' => 'aktif',
        ]);
    }

    /**
     * Aturan 1: VA dibuat dengan format 10 digit yang benar.
     */
    public function test_va_dibuat_dengan_format_10_digit(): void
    {
        $nomor = VirtualAccount::generateNomorVa('2612345');

        $this->assertSame('8800012345', $nomor);
        $this->assertSame(10, strlen($nomor));
        $this->assertStringStartsWith('880', $nomor);
        $this->assertSame('12345', substr($nomor, -5));
    }

    public function test_va_dibuat_lewat_endpoint_dan_tersimpan(): void
    {
        $response = $this->actingAs($this->admin)->post(route('virtual-account.generate'), [
            'siswa_id' => $this->siswa->id_siswa,
        ]);

        $response->assertRedirect(route('virtual-account.index'));

        $va = VirtualAccount::where('siswa_id', $this->siswa->id_siswa)->first();

        $this->assertNotNull($va);
        $this->assertSame('8800012345', $va->nomor_va);
        $this->assertSame('aktif', $va->status);
        $this->assertSame(10, strlen($va->nomor_va));
    }

    public function test_va_untuk_nis_pendek_tetap_10_digit(): void
    {
        $siswa = $this->buatSiswa('77', 'Siswa Nis Pendek');

        $this->actingAs($this->admin)->post(route('virtual-account.generate'), [
            'siswa_id' => $siswa->id_siswa,
        ]);

        $va = VirtualAccount::where('siswa_id', $siswa->id_siswa)->first();

        $this->assertSame('8800000077', $va->nomor_va);
        $this->assertSame(10, strlen($va->nomor_va));
    }

    /**
     * Aturan 2: VA tidak bisa dibuat dua kali untuk siswa yang sama.
     */
    public function test_va_tidak_bisa_dibuat_dua_kali(): void
    {
        $this->actingAs($this->admin)->post(route('virtual-account.generate'), [
            'siswa_id' => $this->siswa->id_siswa,
        ]);

        $response = $this->actingAs($this->admin)->post(route('virtual-account.generate'), [
            'siswa_id' => $this->siswa->id_siswa,
        ]);

        $response->assertSessionHasErrors('siswa_id');
        $this->assertSame(1, VirtualAccount::where('siswa_id', $this->siswa->id_siswa)->count());
    }

    public function test_va_ditolak_kalau_nomor_bentrok_dengan_siswa_lain(): void
    {
        $kembar = $this->buatSiswa('9912345', 'Siswa Nis Kembar');

        $this->actingAs($this->admin)->post(route('virtual-account.generate'), [
            'siswa_id' => $this->siswa->id_siswa,
        ]);

        $response = $this->actingAs($this->admin)->post(route('virtual-account.generate'), [
            'siswa_id' => $kembar->id_siswa,
        ]);

        $response->assertSessionHasErrors('siswa_id');
        $this->assertSame(0, VirtualAccount::where('siswa_id', $kembar->id_siswa)->count());
    }

    /**
     * Aturan 3: pembayaran tunai mengubah status tagihan jadi lunas.
     */
    public function test_pembayaran_tunai_melunasi_tagihan(): void
    {
        $response = $this->actingAs($this->teller)->post(route('pembayaran.tunai'), [
            'tagihan_spp_id' => $this->tagihan->id_tagihan,
            'nominal_pembayaran' => 350000,
        ]);

        $pembayaran = Pembayaran::first();

        $this->assertNotNull($pembayaran);
        $response->assertRedirect(route('pembayaran.show', $pembayaran->id));

        $this->assertSame('lunas', $this->tagihan->fresh()->status);
        $this->assertSame('tunai', $pembayaran->metode_pembayaran);
        $this->assertSame('berhasil', $pembayaran->status);
        $this->assertSame($this->teller->id_user, $pembayaran->user_id);
        $this->assertNull($pembayaran->transaksi_id);
    }

    public function test_pembayaran_qris_melunasi_tagihan(): void
    {
        $this->actingAs($this->admin)->post(route('pembayaran.qris'), [
            'tagihan_spp_id' => $this->tagihan->id_tagihan,
            'nominal_pembayaran' => 350000,
            'nomor_referensi' => 'QR-778899',
        ]);

        $pembayaran = Pembayaran::first();

        $this->assertSame('lunas', $this->tagihan->fresh()->status);
        $this->assertSame('qris', $pembayaran->metode_pembayaran);
        $this->assertSame('QR-778899', $pembayaran->nomor_referensi);
    }

    /**
     * Aturan 4: pembayaran ditolak kalau tagihan sudah lunas.
     */
    public function test_pembayaran_ditolak_kalau_tagihan_sudah_lunas(): void
    {
        $this->actingAs($this->teller)->post(route('pembayaran.tunai'), [
            'tagihan_spp_id' => $this->tagihan->id_tagihan,
            'nominal_pembayaran' => 350000,
        ]);

        $response = $this->actingAs($this->teller)->post(route('pembayaran.tunai'), [
            'tagihan_spp_id' => $this->tagihan->id_tagihan,
            'nominal_pembayaran' => 350000,
        ]);

        $response->assertSessionHasErrors('tagihan_spp_id');
        $this->assertSame(1, Pembayaran::count());
        $this->assertSame('lunas', $this->tagihan->fresh()->status);
    }

    /**
     * Aturan 5: pembayaran dengan nominal nol ditolak.
     */
    public function test_pembayaran_nominal_nol_ditolak(): void
    {
        $response = $this->actingAs($this->teller)->post(route('pembayaran.tunai'), [
            'tagihan_spp_id' => $this->tagihan->id_tagihan,
            'nominal_pembayaran' => 0,
        ]);

        $response->assertSessionHasErrors('nominal_pembayaran');
        $this->assertSame(0, Pembayaran::count());
        $this->assertSame('belum_bayar', $this->tagihan->fresh()->status);
    }

    public function test_pembayaran_nominal_negatif_ditolak(): void
    {
        $response = $this->actingAs($this->teller)->post(route('pembayaran.tunai'), [
            'tagihan_spp_id' => $this->tagihan->id_tagihan,
            'nominal_pembayaran' => -50000,
        ]);

        $response->assertSessionHasErrors('nominal_pembayaran');
        $this->assertSame(0, Pembayaran::count());
    }

    /**
     * Aturan 6: callback dengan transaction_id tidak dikenal membalas 404.
     */
    public function test_callback_transaction_id_tidak_dikenal_membalas_404(): void
    {
        $response = $this->postJson(route('callback.payment'), [
            'transaction_id' => 'TRX-TIDAK-ADA',
            'status' => 'berhasil',
        ]);

        $response->assertStatus(404);
        $response->assertJsonPath('sukses', false);
        $this->assertStringContainsString('tidak dikenal', $response->json('pesan'));
    }

    /**
     * Aturan 7: callback yang sama datang dua kali tidak membuat pembayaran ganda.
     */
    public function test_callback_kembar_tidak_membuat_pembayaran_ganda(): void
    {
        $transaksi = $this->buatTransaksi('TRX-001');

        $pertama = $this->postJson(route('callback.payment'), [
            'transaction_id' => 'TRX-001',
            'status' => 'berhasil',
        ]);

        $kedua = $this->postJson(route('callback.payment'), [
            'transaction_id' => 'TRX-001',
            'status' => 'berhasil',
        ]);

        $pertama->assertStatus(201);
        $pertama->assertJsonPath('dibuat', true);

        $kedua->assertStatus(200);
        $kedua->assertJsonPath('dibuat', false);

        $this->assertSame(1, Pembayaran::count());
        $this->assertSame(1, Pembayaran::where('transaksi_id', $transaksi->id)->count());
        $this->assertSame('lunas', $this->tagihan->fresh()->status);
    }

    public function test_callback_berhasil_melunasi_tagihan_dan_mencatat_pembayaran(): void
    {
        $this->buatTransaksi('TRX-777');

        $response = $this->postJson(route('callback.payment'), [
            'transaction_id' => 'TRX-777',
            'status' => 'berhasil',
        ]);

        $response->assertStatus(201);

        $pembayaran = Pembayaran::first();

        $this->assertSame('va', $pembayaran->metode_pembayaran);
        $this->assertSame('berhasil', $pembayaran->status);
        $this->assertNull($pembayaran->user_id);
        $this->assertSame('lunas', $this->tagihan->fresh()->status);
        $this->assertSame('berhasil', TransaksiPaymentGateway::first()->status_transaksi);
    }

    public function test_callback_status_gagal_tidak_melunasi_tagihan(): void
    {
        $this->buatTransaksi('TRX-GAGAL');

        $response = $this->postJson(route('callback.payment'), [
            'transaction_id' => 'TRX-GAGAL',
            'status' => 'gagal',
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('dibuat', false);

        $this->assertSame(0, Pembayaran::count());
        $this->assertSame('belum_bayar', $this->tagihan->fresh()->status);
        $this->assertSame('gagal', TransaksiPaymentGateway::first()->status_transaksi);
    }

    public function test_transaction_id_wajib_unik(): void
    {
        $this->buatTransaksi('TRX-DOBEL');

        $this->expectException(\Illuminate\Database\QueryException::class);

        $this->buatTransaksi('TRX-DOBEL');
    }

    /**
     * Celah yang ditutup: tagihan sudah dibayar tunai oleh teller, lalu
     * callback Virtual Account untuk tagihan yang sama datang. Tagihan tidak
     * boleh tercatat lunas dua kali dengan dua metode berbeda.
     */
    public function test_callback_tidak_melunasi_tagihan_yang_sudah_dibayar_tunai(): void
    {
        $this->actingAs($this->teller)->post(route('pembayaran.tunai'), [
            'tagihan_spp_id' => $this->tagihan->id_tagihan,
            'nominal_pembayaran' => 350000,
        ]);

        $this->buatTransaksi('TRX-SETELAH-TUNAI');

        $response = $this->postJson(route('callback.payment'), [
            'transaction_id' => 'TRX-SETELAH-TUNAI',
            'status' => 'berhasil',
            'tagihan_spp_id' => $this->tagihan->id_tagihan,
        ]);

        $response->assertStatus(422);
        $response->assertJsonPath('dibuat', false);

        // Hanya satu pembayaran, yaitu yang tunai.
        $this->assertSame(1, Pembayaran::count());
        $this->assertSame('tunai', Pembayaran::first()->metode_pembayaran);
        $this->assertSame('lunas', $this->tagihan->fresh()->status);
    }

    /**
     * Aturan 8: siswa tidak bisa mengakses halaman pembayaran.
     */
    public function test_siswa_tidak_bisa_mengakses_halaman_pembayaran(): void
    {
        $akunSiswa = User::factory()->create([
            'username' => 'siswa01',
            'role' => 'siswa',
            'status' => 'aktif',
        ]);

        $this->actingAs($akunSiswa)->get(route('pembayaran.index'))->assertStatus(403);
        $this->actingAs($akunSiswa)->get(route('virtual-account.index'))->assertStatus(403);

        $this->actingAs($akunSiswa)->post(route('pembayaran.tunai'), [
            'tagihan_spp_id' => $this->tagihan->id_tagihan,
            'nominal_pembayaran' => 350000,
        ])->assertStatus(403);

        $this->actingAs($akunSiswa)->post(route('virtual-account.generate'), [
            'siswa_id' => $this->siswa->id_siswa,
        ])->assertStatus(403);

        $this->assertSame(0, Pembayaran::count());
        $this->assertSame(0, VirtualAccount::count());
    }

    public function test_tamu_diarahkan_ke_login_untuk_halaman_pembayaran(): void
    {
        $this->get(route('pembayaran.index'))->assertRedirect(route('login'));
        $this->get(route('virtual-account.index'))->assertRedirect(route('login'));
    }

    /**
     * Callback harus tetap bisa dipanggil tanpa login, karena yang memanggil
     * adalah server payment gateway, bukan pengguna.
     */
    public function test_callback_bisa_dipanggil_tanpa_login(): void
    {
        $this->buatTransaksi('TRX-TANPA-LOGIN');

        $this->assertGuest();

        $this->postJson(route('callback.payment'), [
            'transaction_id' => 'TRX-TANPA-LOGIN',
            'status' => 'berhasil',
        ])->assertStatus(201);
    }

    public function test_route_segmen_tetap_tidak_terbaca_sebagai_id(): void
    {
        // Kalau urutan route salah, "ringkasan" akan dibaca sebagai id
        // pembayaran dan hasilnya 404, bukan 200.
        $this->actingAs($this->teller)->get(route('pembayaran.ringkasan'))->assertStatus(200);
    }

    /**
     * Buat satu transaksi gateway untuk keperluan test callback.
     */
    private function buatTransaksi(string $transactionId): TransaksiPaymentGateway
    {
        return TransaksiPaymentGateway::create([
            'transaction_id' => $transactionId,
            'siswa_id' => $this->siswa->id_siswa,
            'nominal' => 350000,
            'status_transaksi' => 'pending',
            'payment_channel' => 'VA_BNI',
            'reference_number' => 'REF-'.$transactionId,
        ]);
    }
}
