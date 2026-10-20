<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    /**
     * Pencarian siswa untuk admin dan teller.
     *
     * Bisa dicari berdasarkan NIS, NISN, nama, atau nomor kelas. Hasilnya
     * menampilkan NIS, nama, kelas, dan status tagihannya.
     */
    public function index(Request $request)
    {
        $kata = trim((string) $request->input('q', ''));
        $hasil = collect();

        if ($kata !== '') {
            $hasil = Student::with('kelas.jurusan', 'tagihan')
                ->where(function ($q) use ($kata) {
                    $q->where('nis', 'like', '%'.$kata.'%')
                        ->orWhere('nisn', 'like', '%'.$kata.'%')
                        ->orWhere('nama', 'like', '%'.$kata.'%')
                        ->orWhereHas('kelas', function ($sub) use ($kata) {
                            $sub->where('nama_kelas', 'like', '%'.$kata.'%');
                        });
                })
                ->orderBy('nama')
                ->get()
                ->map(fn (Student $siswa) => [
                    'nis' => $siswa->nis,
                    'nisn' => $siswa->nisn,
                    'nama' => $siswa->nama,
                    'kelas' => $siswa->kelas?->nama_kelas,
                    'jurusan' => $siswa->kelas?->jurusan?->nama_jurusan,
                    'status_tagihan' => $this->summarizeBillStatus($siswa),
                ]);
        }

        return $this->render('admin.search.index', [
            'keyword' => $kata,
            'hasil' => $hasil,
        ]);
    }

    /**
     * Ringkas status tagihan seorang siswa menjadi satu keterangan pendek.
     *
     * Dipakai di hasil pencarian supaya admin dan teller langsung tahu
     * posisi siswa tanpa membuka satu per satu.
     */
    private function summarizeBillStatus(Student $siswa): string
    {
        $tagihan = $siswa->tagihan;

        if ($tagihan->isEmpty()) {
            return 'belum ada tagihan';
        }

        if ($tagihan->contains('status', 'tunggakan')) {
            return 'ada tunggakan';
        }

        if ($tagihan->every(fn ($t) => $t->status === 'lunas')) {
            return 'semua lunas';
        }

        return 'sebagian belum bayar';
    }

    /**
     * Pencarian berdasarkan nomor Virtual Account.
     *
     * BELUM DIPAKAI. Tabel virtual_account belum dibuat, karena bagian
     * pembayaran masih ditanyakan ke guru. Kode ini disiapkan supaya nanti
     * tinggal dihubungkan setelah tabelnya ada. Jangan dipanggil dari route
     * sebelum tabel virtual_account tersedia.
     */
    private function searchByVirtualAccount(string $nomorVa)
    {
        // Contoh pemakaian setelah tabel virtual_account ada:
        //
        // return Student::whereHas('virtualAccount', function ($q) use ($nomorVa) {
        //     $q->where('nomor_va', $nomorVa);
        // })->get();
        //
        // Untuk sekarang sengaja mengembalikan koleksi kosong supaya tidak
        // memanggil relasi yang belum ada.
        return collect();
    }
}
