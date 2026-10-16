<?php

namespace App\Http\Controllers;

use App\Models\Siswa;
use App\Models\TagihanSpp;
use App\Models\TahunAjaran;
use App\Models\TarifSpp;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

class TagihanSppController extends Controller
{
    /**
     * Daftar tagihan, bisa difilter per siswa, per bulan, dan per status.
     */
    public function index(Request $request)
    {
        $kueri = TagihanSpp::with('siswa.kelas', 'tarif', 'tahunAjaran');

        if ($request->filled('id_siswa')) {
            $kueri->where('id_siswa', $request->input('id_siswa'));
        }

        if ($request->filled('bulan')) {
            $kueri->where('bulan', $request->input('bulan'));
        }

        if ($request->filled('tahun')) {
            $kueri->where('tahun', $request->input('tahun'));
        }

        if ($request->filled('status')) {
            $kueri->where('status', $request->input('status'));
        }

        return $this->tampilkan('tagihan-spp.index', [
            'daftar_tagihan' => $kueri->orderByDesc('tahun')->orderByDesc('bulan')->get(),
            'filter' => $request->only(['id_siswa', 'bulan', 'tahun', 'status']),
            'daftar_siswa' => Siswa::orderBy('nama')->get(),
            'daftar_tahun' => TagihanSpp::distinct()->orderByDesc('tahun')->pluck('tahun'),
        ]);
    }

    /**
     * Tampilkan form tambah tagihan.
     */
    public function create()
    {
        return $this->tampilkan('tagihan-spp.create', $this->pilihan());
    }

    /**
     * Simpan tagihan baru.
     *
     * Satu siswa hanya boleh punya satu tagihan per bulan per tahun. Aturan
     * ini juga dijaga kunci unik di database, tetapi diperiksa lebih dulu di
     * sini supaya pesan errornya ramah dan tidak berupa error SQL.
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate($this->aturan(), $this->pesan());

        $sudahAda = TagihanSpp::where('id_siswa', $data['id_siswa'])
            ->where('bulan', $data['bulan'])
            ->where('tahun', $data['tahun'])
            ->exists();

        if ($sudahAda) {
            return back()
                ->withInput()
                ->withErrors(['bulan' => 'Siswa ini sudah punya tagihan untuk bulan dan tahun tersebut.']);
        }

        TagihanSpp::create($data);

        return redirect()->route('tagihan-spp.index')->with('sukses', 'Tagihan berhasil ditambahkan.');
    }

    /**
     * Tampilkan detail satu tagihan.
     */
    public function show(TagihanSpp $tagihanSpp)
    {
        return $this->tampilkan('tagihan-spp.show', [
            'tagihan' => $tagihanSpp->load('siswa.kelas', 'tarif', 'tahunAjaran'),
        ]);
    }

    /**
     * Tampilkan form ubah tagihan.
     */
    public function edit(TagihanSpp $tagihanSpp)
    {
        return $this->tampilkan('tagihan-spp.edit', array_merge(
            ['tagihan' => $tagihanSpp],
            $this->pilihan(),
        ));
    }

    /**
     * Perbarui data tagihan.
     */
    public function update(Request $request, TagihanSpp $tagihanSpp): RedirectResponse
    {
        $data = $request->validate($this->aturan(), $this->pesan());

        $bentrok = TagihanSpp::where('id_siswa', $data['id_siswa'])
            ->where('bulan', $data['bulan'])
            ->where('tahun', $data['tahun'])
            ->where('id_tagihan', '!=', $tagihanSpp->id_tagihan)
            ->exists();

        if ($bentrok) {
            return back()
                ->withInput()
                ->withErrors(['bulan' => 'Siswa ini sudah punya tagihan untuk bulan dan tahun tersebut.']);
        }

        $tagihanSpp->update($data);

        return redirect()->route('tagihan-spp.index')->with('sukses', 'Tagihan berhasil diperbarui.');
    }

    /**
     * Hapus tagihan.
     *
     * Tagihan yang sudah lunas tidak boleh dihapus, karena itu bagian dari
     * riwayat pembayaran.
     */
    public function destroy(TagihanSpp $tagihanSpp): RedirectResponse
    {
        if ($tagihanSpp->status === 'lunas') {
            return redirect()->route('tagihan-spp.index')
                ->with('gagal', 'Tagihan yang sudah lunas tidak boleh dihapus.');
        }

        $tagihanSpp->delete();

        return redirect()->route('tagihan-spp.index')->with('sukses', 'Tagihan berhasil dihapus.');
    }

