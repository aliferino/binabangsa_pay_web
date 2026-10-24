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
<body class="flex min-h-[100dvh] flex-col bg-latar-2 font-sans text-tinta antialiased dark:bg-gelap dark:text-teks-gelap">

    {{-- Bilah atas untuk layar kecil, berisi tombol buka menu. --}}
    <header class="sticky top-0 z-30 flex h-16 items-center justify-between border-b border-garis bg-white px-4 lg:hidden dark:border-garis-gelap dark:bg-gelap-2">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5">
            <img src="{{ asset('images/logo-smk-bina-bangsa-180.webp') }}" alt="Logo SMK Bina Bangsa"
                 class="h-9 w-9 shrink-0 rounded-lg object-contain" width="36" height="36">
            <span class="text-sm font-semibold tracking-tight text-tinta dark:text-teks-gelap">BinaBangsa Pay</span>
        </a>
        <button type="button" id="tombol-menu"
                class="rounded-lg border border-garis px-3 py-1.5 text-sm font-medium text-redup hover:bg-latar-2 hover:text-tinta dark:border-garis-gelap dark:text-redup-gelap dark:hover:bg-gelap dark:hover:text-teks-gelap"
                aria-controls="menu-samping" aria-expanded="false">
            Menu
        </button>
    </header>

    <div class="flex min-w-0 flex-1 flex-col lg:flex-row">

        {{-- Menu samping. Isinya dipilih oleh layouts.partials._sidebar sesuai role.
             Menempel setinggi layar dan menggulir sendiri, tidak ikut halaman. --}}
        <aside id="menu-samping"
               class="hidden w-full shrink-0 bg-white lg:sticky lg:top-0 lg:block lg:h-screen lg:w-64 lg:self-start lg:overflow-hidden dark:bg-gelap-2">
            @include('layouts.partials._sidebar')
        </aside>

        {{-- Kolom isi halaman. Footer ikut kolom ini, bukan selebar halaman. --}}
        <div class="flex min-w-0 flex-1 flex-col">

            <main class="min-w-0 flex-1 bg-latar-2 px-4 py-6 lg:px-8 lg:py-8 dark:bg-gelap">
                {{-- Seluruh isi halaman ada di dalam #isi-halaman supaya bisa
                     diganti tanpa muat ulang saat menu samping diklik. --}}
                <div id="isi-halaman" class="mx-auto max-w-6xl"
                     @if (old('_modal')) data-modal-gagal="{{ old('_modal') }}" data-mode-gagal="{{ old('_mode') }}" data-id-gagal="{{ old('_id') }}" @endif>

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

                    {{-- Pesan sukses atau gagal dari controller tampil sebagai toast. --}}
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

    @include('layouts.partials._toast')
    @include('layouts.partials._konfirmasi')

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