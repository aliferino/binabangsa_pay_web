@extends('layouts.app')

@section('judul', 'Pencarian Siswa')
@section('keterangan', 'Cari siswa berdasarkan NIS, NISN, nama, kelas, nomor Virtual Account, atau ID transaksi')

@section('isi')

    {{-- Form pencarian. --}}
    <form method="GET" action="{{ url()->current() }}"
          class="mb-5 rounded-xl border border-garis bg-white p-4 shadow-sm dark:border-garis-gelap dark:bg-gelap-2">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-end">
            <div class="flex-1">
                <label for="q" class="mb-1.5 block text-xs font-medium text-redup dark:text-redup-gelap">Kata kunci</label>
                <input type="text" name="q" id="q" value="{{ $keyword }}"
                       placeholder="Contoh: 12345, Ahmad, 7770 1001 3333, atau ID transaksi"
                       class="w-full rounded-lg border border-garis px-3 py-2 text-sm text-tinta shadow-sm focus:border-neon-teks focus:outline-none focus:ring-2 focus:ring-neon/40 dark:border-redup dark:bg-gelap dark:text-teks-gelap dark:placeholder:text-redup-gelap">
            </div>
            <div class="flex gap-2">
                <button type="submit"
                        class="border border-transparent tombol-tekan rounded-lg bg-neon px-4 py-2 text-sm font-semibold text-tinta hover:bg-neon-tekan">
                    Cari
                </button>
                <a href="{{ url()->current() }}"
                   class="rounded-lg border border-garis bg-white px-4 py-2 text-sm font-medium text-tinta hover:bg-latar-2 dark:border-garis-gelap dark:bg-gelap-2 dark:text-teks-gelap dark:hover:bg-gelap">
                    Reset
                </a>
            </div>
        </div>
    </form>

    {{-- Kata kunci masih kosong: tampilkan petunjuk, bukan tabel kosong. --}}
    @if ($keyword === '')
        <div class="rounded-xl border border-dashed border-garis bg-white p-8 text-center dark:border-garis-gelap dark:bg-gelap-2">
            <h2 class="text-sm font-semibold text-tinta dark:text-teks-gelap">Masukkan kata kunci</h2>
            <p class="mx-auto mt-2 max-w-md text-sm text-redup dark:text-redup-gelap">
                Pencarian bisa memakai NIS, NISN, nama siswa, nama kelas, nomor Virtual Account, atau ID transaksi gateway.
                Hasilnya menampilkan status tagihan tiap bulan.
            </p>
        </div>
    @else
        <div class="mb-4">
            <h2 class="text-sm font-semibold text-tinta dark:text-teks-gelap">
                Hasil pencarian untuk "{{ $keyword }}"
            </h2>
            <p class="mt-1 text-xs text-redup dark:text-redup-gelap">{{ $results->count() }} siswa ditemukan.</p>
        </div>

        @include('layouts.partials._search_results', ['results' => $results, 'keyword' => $keyword])
    @endif

@endsection