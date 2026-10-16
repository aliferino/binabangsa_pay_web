@extends('layouts.app')

@section('judul', 'Pencarian Siswa')
@section('keterangan', 'Cari siswa berdasarkan NIS, NISN, nama, atau kelas')

@section('isi')

    {{--
        Catatan bentuk data.
        Controller mengirim $hasil sebagai kumpulan array dengan kunci nis, nisn,
        nama, kelas, jurusan, dan status_tagihan. Nilai dibaca lewat data_get
        supaya tetap aman kalau bentuknya berubah.
    --}}

    {{-- Form pencarian. --}}
    <form method="GET" action="{{ route('pencarian.index') }}"
          class="mb-4 rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-end">
            <div class="flex-1">
                <label for="q" class="mb-1 block text-xs font-medium text-slate-600">Kata kunci</label>
                <input type="text" name="q" id="q" value="{{ $kata_kunci }}"
                       placeholder="Contoh: 12345 atau Ahmad"
                       class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-teal-600 focus:outline-none focus:ring-2 focus:ring-teal-100">
            </div>
            <div class="flex gap-2">
                <button type="submit"
                        class="rounded-lg bg-teal-700 px-4 py-2 text-sm font-semibold text-white hover:bg-teal-800">
                    Cari
                </button>
                <a href="{{ route('pencarian.index') }}"
                   class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                    Reset
                </a>
            </div>
        </div>
    </form>

    {{-- Kata kunci masih kosong: tampilkan petunjuk, bukan tabel kosong. --}}
    @if ($kata_kunci === '')
        <div class="rounded-xl border border-dashed border-slate-300 bg-white p-8 text-center">
            <h2 class="text-sm font-semibold text-slate-700">Masukkan kata kunci</h2>
            <p class="mx-auto mt-2 max-w-md text-sm text-slate-500">
                Pencarian bisa memakai NIS, NISN, nama siswa, atau nama kelas.
                Hasilnya menampilkan NIS, nama, kelas, dan ringkasan status tagihannya.
            </p>
        </div>
    @else
        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 px-5 py-4">
                <h2 class="text-sm font-semibold text-slate-700">
                    Hasil pencarian untuk "{{ $kata_kunci }}"
                </h2>
                <p class="mt-1 text-xs text-slate-500">{{ $hasil->count() }} siswa ditemukan.</p>
            </div>

            @if ($hasil->isEmpty())
                <p class="px-5 py-8 text-center text-sm text-slate-500">
                    Tidak ada siswa yang cocok dengan kata kunci itu.
                </p>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                            <tr>
                                <th class="px-5 py-3 font-semibold">NIS</th>
                                <th class="px-5 py-3 font-semibold">NISN</th>
                                <th class="px-5 py-3 font-semibold">Nama</th>
                                <th class="px-5 py-3 font-semibold">Kelas</th>
                                <th class="px-5 py-3 font-semibold">Jurusan</th>
                                <th class="px-5 py-3 font-semibold">Status tagihan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($hasil as $baris)
                                @php
                                    $status = (string) data_get($baris, 'status_tagihan', '');

                                    // Warna mengikuti isi ringkasan status dari controller.
                                    $kelasStatus = match (true) {
                                        $status === 'semua lunas' => 'bg-emerald-100 text-emerald-800',
                                        $status === 'ada tunggakan' => 'bg-red-100 text-red-800',
                                        $status === 'sebagian belum bayar' => 'bg-amber-100 text-amber-800',
                                        default => 'bg-slate-100 text-slate-700',
                                    };
                                @endphp
                                <tr class="hover:bg-slate-50">
                                    <td class="px-5 py-3 font-medium text-slate-800">{{ data_get($baris, 'nis') }}</td>
                                    <td class="px-5 py-3 text-slate-600">{{ data_get($baris, 'nisn') }}</td>
                                    <td class="px-5 py-3 text-slate-800">{{ data_get($baris, 'nama') }}</td>
                                    <td class="px-5 py-3 text-slate-600">{{ data_get($baris, 'kelas') ?? '-' }}</td>
                                    <td class="px-5 py-3 text-slate-600">{{ data_get($baris, 'jurusan') ?? '-' }}</td>
                                    <td class="px-5 py-3">
                                        <span class="inline-flex whitespace-nowrap rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $kelasStatus }}">
                                            {{ ucfirst($status) }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    @endif

@endsection
