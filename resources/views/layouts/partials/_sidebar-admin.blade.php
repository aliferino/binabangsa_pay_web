{{--
    Menu samping khusus Administrator.

    Empat sidebar dipisah per role supaya tiap berkas hanya memuat menu yang
    benar-benar dipakai role itu. Berkas ini tidak perlu @if role lagi.

    Palet mengikuti halaman publik: neon #b8f048 hanya untuk latar penanda,
    teks di atasnya selalu tinta #111111. Teks redup memakai --color-redup.
--}}


<div class="flex h-full min-h-0 flex-col bg-white dark:bg-gelap-2">

    {{-- Merek sekolah. --}}
    <div class="hidden items-center gap-3 border-b border-garis px-4 py-4 lg:flex dark:border-garis-gelap">
        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-neon text-sm font-bold text-tinta">BB</span>
        <div class="min-w-0 leading-tight">
            <p class="truncate text-sm font-semibold text-tinta dark:text-teks-gelap">BinaBangsa Pay</p>
            <p class="truncate text-xs text-redup dark:text-redup-gelap">SMK Bina Bangsa</p>
        </div>
    </div>

    {{-- Identitas pengguna yang sedang masuk. --}}
    <div class="border-b border-garis px-4 py-3.5 dark:border-garis-gelap">
        <p class="text-[11px] font-semibold uppercase tracking-[0.08em] text-redup dark:text-redup-gelap">Masuk sebagai</p>
        <p class="mt-1 truncate text-sm font-semibold text-tinta dark:text-teks-gelap">{{ $pengguna?->nama }}</p>
        @if ($labelRole !== $pengguna?->nama)
            <p class="truncate text-xs text-redup dark:text-redup-gelap">{{ $labelRole }}</p>
        @endif
    </div>

    <nav class="min-h-0 flex-1 overflow-y-auto overscroll-contain px-2.5 py-3" aria-label="Menu utama">

        <a href="{{ route('dashboard') }}"
           @class([$kelasTautan, $kelasAktif => request()->routeIs('dashboard*')])>
            <span @class([
                'absolute inset-y-1.5 left-0 w-0.5 rounded-full bg-neon-teks dark:bg-neon',
                'opacity-0' => ! request()->routeIs('dashboard*'),
            ]) aria-hidden="true"></span>
            Dashboard
        </a>

        <p class="{{ $kelasLabel }}">Data master</p>

        <a href="{{ route('jurusan.index') }}"
           @class([$kelasTautan, $kelasAktif => request()->routeIs('jurusan.*')])>
            <span @class([
                'absolute inset-y-1.5 left-0 w-0.5 rounded-full bg-neon-teks dark:bg-neon',
                'opacity-0' => ! request()->routeIs('jurusan.*'),
            ]) aria-hidden="true"></span>
            Jurusan
        </a>
        <a href="{{ route('kelas.index') }}"
           @class([$kelasTautan, $kelasAktif => request()->routeIs('kelas.*')])>
            <span @class([
                'absolute inset-y-1.5 left-0 w-0.5 rounded-full bg-neon-teks dark:bg-neon',
                'opacity-0' => ! request()->routeIs('kelas.*'),
            ]) aria-hidden="true"></span>
            Kelas
        </a>
        <a href="{{ route('tahun-ajaran.index') }}"
           @class([$kelasTautan, $kelasAktif => request()->routeIs('tahun-ajaran.*')])>
            <span @class([
                'absolute inset-y-1.5 left-0 w-0.5 rounded-full bg-neon-teks dark:bg-neon',
                'opacity-0' => ! request()->routeIs('tahun-ajaran.*'),
            ]) aria-hidden="true"></span>
            Tahun Ajaran
        </a>
        <a href="{{ route('siswa.index') }}"
           @class([$kelasTautan, $kelasAktif => request()->routeIs('siswa.*')])>
            <span @class([
                'absolute inset-y-1.5 left-0 w-0.5 rounded-full bg-neon-teks dark:bg-neon',
                'opacity-0' => ! request()->routeIs('siswa.*'),
            ]) aria-hidden="true"></span>
            Siswa
        </a>
        <a href="{{ route('tarif-spp.index') }}"
           @class([$kelasTautan, $kelasAktif => request()->routeIs('tarif-spp.*')])>
            <span @class([
                'absolute inset-y-1.5 left-0 w-0.5 rounded-full bg-neon-teks dark:bg-neon',
                'opacity-0' => ! request()->routeIs('tarif-spp.*'),
            ]) aria-hidden="true"></span>
            Tarif SPP
        </a>

        <p class="{{ $kelasLabel }}">Operasional</p>

        <a href="{{ route('tagihan-spp.index') }}"
           @class([$kelasTautan, $kelasAktif => request()->routeIs('tagihan-spp.*')])>
            <span @class([
                'absolute inset-y-1.5 left-0 w-0.5 rounded-full bg-neon-teks dark:bg-neon',
                'opacity-0' => ! request()->routeIs('tagihan-spp.*'),
            ]) aria-hidden="true"></span>
            Tagihan SPP
        </a>
        <a href="{{ route('pencarian.index') }}"
           @class([$kelasTautan, $kelasAktif => request()->routeIs('pencarian.*')])>
            <span @class([
                'absolute inset-y-1.5 left-0 w-0.5 rounded-full bg-neon-teks dark:bg-neon',
                'opacity-0' => ! request()->routeIs('pencarian.*'),
            ]) aria-hidden="true"></span>
            Pencarian
        </a>

        <p class="{{ $kelasLabel }}">Laporan</p>

        <a href="{{ route('laporan.pembayaran') }}"
           @class([$kelasTautan, $kelasAktif => request()->routeIs('laporan.pembayaran')])>
            <span @class([
                'absolute inset-y-1.5 left-0 w-0.5 rounded-full bg-neon-teks dark:bg-neon',
                'opacity-0' => ! request()->routeIs('laporan.pembayaran'),
            ]) aria-hidden="true"></span>
            Laporan Pembayaran
        </a>
        <a href="{{ route('laporan.tunggakan') }}"
           @class([$kelasTautan, $kelasAktif => request()->routeIs('laporan.tunggakan')])>
            <span @class([
                'absolute inset-y-1.5 left-0 w-0.5 rounded-full bg-neon-teks dark:bg-neon',
                'opacity-0' => ! request()->routeIs('laporan.tunggakan'),
            ]) aria-hidden="true"></span>
            Laporan Tunggakan
        </a>

        <p class="{{ $kelasLabel }}">Pengaturan</p>

        <a href="{{ route('user.index') }}"
           @class([$kelasTautan, $kelasAktif => request()->routeIs('user.*')])>
            <span @class([
                'absolute inset-y-1.5 left-0 w-0.5 rounded-full bg-neon-teks dark:bg-neon',
                'opacity-0' => ! request()->routeIs('user.*'),
            ]) aria-hidden="true"></span>
            Pengguna
        </a>
    </nav>

    {{-- Tombol keluar. Menempel di bawah, tidak ikut tergulir. --}}
    <div class="border-t border-garis p-2.5 dark:border-garis-gelap">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit"
                    class="w-full rounded-lg border border-garis px-3 py-2 text-sm font-medium text-redup transition-colors duration-150 hover:bg-latar-2 hover:text-tinta dark:border-garis-gelap dark:text-redup-gelap dark:hover:bg-gelap dark:hover:text-teks-gelap">
                Keluar
            </button>
        </form>
    </div>
</div>