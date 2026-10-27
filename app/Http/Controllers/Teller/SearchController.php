<?php

namespace App\Http\Controllers\Teller;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\TuitionBill;
use Illuminate\Http\Request;

/**
 * Pencarian siswa untuk teller.
 *
 * Dipakai teller untuk mencari siswa sebelum mencatat pembayaran. Bisa
 * mencari berdasarkan NIS, NISN, nama, nomor kelas, nomor Virtual Account,
 * atau transaction ID payment gateway.
 */
class SearchController extends Controller
{
    /**
     * Cari siswa, lalu tampilkan status tagihan per bulan.
     */
    public function index(Request $request)
    {
        $kata = trim((string) $request->input('q', ''));
        $hasil = collect();

        if ($kata !== '') {
            $hasil = $this->search($kata)->map(fn (Student $siswa) => [
                'nis' => $siswa->nis,
                'nisn' => $siswa->nisn,
                'nama' => $siswa->nama,
                'kelas' => $siswa->kelas?->nama_kelas,
                'jurusan' => $siswa->kelas?->jurusan?->nama_jurusan,
                'no_va' => $siswa->virtualAccount?->nomor_va,
                'status_tagihan' => $this->summarizeBillStatus($siswa),
                'status_per_bulan' => $this->monthlyStatus($siswa),
            ]);
        }

        return $this->render('teller.search.index', [
            'keyword' => $kata,
            'results' => $hasil,
        ]);
    }

    /**
     * Cari siswa lewat identitas siswa, nomor VA, atau transaction ID.
     */
    private function search(string $kata)
    {
        return Student::with('kelas.jurusan', 'tagihan', 'virtualAccount')
            ->where(function ($q) use ($kata) {
                $q->where('nis', 'like', '%'.$kata.'%')
                    ->orWhere('nisn', 'like', '%'.$kata.'%')
                    ->orWhere('nama', 'like', '%'.$kata.'%')
                    ->orWhereHas('kelas', fn ($sub) => $sub->where('nama_kelas', 'like', '%'.$kata.'%'))
                    ->orWhereHas('virtualAccount', function ($sub) use ($kata) {
                        $sub->where('nomor_va', 'like', '%'.$kata.'%')
                            ->orWhere('account_number', 'like', '%'.$kata.'%');
                    })
                    ->orWhereHas('paymentGatewayTransactions', function ($sub) use ($kata) {
                        $sub->where('transaction_id', 'like', '%'.$kata.'%')
                            ->orWhere('xendit_payment_id', 'like', '%'.$kata.'%');
                    });
            })
            ->orderBy('nama')
            ->get();
    }

    /**
     * Status tagihan per bulan untuk satu siswa.
     *
     * Bentuk array disepakati dengan tampilan pencarian: bulan, tahun,
     * nominal, status, dan jatuh tempo. status memakai tunggakan kalau
     * tagihan terbuka sudah lewat jatuh tempo.
     */
    private function monthlyStatus(Student $siswa): array
    {
        return $siswa->tagihan
            ->sortBy([['tahun', 'asc'], ['bulan', 'asc']])
            ->map(function (TuitionBill $tagihan) {
                $overdue = $tagihan->status !== 'lunas' && $tagihan->tanggal_jatuh_tempo?->isPast();

                return [
                    'bulan' => $tagihan->bulan,
                    'tahun' => $tagihan->tahun,
                    'nama_bulan' => $tagihan->namaBulan(),
                    'nominal' => (float) $tagihan->nominal,
                    'status' => $overdue ? 'tunggakan' : $tagihan->status,
                    'jatuh_tempo' => $tagihan->tanggal_jatuh_tempo?->format('Y-m-d'),
                ];
            })
            ->values()
            ->all();
    }

    /**
     * Ringkas status tagihan menjadi satu keterangan pendek.
     */
    private function summarizeBillStatus(Student $siswa): string
    {
        $tagihan = $siswa->tagihan;

        if ($tagihan->isEmpty()) {
            return 'belum ada tagihan';
        }

        if ($tagihan->contains(fn ($t) => $t->status !== 'lunas' && $t->tanggal_jatuh_tempo?->isPast())) {
            return 'ada tunggakan';
        }

        if ($tagihan->every(fn ($t) => $t->status === 'lunas')) {
            return 'semua lunas';
        }

        return 'sebagian belum bayar';
    }
}