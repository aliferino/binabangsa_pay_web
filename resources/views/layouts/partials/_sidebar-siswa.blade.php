{{-- Menu samping Siswa: brief hanya menyebut Dashboard, dan memang belum ada route tagihan siswa. --}}


<div class="flex h-full min-h-0 flex-col bg-white dark:bg-gelap-2">

    <div class="hidden items-center gap-3 border-b border-garis px-4 py-4 lg:flex dark:border-garis-gelap">
        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-neon text-sm font-bold text-tinta">BB</span>
        <div class="min-w-0 leading-tight">
            <p class="truncate text-sm font-semibold text-tinta dark:text-teks-gelap">BinaBangsa Pay</p>
            <p class="truncate text-xs text-redup dark:text-redup-gelap">SMK Bina Bangsa</p>
        </div>
    </div>

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
    </nav>

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