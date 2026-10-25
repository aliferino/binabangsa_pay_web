<?php

namespace Tests\Feature;

use App\Models\Major;
use App\Models\Classroom;
use App\Models\Payment;
use App\Models\Student;
use App\Models\TuitionBill;
use App\Models\AcademicYear;
use App\Models\TuitionRate;
use App\Models\PaymentGatewayTransaction;
use App\Models\User;
use App\Models\VirtualAccount;
use App\Services\MidtransService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request as ClientRequest;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class MidtransIntegrationTest extends TestCase
{
    use RefreshDatabase;

    // Nilai palsu untuk test. Bentuknya sengaja dibuat tidak menyerupai kunci
    // Midtrans yang asli, supaya tidak ada yang salah kira ini kunci sungguhan.
    private const KUNCI = 'kunci-palsu-midtrans-untuk-test';

    private AcademicYear $tahunAjaran;

    private Classroom $kelas;

    private TuitionRate $tarif;

    private Student $siswa;

    private TuitionBill $tagihan;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        // Konfigurasi diisi di sini, bukan di .env, supaya tidak ada kunci
        // sungguhan yang ikut tersimpan di repo.
        config([
            'midtrans.server_key' => self::KUNCI,
            'midtrans.client_key' => 'client-palsu-untuk-test',
            'midtrans.is_production' => false,
            'midtrans.base_url' => 'https://api.sandbox.midtrans.com',
        ]);

        $jurusan = Major::create([
            'kode_jurusan' => 'RPL',
            'nama_jurusan' => 'Rekayasa Perangkat Lunak',
        ]);

        $this->tahunAjaran = AcademicYear::create([
            'tahun_ajaran' => '2026/2027',
            'tanggal_mulai' => '2026-07-01',
            'tanggal_selesai' => '2027-06-30',
            'status' => 'aktif',
        ]);

        $this->kelas = Classroom::create([
            'nama_kelas' => 'XI RPL 1',
            'tingkat' => 'XI',
            'id_jurusan' => $jurusan->id_jurusan,
            'id_tahun_ajaran' => $this->tahunAjaran->id_tahun_ajaran,
        ]);

        $this->tarif = TuitionRate::create([
            'id_tahun_ajaran' => $this->tahunAjaran->id_tahun_ajaran,
            'nominal' => 350000,
            'status' => 'aktif',
        ]);

        $this->siswa = Student::create([
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

        $this->tagihan = TuitionBill::create([
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
     * Balasan sukses charge dari Midtrans.
     *
     * Nomor hasil meniru perilaku nyata: prefix merchant 77701 di depan nomor
     * yang kita kirim.
     */
    private function balasanCharge(string $vaKirim = '0012345'): array
    {
        return [
            'transaction_id' => '9a1b2c3d-4e5f-6789-abcd-ef0123456789',
            'order_id' => 'MT-2612345-20261105120000',
            'gross_amount' => '350000.00',
            'payment_type' => 'bank_transfer',
            'transaction_time' => '2026-11-05 12:00:00',
            'transaction_status' => 'pending',
            'fraud_status' => 'accept',
            'expiry_time' => '2026-11-06 12:00:00',
            'va_numbers' => [
                [
                    'bank' => 'bca',
                    'va_number' => '77701'.$vaKirim,
                ],
            ],
        ];
    }

    /**
     * Signature Midtrans: SHA512 dari order_id + status_code + gross_amount +
     * server key.
     */
    private function tandaTangan(array $payload, string $kunci = self::KUNCI): string
    {
        return hash('sha512',
            $payload['order_id']
            .$payload['status_code']
            .$payload['gross_amount']
            .$kunci
        );
    }

    /**
     * Notifikasi Midtrans yang sudah ditandatangani dengan benar.
     */
    private function kirimNotifikasi(array $ubah = []): array
    {
        $payload = array_merge([
            'transaction_id' => '9a1b2c3d-4e5f-6789-abcd-ef0123456789',
            'order_id' => 'MT-2612345-20261105120000',
            'status_code' => '200',
            'gross_amount' => '350000.00',
            'transaction_status' => 'settlement',
            'fraud_status' => 'accept',
            'transaction_time' => '2026-11-05 12:00:00',
            'settlement_time' => '2026-11-05 12:05:00',
            'payment_type' => 'bank_transfer',
            'va_numbers' => [
                ['bank' => 'bca', 'va_number' => '777010012345'],
            ],
        ], $ubah);

        $payload['signature_key'] = $this->tandaTangan($payload);

        return $payload;
    }

    /**
     * Siapkan satu transaksi pending untuk siswa, seperti hasil charge.
     */
    private function buatTransaksiPending(): PaymentGatewayTransaction
    {
        $va = VirtualAccount::create([
            'siswa_id' => $this->siswa->id_siswa,
            'nomor_va' => '0012345',
            'prefix' => '77701',
            'status' => 'aktif',
            'account_number' => '777010012345',
            'bank_code' => 'BCA',
            'external_id' => 'MT-2612345-20261105120000',
            'expired_at' => '2026-11-06 12:00:00',
        ]);

        return PaymentGatewayTransaction::create([
            'transaction_id' => 'MT-2612345-20261105120000',
            'virtual_account_id' => $va->id,
            'siswa_id' => $this->siswa->id_siswa,
            'nominal' => 350000,
            'status_transaksi' => 'pending',
            'payment_channel' => 'BCA',
            'reference_number' => '777010012345',
        ]);
    }

    /**
     * Aturan 1: nomor yang dikirim ke Midtrans adalah 00 + lima digit NIS,
     * dan nomor hasil Midtrans yang disimpan berakhiran nomor itu.
     */
    public function test_charge_mengirim_nomor_dua_nol_lalu_menyimpan_nomor_hasil_midtrans(): void
    {
        Http::fake([
            'https://api.sandbox.midtrans.com/v2/charge' => Http::response($this->balasanCharge(), 201),
        ]);

        $this->actingAs($this->admin)->post(
            route('virtual-account.buat-midtrans', $this->siswa->id_siswa),
            ['bank' => 'bca']
        );

        $va = VirtualAccount::where('siswa_id', $this->siswa->id_siswa)->first();

        $this->assertNotNull($va);

        // Yang kita kirim ke Midtrans: 00 + lima digit NIS. Nilai ini yang
        // dicek Http::fake di bawah, dan tidak disimpan lagi di nomor_va.
        $this->assertSame('0012345', VirtualAccount::generateNumber($this->siswa->nis));

        // Yang disimpan: satu nomor saja, 12 digit, dipakai tampil dan cari.
        $this->assertSame('777010012345', $va->nomor_va);

        // Yang disimpan untuk siswa: nomor hasil Midtrans, berakhiran NIS.
        $this->assertSame('777010012345', $va->account_number);
        $this->assertStringEndsWith('12345', $va->account_number);
        $this->assertStringEndsWith($va->nomor_va, $va->account_number);

        // Prefix merchant diambil dari hasil, bukan ditulis di kode.
        $this->assertSame('77701', $va->prefix);

        $this->assertSame('BCA', $va->bank_code);
        $this->assertNotNull($va->expired_at);

        $transaksi = PaymentGatewayTransaction::latest('id')->first();
        $this->assertNotNull($transaksi);
        $this->assertSame('pending', $transaksi->status_transaksi);
        $this->assertSame('777010012345', $transaksi->reference_number);
    }

    /**
     * Payload charge harus memakai payment_type bank_transfer, bank_transfer
     * berisi bank dan va_number, dan Basic Auth server key dengan password kosong.
     */
    public function test_payload_charge_dan_basic_auth_benar(): void
    {
        Http::fake([
            'https://api.sandbox.midtrans.com/v2/charge' => Http::response($this->balasanCharge(), 201),
        ]);

        $this->actingAs($this->admin)->post(
            route('virtual-account.buat-midtrans', $this->siswa->id_siswa),
            ['bank' => 'bca']
        );

        Http::assertSent(function (ClientRequest $request) {
            $harapan = 'Basic '.base64_encode(self::KUNCI.':');

            return str_starts_with($request->url(), 'https://api.sandbox.midtrans.com/v2/charge')
                && $request->hasHeader('Authorization', $harapan)
                && $request['payment_type'] === 'bank_transfer'
                && $request['transaction_details']['gross_amount'] === 350000
                && $request['bank_transfer']['bank'] === 'bca'
                && $request['bank_transfer']['va_number'] === '0012345'
                && $request['customer_details']['first_name'] === 'Budi Santoso';
        });
    }

    /**
     * Base URL diambil dari config, bukan literal di dalam service.
     */
    public function test_base_url_diambil_dari_config(): void
    {
        config(['midtrans.base_url' => 'https://alamat-uji.midtrans.test']);

        Http::fake([
            'https://alamat-uji.midtrans.test/v2/charge' => Http::response($this->balasanCharge(), 201),
        ]);

        $this->actingAs($this->admin)->post(
            route('virtual-account.buat-midtrans', $this->siswa->id_siswa),
            ['bank' => 'bca']
        );

        Http::assertSent(fn (ClientRequest $request) => str_starts_with(
            $request->url(), 'https://alamat-uji.midtrans.test/v2/charge'
        ));
    }

    /**
     * Aturan 5: nomor hasil wajib berakhiran nomor yang kita kirim. Kalau
     * Midtrans menggantinya, transaksi dianggap gagal dan VA tidak ditampilkan.
     */
    public function test_nomor_hasil_yang_diganti_midtrans_dianggap_gagal(): void
    {
        // Nomor 23 digit, persis seperti perilaku Midtrans saat nomor bentrok.
        $balasan = $this->balasanCharge();
        $balasan['va_numbers'][0]['va_number'] = '7770199887766554433221100';

        Http::fake([
            'https://api.sandbox.midtrans.com/v2/charge' => Http::response($balasan, 201),
        ]);

        $response = $this->actingAs($this->admin)->post(
            route('virtual-account.buat-midtrans', $this->siswa->id_siswa),
            ['bank' => 'bca']
        );

        $response->assertSessionHasErrors('bank');

        // Tidak ada VA yang lahir dari transaksi yang dibatalkan.
        $this->assertSame(0, VirtualAccount::where('siswa_id', $this->siswa->id_siswa)->count());

        $transaksi = PaymentGatewayTransaction::latest('id')->first();
        $this->assertNotNull($transaksi);
        $this->assertSame('gagal', $transaksi->status_transaksi);
    }

    /**
     * Aturan 5: kalau masih ada transaksi pending, nomornya dipakai ulang dan
     * tidak ada charge baru ke Midtrans.
     */
    public function test_transaksi_pending_dipakai_ulang_tanpa_charge_baru(): void
    {
        $this->buatTransaksiPending();

        Http::fake([
            'https://api.sandbox.midtrans.com/v2/charge' => Http::response($this->balasanCharge(), 201),
        ]);

        $response = $this->actingAs($this->admin)->post(
            route('virtual-account.buat-midtrans', $this->siswa->id_siswa),
            ['bank' => 'bca']
        );

        $va = VirtualAccount::where('siswa_id', $this->siswa->id_siswa)->first();

        $response->assertRedirect(route('virtual-account.show', $va->id));

        // Midtrans tidak dipanggil sama sekali.
        Http::assertNothingSent();

        // Nomor lama tetap dipakai, tidak ada nomor baru.
        $this->assertSame('0012345', $va->nomor_va);
        $this->assertSame(1, PaymentGatewayTransaction::count());
    }

    /**
     * Aturan 6: charge yang ditolak Midtrans tidak menyisakan data setengah jadi.
     */
    public function test_charge_gagal_tidak_menyisakan_data(): void
    {
        Http::fake([
            'https://api.sandbox.midtrans.com/v2/charge' => Http::response([
                'status_code' => '402',
                'status_message' => 'Invalid bank',
            ], 402),
        ]);

        $response = $this->actingAs($this->admin)->post(
            route('virtual-account.buat-midtrans', $this->siswa->id_siswa),
            ['bank' => 'bank-palsu']
        );

        $response->assertSessionHasErrors('bank');
        $this->assertSame(0, VirtualAccount::where('siswa_id', $this->siswa->id_siswa)->count());
        $this->assertSame(0, PaymentGatewayTransaction::count());
    }

    /**
     * Xendit tetap hidup sebagai jalur cadangan.
     */
    public function test_route_xendit_masih_ada(): void
    {
        $this->assertSame(
            url('/virtual-account/'.$this->siswa->id_siswa.'/buat-xendit'),
            route('virtual-account.buat-xendit', $this->siswa->id_siswa)
        );

        $this->assertSame(url('/callback/xendit'), route('callback.xendit'));
    }

    /**
     * Aturan 4: notifikasi dengan signature salah dibalas 403, dan tidak ada
     * pembayaran yang dibuat.
     */
    public function test_notifikasi_signature_salah_dibalas_403(): void
    {
        $this->buatTransaksiPending();

        $payload = $this->kirimNotifikasi();
        $payload['signature_key'] = str_repeat('a', 128);

        $response = $this->postJson(route('callback.midtrans'), $payload);

        $response->assertStatus(403);
        $this->assertSame(0, Payment::count());
        $this->assertSame('belum_bayar', $this->tagihan->fresh()->status);
    }

    /**
     * Notifikasi dengan status_code dan gross_amount yang diubah tapi signature
     * lama juga harus ditolak, karena signature mengikat ketiga nilainya.
     */
    public function test_notifikasi_dengan_nominal_diubah_dibalas_403(): void
    {
        $this->buatTransaksiPending();

        $payload = $this->kirimNotifikasi();
        $payload['gross_amount'] = '1.00';

        $response = $this->postJson(route('callback.midtrans'), $payload);

        $response->assertStatus(403);
        $this->assertSame(0, Payment::count());
    }

    /**
     * Notifikasi tanpa signature_key dibalas 403.
     */
    public function test_notifikasi_tanpa_signature_dibalas_403(): void
    {
        $this->buatTransaksiPending();

        $payload = $this->kirimNotifikasi();
        unset($payload['signature_key']);

        $this->postJson(route('callback.midtrans'), $payload)->assertStatus(403);
    }

    /**
     * Aturan 4: settlement membuat pembayaran, melunasi tagihan, mengisi paid_at.
     */
    public function test_notifikasi_settlement_membuat_pembayaran_dan_melunasi_tagihan(): void
    {
        $this->buatTransaksiPending();

        $response = $this->postJson(route('callback.midtrans'), $this->kirimNotifikasi());

        $response->assertStatus(201);
        $response->assertJsonPath('dibuat', true);

        $pembayaran = Payment::first();

        $this->assertNotNull($pembayaran);
        $this->assertSame('va', $pembayaran->metode_pembayaran);
        $this->assertSame('berhasil', $pembayaran->status);
        $this->assertSame('MT-MT-2612345-20261105120000', $pembayaran->nomor_transaksi);
        $this->assertSame($this->tagihan->id_tagihan, $pembayaran->tagihan_spp_id);
        $this->assertNull($pembayaran->user_id);

        // Waktu bayar diambil dari settlement_time, bukan waktu server kita.
        $this->assertSame('2026-11-05 12:05:00', $pembayaran->paidAt()->format('Y-m-d H:i:s'));

        $this->assertSame('lunas', $this->tagihan->fresh()->status);

        $transaksi = PaymentGatewayTransaction::latest('id')->first();
        $this->assertSame('berhasil', $transaksi->status_transaksi);
    }

    /**
     * Aturan 4: capture dengan fraud_status accept juga dianggap berhasil.
     */
    public function test_notifikasi_capture_fraud_accept_dianggap_berhasil(): void
    {
        $this->buatTransaksiPending();

        $response = $this->postJson(route('callback.midtrans'), $this->kirimNotifikasi([
            'transaction_status' => 'capture',
            'fraud_status' => 'accept',
        ]));

        $response->assertStatus(201);
        $this->assertSame('lunas', $this->tagihan->fresh()->status);
    }

    /**
     * Capture dengan fraud_status challenge TIDAK boleh melunasi tagihan,
     * karena uangnya belum tentu masuk.
     */
    public function test_notifikasi_capture_fraud_challenge_tidak_melunasi(): void
    {
        $this->buatTransaksiPending();

        $response = $this->postJson(route('callback.midtrans'), $this->kirimNotifikasi([
            'transaction_status' => 'capture',
            'fraud_status' => 'challenge',
        ]));

        $response->assertStatus(200);
        $this->assertSame(0, Payment::count());
        $this->assertSame('belum_bayar', $this->tagihan->fresh()->status);
    }

    /**
     * Aturan 4: pemetaan status expire, cancel, dan deny sesuai tabel.
     */
    public function test_pemetaan_status_expire_cancel_deny(): void
    {
        $harapan = [
            'expire' => 'kedaluwarsa',
            'cancel' => 'gagal',
            'deny' => 'gagal',
            'pending' => 'pending',
        ];

        foreach ($harapan as $status => $tersimpan) {
            Payment::query()->delete();
            PaymentGatewayTransaction::query()->delete();
            VirtualAccount::query()->delete();
            $this->tagihan->update(['status' => 'belum_bayar']);

            $this->buatTransaksiPending();

            $this->postJson(route('callback.midtrans'), $this->kirimNotifikasi([
                'transaction_status' => $status,
            ]))->assertStatus(200);

            $transaksi = PaymentGatewayTransaction::latest('id')->first();

            $this->assertSame($tersimpan, $transaksi->status_transaksi, 'status '.$status);
            $this->assertSame(0, Payment::count(), 'pembayaran untuk status '.$status);
            $this->assertSame('belum_bayar', $this->tagihan->fresh()->status, 'tagihan untuk status '.$status);
        }
    }

    /**
     * Aturan 4: notifikasi settlement kembar tidak membuat pembayaran ganda.
     */
    public function test_notifikasi_settlement_kembar_tidak_membuat_pembayaran_ganda(): void
    {
        $this->buatTransaksiPending();

        $payload = $this->kirimNotifikasi();

        $this->postJson(route('callback.midtrans'), $payload)->assertStatus(201);
        $kedua = $this->postJson(route('callback.midtrans'), $payload);

        $kedua->assertStatus(200);
        $kedua->assertJsonPath('dibuat', false);

        $this->assertSame(1, Payment::count());
        $this->assertSame(1, Payment::where('tagihan_spp_id', $this->tagihan->id_tagihan)->count());
    }

    /**
     * Notifikasi untuk order_id yang tidak dikenal dibalas 404.
     */
    public function test_notifikasi_order_id_tidak_dikenal_dibalas_404(): void
    {
        $response = $this->postJson(route('callback.midtrans'), $this->kirimNotifikasi([
            'order_id' => 'MT-TIDAK-ADA',
        ]));

        $response->assertStatus(404);
    }

    /**
     * Aturan 4: callback bisa dipanggil tanpa login.
     */
    public function test_callback_midtrans_bisa_dipanggil_tanpa_login(): void
    {
        $this->buatTransaksiPending();

        $this->postJson(route('callback.midtrans'), $this->kirimNotifikasi())
            ->assertStatus(201);

        $this->assertGuest();
    }

    /**
     * verifySignature menerima tanda tangan yang benar dan menolak yang salah,
     * termasuk saat server key kosong.
     */
    public function test_verify_signature_benar_salah_dan_kunci_kosong(): void
    {
        $service = new MidtransService;

        $payload = [
            'order_id' => 'MT-1',
            'status_code' => '200',
            'gross_amount' => '350000.00',
        ];

        $benar = $this->tandaTangan($payload);

        $this->assertTrue($service->verifySignature($payload + ['signature_key' => $benar], self::KUNCI));

        $this->assertFalse($service->verifySignature($payload + ['signature_key' => str_repeat('b', 128)], self::KUNCI));
        $this->assertFalse($service->verifySignature($payload + ['signature_key' => $benar], ''));
        $this->assertFalse($service->verifySignature($payload, self::KUNCI));
    }

    /**
     * ambilNomorVa membaca va_numbers dan permata_va_number, dan mengembalikan
     * null kalau tidak ada nomor sama sekali.
     */
    public function test_ambil_nomor_va_dari_dua_bentuk_balasan(): void
    {
        $service = new MidtransService;

        $this->assertSame('777010012345', $service->ambilNomorVa([
            'va_numbers' => [['bank' => 'bca', 'va_number' => '777010012345']],
        ]));

        $this->assertSame('12345678901', $service->ambilNomorVa([
            'permata_va_number' => '12345678901',
        ]));

        $this->assertNull($service->ambilNomorVa(['status_code' => '201']));
        $this->assertNull($service->ambilNomorVa([]));
    }

    /**
     * Aturan 1: nomor yang dikirim selalu dua nol lalu lima digit NIS, untuk
     * NIS panjang maupun NIS pendek.
     */
    public function test_generate_number_memakai_dua_nol_lalu_nis(): void
    {
        $this->assertSame('0012345', VirtualAccount::generateNumber('2612345'));
        $this->assertSame('0000077', VirtualAccount::generateNumber('77'));
        $this->assertSame('0054321', VirtualAccount::generateNumber('0054321'));
        $this->assertSame('0099999', VirtualAccount::generateNumber('abc99999xyz'));
    }

    /**
     * Service menolak lebih awal kalau server key belum diisi, dengan pesan yang
     * menunjuk ke penyebabnya.
     */
    public function test_charge_menolak_kalau_server_key_kosong(): void
    {
        config(['midtrans.server_key' => '']);

        $this->expectException(\App\Exceptions\MidtransException::class);
        $this->expectExceptionMessage('MIDTRANS_SERVER_KEY belum diisi di .env');

        (new MidtransService)->charge([
            'order_id' => 'MT-1',
            'gross_amount' => 350000,
            'va_number' => '0012345',
            'bank' => 'bca',
        ]);
    }

    /**
     * getStatus memakai endpoint /v2/{order_id}/status dari config.
     */
    public function test_get_status_memakai_endpoint_status_dari_config(): void
    {
        Http::fake([
            'https://api.sandbox.midtrans.com/v2/*/status' => Http::response([
                'order_id' => 'MT-1',
                'transaction_status' => 'settlement',
            ], 200),
        ]);

        $hasil = (new MidtransService)->getStatus('MT-1');

        $this->assertSame('settlement', $hasil['transaction_status']);

        Http::assertSent(fn (ClientRequest $request) => $request->url() === 'https://api.sandbox.midtrans.com/v2/MT-1/status');
    }
}