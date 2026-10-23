{{--
    Penentu menu samping.

    Berkas ini tidak memuat HTML. Tugasnya cuma tiga:
      1. menyiapkan variabel yang dipakai semua sidebar (pengguna, label role, kelas),
      2. memilih sidebar sesuai role yang sedang masuk,
      3. tidak menampilkan apa pun kalau role tidak dikenal.

    Variabel di sini otomatis ikut ke dalam @include anak, jadi tiap sidebar
    tidak perlu menghitung ulang dan tidak bisa berbeda gaya.
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

    // Satu kelas untuk semua tautan supaya tinggi, padding, dan warnanya seragam.
    $kelasTautan = 'group relative flex items-center gap-2.5 rounded-lg px-3 py-2 text-sm font-medium text-redup transition-colors duration-150 hover:bg-latar-2 hover:text-tinta dark:text-redup-gelap dark:hover:bg-gelap-2 dark:hover:text-teks-gelap';
    $kelasAktif = 'bg-neon/25 text-tinta hover:bg-neon/30 dark:bg-neon/15 dark:text-teks-gelap dark:hover:bg-neon/20';
    $kelasLabel = 'px-3 pb-1.5 pt-5 text-[11px] font-semibold uppercase tracking-[0.08em] text-redup dark:text-redup-gelap';
@endphp

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