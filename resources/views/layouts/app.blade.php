{{-- Layout induk. Semua halaman memakai berkas ini lewat @extends('layouts.app'). --}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('judul', 'Beranda') | SMK Bina Bangsa</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100 font-sans text-slate-800 antialiased">

    {{-- Bilah atas untuk layar kecil, berisi tombol buka menu. --}}
    <header class="sticky top-0 z-30 flex items-center justify-between border-b border-slate-200 bg-white px-4 py-3 lg:hidden">
        <div class="flex items-center gap-2">
            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-teal-700 text-sm font-bold text-white">BB</span>
            <span class="text-sm font-semibold text-slate-800">BinaBangsa Pay</span>
        </div>
        <button type="button" id="tombol-menu"
                class="rounded-lg border border-slate-300 px-3 py-1.5 text-sm font-medium text-slate-700 hover:bg-slate-50"
                aria-controls="menu-samping" aria-expanded="false">
            Menu
        </button>
    </header>

    <div class="lg:flex">

        {{-- Menu samping. Di layar kecil disembunyikan dulu, dibuka lewat tombol Menu. --}}
        <aside id="menu-samping"
               class="hidden w-full shrink-0 border-b border-slate-200 bg-white lg:block lg:min-h-screen lg:w-64 lg:border-b-0 lg:border-r">
            @include('partials._navigation')
        </aside>

        {{-- Isi halaman. --}}
        <main class="min-w-0 flex-1 px-4 py-6 lg:px-8 lg:py-8">
            <div class="mx-auto max-w-7xl">

                {{-- Judul halaman dan keterangan singkat. --}}
                <div class="mb-6 flex flex-wrap items-end justify-between gap-3">
                    <div>
                        <h1 class="text-xl font-bold text-slate-900 sm:text-2xl">@yield('judul', 'Beranda')</h1>
                        @hasSection('keterangan')
                            <p class="mt-1 text-sm text-slate-500">@yield('keterangan')</p>
                        @endif
                    </div>
                    @hasSection('aksi')
                        <div class="flex flex-wrap items-center gap-2">@yield('aksi')</div>
                    @endif
                </div>

                {{-- Pesan sukses atau gagal dari controller. --}}
                @include('partials._flash')

                @yield('isi')
            </div>
        </main>
    </div>

    <footer class="border-t border-slate-200 bg-white px-4 py-4 text-center text-xs text-slate-500 lg:px-8">
        SMK Bina Bangsa, Sistem Pembayaran SPP
    </footer>

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
