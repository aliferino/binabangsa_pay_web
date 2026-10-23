{{-- Layout induk. Semua halaman memakai berkas ini lewat @extends('layouts.app'). --}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('judul', 'Beranda') | SMK Bina Bangsa</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-latar-2 font-sans text-tinta antialiased dark:bg-gelap dark:text-teks-gelap">

    {{-- Bilah atas untuk layar kecil, berisi tombol buka menu. --}}
    <header class="sticky top-0 z-30 flex items-center justify-between border-b border-garis bg-white px-4 py-3 lg:hidden dark:border-garis-gelap dark:bg-gelap-2">
        <div class="flex items-center gap-2">
            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-neon text-sm font-bold text-tinta">BB</span>
            <span class="text-sm font-semibold text-tinta dark:text-teks-gelap">BinaBangsa Pay</span>
        </div>
        <button type="button" id="tombol-menu"
                class="rounded-lg border border-garis px-3 py-1.5 text-sm font-medium text-redup hover:bg-latar-2 hover:text-tinta dark:border-garis-gelap dark:text-redup-gelap dark:hover:bg-gelap dark:hover:text-teks-gelap"
                aria-controls="menu-samping" aria-expanded="false">
            Menu
        </button>
    </header>

    <div class="lg:flex lg:items-start">

        {{-- Menu samping. Isinya dipilih oleh layouts.partials._sidebar sesuai role.
             Menempel setinggi layar dan menggulir sendiri, tidak ikut halaman. --}}
        <aside id="menu-samping"
               class="hidden w-full shrink-0 border-b border-garis lg:sticky lg:top-0 lg:block lg:h-screen lg:w-64 lg:self-start lg:overflow-hidden lg:border-b-0 lg:border-r dark:border-garis-gelap">
            @include('layouts.partials._sidebar')
        </aside>

        {{-- Kolom isi halaman. Footer ikut kolom ini, bukan selebar halaman. --}}
        <div class="flex min-w-0 flex-1 flex-col">

            <main class="min-w-0 flex-1 px-4 py-6 lg:px-8 lg:py-8">
                <div class="mx-auto max-w-6xl">

                    {{-- Judul halaman dan keterangan singkat. --}}
                    <div class="mb-6 flex flex-wrap items-end justify-between gap-3">
                        <div class="min-w-0">
                            <h1 class="text-xl font-bold tracking-tight text-tinta sm:text-2xl dark:text-teks-gelap">@yield('judul', 'Beranda')</h1>
                            @hasSection('keterangan')
                                <p class="mt-1 text-sm text-redup dark:text-redup-gelap">@yield('keterangan')</p>
                            @endif
                        </div>
                        @hasSection('aksi')
                            <div class="flex flex-wrap items-center gap-2">@yield('aksi')</div>
                        @endif
                    </div>

                    {{-- Pesan sukses atau gagal dari controller. --}}
                    @include('layouts.partials._flash')

                    @yield('isi')
                </div>
            </main>

            <footer class="border-t border-garis bg-white dark:border-garis-gelap dark:bg-gelap-2">
                <div class="mx-auto max-w-6xl px-4 py-4 text-center text-xs text-redup lg:px-8 dark:text-redup-gelap">
                    SMK Bina Bangsa, Sistem Pembayaran SPP
                </div>
            </footer>
        </div>
    </div>

    <script>
        // Buka dan tutup menu samping di layar kecil.
        (function () {
            var tombol = document.getElementById('tombol-menu');
            var menu = document.getElementById('menu-samping');

            if (!tombol || !menu) {
                return;
            }

            tombol.addEventListener('click', function () {
                var terbuka = !menu.classList.contains('hidden');
                menu.classList.toggle('hidden', terbuka);
                tombol.setAttribute('aria-expanded', String(!terbuka));
            });
        })();
    </script>
</body>
</html>