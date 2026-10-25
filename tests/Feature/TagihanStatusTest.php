<?php

namespace Tests\Feature;

use App\Http\Controllers\Public\MidtransCallbackController;
use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Major;
use App\Models\PaymentGatewayTransaction;
use App\Models\Student;
use App\Models\TuitionBill;
use App\Models\TuitionRate;
use App\Models\VirtualAccount;
use App\Services\MidtransService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use ReflectionMethod;
use Tests\TestCase;

/**
 * Status tagihan hanya boleh bernilai belum_bayar atau lunas.
 *
 * Dulu ada baris berstatus 'belum' dan 'tunggakan' yang tidak pernah terpilih
 * oleh query, sehingga tagihan termuda yang terbayar, bukan yang tertua.
 */
class TagihanStatusTest extends TestCase
{
    use RefreshDatabase;

    private Student $siswa;

    private TuitionRate $tarif;

    private AcademicYear $tahunAjaran;

    protected function setUp(): void
    {
        parent::setUp();

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

        $kelas = Classroom::create([
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
            'nis' => '12345',
            'nisn' => '0012345678',
            'nama' => 'Ahmad Fauzan',
            'jenis_kelamin' => 'L',
            'tempat_lahir' => 'Sidoarjo',
            'tanggal_lahir' => '2009-01-15',
            'alamat' => 'Desa Sumber Makmur',
            'id_kelas' => $kelas->id_kelas,
            'id_tahun_ajaran' => $this->tahunAjaran->id_tahun_ajaran,
            'status' => 'aktif',
        ]);
    }

    private function buatTagihan(int $bulan, string $status): TuitionBill
    {
        return TuitionBill::create([
            'id_siswa' => $this->siswa->id_siswa,
            'id_tarif' => $this->tarif->id_tarif,
            'id_tahun_ajaran' => $this->tahunAjaran->id_tahun_ajaran,
            'bulan' => $bulan,
            'tahun' => 2026,
            'nominal' => 350000,
            'tanggal_jatuh_tempo' => sprintf('2026-%02d-30', $bulan),
            'status' => $status,
        ]);
    }

    private function selectBill(): ?TuitionBill
    {
        $transaksi = PaymentGatewayTransaction::create([
            'transaction_id' => 'TRX-'.uniqid(),
            'siswa_id' => $this->siswa->id_siswa,
            'nominal' => 350000,
            'status_transaksi' => 'pending',
            'payment_channel' => 'BCA',
            'reference_number' => '777010012345',
        ]);

        $kendali = new MidtransCallbackController(app(MidtransService::class));
        $metode = new ReflectionMethod($kendali, 'selectBill');
        $metode->setAccessible(true);

        return DB::transaction(fn () => $metode->invoke($kendali, $transaksi));
    }

    public function test_konstanta_status_hanya_dua_nilai(): void
    {
        $this->assertSame('belum_bayar', TuitionBill::STATUS_BELUM);
        $this->assertSame('lunas', TuitionBill::STATUS_LUNAS);
    }

    public function test_baris_berstatus_belum_tetap_dihitung_belum_lunas(): void
    {
        // Nilai lama yang tidak pernah terpilih oleh whereIn.
        $tagihan = $this->buatTagihan(7, 'belum');

        $this->assertNotSame(TuitionBill::STATUS_LUNAS, $tagihan->status);
        $this->assertTrue($tagihan->isPayable());
        $this->assertTrue(TuitionBill::where('status', '!=', TuitionBill::STATUS_LUNAS)->exists());
    }

    public function test_baris_berstatus_tunggakan_juga_dihitung_belum_lunas(): void
    {
        $tagihan = $this->buatTagihan(8, 'tunggakan');

        $this->assertTrue($tagihan->isPayable());
        $this->assertTrue(TuitionBill::overdue()->exists());
    }

    public function test_select_bill_memilih_bulan_paling_tua_bukan_yang_terdekat(): void
    {
        // Persis kasus nyata: Juli sampai September berstatus 'belum',
        // Oktober 'lunas'.
        $this->buatTagihan(7, 'belum');
        $this->buatTagihan(8, 'belum');
        $this->buatTagihan(9, 'belum');
        $this->buatTagihan(10, 'lunas');

        $dipilih = $this->selectBill();

        $this->assertNotNull($dipilih);
        $this->assertSame(7, $dipilih->bulan, 'tagihan tertua harus dipilih, bukan yang termuda');
        $this->assertSame(2026, $dipilih->tahun);
    }

    public function test_select_bill_tidak_pernah_memilih_tagihan_lunas(): void
    {
        $this->buatTagihan(7, 'lunas');
        $this->buatTagihan(8, 'lunas');

        $this->assertNull($this->selectBill());
    }

    public function test_tunggakan_dihitung_dari_tanggal_bukan_status(): void
    {
        $this->buatTagihan(1, 'belum_bayar');

        $this->assertSame(1, TuitionBill::overdue()->count());
        $this->assertSame(0, TuitionBill::where('status', 'tunggakan')->count());
    }

    public function test_migrasi_menormalkan_nilai_asing(): void
    {
        DB::table('tagihan_spp')->insert([
            'id_siswa' => $this->siswa->id_siswa,
            'id_tarif' => $this->tarif->id_tarif,
            'id_tahun_ajaran' => $this->tahunAjaran->id_tahun_ajaran,
            'bulan' => 9,
            'tahun' => 2026,
            'nominal' => 350000,
            'tanggal_jatuh_tempo' => '2026-09-30',
            'status' => 'belum',
        ]);

        $migrasi = require database_path('migrations/2026_11_06_000001_normalkan_status_tagihan_spp.php');
        $migrasi->up();

        $this->assertSame(0, DB::table('tagihan_spp')->whereNotIn('status', ['belum_bayar', 'lunas'])->count());
    }

    public function test_va_lama_tetap_tercari_dengan_nomor_tanpa_spasi(): void
    {
        $va = VirtualAccount::create([
            'siswa_id' => $this->siswa->id_siswa,
            'nomor_va' => '777010012345',
            'prefix' => '77701',
            'account_number' => '777010012345',
            'status' => 'aktif',
        ]);

        $this->assertSame('7770 1001 2345', $va->nomorTampil());
        $this->assertTrue(str_ends_with($va->account_number, substr($this->siswa->nis, -5)));
    }
}