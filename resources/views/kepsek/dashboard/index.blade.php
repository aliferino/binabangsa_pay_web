@extends('layouts.app')

@section('judul', 'Dashboard Kepala Sekolah')
@section('keterangan', 'Rekap kondisi pembayaran SPP')

@section('isi')

    @php
        $rupiah = fn ($n) => 'Rp'.number_format((float) $n, 0, ',', '.');

        $totalSiswa = max(1, (int) $totalStudents);
        $persenLunas = round($paidStudents / $totalSiswa * 100);
        $persenBelum = round($unpaidStudents / $totalSiswa * 100);
        $persenMenunggak = round($overdueStudents / $totalSiswa * 100);

        $puncak = $monthlyIncome->max('nominal') ?: 1;
    @endphp

    <div class="space-y-5">

        {{-- Keadaan siswa: satu panel dengan batang porsi, bukan empat kartu kembar. --}}
        <section class="rounded-xl border border-garis bg-white dark:border-garis-gelap dark:bg-gelap-2">
            <div class="grid gap-px bg-garis sm:grid-cols-[1.35fr_1fr] dark:bg-garis-gelap">

                <div class="bg-white px-5 py-5 dark:bg-gelap-2">
                    <p class="text-xs font-medium text-redup dark:text-redup-gelap">Siswa aktif</p>
                    <p class="mt-1.5 text-3xl font-bold tabular-nums tracking-tight text-tinta sm:text-4xl dark:text-teks-gelap">
                        {{ number_format($activeStudents, 0, ',', '.') }}
                        <span class="text-sm font-medium text-redup dark:text-redup-gelap">dari {{ number_format($totalStudents, 0, ',', '.') }} terdaftar</span>
                    </p>

                    <p class="mt-3 text-[11px] text-redup dark:text-redup-gelap">
                        Dihitung per siswa, bukan per tagihan. Satu siswa bisa muncul di lebih dari satu baris.
                    </p>

                    <div class="mt-3 space-y-2.5">
                        @foreach ([
                            ['Punya tagihan lunas', $paidStudents, $persenLunas, 'bg-neon'],
                            ['Punya tagihan belum bayar', $unpaidStudents, $persenBelum, 'bg-amber-400'],
                            ['Punya tagihan menunggak', $overdueStudents, $persenMenunggak, 'bg-red-500'],
                        ] as [$label, $jumlah, $persen, $warna])
                            <div>
                                <div class="flex items-baseline justify-between text-[11px] text-redup dark:text-redup-gelap">
                                    <span>{{ $label }}</span>
                                    <span class="tabular-nums">{{ $jumlah }} siswa &middot; {{ $persen }}%</span>
                                </div>
                                <div class="mt-1 h-1.5 overflow-hidden rounded-full bg-latar-2 dark:bg-gelap">
                                    <div class="{{ $warna }}" style="width: {{ $persen }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="grid grid-rows-2 gap-px bg-garis dark:bg-garis-gelap">
                    <div class="bg-white px-5 py-4 dark:bg-gelap-2">
                        <p class="text-[11px] font-medium text-redup dark:text-redup-gelap">Penerimaan SPP</p>
                        <p class="mt-1 text-2xl font-bold tabular-nums tracking-tight text-tinta dark:text-teks-gelap">{{ $rupiah($totalPaid) }}</p>
                        <p class="mt-0.5 text-[11px] text-redup dark:text-redup-gelap">Dari seluruh pembayaran berhasil</p>
                    </div>
                    <div class="bg-white px-5 py-4 dark:bg-gelap-2">
                        <p class="text-[11px] font-medium text-redup dark:text-redup-gelap">Belum masuk</p>
                        <p class="mt-1 text-2xl font-bold tabular-nums tracking-tight text-tinta dark:text-teks-gelap">{{ $rupiah($totalOutstanding) }}</p>
                        <p class="mt-0.5 text-[11px] leading-snug text-redup dark:text-redup-gelap">
                            Dari {{ $totalBills }} tagihan belum lunas.
                            Yang sudah lewat jatuh tempo: {{ $rupiah($totalArrears) }}.
                        </p>
                    </div>
                </div>

            </div>
        </section>

        {{-- Penerimaan per bulan. Kosong berarti belum ada pembayaran masuk. --}}
        <section class="rounded-xl border border-garis bg-white px-5 py-5 dark:border-garis-gelap dark:bg-gelap-2">
            <div class="flex flex-wrap items-baseline justify-between gap-2">
                <div>
                    <h2 class="text-sm font-semibold text-tinta dark:text-teks-gelap">Penerimaan per bulan</h2>
                    <p class="mt-0.5 text-xs text-redup dark:text-redup-gelap">Nominal pembayaran berhasil yang masuk tiap bulan.</p>
                </div>
            </div>

            @if ($monthlyIncome->isEmpty())
                <p class="py-6 text-sm leading-relaxed text-redup dark:text-redup-gelap">
                    Belum ada pembayaran masuk. Grafik terisi setelah siswa membayar lewat virtual account
                    atau teller mencatat pembayaran.
                </p>
            @else
                <div class="mt-5 flex items-end gap-3 sm:gap-5">
                    @foreach ($monthlyIncome as $batang)
                        @php $tinggi = max(6, (int) round($batang['nominal'] / $puncak * 132)); @endphp
                        <div class="flex min-w-0 flex-1 flex-col items-center gap-2">
                            <span class="text-[11px] font-medium tabular-nums text-redup dark:text-redup-gelap">
                                Rp{{ number_format($batang['nominal'] / 1000, 0, ',', '.') }}rb
                            </span>
                            <div class="w-full max-w-14 rounded-md bg-neon" style="height: {{ $tinggi }}px"
                                 title="{{ $batang['nama'] }} {{ $batang['tahun'] }}: {{ $rupiah($batang['nominal']) }}"></div>
                            <span class="w-full truncate border-t border-garis pt-1.5 text-center text-[11px] text-redup dark:border-garis-gelap dark:text-redup-gelap">
                                {{ $batang['nama'] }} {{ substr($batang['tahun'], 2) }}
                            </span>
                        </div>
                    @endforeach
                </div>
            @endif
        </section>

        <div class="grid items-start gap-5 lg:grid-cols-2">

            {{-- Laporan. --}}
            <section class="rounded-xl border border-garis bg-white dark:border-garis-gelap dark:bg-gelap-2">
                <h2 class="border-b border-garis px-5 py-3.5 text-sm font-semibold text-tinta dark:border-garis-gelap dark:text-teks-gelap">Laporan</h2>
                <ul class="divide-y divide-garis dark:divide-garis-gelap">
                    @foreach ([
                        ['rute' => 'laporan.pembayaran', 'teks' => 'Laporan pembayaran', 'ket' => 'Uang masuk per periode'],
                        ['rute' => 'laporan.tunggakan', 'teks' => 'Laporan tunggakan', 'ket' => 'Tagihan lewat jatuh tempo'],
                        ['rute' => 'siswa.index', 'teks' => 'Data siswa', 'ket' => 'Daftar siswa dan kelasnya'],
                    ] as $pintasan)
                        <li>
                            <a href="{{ route($pintasan['rute']) }}"
                               class="group flex items-center justify-between gap-3 px-5 py-3 transition-colors duration-150 hover:bg-latar-2 dark:hover:bg-gelap">
                                <span class="min-w-0">
                                    <span class="block truncate text-sm font-medium text-tinta dark:text-teks-gelap">{{ $pintasan['teks'] }}</span>
                                    <span class="block truncate text-[11px] text-redup dark:text-redup-gelap">{{ $pintasan['ket'] }}</span>
                                </span>
                                <svg class="h-4 w-4 shrink-0 text-redup transition-transform duration-150 group-hover:translate-x-0.5 dark:text-redup-gelap"
                                     viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true">
                                    <path d="M7.5 4.5 13 10l-5.5 5.5" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </section>

            {{-- Catatan cakupan data. Jujur soal apa yang belum ada. --}}
            <section class="rounded-xl border border-garis bg-white px-5 py-5 dark:border-garis-gelap dark:bg-gelap-2">
                <h2 class="text-sm font-semibold text-tinta dark:text-teks-gelap">Cakupan angka</h2>
                <dl class="mt-3 space-y-2.5 text-xs leading-relaxed">
                    @foreach ([
                        ['Sudah dihitung', 'Jumlah siswa per status, nominal tagihan, penerimaan dari pembayaran berhasil.'],
                        ['Belum dihitung', 'Tunggakan per kelas dan per jurusan, karena belum ada laporan rekap per unit.'],
                        ['Sumber', 'Tabel siswa, tagihan_spp, dan pembayaran. Virtual account belum memengaruhi angka mana pun.'],
                    ] as [$judul, $isi])
                        <div>
                            <dt class="font-medium text-tinta dark:text-teks-gelap">{{ $judul }}</dt>
                            <dd class="mt-0.5 text-redup dark:text-redup-gelap">{{ $isi }}</dd>
                        </div>
                    @endforeach
                </dl>
            </section>

        </div>

    </div>

@endsection