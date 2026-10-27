<?php

namespace Tests\Feature;

use App\Http\Controllers\Public\MockCallbackController;
use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Major;
use App\Models\Payment;
use App\Models\PaymentGatewayTransaction;
use App\Models\Student;
use App\Models\TuitionBill;
use App\Models\TuitionRate;
use App\Models\User;
use App\Models\VirtualAccount;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class MockCallbackTest extends TestCase
{
    use RefreshDatabase;

    // Kunci palsu untuk test. Sama dengan nilai bawaan config, dan tetap
    // ditulis ulang di sini supaya test tidak bergantung nilai .env mesin.
    private const KUNCI = 'mock-gateway-lokal-untuk-ukk';

    private AcademicYear $tahunAjaran;

    private Classroom $kelas;

    private TuitionRate $tarif;

    private Student $siswa;

    private TuitionBill $tagihan;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        config(['mock_gateway.secret' => self::KUNCI]);

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
     * Tanda tangan sesuai kontrak yang dibekukan: sha256 dari
     * order_id|status_code|gross_amount|transaction_status dengan kunci.
     */
    private function tandaTangan(array $payload): string
    {
        return hash_hmac(
            'sha256',
            $payload['order_id']
                .'|'.$payload['status_code']
                .'|'.$payload['gross_amount']
                .'|'.$payload['transaction_status'],
            self::KUNCI
        );
    }

    /**
     * Notifikasi mock yang sudah ditandatangani dengan benar.
     */
    private function payloadMock(array $ubah = []): array
    {
        $payload = array_merge([
            'order_id' => '2612345-20261105120000',
            'status_code' => '200',
            'gross_amount' => '350000.00',
            'transaction_status' => 'settlement',
            'transaction_id' => 'mock-9a1b2c3d-4e5f-6789',
            'settlement_time' => '2026-11-05 12:05:00',
            'keterangan_mock' => 'Uji coba gateway tiruan.',
        ], $ubah);

        $payload['signature_key'] = $this->tandaTangan($payload);

        return $payload;
    }

    /**
     * Satu transaksi pending untuk siswa, seperti hasil bikin VA.
     *
     * $orderId bisa diisi untuk menguji transaction_id yang sudah berawalan
     * MOCK-, persis yang dibuat SimulationController::gateway().
     */
    private function buatTransaksiPending(string $orderId = '2612345-20261105120000'): PaymentGatewayTransaction
    {
        $va = VirtualAccount::create([
            'siswa_id' => $this->siswa->id_siswa,
            'nomor_va' => '777010012345',
            'prefix' => '77701',
            'status' => 'aktif',
            'account_number' => '777010012345',
            'bank_code' => 'BCA',
            'external_id' => $orderId,
            'expired_at' => '2026-11-06 12:00:00',
        ]);

        return PaymentGatewayTransaction::create([
            'transaction_id' => $orderId,
            'virtual_account_id' => $va->id,
            'siswa_id' => $this->siswa->id_siswa,
            'nominal' => 350000,
            'status_transaksi' => 'pending',
            'payment_channel' => 'MOCK',
            'reference_number' => '777010012345',
        ]);
    }

    /**
     * Panggil controller langsung, tanpa lewat jaringan.
     */
    private function kirim(array $payload)
    {
        return (new MockCallbackController)->handle(
            Request::create('/callback/mock', 'POST', $payload)
        );
    }

    /**
     * Tanda tangan salah dibalas 403, dan tidak ada pembayaran yang dibuat.
     */
    public function test_tanda_tangan_salah_dibalas_403(): void
    {
        $this->buatTransaksiPending();

        $payload = $this->payloadMock();
        $payload['signature_key'] = str_repeat('a', 64);

        $response = $this->kirim($payload);

        $this->assertSame(403, $response->getStatusCode());
        $this->assertDatabaseCount('pembayaran', 0);
        $this->assertSame('belum_bayar', $this->tagihan->fresh()->status);
    }

    /**
     * Tanda tangan sah membuat pembayaran, melunasi tagihan, dan mengembalikan
     * 201.
     */
    public function test_tanda_tangan_sah_melunasi_tagihan_dan_mencatat_pembayaran(): void
    {
        $this->buatTransaksiPending();

        $response = $this->kirim($this->payloadMock());

        $this->assertSame(201, $response->getStatusCode());
        $this->assertTrue($response->getData(true)['dibuat']);

        $pembayaran = Payment::first();

        $this->assertNotNull($pembayaran);
        $this->assertSame('MOCK-2612345-20261105120000', $pembayaran->nomor_transaksi);
        $this->assertSame('va', $pembayaran->metode_pembayaran);
        $this->assertSame('berhasil', $pembayaran->status);
        $this->assertNull($pembayaran->user_id);
        $this->assertSame($this->tagihan->id_tagihan, $pembayaran->tagihan_spp_id);

        // Waktu bayar diambil dari settlement_time, bukan waktu server.
        $this->assertSame('2026-11-05 12:05:00', $pembayaran->paidAt()->format('Y-m-d H:i:s'));

        $this->assertSame('lunas', $this->tagihan->fresh()->status);

        $transaksi = PaymentGatewayTransaction::first();
        $this->assertSame('berhasil', $transaksi->status_transaksi);
        $this->assertNotNull($transaksi->paid_at);
    }

    /**
     * Nominal tidak cocok dibalas 422, dan tagihan TETAP belum lunas.
     */
    public function test_nominal_tidak_cocok_dibalas_422(): void
    {
        $this->buatTransaksiPending();

        $response = $this->kirim($this->payloadMock([
            'gross_amount' => '100000.00',
        ]));

        $this->assertSame(422, $response->getStatusCode());
        $this->assertDatabaseCount('pembayaran', 0);
        $this->assertSame('belum_bayar', $this->tagihan->fresh()->status);

        // Pesan memuat kedua nominal dalam format rupiah.
        $pesan = $response->getData(true)['pesan'];
        $this->assertStringContainsString('350.000', $pesan);
        $this->assertStringContainsString('100.000', $pesan);
    }

    /**
     * Callback kembar tidak membuat pembayaran ganda, hanya satu baris.
     */
    public function test_callback_kembar_hanya_membuat_satu_pembayaran(): void
    {
        $this->buatTransaksiPending();

        $payload = $this->payloadMock();

        $pertama = $this->kirim($payload);
        $kedua = $this->kirim($payload);

        $this->assertSame(201, $pertama->getStatusCode());
        $this->assertSame(200, $kedua->getStatusCode());
        $this->assertFalse($kedua->getData(true)['dibuat']);

        $this->assertDatabaseCount('pembayaran', 1);
    }

    /**
     * Transaksi yang tidak dikenal dibalas 404.
     */
    public function test_transaksi_tidak_ditemukan_dibalas_404(): void
    {
        $response = $this->kirim($this->payloadMock([
            'order_id' => 'MOCK-TIDAK-ADA',
        ]));

        $this->assertSame(404, $response->getStatusCode());
        $this->assertDatabaseCount('pembayaran', 0);
    }

    /**
     * transaction_id yang sudah berawalan MOCK- tidak boleh jadi MOCK-MOCK-.
     *
     * Ini bentuk asli yang dibuat SimulationController::gateway(), dan dulu
     * nomor pembayarannya jadi dobel awalan. Test ini yang mengunci perbaikan
     * itu, karena bentuk order_id tanpa awalan tidak pernah menyentuh jalur
     * tersebut.
     */
    public function test_awalan_mock_tidak_digandakan(): void
    {
        $orderId = 'MOCK-777010012345-20261106120000';

        $this->buatTransaksiPending($orderId);

        $response = $this->kirim($this->payloadMock(['order_id' => $orderId]));

        $this->assertSame(201, $response->getStatusCode());
        $this->assertSame($orderId, $response->getData(true)['nomor_transaksi']);
        $this->assertSame($orderId, Payment::first()->nomor_transaksi);
        $this->assertSame('lunas', $this->tagihan->fresh()->status);
    }
}
