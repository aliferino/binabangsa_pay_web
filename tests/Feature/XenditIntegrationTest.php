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
use Illuminate\Http\Client\Request as ClientRequest;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class XenditIntegrationTest extends TestCase
{
    use RefreshDatabase;

    private const TOKEN = 'token-callback-uji';

    // Nilai palsu untuk test. Bentuknya sengaja dibuat tidak menyerupai kunci
    // Xendit yang asli, supaya tidak ada yang salah kira ini kunci sungguhan.
    private const KUNCI = 'kunci-palsu-untuk-test';

    private TahunAjaran $tahunAjaran;

    private Kelas $kelas;

    private TarifSpp $tarif;

    private Siswa $siswa;

    private TagihanSpp $tagihan;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        // Konfigurasi diisi di sini, bukan di .env, supaya tidak ada kunci
        // sungguhan yang ikut tersimpan di repo.
        config([
            'services.xendit.secret_key' => self::KUNCI,
            'services.xendit.callback_token' => self::TOKEN,
            'services.xendit.base_url' => 'https://api.xendit.co',
        ]);

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

        $this->siswa = Siswa::create([
            'nis' => '2612345',
            'nisn' => '002612345',
            'nama' => 'Budi Santoso',
            'jenis_kelamin' => 'L',
            'tempat_lahir' => 'Sidoarjo',
            'tanggal_lahir' => '2009-05-12',
            'alamat' => 'Desa Sumber Makmur',
            'id_kelas' => $this->kelas->id_kelas,
            'id_tahun_ajaran' => $this->tahunAjaran->id_tahun_ajaran,
            'status' => 'aktif',
        ]);

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
    }

    /**
     * Balasan sukses pembuatan VA dari Xendit.
     */
    private function balasanBuatVa(string $externalId = '8800012345'): array
    {
        return [
            'id' => '5f2a1b3c4d5e6f708192a3b4',
            'account_number' => '8808123456789012',
            'external_id' => $externalId,
            'bank_code' => 'BRI',
            'expected_amount' => 350000,
            'expiration_date' => '2026-11-24T23:59:59.000Z',
            'status' => 'PENDING',
            'name' => 'Budi Santoso',
        ];
    }

    private function buatVaXendit(): VirtualAccount
    {
        return VirtualAccount::create([
            'siswa_id' => $this->siswa->id_siswa,
            'nomor_va' => '8800012345',
            'prefix' => '880',
            'status' => 'aktif',
            'xendit_id' => '5f2a1b3c4d5e6f708192a3b4',
            'account_number' => '8808123456789012',
            'bank_code' => 'BRI',
            'external_id' => '8800012345',
            'expired_at' => '2026-11-24 23:59:59',
        ]);
    }

    private function kirimCallback(array $isi, ?string $token = self::TOKEN)
    {
        $header = $token === null ? [] : ['x-callback-token' => $token];

        return $this->withHeaders($header)->postJson(route('callback.xendit'), $isi);
    }

    /**
     * Aturan 1: buat VA berhasil menyimpan account_number dan xendit_id.
     */
    public function test_buat_va_berhasil_menyimpan_account_number_dan_xendit_id(): void
    {
        Http::fake([
            'api.xendit.co/callback_virtual_accounts' => Http::response($this->balasanBuatVa(), 200),
        ]);

        $response = $this->actingAs($this->admin)->post(
            route('virtual-account.buat-xendit', $this->siswa->id_siswa),
            ['bank_code' => 'BRI', 'expected_amount' => 350000]
        );

        $va = VirtualAccount::where('siswa_id', $this->siswa->id_siswa)->first();

        $this->assertNotNull($va);
        $response->assertRedirect(route('virtual-account.show', $va->id));

        // Nomor internal dan nomor Xendit harus berbeda dan keduanya tersimpan.
        $this->assertSame('8800012345', $va->nomor_va);
        $this->assertSame('8808123456789012', $va->account_number);
        $this->assertSame('5f2a1b3c4d5e6f708192a3b4', $va->xendit_id);
        $this->assertSame('8800012345', $va->external_id);
        $this->assertSame('BRI', $va->bank_code);
        $this->assertNotNull($va->expired_at);
        $this->assertNotSame($va->nomor_va, $va->account_number);
    }

    /**
     * Basic auth Xendit: secret key sebagai username, password kosong.
     */
    public function test_permintaan_ke_xendit_memakai_basic_auth_dengan_password_kosong(): void
    {
        Http::fake([
            'api.xendit.co/callback_virtual_accounts' => Http::response($this->balasanBuatVa(), 200),
        ]);

        $this->actingAs($this->admin)->post(
            route('virtual-account.buat-xendit', $this->siswa->id_siswa),
            ['bank_code' => 'BRI']
        );

        Http::assertSent(function (ClientRequest $request) {
            $harapan = 'Basic '.base64_encode(self::KUNCI.':');

            return $request->url() === 'https://api.xendit.co/callback_virtual_accounts'
                && $request->hasHeader('Authorization', $harapan)
                && $request['external_id'] === '8800012345'
                && $request['bank_code'] === 'BRI'
                && $request['is_closed'] === true;
        });
    }

    /**
     * Aturan 2: buat VA gagal kalau Xendit membalas error, dan tidak ada data
     * yang tersimpan setengah jadi.
     */
    public function test_buat_va_gagal_kalau_xendit_membalas_error(): void
    {
        Http::fake([
            'api.xendit.co/callback_virtual_accounts' => Http::response([
                'error_code' => 'INVALID_BANK_CODE',
                'message' => 'Bank code tidak dikenal',
            ], 400),
        ]);

        $response = $this->actingAs($this->admin)->post(
            route('virtual-account.buat-xendit', $this->siswa->id_siswa),
            ['bank_code' => 'BANK_PALSU']
        );

        $response->assertSessionHasErrors('bank_code');

        // Tidak ada baris VA yang lahir dari kegagalan ini.
        $this->assertSame(0, VirtualAccount::where('siswa_id', $this->siswa->id_siswa)->count());
    }

    /**
     * Aturan 3: buat VA dua kali untuk siswa yang sama ditolak.
     */
    public function test_buat_va_dua_kali_untuk_siswa_sama_ditolak(): void
    {
        Http::fake([
            'api.xendit.co/callback_virtual_accounts' => Http::response($this->balasanBuatVa(), 200),
        ]);

        $this->actingAs($this->admin)->post(
            route('virtual-account.buat-xendit', $this->siswa->id_siswa),
            ['bank_code' => 'BRI']
        );

        $response = $this->actingAs($this->admin)->post(
            route('virtual-account.buat-xendit', $this->siswa->id_siswa),
            ['bank_code' => 'BNI']
        );

        $response->assertSessionHasErrors('bank_code');

        // Tetap satu baris, dan Xendit hanya dipanggil sekali.
        $this->assertSame(1, VirtualAccount::where('siswa_id', $this->siswa->id_siswa)->count());
        Http::assertSentCount(1);
    }

    /**
     * Aturan 4: callback dengan token salah dibalas 401.
     */
    public function test_callback_dengan_token_salah_dibalas_401(): void
    {
        $this->buatVaXendit();

        $response = $this->kirimCallback([
            'external_id' => '8800012345',
            'status' => 'COMPLETED',
            'payment_id' => 'pay-salah-token',
        ], 'token-palsu');

        $response->assertStatus(401);
        $response->assertJsonPath('sukses', false);
        $this->assertSame(0, Pembayaran::count());
        $this->assertSame('belum_bayar', $this->tagihan->fresh()->status);
    }

    /**
     * Aturan 5: callback dengan external_id tidak dikenal dibalas 404.
     */
    public function test_callback_dengan_external_id_tidak_dikenal_dibalas_404(): void
    {
        $this->buatVaXendit();

        $response = $this->kirimCallback([
            'external_id' => '8800099999',
            'account_number' => '0000000000000000',
            'status' => 'COMPLETED',
            'payment_id' => 'pay-tidak-dikenal',
        ]);

        $response->assertStatus(404);
        $response->assertJsonPath('sukses', false);
        $this->assertSame(0, Pembayaran::count());
    }

    /**
     * Aturan 6: callback COMPLETED membuat pembayaran dan melunasi tagihan.
     */
    public function test_callback_completed_membuat_pembayaran_dan_melunasi_tagihan(): void
    {
        $va = $this->buatVaXendit();

        $response = $this->kirimCallback([
            'id' => $va->xendit_id,
            'external_id' => '8800012345',
            'account_number' => '8808123456789012',
            'bank_code' => 'BRI',
            'expected_amount' => 350000,
            'amount' => 350000,
            'status' => 'COMPLETED',
            'transaction_id' => 'trx-xendit-001',
            'payment_id' => 'pay-xendit-001',
            'paid_at' => '2026-10-25T10:15:00.000Z',
        ]);

        $response->assertStatus(201);
        $response->assertJsonPath('dibuat', true);

        $pembayaran = Pembayaran::first();

        $this->assertNotNull($pembayaran);
        $this->assertSame('va', $pembayaran->metode_pembayaran);
        $this->assertSame('berhasil', $pembayaran->status);
        $this->assertSame('lunas', $this->tagihan->fresh()->status);
        $this->assertSame($this->tagihan->id_tagihan, $pembayaran->tagihan_spp_id);
        $this->assertNull($pembayaran->user_id);

        $transaksi = TransaksiPaymentGateway::first();

        $this->assertNotNull($transaksi);
        $this->assertSame('pay-xendit-001', $transaksi->xendit_payment_id);
        $this->assertSame('berhasil', $transaksi->status_transaksi);
        $this->assertNotNull($transaksi->paid_at);
        $this->assertSame($va->id, $transaksi->virtual_account_id);
    }

    /**
     * Aturan 7: callback COMPLETED yang sama datang dua kali tidak membuat
     * pembayaran ganda.
     */
    public function test_callback_completed_kembar_tidak_membuat_pembayaran_ganda(): void
    {
        $this->buatVaXendit();

        $isi = [
            'external_id' => '8800012345',
            'account_number' => '8808123456789012',
            'amount' => 350000,
            'status' => 'COMPLETED',
            'transaction_id' => 'trx-kembar-001',
            'payment_id' => 'pay-kembar-001',
            'paid_at' => '2026-10-25T10:15:00.000Z',
        ];

        $pertama = $this->kirimCallback($isi);
        $kedua = $this->kirimCallback($isi);

        $pertama->assertStatus(201);
        $pertama->assertJsonPath('dibuat', true);

        $kedua->assertStatus(200);
        $kedua->assertJsonPath('dibuat', false);

        $this->assertSame(1, Pembayaran::count());
        $this->assertSame(1, TransaksiPaymentGateway::count());
        $this->assertSame('lunas', $this->tagihan->fresh()->status);
    }

    /**
     * Aturan 8: callback status PENDING tidak mengubah tagihan.
     */
    public function test_callback_status_pending_tidak_mengubah_tagihan(): void
    {
        $this->buatVaXendit();

        $response = $this->kirimCallback([
            'external_id' => '8800012345',
            'account_number' => '8808123456789012',
            'status' => 'PENDING',
            'transaction_id' => 'trx-pending-001',
            'payment_id' => 'pay-pending-001',
            'expected_amount' => 350000,
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('dibuat', false);

        $this->assertSame(0, Pembayaran::count());
        $this->assertSame('belum_bayar', $this->tagihan->fresh()->status);

        // Jejaknya tetap dicatat sebagai pending.
        $this->assertSame(1, TransaksiPaymentGateway::count());
        $this->assertSame('pending', TransaksiPaymentGateway::first()->status_transaksi);
    }

    /**
     * Aturan 9: callback tanpa token dibalas 401.
     */
    public function test_callback_tanpa_token_dibalas_401(): void
    {
        $this->buatVaXendit();

        $response = $this->kirimCallback([
            'external_id' => '8800012345',
            'status' => 'COMPLETED',
            'payment_id' => 'pay-tanpa-token',
        ], null);

        $response->assertStatus(401);
        $response->assertJsonPath('sukses', false);
        $this->assertSame(0, Pembayaran::count());
        $this->assertSame('belum_bayar', $this->tagihan->fresh()->status);
    }

    /**
     * Aturan 10: simulasi hanya jalan kalau mode test. Kalau Xendit menolak
     * (seperti di mode production), tidak ada data yang berubah.
     */
    public function test_simulasi_gagal_kalau_xendit_menolak_karena_bukan_mode_test(): void
    {
        $va = $this->buatVaXendit();

        Http::fake([
            'api.xendit.co/callback_virtual_accounts/*/simulate_payment' => Http::response([
                'error_code' => 'NOT_FOUND',
                'message' => 'Simulation is only available in test mode',
            ], 404),
        ]);

        $response = $this->actingAs($this->admin)->post(
            route('virtual-account.simulasi', $va->id)
        );

        $response->assertRedirect(route('virtual-account.show', $va->id));
        $response->assertSessionHas('gagal');

        $this->assertSame(0, Pembayaran::count());
        $this->assertSame('belum_bayar', $this->tagihan->fresh()->status);
    }

    public function test_simulasi_berhasil_dikirim_ke_xendit_tanpa_melunasi_tagihan(): void
    {
        $va = $this->buatVaXendit();

        Http::fake([
            'api.xendit.co/callback_virtual_accounts/*/simulate_payment' => Http::response([
                'status' => 'COMPLETED',
            ], 200),
        ]);

        $response = $this->actingAs($this->admin)->post(
            route('virtual-account.simulasi', $va->id)
        );

        $response->assertRedirect(route('virtual-account.show', $va->id));
        $response->assertSessionHas('sukses');

        Http::assertSent(function (ClientRequest $request) {
            return str_contains($request->url(), '/8800012345/simulate_payment')
                && $request['amount'] === 350000;
        });

        // Simulasi tidak boleh langsung melunasi. Yang melunasi tetap callback.
        $this->assertSame(0, Pembayaran::count());
        $this->assertSame('belum_bayar', $this->tagihan->fresh()->status);
    }

    /**
     * Token callback yang dikosongkan di .env harus dianggap tidak sah, bukan
     * dianggap cocok. Kalau salah, siapa pun yang mengirim header kosong lolos.
     */
    public function test_token_kosong_di_konfigurasi_tidak_pernah_cocok(): void
    {
        config(['services.xendit.callback_token' => '']);

        $this->buatVaXendit();

        $response = $this->kirimCallback([
            'external_id' => '8800012345',
            'status' => 'COMPLETED',
            'payment_id' => 'pay-token-kosong',
        ], '');

        $response->assertStatus(401);
        $this->assertSame(0, Pembayaran::count());
    }

    /**
     * Alur sungguhan Xendit: PENDING dulu, lalu COMPLETED dengan payment_id yang
     * sama. COMPLETED tetap harus membuat pembayaran, bukan dianggap kembar.
     *
     * Ini menutup celah yang mudah terlewat: kalau pemeriksaan idempoten
     * menghitung semua baris tanpa melihat status, alur ini akan macet dan
     * pembayaran tidak pernah tercatat.
     */
    public function test_alur_pending_lalu_completed_tetap_membuat_pembayaran(): void
    {
        $this->buatVaXendit();

        $pending = $this->kirimCallback([
            'external_id' => '8800012345',
            'account_number' => '8808123456789012',
            'status' => 'PENDING',
            'transaction_id' => 'trx-alur-001',
            'payment_id' => 'pay-alur-001',
            'expected_amount' => 350000,
        ]);

        $pending->assertStatus(200);
        $this->assertSame(0, Pembayaran::count());

        $completed = $this->kirimCallback([
            'external_id' => '8800012345',
            'account_number' => '8808123456789012',
            'status' => 'COMPLETED',
            'transaction_id' => 'trx-alur-001',
            'payment_id' => 'pay-alur-001',
            'amount' => 350000,
            'paid_at' => '2026-10-25T10:15:00.000Z',
        ]);

        $completed->assertStatus(201);
        $completed->assertJsonPath('dibuat', true);

        $this->assertSame(1, Pembayaran::count());
        $this->assertSame('lunas', $this->tagihan->fresh()->status);
        $this->assertSame(1, TransaksiPaymentGateway::count());
        $this->assertSame('berhasil', TransaksiPaymentGateway::first()->status_transaksi);
    }

    public function test_siswa_tidak_bisa_membuat_va_xendit(): void
    {
        Http::fake();

        $akunSiswa = User::factory()->create([
            'username' => 'siswa01',
            'role' => 'siswa',
            'status' => 'aktif',
        ]);

        $this->actingAs($akunSiswa)->post(
            route('virtual-account.buat-xendit', $this->siswa->id_siswa),
            ['bank_code' => 'BRI']
        )->assertStatus(403);

        Http::assertNothingSent();
        $this->assertSame(0, VirtualAccount::count());
    }

    public function test_callback_xendit_bisa_dipanggil_tanpa_login(): void
    {
        $this->buatVaXendit();

        $this->assertGuest();

        $this->kirimCallback([
            'external_id' => '8800012345',
            'status' => 'COMPLETED',
            'transaction_id' => 'trx-tanpa-login',
            'payment_id' => 'pay-tanpa-login',
            'amount' => 350000,
        ])->assertStatus(201);
    }
}
