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
        </defs>
    </svg>

    {{-- Merek aplikasi. Tinggi tetap supaya sejajar dengan bilah atas di layar kecil. --}}
    <a href="{{ route('dashboard') }}" class="flex h-20 shrink-0 items-center gap-3 border-b border-garis px-4 dark:border-garis-gelap">
        <img src="{{ asset('images/logo-smk-bina-bangsa-180.webp') }}" alt="Logo SMK Bina Bangsa"
             class="h-11 w-11 shrink-0 object-contain" width="44" height="44">
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

    {{-- Profil pengguna yang sedang masuk, lalu tombol keluar di bawahnya. --}}
    <div class="px-2.5 pb-3 pt-1">
        <div class="flex items-center gap-2.5 rounded-xl bg-latar-2 px-2.5 py-2 dark:bg-gelap">
            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-neon text-xs font-bold text-tinta">
                {{ strtoupper(substr($pengguna?->nama ?? '?', 0, 1)) }}
            </span>
            <div class="min-w-0">
                <p class="truncate text-sm font-semibold text-tinta dark:text-teks-gelap">{{ $pengguna?->nama }}</p>
                @if ($labelRole !== $pengguna?->nama)
                    <p class="truncate text-xs text-redup dark:text-redup-gelap">{{ $labelRole }}</p>
                @endif
            </div>
        </div>

        <form method="POST" action="{{ route('logout') }}" class="mt-1.5">
            @csrf
            <button type="submit"
                    class="flex w-full items-center gap-2.5 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm font-semibold text-red-700 transition-colors duration-150 hover:border-red-300 hover:bg-red-100 dark:border-red-900 dark:bg-red-950/40 dark:text-red-400 dark:hover:bg-red-950/70">
                <svg class="{{ $kelasIkon }}" aria-hidden="true"><use href="#i-keluar"></use></svg>
                Keluar
            </button>
        </form>
    </div>
</div>