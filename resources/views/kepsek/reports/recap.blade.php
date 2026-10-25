@extends('layouts.app')

@section('judul', 'Rekap Pembayaran')
@section('keterangan', 'Uang masuk SPP per bulan dan per kelas')

@section('isi')

    {{-- Form filter tanggal. --}}
    <form method="GET" action="{{ route('kepsek.reports.recap') }}"
          class="mb-4 rounded-xl border border-garis dark:border-garis-gelap bg-white dark:bg-gelap-2 p-4 shadow-sm">
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">

            <div>
                <label for="dari" class="mb-1 block text-xs font-medium text-redup dark:text-redup-gelap">Tanggal dari</label>
                <input type="date" name="dari" id="dari" value="{{ $filter['dari'] ?? '' }}"
                       class="w-full rounded-lg border border-garis bg-white px-3 py-2 text-sm text-tinta shadow-sm focus:border-neon-teks focus:outline-none focus:ring-2 focus:ring-neon/40 dark:border-redup dark:bg-gelap dark:text-teks-gelap">
            </div>

            <div>
                <label for="sampai" class="mb-1 block text-xs font-medium text-redup dark:text-redup-gelap">Tanggal sampai</label>
                <input type="date" name="sampai" id="sampai" value="{{ $filter['sampai'] ?? '' }}"
                       class="w-full rounded-lg border border-garis bg-white px-3 py-2 text-sm text-tinta shadow-sm focus:border-neon-teks focus:outline-none focus:ring-2 focus:ring-neon/40 dark:border-redup dark:bg-gelap dark:text-teks-gelap">
            </div>

            <div class="flex items-end gap-2 sm:col-span-2">
                <button type="submit"
                        class="rounded-lg bg-neon px-4 py-2 text-sm font-semibold text-tinta hover:bg-neon-tekan tombol-tekan">
                    Terapkan
                </button>
                <a href="{{ route('kepsek.reports.recap') }}"
                   class="rounded-lg border border-garis dark:border-garis-gelap bg-white dark:bg-gelap-2 px-4 py-2 text-sm font-medium text-tinta dark:text-teks-gelap hover:bg-latar-2 dark:hover:bg-gelap">
                    Reset
                </a>
            </div>
        </div>
    </form>

    {{-- Total uang masuk. --}}
    <div class="mb-5 grid grid-cols-1 gap-4 sm:grid-cols-2">
        <div class="rounded-xl border border-garis dark:border-garis-gelap bg-white dark:bg-gelap-2 p-5 shadow-sm">
            <p class="text-sm text-redup dark:text-redup-gelap">Total uang masuk</p>
            <p class="mt-1 text-2xl font-bold text-neon-teks dark:text-neon">
                Rp{{ number_format((float) $grandTotal, 0, ',', '.') }}
            </p>
            <p class="mt-1 text-xs text-redup dark:text-redup-gelap">Dari tabel pembayaran berstatus berhasil</p>
        </div>

        <div class="rounded-xl border border-garis dark:border-garis-gelap bg-white dark:bg-gelap-2 p-5 shadow-sm">
            <p class="text-sm text-redup dark:text-redup-gelap">Jumlah transaksi</p>
            <p class="mt-1 text-2xl font-bold text-tinta dark:text-teks-gelap">{{ $transactionCount }}</p>
            <p class="mt-1 text-xs text-redup dark:text-redup-gelap">Pembayaran berhasil pada filter ini</p>
        </div>
    </div>

    <div class="space-y-5">

        {{-- Rekap per bulan. --}}
        <div class="overflow-hidden rounded-xl border border-garis dark:border-garis-gelap bg-white dark:bg-gelap-2 shadow-sm">
            <div class="border-b border-garis dark:border-garis-gelap px-5 py-4">
                <h2 class="text-sm font-semibold text-tinta dark:text-teks-gelap">Rekap per bulan</h2>
            </div>

            @if ($byMonth->isEmpty())
                <p class="px-5 py-6 text-sm text-redup dark:text-redup-gelap">Belum ada pembayaran pada filter ini.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-garis dark:divide-garis-gelap text-sm">
                        <thead class="bg-latar-2 dark:bg-gelap text-left text-xs uppercase tracking-wide text-redup dark:text-redup-gelap">
                            <tr>
                                <th class="px-5 py-3 font-semibold">Periode</th>
                                <th class="px-5 py-3 font-semibold">Transaksi</th>
                                <th class="px-5 py-3 text-right font-semibold">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-garis dark:divide-garis-gelap">
                            @foreach ($byMonth as $row)
                                <tr class="hover:bg-latar-2 dark:hover:bg-gelap">
                                    <td class="px-5 py-3 font-medium text-tinta dark:text-teks-gelap">{{ data_get($row, 'label') }}</td>
                                    <td class="px-5 py-3 text-redup dark:text-redup-gelap">{{ data_get($row, 'transaksi') }}</td>
                                    <td class="px-5 py-3 text-right font-mono text-tinta dark:text-teks-gelap">
                                        Rp{{ number_format((float) data_get($row, 'total', 0), 0, ',', '.') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        {{-- Rekap per kelas. --}}
        <div class="overflow-hidden rounded-xl border border-garis dark:border-garis-gelap bg-white dark:bg-gelap-2 shadow-sm">
            <div class="border-b border-garis dark:border-garis-gelap px-5 py-4">
                <h2 class="text-sm font-semibold text-tinta dark:text-teks-gelap">Rekap per kelas</h2>
            </div>

            @if ($byClassroom->isEmpty())
                <p class="px-5 py-6 text-sm text-redup dark:text-redup-gelap">Belum ada pembayaran pada filter ini.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-garis dark:divide-garis-gelap text-sm">
                        <thead class="bg-latar-2 dark:bg-gelap text-left text-xs uppercase tracking-wide text-redup dark:text-redup-gelap">
                            <tr>
                                <th class="px-5 py-3 font-semibold">Kelas</th>
                                <th class="px-5 py-3 font-semibold">Transaksi</th>
                                <th class="px-5 py-3 text-right font-semibold">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-garis dark:divide-garis-gelap">
                            @foreach ($byClassroom as $row)
                                <tr class="hover:bg-latar-2 dark:hover:bg-gelap">
                                    <td class="px-5 py-3 font-medium text-tinta dark:text-teks-gelap">{{ data_get($row, 'kelas') }}</td>
                                    <td class="px-5 py-3 text-redup dark:text-redup-gelap">{{ data_get($row, 'transaksi') }}</td>
                                    <td class="px-5 py-3 text-right font-mono text-tinta dark:text-teks-gelap">
                                        Rp{{ number_format((float) data_get($row, 'total', 0), 0, ',', '.') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

@endsection