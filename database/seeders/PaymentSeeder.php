<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\Payment;
use App\Models\PaymentGatewayTransaction;
use App\Models\Student;
use App\Models\TuitionBill;
use App\Models\TuitionRate;
use App\Models\VirtualAccount;
use App\Services\MockGatewayService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Isi data transaksi untuk demo: sekitar 80% tagihan lunas.
 *
 * Yang dibuat bukan sekadar baris tabel. Tiap pembayaran punya transaksi
 * gateway berstatus berhasil, dan tagihannya ditandai lunas, jadi angkanya
 * konsisten kalau dibuka dari halaman admin, teller, maupun siswa.
 *
 * Sisanya dibiarkan belum bayar supaya laporan tunggakan tetap ada isinya.
 * Nominal mengikuti tarif tingkat masing-masing siswa, bukan satu angka.
 *
 * Aman dijalankan berulang: hanya baris demo berawalan MOCK- yang dibersihkan,
 * pembayaran sungguhan (VA-, MT-, XD-) tidak disentuh.
 */
class PaymentSeeder extends Seeder
{
    /** Bagian tagihan yang dilunasi. 80 artinya 80 persen. */
    private const PERSEN_LUNAS = 80;

    /** Bulan tagihan yang dipakai demo, Juli sampai Oktober 2026. */
    private const BULAN = [7, 8, 9, 10];

    /**
     * Kolom tingkat di tabel kelas berisi angka romawi, sedangkan tarif
     * memakai angka. Peta ini yang menyambungkan keduanya.
     */
    private const TINGKAT = ['X' => '10', 'XI' => '11', 'XII' => '12'];

