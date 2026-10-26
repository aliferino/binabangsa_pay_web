{{--
    Penentu dan kerangka menu samping.

    Berkas ini memegang bagian yang sama untuk semua role: logo, wadah gulir,
    kartu profil, dan tombol keluar. Tiap _sidebar-{role} hanya berisi daftar
    tautan, jadi tidak ada HTML kerangka yang diulang empat kali.

    Ikon ditulis sekali sebagai <symbol> lalu dipakai lewat <use>. Tidak perlu
    paket ikon baru, dan empat sidebar memakai gambar yang sama persis.
--}}

@php
    $pengguna = auth()->user();
    $roleSekarang = $pengguna?->role;

    $labelRole = [
            'admin' => 'Administrator',
            'kepala_sekolah' => 'Kepala Sekolah',
            'teller' => 'Teller',
            'siswa' => 'Siswa',
        ][$roleSekarang] ?? 'Pengguna';

    // Tautan biasa dan tautan aktif. Aktif memakai latar neon penuh supaya
    // terbaca seperti tombol, bukan sekadar teks yang diberi warna.
    $kelasTautan = 'flex items-center gap-2.5 rounded-lg px-3 py-2 text-sm font-medium text-redup transition-colors duration-150 hover:bg-latar-2 hover:text-tinta dark:text-redup-gelap dark:hover:bg-gelap dark:hover:text-teks-gelap';
    $kelasAktif = 'bg-neon font-semibold text-tinta shadow-sm hover:bg-neon dark:bg-neon dark:text-tinta dark:hover:bg-neon';
    $kelasLabel = 'px-3 pb-1 pt-4 text-[11px] font-semibold uppercase tracking-[0.1em] text-redup dark:text-redup-gelap';
    $kelasIkon = 'h-[18px] w-[18px] shrink-0';
@endphp

