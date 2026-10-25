{{--
    Daftar menu Administrator.

    Isinya hanya tautan. Logo, profil, dan tombol keluar ada di _sidebar.blade.php
    supaya tidak diulang di tiap role. Kelas $kelasTautan, $kelasAktif, $kelasLabel,
    dan $kelasIkon juga dari sana.

    Urutan kelompok mengikuti alur kerja admin: lihat ringkasan, urus data induk,
    jalankan operasional harian, periksa uang masuk, baca laporan, lalu atur akun.
    Istilah "Virtual Account" dipakai penuh di semua label, tidak disingkat VA.
--}}

@php
    $grup = [
        [
            'label' => null,
            'menu' => [
                ['rute' => 'dashboard', 'pola' => 'dashboard*', 'ikon' => 'i-dashboard', 'teks' => 'Dashboard'],
            ],
        ],
        [
            'label' => 'Data Master',
            'menu' => [
                ['rute' => 'tahun-ajaran.index', 'pola' => 'tahun-ajaran.*', 'ikon' => 'i-kalender', 'teks' => 'Tahun Ajaran'],
                ['rute' => 'jurusan.index', 'pola' => 'jurusan.*', 'ikon' => 'i-jurusan', 'teks' => 'Jurusan'],
                ['rute' => 'kelas.index', 'pola' => 'kelas.*', 'ikon' => 'i-kelas', 'teks' => 'Kelas'],
                ['rute' => 'siswa.index', 'pola' => 'siswa.*', 'ikon' => 'i-siswa', 'teks' => 'Siswa'],
                ['rute' => 'tarif-spp.index', 'pola' => 'tarif-spp.*', 'ikon' => 'i-tarif', 'teks' => 'Tarif SPP'],
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
        [
            'label' => 'Operasional',
            'menu' => [
                ['rute' => 'tagihan-spp.index', 'pola' => 'tagihan-spp.*', 'ikon' => 'i-tagihan', 'teks' => 'Tagihan SPP'],
                ['rute' => 'pencarian.index', 'pola' => 'pencarian.*', 'ikon' => 'i-cari', 'teks' => 'Pencarian'],
            ],
        ],
        [
            'label' => 'Pengaturan',
            'menu' => [
                ['rute' => 'integrasi.index', 'pola' => 'integrasi.*', 'ikon' => 'i-hubung', 'teks' => 'Integrasi Gateway'],
                ['rute' => 'user.index', 'pola' => 'user.*', 'ikon' => 'i-pengguna', 'teks' => 'Pengguna'],
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