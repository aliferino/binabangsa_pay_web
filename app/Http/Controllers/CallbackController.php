<?php

namespace App\Http\Controllers;

use App\Models\Pembayaran;
use App\Models\TagihanSpp;
use App\Models\TransaksiPaymentGateway;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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

        $transaksi = TransaksiPaymentGateway::where('transaction_id', $data['transaction_id'])->first();

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
            'status_transaksi' => $this->petakanStatus($statusMasuk),
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
        if (Pembayaran::where('transaksi_id', $transaksi->id)->exists()) {
            return response()->json([
                'sukses' => true,
                'pesan' => 'Callback ini sudah pernah diproses sebelumnya.',
                'transaction_id' => $transaksi->transaction_id,
                'dibuat' => false,
            ], 200);
        }

        try {
            $hasil = DB::transaction(function () use ($transaksi, $data) {
                /** @var TransaksiPaymentGateway $terkunci */
                $terkunci = TransaksiPaymentGateway::whereKey($transaksi->id)->lockForUpdate()->firstOrFail();

                // Periksa sekali lagi setelah baris terkunci, karena callback
                // kembar bisa saja menunggu di antrean yang sama.
                if (Pembayaran::where('transaksi_id', $terkunci->id)->exists()) {
                    return null;
                }

                $tagihan = $this->pilihTagihan($terkunci, $data['tagihan_spp_id'] ?? null);

                if ($tagihan === null) {
                    return 'tanpa_tagihan';
                }

                $pembayaran = Pembayaran::create([
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
    private function pilihTagihan(TransaksiPaymentGateway $transaksi, ?int $tagihanId): ?TagihanSpp
    {
        if ($tagihanId !== null) {
            return TagihanSpp::whereKey($tagihanId)
                ->where('id_siswa', $transaksi->siswa_id)
                ->where('status', '!=', 'lunas')
                ->first();
        }

        return TagihanSpp::where('id_siswa', $transaksi->siswa_id)
            ->whereIn('status', ['belum_bayar', 'tunggakan'])
            ->orderBy('tahun')
            ->orderBy('bulan')
            ->first();
    }

    /**
     * Petakan status dari gateway ke status yang dipakai tabel.
     */
    private function petakanStatus(string $status): string
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
