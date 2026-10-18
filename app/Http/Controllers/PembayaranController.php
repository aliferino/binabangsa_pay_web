<?php

namespace App\Http\Controllers;

use App\Models\Pembayaran;
use App\Models\Siswa;
use App\Models\TagihanSpp;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PembayaranController extends Controller
{
    /**
     * Jumlah percobaan ulang kalau nomor transaksi bentrok.
     */
    private const PERCOBAAN_NOMOR = 5;

    /**
     * Daftar pembayaran, bisa difilter per tanggal, metode, siswa, dan status.
     */
    public function index(Request $request)
    {
        $kueri = Pembayaran::with('siswa.kelas', 'tagihanSpp', 'user', 'transaksi');

        if ($request->filled('dari')) {
            $kueri->whereDate('created_at', '>=', $request->input('dari'));
        }

        if ($request->filled('sampai')) {
            $kueri->whereDate('created_at', '<=', $request->input('sampai'));
        }

        if ($request->filled('metode_pembayaran')) {
            $kueri->where('metode_pembayaran', $request->input('metode_pembayaran'));
        }

        if ($request->filled('siswa_id')) {
            $kueri->where('siswa_id', $request->input('siswa_id'));
        }

        if ($request->filled('status')) {
            $kueri->where('status', $request->input('status'));
        }

        return $this->tampilkan('pembayaran.index', [
            'daftar_pembayaran' => $kueri->orderByDesc('id')->get(),
            'filter' => $request->only([
                'dari', 'sampai', 'metode_pembayaran', 'siswa_id', 'status',
            ]),
            'daftar_siswa' => Siswa::orderBy('nama')->get(),
        ]);
    }

    /**
     * Detail satu pembayaran.
     */
    public function show(Pembayaran $pembayaran)
    {
        return $this->tampilkan('pembayaran.show', [
            'pembayaran' => $pembayaran->load('siswa.kelas', 'tagihanSpp', 'user', 'transaksi'),
        ]);
    }

    /**
     * Catat pembayaran tunai.
     */
    public function tunai(Request $request): RedirectResponse
    {
        return $this->catat($request, 'tunai');
    }

    /**
     * Catat pembayaran QRIS.
     */
    public function qris(Request $request): RedirectResponse
    {
        return $this->catat($request, 'qris');
    }

    /**
     * Inti pencatatan pembayaran tunai dan QRIS.
     *
     * Aturan yang dijaga di sini:
     * 1. Nominal wajib lebih besar dari nol.
     * 2. Tagihan yang sudah lunas ditolak, dengan pesan yang jelas.
     * 3. Status tagihan berubah jadi lunas dalam transaksi database yang sama
     *    dengan pencatatan pembayaran. Kalau salah satu gagal, keduanya batal,
     *    jadi tidak ada pembayaran tercatat tanpa tagihan ikut lunas.
     * 4. Tagihan dikunci selama proses (lockForUpdate) supaya dua teller yang
     *    menekan tombol hampir bersamaan tidak sama-sama lolos.
     * 5. user_id diisi dari user yang sedang login.
     */
    private function catat(Request $request, string $metode): RedirectResponse
    {
        $data = $request->validate([
            'tagihan_spp_id' => ['required', 'exists:tagihan_spp,id_tagihan'],
            'nominal_pembayaran' => ['required', 'numeric', 'gt:0'],
            'nomor_referensi' => ['nullable', 'string', 'max:255'],
            'keterangan' => ['nullable', 'string'],
            'status' => ['nullable', Rule::in(['berhasil', 'gagal', 'dibatalkan'])],
        ], [
            'tagihan_spp_id.required' => 'Tagihan wajib dipilih.',
            'tagihan_spp_id.exists' => 'Tagihan yang dipilih tidak ditemukan.',
            'nominal_pembayaran.required' => 'Nominal pembayaran wajib diisi.',
            'nominal_pembayaran.numeric' => 'Nominal pembayaran harus berupa angka.',
            'nominal_pembayaran.gt' => 'Nominal pembayaran harus lebih besar dari nol.',
            'status.in' => 'Status harus berhasil, gagal, atau dibatalkan.',
        ]);

        // Nomor transaksi disusun dari urutan terakhir hari itu. Kalau dua
        // teller mencatat pada saat yang hampir sama, nomornya bisa kembar.
        // Kunci unik di database menolak yang kedua, lalu di sini dicoba lagi
        // dengan nomor berikutnya. Tanpa ini, teller kedua mendapat error SQL
        // padahal pembayarannya sah.
        for ($percobaan = 1; $percobaan <= self::PERCOBAAN_NOMOR; $percobaan++) {
            try {
                $pembayaran = $this->simpan($data, $metode, $request);
                break;
            } catch (QueryException $e) {
                $bentrokNomor = $this->karenaDuplikat($e);

                if (! $bentrokNomor || $percobaan === self::PERCOBAAN_NOMOR) {
                    throw $e;
                }
            }
        }

        if ($pembayaran === 'lunas') {
            return back()
                ->withInput()
                ->withErrors(['tagihan_spp_id' => 'Tagihan ini sudah lunas, jadi tidak bisa dibayar lagi.']);
        }

        return redirect()->route('pembayaran.show', $pembayaran->id)
            ->with('sukses', 'Pembayaran '.$pembayaran->nomor_transaksi.' berhasil dicatat.');
    }

    /**
     * Tulis satu baris pembayaran dan ubah status tagihan dalam satu transaksi.
     *
     * Mengembalikan objek Pembayaran kalau berhasil, atau string 'lunas' kalau
     * tagihannya ternyata sudah lunas.
     */
    private function simpan(array $data, string $metode, Request $request): Pembayaran|string
    {
        return DB::transaction(function () use ($data, $metode, $request) {
            /** @var TagihanSpp $tagihan */
            $tagihan = TagihanSpp::whereKey($data['tagihan_spp_id'])->lockForUpdate()->firstOrFail();

            if ($tagihan->status === 'lunas') {
                return 'lunas';
            }

            $status = $data['status'] ?? 'berhasil';

            $catatan = Pembayaran::create([
                'nomor_transaksi' => Pembayaran::buatNomorTransaksi(),
                'transaksi_id' => null,
                'tagihan_spp_id' => $tagihan->id_tagihan,
                'siswa_id' => $tagihan->id_siswa,
                'nominal_pembayaran' => $data['nominal_pembayaran'],
                'metode_pembayaran' => $metode,
                'status' => $status,
                'user_id' => $request->user()->id_user,
                'keterangan' => $data['keterangan'] ?? null,
                'nomor_referensi' => $data['nomor_referensi'] ?? null,
            ]);

            // Tagihan hanya jadi lunas kalau pembayarannya berhasil. Pembayaran
            // yang gagal atau dibatalkan tetap dicatat sebagai riwayat, tetapi
            // tidak mengubah status tagihan.
            if ($status === 'berhasil') {
                $tagihan->update(['status' => 'lunas']);
            }

            return $catatan;
        });
    }

    /**
     * Ringkasan pembayaran per hari, dipakai laporan cepat di halaman daftar.
     */
    public function ringkasan(Request $request)
    {
        $tanggal = $request->filled('tanggal')
            ? Carbon::parse($request->input('tanggal'))->toDateString()
            : Carbon::today()->toDateString();

        $baris = Pembayaran::whereDate('created_at', $tanggal)
            ->where('status', 'berhasil')
            ->selectRaw('metode_pembayaran, COUNT(*) as jumlah, SUM(nominal_pembayaran) as total')
            ->groupBy('metode_pembayaran')
            ->get();

        return response()->json([
            'tanggal' => $tanggal,
            'rincian' => $baris,
            'total' => (float) $baris->sum('total'),
        ]);
    }

    /**
     * Periksa apakah kegagalan SQL disebabkan pelanggaran kunci unik.
     */
    private function karenaDuplikat(QueryException $e): bool
    {
        return (int) ($e->errorInfo[1] ?? 0) === 1062;
    }
}
