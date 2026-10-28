{{--
    Daftar menu Teller.

    Fokus kasir: cari siswa, periksa tagihan, catat pembayaran. Kelompoknya
    sengaja mengikuti alur itu, bukan urutan tabel di database. Data master
    yang boleh dilihat teller hanya siswa dan kelas, sisanya milik admin.
    Istilah "Virtual Account" dipakai penuh di semua label, tidak disingkat VA.
--}}

@php
    $grup = [
        [
            'label' => null,
            'menu' => [
                ['rute' => 'dashboard', 'pola' => 'dashboard*', 'ikon' => 'i-dashboard', 'teks' => 'Dashboard'],
                ['rute' => 'pencarian.index', 'pola' => 'pencarian.*', 'ikon' => 'i-cari', 'teks' => 'Pencarian'],
            ],
        ],
        [
            'label' => 'Operasional',
            'menu' => [
                ['rute' => 'virtual-account.index', 'pola' => 'virtual-account.*', 'ikon' => 'i-va', 'teks' => 'Virtual Account'],
            ],
        ],
        [
            'label' => 'Transaksi',
            'menu' => [
                ['rute' => 'gateway-transactions.index', 'pola' => 'gateway-transactions.*', 'ikon' => 'i-transaksi', 'teks' => 'Payment Gateway'],
                ['rute' => 'va-transactions.index', 'pola' => 'va-transactions.*', 'ikon' => 'i-va', 'teks' => 'Transaksi Virtual Account'],
            ],
        ],
        [
            'label' => 'Laporan',
            'menu' => [
                ['rute' => 'laporan.pembayaran', 'pola' => 'laporan.pembayaran', 'ikon' => 'i-laporan', 'teks' => 'Laporan Pembayaran'],
                ['rute' => 'laporan.tunggakan', 'pola' => 'laporan.tunggakan', 'ikon' => 'i-peringatan', 'teks' => 'Laporan Tunggakan'],
                ['rute' => 'laporan.rekap', 'pola' => 'laporan.rekap', 'ikon' => 'i-transaksi', 'teks' => 'Rekap Pembayaran'],
            ],
        ],
    ];
@endphp

@foreach ($grup as $bagian)
    @if ($bagian['label'])
        <p class="{{ $kelasLabel }}">{{ $bagian['label'] }}</p>
    @endif

    @foreach ($bagian['menu'] as $item)
        <a href="{{ route($item['rute']) }}"
           @class([$kelasTautan, $kelasAktif => request()->routeIs($item['pola'])])
           @if (request()->routeIs($item['pola'])) aria-current="page" @endif>
            <svg class="{{ $kelasIkon }}" aria-hidden="true"><use href="#{{ $item['ikon'] }}"></use></svg>
            <span class="truncate">{{ $item['teks'] }}</span>
        </a>
    @endforeach
@endforeach