<div class="flex h-full min-h-0 flex-col bg-white dark:bg-gelap-2">

    {{-- Kumpulan ikon. Ukuran 0 supaya tidak memakan ruang tapi tetap bisa dipakai <use>. --}}
    <svg class="absolute h-0 w-0 overflow-hidden" aria-hidden="true" focusable="false">
        <defs>
            <symbol id="i-dashboard" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="3" width="7" height="9" rx="1.5"/><rect x="14" y="3" width="7" height="5" rx="1.5"/>
                <rect x="14" y="12" width="7" height="9" rx="1.5"/><rect x="3" y="16" width="7" height="5" rx="1.5"/>
            </symbol>
            <symbol id="i-jurusan" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                <path d="M6 22V4a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v18"/><path d="M14 10h3a1 1 0 0 1 1 1v11"/>
                <path d="M2 22h20"/><path d="M9 7h1M9 11h1M9 15h1"/>
            </symbol>
            <symbol id="i-kelas" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                <path d="M3 21h18"/><path d="M5 21V8l7-5 7 5v13"/><path d="M9.5 21v-6h5v6"/>
            </symbol>
            <symbol id="i-kalender" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="5" width="18" height="16" rx="2"/><path d="M3 10h18M8 3v4M16 3v4"/>
            </symbol>
            <symbol id="i-siswa" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/>
                <path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>
            </symbol>
            <symbol id="i-tarif" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                <path d="M20.6 13.4 12.4 5.2A2 2 0 0 0 11 4.6H5a2 2 0 0 0-2 2v6a2 2 0 0 0 .6 1.4l8.2 8.2a2 2 0 0 0 2.8 0l6-6a2 2 0 0 0 0-2.8Z"/>
                <circle cx="7.8" cy="7.8" r="1.2"/>
            </symbol>
            <symbol id="i-tagihan" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                <path d="M5 3h14v18l-3-2-2 2-2-2-2 2-2-2-3 2V3Z"/><path d="M9 8h6M9 12h6"/>
            </symbol>
            {{-- Virtual account: kartu dengan angka, dibedakan dari ikon tagihan
                 yang berbentuk lembar bertepi sobek. --}}
            {{-- Transaksi: dua panah berlawanan arah, beda dari ikon tagihan dan VA. --}}
            {{-- Panah kembali, dipakai halaman detail untuk balik ke daftar. --}}
            <symbol id="i-kembali" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                <path d="M19 12H5"/><path d="M11 18l-6-6 6-6"/>
            </symbol>

            <symbol id="i-transaksi" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                <path d="M4 8h13l-3-3"/><path d="M20 16H7l3 3"/>
            </symbol>

            <symbol id="i-va" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                <rect x="2.5" y="5" width="19" height="14" rx="2"/><path d="M2.5 9.5h19"/><path d="M6 14.5h4M13 14.5h5"/>
            </symbol>

            {{-- Saklar daya, untuk mengaktifkan dan menonaktifkan. --}}
            <symbol id="i-padam" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                <path d="M12 3.5v8"/><path d="M18.4 6.6a9 9 0 1 1-12.8 0"/>
            </symbol>
            <symbol id="i-cari" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="11" cy="11" r="7"/><path d="m20 20-3.6-3.6"/>
            </symbol>
            <symbol id="i-laporan" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                <path d="M3 3v18h18"/><path d="M8 16v-4M13 16V8M18 16v-6"/>
            </symbol>
            <symbol id="i-peringatan" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                <path d="M10.3 3.9 1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0Z"/>
                <path d="M12 9v4M12 17h.01"/>
            </symbol>
            <symbol id="i-pengguna" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="8" r="4"/><path d="M4 21v-1a6 6 0 0 1 6-6h4a6 6 0 0 1 6 6v1"/>
            </symbol>
            <symbol id="i-kasir" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                <path d="M3 7a2 2 0 0 1 2-2h13a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7Z"/><path d="M3 10h18"/>
                <circle cx="16.4" cy="14.6" r="1.1"/>
            </symbol>
            <symbol id="i-keluar" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="m16 17 5-5-5-5"/><path d="M21 12H9"/>
            </symbol>
            <symbol id="i-lihat" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <path d="M2.5 12S6 5.5 12 5.5 21.5 12 21.5 12 18 18.5 12 18.5 2.5 12 2.5 12Z"/><circle cx="12" cy="12" r="3"/>
            </symbol>
            <symbol id="i-ubah" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <path d="M4 20h4L19.5 8.5a2.1 2.1 0 0 0-3-3L5 17v3Z"/><path d="m14.5 6.5 3 3"/>
            </symbol>
            <symbol id="i-hapus" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <path d="M4 7h16"/><path d="M9.5 7V4.8a1 1 0 0 1 1-1h3a1 1 0 0 1 1 1V7"/>
                <path d="M6.5 7l.9 12.2a1.6 1.6 0 0 0 1.6 1.5h6a1.6 1.6 0 0 0 1.6-1.5L17.5 7"/>
                <path d="M10.5 11v6M13.5 11v6"/>
            </symbol>
            <symbol id="i-tutup" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <path d="M6.5 6.5 17.5 17.5M17.5 6.5 6.5 17.5"/>
            </symbol>
            <symbol id="i-simpan" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="m5 12.5 4.5 4.5L19 7.5"/>
                        </symbol>

                        {{-- Steker, untuk aksi menghubungkan Virtual Account ke Xendit. --}}
                        <symbol id="i-hubung" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M9 2v6M15 2v6"/><path d="M6 8h12v3a6 6 0 0 1-12 0V8Z"/>
                            <path d="M12 17v5"/>
                        </symbol>

                        {{-- Tombol putar, untuk simulasi pembayaran mode uji. --}}
                        <symbol id="i-simulasi" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="9"/><path d="m10 8.5 6 3.5-6 3.5v-7Z"/>
                        </symbol>
                    </defs>
    </svg>

    {{-- Merek aplikasi. Tinggi tetap supaya sejajar dengan bilah atas di layar kecil. --}}
    <a href="{{ route('dashboard') }}" class="flex h-20 shrink-0 items-center gap-3 border-b border-garis px-4 dark:border-garis-gelap">
        <img src="{{ asset('images/logo-smk-bina-bangsa-180.webp') }}" alt="Logo SMK Bina Bangsa"
             class="h-11 w-11 shrink-0 rounded-full object-contain ring-1 ring-garis dark:ring-garis-gelap" width="44" height="44">
        <div class="min-w-0 leading-normal">
            <p class="truncate text-base font-semibold tracking-tight text-tinta dark:text-teks-gelap">BinaBangsa Pay</p>
            <p class="truncate text-xs text-redup dark:text-redup-gelap">SMK Bina Bangsa</p>
        </div>
    </a>

    <nav class="gulir-tanpa-batang min-h-0 flex-1 space-y-0.5 overflow-y-auto overscroll-contain px-2.5 pb-2 pt-2.5" aria-label="Menu utama">
        @switch($roleSekarang)
            @case('admin')
                @include('layouts.partials._sidebar-admin')
                @break
            @case('kepala_sekolah')
                @include('layouts.partials._sidebar-kepsek')
                @break
            @case('teller')
                @include('layouts.partials._sidebar-teller')
                @break
            @case('siswa')
                @include('layouts.partials._sidebar-siswa')
                @break
        @endswitch
    </nav>

    {{-- Kartu profil. Tombol, bukan <details>, karena bubble-nya harus menutup
             saat klik di luar dan <details> bawaan peramban tidak melakukannya.
             Identitas cukup di kartu ini; bubble hanya berisi dua tombol. --}}
        <div class="px-2.5 pb-3 pt-1">
            <div class="relative" data-profil>
                <button type="button" data-profil-pemicu aria-expanded="false" aria-controls="bubble-profil"
                        @class(['tombol-tekan flex w-full cursor-pointer items-center gap-3 rounded-xl bg-latar-2 px-3 py-2.5 text-left transition-colors duration-150 hover:bg-garis/60 dark:bg-gelap dark:hover:bg-garis-gelap/40'])>
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-neon text-base font-bold text-tinta">
                        {{ strtoupper(substr($pengguna?->nama ?? '?', 0, 1)) }}
                    </span>
                    <span class="min-w-0 flex-1">
                        <span class="block truncate text-sm font-semibold leading-tight text-tinta dark:text-teks-gelap">{{ $pengguna?->nama }}</span>
                        <span class="block truncate text-xs leading-tight text-redup dark:text-redup-gelap">{{ $labelRole }}</span>
                    </span>
                </button>

                {{-- Bubble dibuka KE ATAS di dalam lebar sidebar, bukan ke kanan
                     keluar sidebar. Dulu di layar besar ia dipindah ke sisi kanan
                     kartu, dan hasilnya dua hal rusak: tepi kirinya terpotong
                     karena aside meng-clip, dan badannya menimpa kartu isi
                     halaman. Posisi satu ini dipakai semua lebar layar, jadi
                     tidak ada lagi aturan lg: yang membuatnya meleset. --}}
                <div id="bubble-profil" data-profil-bubble hidden
                     class="absolute bottom-full left-0 right-0 mb-2 overflow-hidden rounded-xl border border-garis bg-white p-1.5 shadow-lg dark:border-garis-gelap dark:bg-gelap-2">

                    <button type="button" data-tombol-tema aria-pressed="false"
                            class="tombol-tekan flex w-full items-center gap-2.5 rounded-lg px-2.5 py-2 text-sm font-medium text-tinta transition-colors duration-150 hover:bg-latar-2 dark:text-teks-gelap dark:hover:bg-gelap">
                        <svg class="h-[18px] w-[18px] shrink-0" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                            <path d="M16 11.5A6.5 6.5 0 0 1 8.5 4a6.5 6.5 0 1 0 7.5 7.5Z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round" class="dark:hidden"/>
                            <g class="hidden dark:block">
                                <circle cx="10" cy="10" r="3.5" stroke="currentColor" stroke-width="1.5"/>
                                <path d="M10 2.5v2M10 15.5v2M2.5 10h2M15.5 10h2M4.7 4.7l1.4 1.4M13.9 13.9l1.4 1.4M15.3 4.7l-1.4 1.4M6.1 13.9l-1.4 1.4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                            </g>
                        </svg>
                        <span data-label-tema>Mode gelap</span>
                    </button>

                    <div class="my-1 h-px bg-garis dark:bg-garis-gelap"></div>

                    {{-- Keluar memutus sesi tanpa bisa dibatalkan, jadi tetap lewat
                         modal konfirmasi yang sama dengan hapus data. --}}
                    <form method="POST" action="{{ route('logout') }}"
                          data-konfirmasi="Keluar dari akun ini?" data-konfirmasi-jenis="keluar">
                        @csrf
                        <button type="submit"
                                class="tombol-tekan flex w-full items-center gap-2.5 rounded-lg px-2.5 py-2 text-sm font-semibold text-[#c10007] transition-colors duration-150 hover:bg-[#fdf3f2] dark:hover:bg-[#2a1a1a]">
                            <svg class="{{ $kelasIkon }}" aria-hidden="true"><use href="#i-keluar"></use></svg>
                            Keluar
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>