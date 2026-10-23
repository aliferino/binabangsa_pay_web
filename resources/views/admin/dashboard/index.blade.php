@extends('layouts.app')

@section('judul', 'Dashboard Admin')
@section('keterangan', 'Ringkasan data sekolah')

@section('isi')

    @php
        // Angka ringkasan. Nama variabel mengikuti yang dikirim controller.
        // Nada warna hanya dipakai kalau angkanya benar-benar ada.
        $ringkasan = [
            [
                'label' => 'Total siswa',
                'nilai' => $total_siswa,
                'catatan' => 'Seluruh siswa terdaftar',
                'nada' => 'netral',
            ],
            [
                'label' => 'Total tagihan',
                'nilai' => $total_tagihan,
                'catatan' => 'Semua tagihan SPP',
                'nada' => 'netral',
            ],
            [
                'label' => 'Siswa belum bayar',
                'nilai' => $total_siswa_belum_bayar,
                'catatan' => 'Punya tagihan belum dibayar',
                'nada' => $total_siswa_belum_bayar > 0 ? 'waspada' : 'netral',
            ],
            [
                'label' => 'Tagihan tunggakan',
                'nilai' => $total_tunggakan,
                'catatan' => 'Sudah lewat jatuh tempo',
                'nada' => $total_tunggakan > 0 ? 'bahaya' : 'netral',
            ],
        ];

        $adaTunggakan = (float) $nominal_tunggakan > 0;
    @endphp

    <div class="space-y-4">

        {{-- Ringkasan angka utama. --}}
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 sm:gap-4 xl:grid-cols-4">
            @foreach ($ringkasan as $kartu)
                <div class="rounded-xl border border-garis bg-white p-4 dark:border-garis-gelap dark:bg-gelap-2">
                    <p class="truncate text-xs font-medium text-redup dark:text-redup-gelap">{{ $kartu['label'] }}</p>
                    <p @class([
                        'mt-2 text-2xl font-bold tabular-nums tracking-tight sm:text-3xl',
                        'text-tinta dark:text-teks-gelap' => $kartu['nada'] === 'netral',
                        'text-amber-700 dark:text-amber-400' => $kartu['nada'] === 'waspada',
                        'text-red-700 dark:text-red-400' => $kartu['nada'] === 'bahaya',
                    ])>
                        {{ number_format($kartu['nilai'], 0, ',', '.') }}
                    </p>
                    <p class="mt-1 text-xs leading-snug text-redup dark:text-redup-gelap">{{ $kartu['catatan'] }}</p>
                </div>
            @endforeach
        </div>

        {{-- Nominal tunggakan. Aksen tepi kiri hanya muncul kalau memang ada tunggakan. --}}
        <div @class([
            'flex flex-wrap items-center justify-between gap-x-6 gap-y-2 rounded-xl border border-garis bg-white px-4 py-3.5 dark:border-garis-gelap dark:bg-gelap-2',
            'border-l-2 border-l-red-600 dark:border-l-red-400' => $adaTunggakan,
        ])>
            <div class="min-w-0">
                <p class="text-xs font-medium text-redup dark:text-redup-gelap">Total nominal tunggakan</p>
                <p class="mt-0.5 text-xs leading-snug text-redup dark:text-redup-gelap">
                    Jumlah rupiah dari seluruh tagihan berstatus tunggakan.
                </p>
            </div>
            <p @class([
                'text-2xl font-bold tabular-nums tracking-tight',
                'text-red-700 dark:text-red-400' => $adaTunggakan,
                'text-tinta dark:text-teks-gelap' => ! $adaTunggakan,
            ])>
                Rp{{ number_format((float) $nominal_tunggakan, 0, ',', '.') }}
            </p>
        </div>

        {{-- Bagian yang belum bisa ditampilkan. --}}
        <div class="flex items-start gap-2.5 rounded-xl border border-dashed border-garis bg-white px-4 py-3 dark:border-garis-gelap dark:bg-gelap-2">
            <svg class="mt-0.5 h-4 w-4 shrink-0 text-redup dark:text-redup-gelap" viewBox="0 0 20 20" fill="none"
                 stroke="currentColor" stroke-width="1.6" aria-hidden="true">
                <circle cx="10" cy="10" r="7.25"></circle>
                <path d="M10 6.5v4.25M10 13.4v.1" stroke-linecap="round"></path>
            </svg>
            <div class="min-w-0">
                <p class="text-xs font-medium text-tinta dark:text-teks-gelap">Belum tersedia</p>
                <p class="mt-0.5 text-xs leading-relaxed text-redup dark:text-redup-gelap">
                    Data pembayaran, transaksi, dan virtual account tampil setelah skema pembayaran disetujui.
                    Rencananya: total pembayaran, transaksi hari ini, grafik pembayaran, transaksi terbaru.
                </p>
            </div>
        </div>

        {{-- Jalan pintas ke menu yang sering dipakai. --}}
        <div>
            <h2 class="text-xs font-semibold uppercase tracking-[0.08em] text-redup dark:text-redup-gelap">Jalan pintas</h2>
            <div class="mt-2.5 grid grid-cols-1 gap-2 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ([
                    ['rute' => 'siswa.index', 'teks' => 'Data siswa'],
                    ['rute' => 'tagihan-spp.index', 'teks' => 'Tagihan SPP'],
                    ['rute' => 'laporan.tunggakan', 'teks' => 'Laporan tunggakan'],
                    ['rute' => 'user.index', 'teks' => 'Pengguna'],
                ] as $pintasan)
                    <a href="{{ route($pintasan['rute']) }}"
                       class="flex items-center justify-between gap-2 rounded-lg border border-garis bg-white px-3 py-2.5 text-sm font-medium text-tinta transition-colors duration-150 hover:border-neon-teks/40 hover:bg-latar-2 dark:border-garis-gelap dark:bg-gelap-2 dark:text-teks-gelap dark:hover:border-neon/40 dark:hover:bg-gelap">
                        <span class="truncate">{{ $pintasan['teks'] }}</span>
                        <svg class="h-4 w-4 shrink-0 text-redup dark:text-redup-gelap" viewBox="0 0 20 20" fill="none"
                             stroke="currentColor" stroke-width="1.6" aria-hidden="true">
                            <path d="M7.5 4.5 13 10l-5.5 5.5" stroke-linecap="round" stroke-linejoin="round"></path>
                        </svg>
                    </a>
                @endforeach
            </div>
        </div>

    </div>

@endsection