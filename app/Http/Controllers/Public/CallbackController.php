<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Exceptions\XenditException;
use App\Models\Payment;
use App\Models\TuitionBill;
use App\Models\PaymentGatewayTransaction;
use App\Models\VirtualAccount;
use App\Services\XenditService;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CallbackController extends Controller
{
    /**
     * Terima callback dari payment gateway.
     *
     * Route ini sengaja tanpa middleware auth, karena yang memanggil adalah
     * server payment gateway, bukan pengguna. Pengamannya bukan sesi login,
     * tetapi pencocokan transaction_id yang wajib unik.
     *
     * Selalu membalas JSON, tidak pernah HTML, supaya payment gateway tidak
     * menerima halaman error yang tidak bisa dibaca.
     *
     * Sifatnya idempoten. Callback yang sama boleh datang berkali-kali, dan
     * hasilnya tetap satu pembayaran. Ada tiga lapis penjaga:
     * 1. Pemeriksaan awal: kalau transaksi ini sudah punya pembayaran, langsung
     *    balas 200 tanpa menulis apa pun.
     * 2. Transaksi database dengan lockForUpdate, supaya dua callback yang
     *    datang hampir bersamaan diproses bergantian, bukan bersamaan.
     * 3. Kunci unik nomor_transaksi di database, sebagai lapis terakhir kalau
     *    dua-duanya tetap lolos.
     */
    public function handle(Request $request): JsonResponse
    {
        $data = $request->validate([
            'transaction_id' => ['required', 'string'],
            'status' => ['required', 'string'],
            'tagihan_spp_id' => ['nullable', 'integer', 'exists:tagihan_spp,id_tagihan'],
        ], [
            'transaction_id.required' => 'transaction_id wajib dikirim.',
            'status.required' => 'status wajib dikirim.',
            'tagihan_spp_id.exists' => 'Tagihan yang ditunjuk tidak ditemukan.',
        ]);

        $transaksi = PaymentGatewayTransaction::where('transaction_id', $data['transaction_id'])->first();

        if ($transaksi === null) {
            return response()->json([
                'sukses' => false,
                'pesan' => 'Transaksi dengan transaction_id '.$data['transaction_id'].' tidak dikenal.',
            ], 404);
        }

        $statusMasuk = strtolower(trim($data['status']));

        // Simpan balasan asli dan status terbaru dari gateway, walaupun
        // statusnya belum berhasil. Ini jejak audit yang berguna saat sengketa.
        $transaksi->update([
            'status_transaksi' => $this->mapStatus($statusMasuk),
            'raw_response' => $request->all(),
        ]);

        if ($statusMasuk !== 'berhasil' && $statusMasuk !== 'success' && $statusMasuk !== 'paid') {
            return response()->json([
                'sukses' => true,
                'pesan' => 'Callback diterima, status transaksi dicatat sebagai '.$transaksi->status_transaksi.'.',
                'transaction_id' => $transaksi->transaction_id,
                'dibuat' => false,
            ], 200);
        }

        // Lapis 1: sudah pernah diproses.
        if (Payment::where('transaksi_id', $transaksi->id)->exists()) {
            return response()->json([
                'sukses' => true,
                'pesan' => 'Callback ini sudah pernah diproses sebelumnya.',
                'transaction_id' => $transaksi->transaction_id,
                'dibuat' => false,
            ], 200);
        }

        try {
            $hasil = DB::transaction(function () use ($transaksi, $data) {
                /** @var PaymentGatewayTransaction $terkunci */
                $terkunci = PaymentGatewayTransaction::whereKey($transaksi->id)->lockForUpdate()->firstOrFail();

                // Periksa sekali lagi setelah baris terkunci, karena callback
                // kembar bisa saja menunggu di antrean yang sama.
                if (Payment::where('transaksi_id', $terkunci->id)->exists()) {
                    return null;
                }

                $tagihan = $this->selectBill($terkunci, $data['tagihan_spp_id'] ?? null);

                if ($tagihan === null) {
                    return 'tanpa_tagihan';
                }

                $pembayaran = Payment::create([
                    'nomor_transaksi' => 'VA-'.$terkunci->transaction_id,
                    'transaksi_id' => $terkunci->id,
                    'tagihan_spp_id' => $tagihan->id_tagihan,
                    'siswa_id' => $terkunci->siswa_id,
                    'nominal_pembayaran' => $terkunci->nominal,
                    'metode_pembayaran' => 'va',
                    'status' => 'berhasil',
                    'user_id' => null,
                    'keterangan' => 'Pembayaran Virtual Account lewat payment gateway.',
                    'nomor_referensi' => $terkunci->reference_number,
                ]);

                $tagihan->update(['status' => 'lunas']);

                return $pembayaran;
            });
        } catch (QueryException $e) {
            // Lapis 3: kunci unik menolak baris kembar. Ini bukan kegagalan
            // dari sisi gateway, karena pembayarannya memang sudah tercatat.
            if ($this->karenaDuplikat($e)) {
                Log::warning('Callback kembar ditolak kunci unik', [
                    'transaction_id' => $transaksi->transaction_id,
                ]);

                return response()->json([
                    'sukses' => true,
                    'pesan' => 'Callback ini sudah pernah diproses sebelumnya.',
                    'transaction_id' => $transaksi->transaction_id,
                    'dibuat' => false,
                ], 200);
            }

            throw $e;
        }

        if ($hasil === 'tanpa_tagihan') {
            return response()->json([
                'sukses' => false,
                'pesan' => 'Transaksi ditemukan, tetapi tidak ada tagihan yang belum dibayar untuk siswa ini.',
                'transaction_id' => $transaksi->transaction_id,
                'dibuat' => false,
            ], 422);
        }

        if ($hasil === null) {
            return response()->json([
                'sukses' => true,
                'pesan' => 'Callback ini sudah pernah diproses sebelumnya.',
                'transaction_id' => $transaksi->transaction_id,
                'dibuat' => false,
            ], 200);
        }

        return response()->json([
            'sukses' => true,
            'pesan' => 'Pembayaran berhasil dicatat.',
            'transaction_id' => $transaksi->transaction_id,
            'nomor_transaksi' => $hasil->nomor_transaksi,
            'tagihan_spp_id' => $hasil->tagihan_spp_id,
            'dibuat' => true,
        ], 201);
    }

    /**
     * Terima callback dari Xendit untuk Virtual Account.
     *
     * Urutan pemeriksaan sengaja dibuat dari yang paling murah dan paling
     * menentukan, yaitu token. Token diperiksa SEBELUM apa pun disentuh, karena
     * tanpa itu siapa pun bisa mengirim callback palsu dan melunasi tagihan
     * tanpa membayar.
     *
     * Selalu membalas JSON, karena Xendit membaca balasan mesin, bukan halaman.
     */
    public function xendit(Request $request, XenditService $xendit): JsonResponse
    {
        $token = $request->header('x-callback-token');

        if (! $xendit->verifyCallbackToken(is_string($token) ? $token : null)) {
            Log::warning('Callback Xendit ditolak, token tidak cocok', [
                'ip' => $request->ip(),
            ]);

            return response()->json([
                'sukses' => false,
                'pesan' => 'Token callback tidak sah.',
            ], 401);
        }

        $externalId = $request->input('external_id');
        $accountNumber = $request->input('account_number');

        if (empty($externalId) && empty($accountNumber)) {
            return response()->json([
                'sukses' => false,
                'pesan' => 'Callback tidak memuat external_id maupun account_number.',
            ], 422);
        }

        $va = $this->findVirtualAccount($externalId, $accountNumber);

        if ($va === null) {
            return response()->json([
                'sukses' => false,
                'pesan' => 'Virtual Account dengan external_id '
                    .($externalId ?? '(kosong)').' dan account_number '
                    .($accountNumber ?? '(kosong)').' tidak dikenal.',
            ], 404);
        }

        $status = strtoupper(trim((string) $request->input('status', '')));

        if ($status !== 'COMPLETED') {
            $this->recordWithoutSettlement($request, $va, $status);

            return response()->json([
                'sukses' => true,
                'pesan' => 'Callback diterima dengan status '.$status.'. Tagihan tidak diubah.',
                'external_id' => $va->external_id,
                'dibuat' => false,
            ], 200);
        }

        $paymentId = $request->input('payment_id');
        $transactionId = $request->input('transaction_id');
        $paidAt = $this->bacaWaktu($request->input('paid_at'));

        // Lapis 1 idempoten: payment_id yang sama tidak boleh diproses dua kali.
        //
        // PENTING: yang dihitung hanya baris yang sudah berstatus berhasil.
        // Kalau semua baris dihitung, callback PENDING yang menyimpan payment_id
        // akan membuat callback COMPLETED berikutnya dianggap sudah diproses,
        // padahal pembayarannya belum pernah dicatat.
        if (! empty($paymentId) && $this->sudahDiproses($paymentId)) {
            return response()->json([
                'sukses' => true,
                'pesan' => 'Callback ini sudah pernah diproses sebelumnya.',
                'external_id' => $va->external_id,
                'dibuat' => false,
            ], 200);
        }

        try {
            $hasil = DB::transaction(function () use ($request, $va, $paymentId, $transactionId, $paidAt) {
                /** @var VirtualAccount $vaTerkunci */
                $vaTerkunci = VirtualAccount::whereKey($va->id)->lockForUpdate()->firstOrFail();

                // Periksa sekali lagi setelah baris terkunci. Callback kembar
                // bisa menunggu di antrean yang sama, dan yang kedua harus
                // melihat hasil kerja yang pertama.
                if (! empty($paymentId) && $this->sudahDiproses($paymentId)) {
                    return null;
                }

                $tagihan = TuitionBill::where('id_siswa', $vaTerkunci->siswa_id)
                    ->whereIn('status', ['belum_bayar', 'tunggakan'])
                    ->orderBy('tahun')
                    ->orderBy('bulan')
                    ->lockForUpdate()
                    ->first();

                if ($tagihan === null) {
                    return 'tanpa_tagihan';
                }

                $nominal = (float) $request->input('amount', $tagihan->nominal);

                $transaksi = $this->storeTransaction(
                    $vaTerkunci,
                    $transactionId,
                    $paymentId,
                    $nominal,
                    $paidAt,
                    $request->all()
                );

                $pembayaran = Payment::create([
                    'nomor_transaksi' => 'XD-'.($transactionId ?: $paymentId ?: $vaTerkunci->external_id),
                    'transaksi_id' => $transaksi->id,
                    'tagihan_spp_id' => $tagihan->id_tagihan,
                    'siswa_id' => $vaTerkunci->siswa_id,
                    'nominal_pembayaran' => $nominal,
                    'metode_pembayaran' => 'va',
                    'status' => 'berhasil',
                    'user_id' => null,
                    'keterangan' => 'Pembayaran Virtual Account Xendit '.$vaTerkunci->account_number.'.',
                    'nomor_referensi' => $vaTerkunci->account_number,
                ]);

                $tagihan->update(['status' => 'lunas']);

                return $pembayaran;
            });
        } catch (QueryException $e) {
            // Lapis 3: kunci unik menolak baris kembar. Dari sisi Xendit ini
            // bukan kegagalan, karena pembayarannya memang sudah tercatat.
            if ($this->karenaDuplikat($e)) {
                Log::warning('Callback Xendit kembar ditolak kunci unik', [
                    'external_id' => $va->external_id,
                    'payment_id' => $paymentId,
                ]);

                return response()->json([
                    'sukses' => true,
                    'pesan' => 'Callback ini sudah pernah diproses sebelumnya.',
                    'external_id' => $va->external_id,
                    'dibuat' => false,
                ], 200);
            }

            throw $e;
        }

        if ($hasil === null) {
            return response()->json([
                'sukses' => true,
                'pesan' => 'Callback ini sudah pernah diproses sebelumnya.',
                'external_id' => $va->external_id,
                'dibuat' => false,
            ], 200);
        }

        if ($hasil === 'tanpa_tagihan') {
            return response()->json([
                'sukses' => false,
                'pesan' => 'Virtual Account ditemukan, tetapi tidak ada tagihan yang belum dibayar untuk siswa ini.',
                'external_id' => $va->external_id,
                'dibuat' => false,
            ], 422);
        }

        return response()->json([
            'sukses' => true,
            'pesan' => 'Pembayaran Virtual Account berhasil dicatat.',
            'external_id' => $va->external_id,
            'nomor_transaksi' => $hasil->nomor_transaksi,
            'tagihan_spp_id' => $hasil->tagihan_spp_id,
            'dibuat' => true,
        ], 201);
    }

    /**
     * Apakah sebuah xendit_payment_id sudah benar benar diproses menjadi
     * pembayaran.
     *
     * Yang dihitung hanya baris transaksi berstatus berhasil. Baris berstatus
     * pending atau kedaluwarsa tidak dihitung, karena itu baru jejak callback,
     * belum pembayaran. Tanpa pembedaan ini, alur PENDING lalu COMPLETED untuk
     * pembayaran yang sama akan macet: COMPLETED dianggap kembar, padahal
     * pembayarannya belum pernah dicatat.
     */
    private function sudahDiproses(string $paymentId): bool
    {
        return PaymentGatewayTransaction::where('xendit_payment_id', $paymentId)
            ->where('status_transaksi', 'berhasil')
            ->exists();
    }

    /**
     * Cari Virtual Account dari external_id atau account_number.
     *
     * external_id diutamakan karena itu yang kita tentukan sendiri, sedangkan
     * account_number berasal dari Xendit dan bisa berubah kalau VA dibuat ulang.
     */
    private function findVirtualAccount(?string $externalId, ?string $accountNumber): ?VirtualAccount
    {
        if (! empty($externalId)) {
            $va = VirtualAccount::where('external_id', $externalId)->first();

            if ($va !== null) {
                return $va;
            }
        }

        if (! empty($accountNumber)) {
            return VirtualAccount::where('account_number', $accountNumber)->first();
        }

        return null;
    }

    /**
     * Simpan atau perbarui baris transaksi gateway untuk callback Xendit.
     *
     * Dipakai updateOrCreate supaya alur PENDING lalu COMPLETED untuk
     * pembayaran yang sama tidak menabrak kunci unik transaction_id.
     */
    private function storeTransaction(
        VirtualAccount $va,
        ?string $transactionId,
        ?string $paymentId,
        float $nominal,
        ?Carbon $paidAt,
        array $mentah
    ): PaymentGatewayTransaction {
        $kunci = $transactionId ?: ('VA-'.$va->external_id);

        return PaymentGatewayTransaction::updateOrCreate(
            ['transaction_id' => $kunci],
            [
                'virtual_account_id' => $va->id,
                'siswa_id' => $va->siswa_id,
                'nominal' => $nominal,
                'status_transaksi' => 'berhasil',
                'payment_channel' => $va->bank_code,
                'reference_number' => $va->account_number,
                'raw_response' => $mentah,
                'xendit_payment_id' => $paymentId,
                'paid_at' => $paidAt,
            ]
        );
    }

    /**
     * Catat callback yang statusnya bukan COMPLETED.
     *
     * Tagihan sengaja tidak disentuh. Kalau transaksi belum pernah tercatat,
     * dibuat baris baru berstatus pending atau kedaluwarsa, supaya jejaknya
     * tetap ada saat diperiksa nanti.
     */
    private function recordWithoutSettlement(Request $request, VirtualAccount $va, string $status): void
    {
        $transactionId = $request->input('transaction_id');
        $paymentId = $request->input('payment_id');

        $statusTransaksi = match ($status) {
            'PENDING' => 'pending',
            'EXPIRED', 'KEDALUWARSA' => 'kedaluwarsa',
            'FAILED', 'GAGAL' => 'gagal',
            default => 'pending',
        };

        $nominal = (float) $request->input('amount', $request->input('expected_amount', 0));

        try {
            PaymentGatewayTransaction::updateOrCreate(
                ['transaction_id' => $transactionId ?: ('VA-'.$va->external_id.'-'.$status)],
                [
                    'virtual_account_id' => $va->id,
                    'siswa_id' => $va->siswa_id,
                    'nominal' => $nominal,
                    'status_transaksi' => $statusTransaksi,
                    'payment_channel' => $va->bank_code,
                    'reference_number' => $va->account_number,
                    'raw_response' => $request->all(),
                    'xendit_payment_id' => $paymentId,
                    'paid_at' => null,
                ]
            );
        } catch (\Throwable $e) {
            // Pencatatan jejak tidak boleh membuat Xendit mengulang callback
            // terus menerus. Cukup dicatat di log.
            Log::warning('Gagal mencatat callback Xendit non COMPLETED', [
                'external_id' => $va->external_id,
                'status' => $status,
                'pesan' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Baca waktu dari balasan Xendit dengan aman, kembalikan null kalau tidak
     * dikenali, supaya satu format tanggal aneh tidak membatalkan pencatatan.
     */
    private function bacaWaktu(mixed $nilai): ?Carbon
    {
        if ($nilai === null || ! is_string($nilai) || trim($nilai) === '') {
            return null;
        }

        try {
            return Carbon::parse($nilai);
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Tentukan tagihan yang dibayar oleh transaksi ini.
     *
     * Kalau gateway mengirim tagihan_spp_id, pakai itu, asalkan benar-benar
     * milik siswa yang bersangkutan. Kalau tidak dikirim, ambil tagihan
     * tertua yang belum lunas milik siswa tersebut.
     *
     * Tagihan yang sudah lunas tidak pernah dipilih. Ini menutup celah nyata:
     * teller mencatat pembayaran tunai, tagihan jadi lunas, lalu callback
     * Virtual Account untuk tagihan yang sama datang. Tanpa penjagaan ini,
     * satu tagihan akan tercatat lunas dua kali dengan dua metode berbeda.
     */
    private function selectBill(PaymentGatewayTransaction $transaksi, ?int $tagihanId): ?TuitionBill
    {
        if ($tagihanId !== null) {
            return TuitionBill::whereKey($tagihanId)
                ->where('id_siswa', $transaksi->siswa_id)
                ->where('status', '!=', 'lunas')
                ->first();
        }

        return TuitionBill::where('id_siswa', $transaksi->siswa_id)
            ->whereIn('status', ['belum_bayar', 'tunggakan'])
            ->orderBy('tahun')
            ->orderBy('bulan')
            ->first();
    }

    /**
     * Petakan status dari gateway ke status yang dipakai tabel.
     */
    private function mapStatus(string $status): string
    {
        return match ($status) {
            'berhasil', 'success', 'paid', 'settlement', 'capture' => 'berhasil',
            'gagal', 'failed', 'deny', 'failure' => 'gagal',
            'kedaluwarsa', 'expired', 'expire' => 'kedaluwarsa',
            default => 'pending',
        };
    }

    /**
     * Periksa apakah kegagalan SQL disebabkan pelanggaran kunci unik.
     */
    private function karenaDuplikat(QueryException $e): bool
    {
        return (int) $e->errorInfo[1] === 1062;
    }
}