    /**
     * Ubah tagihan yang sudah lewat jatuh tempo menjadi tunggakan.
     *
     * Aturannya: batas pembayaran adalah tanggal terakhir bulan tagihan.
     * Jadi mulai tanggal 1 bulan berikutnya, tagihan yang masih berstatus
     * belum_bayar otomatis menjadi tunggakan. Tagihan yang sudah lunas
     * tidak pernah disentuh.
     */
    public function tandaiTunggakan(): RedirectResponse
    {
        $hariIni = Carbon::today();
        $jumlah = 0;

        TagihanSpp::where('status', 'belum_bayar')
            ->get()
            ->each(function (TagihanSpp $tagihan) use ($hariIni, &$jumlah) {
                $batas = Carbon::create($tagihan->tahun, $tagihan->bulan, 1)->endOfMonth();

                if ($hariIni->greaterThan($batas)) {
                    $tagihan->update(['status' => 'tunggakan']);
                    $jumlah++;
                }
            });

        if ($jumlah === 0) {
            return redirect()->route('tagihan-spp.index')
                ->with('sukses', 'Tidak ada tagihan yang perlu ditandai tunggakan.');
        }

        return redirect()->route('tagihan-spp.index')
            ->with('sukses', $jumlah.' tagihan ditandai sebagai tunggakan.');
    }

    /**
     * Aturan validasi tagihan.
     */
    private function aturan(): array
    {
        return [
            'id_siswa' => ['required', 'exists:siswa,id_siswa'],
            'id_tarif' => ['required', 'exists:tarif_spp,id_tarif'],
            'id_tahun_ajaran' => ['required', 'exists:tahun_ajaran,id_tahun_ajaran'],
            'bulan' => ['required', 'integer', 'between:1,12'],
            'tahun' => ['required', 'integer', 'min:2000', 'max:2100'],
            'nominal' => ['required', 'numeric', 'gt:0'],
            'tanggal_jatuh_tempo' => ['required', 'date'],
            'status' => ['required', Rule::in(['belum_bayar', 'lunas', 'tunggakan'])],
        ];
    }

    /**
     * Pesan validasi dalam bahasa Indonesia.
     */
    private function pesan(): array
    {
        return [
            'id_siswa.required' => 'Siswa wajib dipilih.',
            'id_siswa.exists' => 'Siswa yang dipilih tidak ditemukan.',
            'id_tarif.required' => 'Tarif wajib dipilih.',
            'id_tarif.exists' => 'Tarif yang dipilih tidak ditemukan.',
            'id_tahun_ajaran.required' => 'Tahun ajaran wajib dipilih.',
            'id_tahun_ajaran.exists' => 'Tahun ajaran yang dipilih tidak ditemukan.',
            'bulan.required' => 'Bulan wajib diisi.',
            'bulan.between' => 'Bulan harus antara 1 sampai 12.',
            'tahun.required' => 'Tahun wajib diisi.',
            'tahun.min' => 'Tahun tidak masuk akal, minimal 2000.',
            'tahun.max' => 'Tahun tidak masuk akal, maksimal 2100.',
            'nominal.required' => 'Nominal wajib diisi.',
            'nominal.numeric' => 'Nominal harus berupa angka.',
            'nominal.gt' => 'Nominal harus lebih besar dari nol.',
            'tanggal_jatuh_tempo.required' => 'Tanggal jatuh tempo wajib diisi.',
            'tanggal_jatuh_tempo.date' => 'Tanggal jatuh tempo harus berupa tanggal yang sah.',
            'status.in' => 'Status harus belum_bayar, lunas, atau tunggakan.',
        ];
    }

    /**
     * Data pilihan untuk form.
     */
    private function pilihan(): array
    {
        return [
            'daftar_siswa' => Siswa::with('kelas')->orderBy('nama')->get(),
            'daftar_tarif' => TarifSpp::with('tahunAjaran')->orderByDesc('id_tahun_ajaran')->get(),
            'daftar_tahun_ajaran' => TahunAjaran::orderByDesc('tahun_ajaran')->get(),
        ];
    }
}