    public function run(): void
    {
        $tarif = TuitionRate::where('status', 'aktif')
            ->get()
            ->keyBy(fn (TuitionRate $t) => (string) ($t->tingkat ?? 'semua'));

        if ($tarif->isEmpty()) {
            $this->command?->warn('Belum ada tarif SPP aktif. Jalankan TuitionRateSeeder dulu.');

            return;
        }

        $tahunAjaran = AcademicYear::where('status', 'aktif')->first();

        if ($tahunAjaran === null) {
            $this->command?->warn('Belum ada tahun ajaran aktif.');

            return;
        }

        mt_srand(20261107);

        // Bersihkan jejak demo sebelumnya, lalu lepaskan tagihannya supaya bisa
        // diberi pola baru di bawah.
        $tagihanDemo = Payment::where('nomor_transaksi', 'like', 'MOCK-%')->pluck('tagihan_spp_id')->all();

        Payment::where('nomor_transaksi', 'like', 'MOCK-%')->delete();
        PaymentGatewayTransaction::where('transaction_id', 'like', 'MOCK-%')->delete();

        if ($tagihanDemo !== []) {
            TuitionBill::whereIn('id_tagihan', $tagihanDemo)
                ->update(['status' => TuitionBill::STATUS_BELUM]);
        }

        $dibuat = 0;

        foreach (Student::with('kelas')->orderBy('nis')->get() as $siswa) {
            $romawi = (string) ($siswa->kelas?->tingkat ?? '');
            $tingkat = self::TINGKAT[$romawi] ?? $romawi;

            // Tarif khusus tingkatan didahulukan, tarif tanpa tingkat jadi
            // cadangan. Dengan begitu siswa tingkat 11 tidak memakai tarif 10.
            $acuan = $tarif[$tingkat] ?? $tarif['semua'] ?? null;

            if ($acuan === null) {
                continue;
            }

            // Siswa baru belum punya tagihan sama sekali, jadi dibuatkan dulu
            // supaya angka 80% bisa dicapai merata.
            foreach (self::BULAN as $bulan) {
                TuitionBill::firstOrCreate(
                    [
                        'id_siswa' => $siswa->id_siswa,
                        'bulan' => $bulan,
                        'tahun' => 2026,
                    ],
                    [
                        'id_tarif' => $acuan->id_tarif,
                        'id_tahun_ajaran' => $tahunAjaran->id_tahun_ajaran,
                        'status' => TuitionBill::STATUS_BELUM,
                    ]
                );
            }

            // Nominal dan acuan tarif disamakan untuk SEMUA tagihan siswa ini,
            // termasuk yang dibuat seeder lama dengan tarif tanpa tingkat.
            TuitionBill::where('id_siswa', $siswa->id_siswa)->update([
                'id_tarif' => $acuan->id_tarif,
                'nominal' => $acuan->nominal,
                'tanggal_jatuh_tempo' => DB::raw('LAST_DAY(STR_TO_DATE(CONCAT(tahun, "-", bulan, "-01"), "%Y-%m-%d"))'),
            ]);

            $tagihan = TuitionBill::where('id_siswa', $siswa->id_siswa)
                ->orderBy('tahun')
                ->orderBy('bulan')
                ->get();

            $va = VirtualAccount::where('siswa_id', $siswa->id_siswa)->first();

            foreach ($tagihan as $i => $t) {
                // Pola tetap per siswa supaya hasilnya sama setiap kali seeder
                // dijalankan, dan supaya sisa tunggakan tersebar rata.
                $lunas = (($i * 37 + $siswa->id_siswa * 11) % 100) < self::PERSEN_LUNAS;

                if (! $lunas) {
                    continue;
                }

                $waktu = Carbon::create((int) $t->tahun, (int) $t->bulan, 10, 9, 30)
                    ->addDays(mt_rand(0, 8));

                DB::transaction(function () use ($siswa, $t, $va, $acuan, $waktu, &$dibuat) {
                    $t->update(['status' => TuitionBill::STATUS_LUNAS]);

                    $nomor = 'MOCK-VA-'.$siswa->nis.'-'.$t->bulan.'-'.$t->tahun;

                    $transaksi = PaymentGatewayTransaction::create([
                        'transaction_id' => $nomor,
                        'virtual_account_id' => $va?->id,
                        'siswa_id' => $siswa->id_siswa,
                        'nominal' => $acuan->nominal,
                        'status_transaksi' => 'berhasil',
                        'payment_channel' => MockGatewayService::CHANNEL,
                        'reference_number' => $va?->account_number ?: $va?->nomor_va,
                        'paid_at' => $waktu,
                    ]);

                    Payment::create([
                        'nomor_transaksi' => $nomor,
                        'transaksi_id' => $transaksi->id,
                        'tagihan_spp_id' => $t->id_tagihan,
                        'siswa_id' => $siswa->id_siswa,
                        'nominal_pembayaran' => $acuan->nominal,
                        'tanggal_pembayaran' => $waktu,
                        'metode_pembayaran' => 'va',
                        'status' => 'berhasil',
                                                'user_id' => null,
                                                'keterangan' => 'Data demo: pembayaran Virtual Account.',
                                                'nomor_referensi' => $va?->account_number ?: $va?->nomor_va,
                                                // created_at ikut waktunya. Kepsek menghitung penerimaan
                                                // per bulan dari created_at, jadi tanpa ini semua
                                                // pembayaran menumpuk di bulan saat seeder dijalankan.
                                                'created_at' => $waktu,
                                                'updated_at' => $waktu,
                                            ]);

                                            // Transaksi gateway juga, supaya daftar transaksi konsisten.
                                            $transaksi->update(['created_at' => $waktu, 'updated_at' => $waktu]);

                    $dibuat++;
                });
            }
        }

        $total = TuitionBill::count();
        $lunas = TuitionBill::where('status', TuitionBill::STATUS_LUNAS)->count();

        $this->command?->info(sprintf(
            'Pembayaran dibuat: %d. Tagihan lunas %d dari %d (%.0f%%).',
            $dibuat,
            $lunas,
            $total,
            $total > 0 ? $lunas / $total * 100 : 0
        ));
    }
}