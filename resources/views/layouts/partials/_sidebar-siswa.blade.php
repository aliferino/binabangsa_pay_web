{{-- Daftar menu Siswa. Hanya dashboard; menu tagihan belum punya route. --}}

@php
    $grup = [
        [
            'label' => null,
            'menu' => [
                ['rute' => 'dashboard', 'pola' => 'dashboard*', 'ikon' => 'i-dashboard', 'teks' => 'Dashboard'],
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