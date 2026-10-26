{{-- Daftar menu Siswa. Tiga menu uang: tagihan, nomor bayar, lalu riwayat. --}}

@php
    $grup = [
        [
            'label' => null,
            'menu' => [
                ['rute' => 'siswa.dashboard', 'pola' => 'siswa.dashboard', 'ikon' => 'i-dashboard', 'teks' => 'Dashboard'],
            ],
        ],
        [
            'label' => 'Pembayaran',
            'menu' => [
                ['rute' => 'siswa.bills.index', 'pola' => 'siswa.bills.*', 'ikon' => 'i-tagihan', 'teks' => 'Tagihan'],
                ['rute' => 'siswa.virtual-accounts.index', 'pola' => 'siswa.virtual-accounts.*', 'ikon' => 'i-va', 'teks' => 'Virtual Account'],
                ['rute' => 'siswa.payments.index', 'pola' => 'siswa.payments.*', 'ikon' => 'i-transaksi', 'teks' => 'Riwayat Pembayaran'],
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