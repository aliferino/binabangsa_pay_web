@extends('layouts.app')

@section('judul', 'Dashboard Teller')
@section('keterangan', 'Layanan pembayaran SPP harian')

@section('isi')

    @php
        $rupiah = fn ($n) => 'Rp'.number_format((float) $n, 0, ',', '.');
    @endphp

    <div class="space-y-5">

        {{-- Angka hari ini: uang masuk dulu, baru jumlah transaksi. --}}
        <section class="rounded-xl border border-garis bg-white dark:border-garis-gelap dark:bg-gelap-2">
            <div class="grid gap-px bg-garis sm:grid-cols-[1.35fr_1fr] dark:bg-garis-gelap">

                <div class="bg-white px-5 py-5 dark:bg-gelap-2">
                    <p class="text-xs font-medium text-redup dark:text-redup-gelap">Uang masuk hari ini</p>
                    <p class="mt-1.5 text-3xl font-bold tabular-nums tracking-tight text-tinta sm:text-4xl dark:text-teks-gelap">
                        {{ $rupiah($todayAmount) }}
                    </p>
                    <p class="mt-1 text-xs leading-relaxed text-redup dark:text-redup-gelap">
                        Dari {{ $todayPayments }} pembayaran berhasil
                        @if ($todayTransactions > 0)
                            dan {{ $todayTransactions }} transaksi gateway
                        @endif
                        hari ini.
                    </p>
                </div>

                <dl class="grid grid-cols-2 gap-px bg-garis dark:bg-garis-gelap">
                    @foreach ([
                        ['label' => 'Jatuh tempo hari ini', 'nilai' => $billsDueToday],
                        ['label' => 'Siswa belum bayar', 'nilai' => $unpaidStudents, 'satuan' => 'siswa'],
                        ['label' => 'Tagihan menunggak', 'nilai' => $overdueBills, 'satuan' => 'tagihan'],
                        ['label' => 'Transaksi hari ini', 'nilai' => $todayTransactions],
                    ] as $item)
                        <div class="bg-white px-4 py-4 dark:bg-gelap-2">
                                                    <dt class="text-[11px] font-medium leading-snug text-redup dark:text-redup-gelap">{{ $item['label'] }}</dt>
                                                    <dd class="mt-1 flex items-baseline gap-1 text-xl font-bold tabular-nums tracking-tight text-tinta dark:text-teks-gelap">
                                                        {{ number_format($item['nilai'], 0, ',', '.') }}
                                                        @isset($item['satuan'])
                                                            <span class="text-[11px] font-medium text-redup dark:text-redup-gelap">{{ $item['satuan'] }}</span>
                                                        @endisset
                                                    </dd>
                                                </div>
                    @endforeach
                </dl>

            </div>
        </section>

        <div class="grid items-start gap-5 lg:grid-cols-[1.4fr_1fr]">

            {{-- Tagihan yang perlu ditindaklanjuti, urut jatuh tempo terdekat. --}}
            <section class="rounded-xl border border-garis bg-white dark:border-garis-gelap dark:bg-gelap-2">
                <div class="flex items-baseline justify-between gap-2 border-b border-garis px-5 py-3.5 dark:border-garis-gelap">
                    <h2 class="text-sm font-semibold text-tinta dark:text-teks-gelap">Perlu ditindaklanjuti</h2>
                    <a href="{{ route('tagihan-spp.index') }}" class="text-[11px] font-medium text-neon-teks hover:underline dark:text-neon">
                        Semua tagihan
                    </a>
                </div>

                @if ($dueSoon->isEmpty())
                    <p class="px-5 py-6 text-sm text-redup dark:text-redup-gelap">Tidak ada tagihan terbuka. Semua sudah lunas.</p>
                @else
                    <ul class="divide-y divide-garis dark:divide-garis-gelap">
                        @foreach ($dueSoon as $tagihan)
                            <li class="flex items-center justify-between gap-3 px-5 py-3">
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-medium text-tinta dark:text-teks-gelap">
                                        {{ $tagihan->student?->nama ?? 'Siswa terhapus' }}
                                    </p>
                                    <p class="truncate text-[11px] text-redup dark:text-redup-gelap">
                                        {{ $tagihan->namaBulan() }} {{ $tagihan->tahun }}
                                        &middot; jatuh tempo {{ $tagihan->tanggal_jatuh_tempo?->format('d/m/Y') ?? '-' }}
                                    </p>
                                </div>
                                <div class="shrink-0 text-right">
                                    <p class="text-sm font-semibold tabular-nums text-tinta dark:text-teks-gelap">
                                        {{ $rupiah($tagihan->nominal) }}
                                    </p>
                                    @include('layouts.partials._status-badge', ['tagihan' => $tagihan])
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </section>

            <div class="space-y-5">

                {{-- Alat kerja. --}}
                <section class="rounded-xl border border-garis bg-white dark:border-garis-gelap dark:bg-gelap-2">
                    <h2 class="border-b border-garis px-5 py-3.5 text-sm font-semibold text-tinta dark:border-garis-gelap dark:text-teks-gelap">Alat kerja</h2>
                    <ul class="divide-y divide-garis dark:divide-garis-gelap">
                        @foreach ([
                            ['rute' => 'pencarian.index', 'teks' => 'Cari siswa', 'ket' => 'NIS, NISN, nama, atau kelas'],
                            ['rute' => 'tagihan-spp.index', 'teks' => 'Tagihan SPP', 'ket' => 'Saring per bulan dan status'],
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

                {{-- Pembayaran terakhir. --}}
                <section class="rounded-xl border border-garis bg-white dark:border-garis-gelap dark:bg-gelap-2">
                    <h2 class="border-b border-garis px-5 py-3.5 text-sm font-semibold text-tinta dark:border-garis-gelap dark:text-teks-gelap">Pembayaran terakhir</h2>
                    @if ($recentPayments->isEmpty())
                        <p class="px-5 py-6 text-sm leading-relaxed text-redup dark:text-redup-gelap">
                            Belum ada pembayaran tercatat. Baris muncul setelah pembayaran pertama masuk.
                        </p>
                    @else
                        <ul class="divide-y divide-garis dark:divide-garis-gelap">
                            @foreach ($recentPayments as $bayar)
                                <li class="flex items-center justify-between gap-3 px-5 py-3">
                                    <div class="min-w-0">
                                        <p class="truncate text-sm font-medium text-tinta dark:text-teks-gelap">{{ $bayar->student?->nama ?? 'Siswa terhapus' }}</p>
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

            </div>

        </div>

    </div>

@endsection