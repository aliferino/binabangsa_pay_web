@extends('layouts.app')

@section('judul', 'Dashboard Admin')
@section('keterangan', 'Ringkasan data sekolah')

@section('isi')

    @php
        // Angka dari controller. Semua nominal sudah float, jadi aman dijumlah.
        $lunas = (float) $totalPaid;
        $belum = (float) $totalOutstanding;

        // Bagian grafik: tinggi batang dihitung dari nominal terbesar,
        // supaya bulan dengan angka kecil tetap terlihat.
        $puncak = $monthlyChart->max(fn ($b) => $b['lunas'] + $b['belum']) ?: 1;

        $angka = [
            ['label' => 'Total siswa', 'nilai' => number_format($totalStudents, 0, ',', '.'), 'satuan' => 'siswa terdaftar'],
            ['label' => 'Total tagihan', 'nilai' => number_format($totalBills, 0, ',', '.'), 'satuan' => 'tagihan SPP'],
            ['label' => 'Siswa belum bayar', 'nilai' => number_format($totalUnpaidStudents, 0, ',', '.'), 'satuan' => 'punya tagihan terbuka'],
            ['label' => 'Transaksi hari ini', 'nilai' => number_format($todayTransactions, 0, ',', '.'), 'satuan' => 'pembayaran masuk'],
        ];

        $rupiah = fn ($n) => 'Rp'.number_format((float) $n, 0, ',', '.');
    @endphp

    <div class="space-y-5">

        {{-- Neraca tagihan: satu panel, bukan empat kartu kembar. --}}
        <section class="overflow-hidden rounded-xl border border-garis bg-white dark:border-garis-gelap dark:bg-gelap-2">

            <div class="grid gap-px bg-garis sm:grid-cols-[1.35fr_1fr] dark:bg-garis-gelap">

                <div class="bg-white px-5 py-5 dark:bg-gelap-2">
                    <p class="text-xs font-medium text-redup dark:text-redup-gelap">Belum dibayar</p>
                    <p class="mt-1.5 text-3xl font-bold tabular-nums tracking-tight text-tinta sm:text-4xl dark:text-teks-gelap">
                        {{ $rupiah($belum) }}
                    </p>
                    <p class="mt-1 text-xs leading-relaxed text-redup dark:text-redup-gelap">
                        Dari {{ $totalBills }} tagihan yang belum lunas. Tunggakan lewat jatuh tempo:
                        <span class="font-semibold text-tinta dark:text-teks-gelap">{{ $totalArrears }} tagihan</span>,
                        senilai {{ $rupiah($arrearsAmount) }}.
                    </p>

                    {{-- Porsi terbayar. Batang tipis, bukan donat. --}}
                    @php
                        $totalSemua = $lunas + $belum;
                        $persenLunas = $totalSemua > 0 ? round($lunas / $totalSemua * 100) : 0;
                    @endphp
                    <div class="mt-4">
                        <div class="flex h-1.5 overflow-hidden rounded-full bg-latar-2 dark:bg-gelap">
                            <div class="bg-neon" style="width: {{ $persenLunas }}%"></div>
                        </div>
                        <div class="mt-1.5 flex items-center justify-between text-[11px] text-redup dark:text-redup-gelap">
                            <span>Terbayar {{ $persenLunas }}%</span>
                            <span>{{ $rupiah($lunas) }} dari {{ $rupiah($totalSemua) }}</span>
                        </div>
                    </div>
                </div>

                <dl class="grid grid-cols-2 gap-px bg-garis dark:bg-garis-gelap">
                    @foreach ($angka as $item)
                        <div class="bg-white px-4 py-4 dark:bg-gelap-2">
                            <dt class="text-[11px] font-medium text-redup dark:text-redup-gelap">{{ $item['label'] }}</dt>
                            <dd class="mt-1 text-xl font-bold tabular-nums tracking-tight text-tinta dark:text-teks-gelap">{{ $item['nilai'] }}</dd>
                            <dd class="mt-0.5 text-[11px] leading-snug text-redup dark:text-redup-gelap">{{ $item['satuan'] }}</dd>
                        </div>
                    @endforeach
                </dl>

            </div>

        </section>

        {{-- Grafik tagihan per bulan. Batang disusun dari nominal, tanpa pustaka grafik. --}}
        <section class="rounded-xl border border-garis bg-white px-5 py-5 dark:border-garis-gelap dark:bg-gelap-2">
            <div class="flex flex-wrap items-baseline justify-between gap-2">
                <div>
                    <h2 class="text-sm font-semibold text-tinta dark:text-teks-gelap">Tagihan per bulan</h2>
                    <p class="mt-0.5 text-xs text-redup dark:text-redup-gelap">Nominal yang sudah dan belum masuk, dihitung dari tagihan SPP.</p>
                </div>
                <div class="flex items-center gap-3 text-[11px] text-redup dark:text-redup-gelap">
                    <span class="flex items-center gap-1.5">
                        <span class="h-2 w-2 rounded-sm bg-neon"></span> Lunas
                    </span>
                    <span class="flex items-center gap-1.5">
                        <span class="h-2 w-2 rounded-sm bg-redup/30 dark:bg-teks-gelap/30"></span> Belum
                    </span>
                </div>
            </div>

            @if ($monthlyChart->isEmpty())
                <p class="mt-6 text-sm text-redup dark:text-redup-gelap">Belum ada tagihan yang bisa digambar.</p>
            @else
                <div class="mt-5 flex items-end gap-3 sm:gap-5">
                    @foreach ($monthlyChart as $batang)
                        @php
                            $jumlah = $batang['lunas'] + $batang['belum'];
                            $tinggi = max(6, (int) round($jumlah / $puncak * 132));
                            $bagianLunas = $jumlah > 0 ? round($batang['lunas'] / $jumlah * 100) : 0;
                        @endphp
                        <div class="flex min-w-0 flex-1 flex-col items-center gap-2">
                            <span class="text-[11px] font-medium tabular-nums text-redup dark:text-redup-gelap">
                                Rp{{ number_format($jumlah / 1000, 0, ',', '.') }}rb
                            </span>

                            {{-- Batang bertumpuk, diisi dari DASAR seperti air: bagian
                                 yang sudah lunas (hijau) menempel di bawah, sisanya abu
                                 di atas. Latar wadah sudah abu, jadi bagian belum bayar
                                 tidak perlu elemen sendiri. --}}
                            <div class="flex w-full max-w-14 flex-col justify-end overflow-hidden rounded-md bg-redup/25 dark:bg-teks-gelap/25"
                                 style="height: {{ $tinggi }}px"
                                 title="{{ $batang['nama'] }} {{ $batang['tahun'] }}: {{ $rupiah($jumlah) }} (lunas {{ $bagianLunas }}%)">
                                @if ($bagianLunas > 0)
                                    <div class="w-full bg-neon" style="height: {{ $bagianLunas }}%"></div>
                                @endif
                            </div>

                            <span class="w-full truncate border-t border-garis pt-1.5 text-center text-[11px] text-redup dark:border-garis-gelap dark:text-redup-gelap">
                                {{ $batang['nama'] }} {{ substr($batang['tahun'], 2) }}
                            </span>
                        </div>
                    @endforeach
                </div>
            @endif
        </section>

        <div class="grid items-start gap-5 lg:grid-cols-[1.4fr_1fr]">

            {{-- Transaksi terbaru. Kosong berarti memang belum ada pembayaran. --}}
            <section class="rounded-xl border border-garis bg-white dark:border-garis-gelap dark:bg-gelap-2">
                <div class="flex items-baseline justify-between gap-2 border-b border-garis px-5 py-3.5 dark:border-garis-gelap">
                    <h2 class="text-sm font-semibold text-tinta dark:text-teks-gelap">Transaksi terbaru</h2>
                    <span class="text-[11px] text-redup dark:text-redup-gelap">
                        {{ number_format($paymentCount, 0, ',', '.') }} pembayaran tercatat
                    </span>
                </div>

                @if ($recentPayments->isEmpty())
                    <p class="px-5 py-6 text-sm leading-relaxed text-redup dark:text-redup-gelap">
                        Belum ada pembayaran. Tabel pembayaran dan virtual account sudah siap, tapi
                        datanya baru terisi setelah tagihan dibayar lewat virtual account.
                    </p>
                @else
                    <ul class="divide-y divide-garis dark:divide-garis-gelap">
                        @foreach ($recentPayments as $bayar)
                            <li class="flex items-center justify-between gap-3 px-5 py-3">
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-medium text-tinta dark:text-teks-gelap">
                                        {{ $bayar->student?->nama ?? 'Siswa terhapus' }}
                                    </p>
                                    <p class="truncate text-[11px] text-redup dark:text-redup-gelap">
                                        {{ $bayar->nomor_transaksi }} &middot; {{ strtoupper($bayar->metode_pembayaran) }}
                                    </p>
                                </div>
                                <p class="shrink-0 text-sm font-semibold tabular-nums text-tinta dark:text-teks-gelap">
                                    {{ $rupiah($bayar->nominal_pembayaran) }}
                                </p>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </section>

            {{-- Jalan pintas, dibuat seperti daftar supaya tidak jadi deretan tombol kembar. --}}
            <section class="rounded-xl border border-garis bg-white dark:border-garis-gelap dark:bg-gelap-2">
                <h2 class="border-b border-garis px-5 py-3.5 text-sm font-semibold text-tinta dark:border-garis-gelap dark:text-teks-gelap">
                    Jalan pintas
                </h2>
                <ul class="divide-y divide-garis dark:divide-garis-gelap">
                    @foreach ([
                        ['rute' => 'siswa.index', 'teks' => 'Data siswa', 'ket' => 'Daftar dan tambah siswa'],
                        ['rute' => 'tagihan-spp.index', 'teks' => 'Tagihan SPP', 'ket' => 'Buat dan periksa tagihan'],
                        ['rute' => 'laporan.tunggakan', 'teks' => 'Laporan tunggakan', 'ket' => 'Tagihan lewat jatuh tempo'],
                        ['rute' => 'user.index', 'teks' => 'Pengguna', 'ket' => 'Akun admin, teller, siswa'],
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

        </div>

    </div>

@endsection