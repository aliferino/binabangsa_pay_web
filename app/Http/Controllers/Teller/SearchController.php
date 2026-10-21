<?php

namespace App\Http\Controllers\Teller;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Illuminate\Http\Request;

/**
 * Pencarian siswa untuk teller.
 *
 * Dipakai teller untuk mencari siswa sebelum mencatat pembayaran. Bisa
 * mencari berdasarkan NIS, NISN, nama, atau nomor kelas. Hasilnya
 * menampilkan NIS, nama, kelas, dan status tagihannya.
 */
class SearchController extends Controller
{
    /**
     * Cari siswa berdasarkan NIS, NISN, nama, atau nomor kelas.
     *
     * Kolom hasil: NIS, nama, kelas, status tagihan.
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

        return $this->render('teller.search.index', [
            'keyword' => $kata,
            'results' => $hasil,
        ]);
    }

    /**
     * Ringkas status tagihan seorang siswa menjadi satu keterangan pendek.
     *
     * Dipakai di hasil pencarian supaya teller langsung tahu posisi
     * siswa tanpa membuka satu per satu.
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
}
