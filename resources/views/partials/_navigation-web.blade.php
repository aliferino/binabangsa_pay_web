{{-- Bilah atas bersama untuk beranda dan login. Nama partial memakai
     awalan garis bawah (konvensi partial repo ini) dan akhiran -web supaya
     tidak bentrok dengan _navigation.blade.php yang dipakai menu samping
     panel setelah login. --}}
<header class="sticky top-0 z-30 border-b border-[#e2e8dd] bg-white/90 backdrop-blur dark:border-[#2a3038] dark:bg-[#171c21]/90">
    <div class="mx-auto flex max-w-6xl items-center justify-between px-4 py-3 sm:px-6">
        <a href="{{ route('beranda') }}" class="flex items-center gap-2.5">
            <img src="{{ asset('images/logo-smk-bina-bangsa-180.webp') }}"
                 alt="Logo SMK Bina Bangsa" width="28" height="28"
                 class="h-7 w-7 rounded-full ring-1 ring-[#e2e8dd] dark:ring-[#2a3038]">
            <span class="text-sm font-semibold tracking-tight text-[#111111] dark:text-[#e8ecf1]">BinaBangsa Pay</span>
        </a>
        <div class="flex items-center gap-2.5">
            <button type="button" id="tombol-tema" aria-pressed="false" aria-label="Ganti tema terang atau gelap"
                    class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-[#e2e8dd] text-[#5b6659] transition hover:bg-[#f6f8f4] focus:outline-none focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#4a7a0a] dark:border-[#2a3038] dark:text-[#8b95a1] dark:hover:bg-[#1b2026]">
                <svg viewBox="0 0 20 20" fill="none" aria-hidden="true" class="hidden h-5 w-5 dark:block">
                    <circle cx="10" cy="10" r="3.5" stroke="currentColor" stroke-width="1.5"/>
                    <path d="M10 2.5v2M10 15.5v2M2.5 10h2M15.5 10h2M4.7 4.7l1.4 1.4M13.9 13.9l1.4 1.4M15.3 4.7l-1.4 1.4M6.1 13.9l-1.4 1.4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                </svg>
                <svg viewBox="0 0 20 20" fill="none" aria-hidden="true" class="h-5 w-5 dark:hidden">
                    <path d="M16 11.5A6.5 6.5 0 0 1 8.5 4a6.5 6.5 0 1 0 7.5 7.5Z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/>
                </svg>
            </button>
            @unless (request()->routeIs('login'))
                <a href="{{ route('login') }}"
                   class="tombol-tekan inline-flex h-10 items-center rounded-lg bg-[#b8f048] px-4 text-sm font-semibold text-[#111111] hover:bg-[#a0d040] focus:outline-none focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#4a7a0a] dark:focus-visible:outline-[#b8f048]">
                    Masuk
                </a>
            @endunless
        </div>
    </div>
</header>
