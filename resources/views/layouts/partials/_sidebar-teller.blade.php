{{-- Daftar menu Teller. Fokus kasir: tagihan, pembayaran, pencarian siswa. --}}

@php
    $grup = [
        [
            'label' => null,
            'menu' => [
                ['rute' => 'dashboard', 'pola' => 'dashboard*', 'ikon' => 'i-dashboard', 'teks' => 'Dashboard'],
            ],
        ],
        [
            'label' => 'Kasir',
            'menu' => [
                ['rute' => 'tagihan-spp.index', 'pola' => 'tagihan-spp.*', 'ikon' => 'i-tagihan', 'teks' => 'Tagihan SPP'],
                ['rute' => 'pencarian.index', 'pola' => 'pencarian.*', 'ikon' => 'i-cari', 'teks' => 'Pencarian'],
            ],
        ],
        [
            'label' => 'Data',
            'menu' => [
                ['rute' => 'siswa.index', 'pola' => 'siswa.*', 'ikon' => 'i-siswa', 'teks' => 'Siswa'],
                ['rute' => 'kelas.index', 'pola' => 'kelas.*', 'ikon' => 'i-kelas', 'teks' => 'Kelas'],
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