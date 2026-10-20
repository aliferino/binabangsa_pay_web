{{--
    Menu navigasi. Isinya menyesuaikan role yang sedang masuk.

    Aturan tampil:
      admin          semua menu
      kepala_sekolah dashboard, jurusan, kelas, tahun ajaran, siswa, tarif, laporan
      teller         dashboard, tagihan, pencarian, laporan
      siswa          dashboard saja
--}}

@php
    $pengguna = auth()->user();
    $role = $pengguna?->role;

    // Cek menu dipakai berulang, jadi dikumpulkan dulu supaya rapi.
    $bolehMaster = in_array($role, ['admin', 'kepala_sekolah'], true);
    $bolehTulis = $role === 'admin';
    $bolehTagihan = in_array($role, ['admin', 'teller'], true);
    $bolehLaporan = in_array($role, ['admin', 'kepala_sekolah', 'teller'], true);
    $bolehUser = $role === 'admin';
@endphp

<div class="flex h-full flex-col">

    {{-- Merek sekolah. --}}
    <div class="hidden items-center gap-3 border-b border-slate-200 px-5 py-4 lg:flex">
        <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-teal-700 text-sm font-bold text-white">BB</span>
        <div class="leading-tight">
            <p class="text-sm font-semibold text-slate-800">BinaBangsa Pay</p>
            <p class="text-xs text-slate-500">SMK Bina Bangsa</p>
        </div>
    </div>

    {{-- Identitas pengguna yang sedang masuk. --}}
    @php
        $labelRole = [
            'admin' => 'Administrator',
            'kepala_sekolah' => 'Kepala Sekolah',
            'teller' => 'Teller',
            'siswa' => 'Siswa',
        ][$role] ?? 'Pengguna';
    @endphp
    <div class="border-b border-slate-200 px-5 py-4">
        <p class="text-xs uppercase tracking-wide text-slate-400">Masuk sebagai</p>
        <p class="mt-0.5 truncate text-sm font-semibold text-slate-800">{{ $pengguna?->nama }}</p>
        <p class="text-xs text-slate-500">{{ $labelRole }}</p>
    </div>

    <nav class="flex-1 space-y-1 overflow-y-auto px-3 py-4" aria-label="Menu utama">

        @php
            // Kelas tautan dibuat sekali supaya tidak ditulis berulang.
            $kelasTautan = 'flex items-center rounded-lg px-3 py-2 text-sm font-medium transition';
            $kelasAktif = ' bg-teal-50 text-teal-800';
            $kelasDiam = ' text-slate-600 hover:bg-slate-100 hover:text-slate-900';
        @endphp

        <a href="{{ route('dashboard') }}"
           class="{{ $kelasTautan }}{{ request()->routeIs('dashboard*') ? $kelasAktif : $kelasDiam }}">
            Dashboard
        </a>

        @if ($bolehMaster)
            <p class="px-3 pb-1 pt-4 text-xs font-semibold uppercase tracking-wide text-slate-400">Data master</p>

            <a href="{{ route('jurusan.index') }}"
               class="{{ $kelasTautan }}{{ request()->routeIs('jurusan.*') ? $kelasAktif : $kelasDiam }}">
                Jurusan
            </a>
            <a href="{{ route('kelas.index') }}"
               class="{{ $kelasTautan }}{{ request()->routeIs('kelas.*') ? $kelasAktif : $kelasDiam }}">
                Kelas
            </a>
            <a href="{{ route('tahun-ajaran.index') }}"
               class="{{ $kelasTautan }}{{ request()->routeIs('tahun-ajaran.*') ? $kelasAktif : $kelasDiam }}">
                Tahun Ajaran
            </a>
            <a href="{{ route('siswa.index') }}"
               class="{{ $kelasTautan }}{{ request()->routeIs('siswa.*') ? $kelasAktif : $kelasDiam }}">
                Siswa
            </a>
            <a href="{{ route('tarif-spp.index') }}"
               class="{{ $kelasTautan }}{{ request()->routeIs('tarif-spp.*') ? $kelasAktif : $kelasDiam }}">
                Tarif SPP
            </a>
        @endif

        @if ($bolehTagihan)
            <p class="px-3 pb-1 pt-4 text-xs font-semibold uppercase tracking-wide text-slate-400">Operasional</p>

            <a href="{{ $bolehTulis ? route('tagihan-spp.index') : route('tagihan-spp.lihat') }}"
               class="{{ $kelasTautan }}{{ request()->routeIs('tagihan-spp.*') ? $kelasAktif : $kelasDiam }}">
                Tagihan SPP
            </a>
            <a href="{{ route('pencarian.index') }}"
               class="{{ $kelasTautan }}{{ request()->routeIs('pencarian.*') ? $kelasAktif : $kelasDiam }}">
                Pencarian
            </a>
        @endif

        @if ($bolehLaporan)
            <p class="px-3 pb-1 pt-4 text-xs font-semibold uppercase tracking-wide text-slate-400">Laporan</p>

            <a href="{{ route('laporan.pembayaran') }}"
               class="{{ $kelasTautan }}{{ request()->routeIs('laporan.pembayaran') ? $kelasAktif : $kelasDiam }}">
                Laporan Pembayaran
            </a>
            <a href="{{ route('laporan.tunggakan') }}"
               class="{{ $kelasTautan }}{{ request()->routeIs('laporan.tunggakan') ? $kelasAktif : $kelasDiam }}">
                Laporan Tunggakan
            </a>
        @endif

        @if ($bolehUser)
            <p class="px-3 pb-1 pt-4 text-xs font-semibold uppercase tracking-wide text-slate-400">Pengaturan</p>

            <a href="{{ route('user.index') }}"
               class="{{ $kelasTautan }}{{ request()->routeIs('user.*') ? $kelasAktif : $kelasDiam }}">
                Pengguna
            </a>
        @endif
    </nav>

    {{-- Tombol keluar. --}}
    <div class="border-t border-slate-200 px-3 py-4">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit"
                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100">
                Keluar
            </button>
        </form>
    </div>
</div>
