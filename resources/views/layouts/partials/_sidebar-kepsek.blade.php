{{-- Daftar menu Kepala Sekolah. Hanya baca: tanpa menu tulis. --}}

@php
    $grup = [
        [
            'label' => null,
            'menu' => [
                ['rute' => 'dashboard', 'pola' => 'dashboard*', 'ikon' => 'i-dashboard', 'teks' => 'Dashboard'],
            ],
        ],
        [
            'label' => 'Data',
            'menu' => [
                ['rute' => 'jurusan.index', 'pola' => 'jurusan.*', 'ikon' => 'i-jurusan', 'teks' => 'Jurusan'],
                ['rute' => 'kelas.index', 'pola' => 'kelas.*', 'ikon' => 'i-kelas', 'teks' => 'Kelas'],
                ['rute' => 'siswa.index', 'pola' => 'siswa.*', 'ikon' => 'i-siswa', 'teks' => 'Siswa'],
                ['rute' => 'tarif-spp.index', 'pola' => 'tarif-spp.*', 'ikon' => 'i-tarif', 'teks' => 'Tarif SPP'],
            ],
        ],
        [
            'label' => 'Laporan',
            'menu' => [
                ['rute' => 'laporan.pembayaran', 'pola' => 'laporan.pembayaran', 'ikon' => 'i-laporan', 'teks' => 'Laporan Pembayaran'],
                ['rute' => 'laporan.tunggakan', 'pola' => 'laporan.tunggakan', 'ikon' => 'i-peringatan', 'teks' => 'Laporan Tunggakan'],
                ['rute' => 'laporan.rekap', 'pola' => 'laporan.rekap', 'ikon' => 'i-transaksi', 'teks' => 'Rekap Pembayaran'],
                ['rute' => 'pencarian.index', 'pola' => 'pencarian.*', 'ikon' => 'i-cari', 'teks' => 'Pencarian'],
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