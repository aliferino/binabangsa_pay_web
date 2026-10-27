{{--
    Kerangka halaman simulasi publik. Berdiri sendiri, tanpa menu samping dan
    tanpa login, karena dipakai untuk latihan transfer dari sisi siswa.

    Variabel: $judul (opsional).
--}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex">
    <title>{{ $judul ?? 'Simulasi Transfer' }} | BinaBangsa Pay</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @include('layouts.partials._tema')
</head>
<body class="flex min-h-[100dvh] flex-col bg-latar-2 font-sans text-tinta antialiased dark:bg-gelap dark:text-teks-gelap">

    <header class="border-b border-garis bg-white dark:border-garis-gelap dark:bg-gelap-2">
        <div class="mx-auto flex w-full max-w-lg items-center justify-between gap-3 px-4 py-3">

            <a href="{{ route('beranda') }}" class="flex min-w-0 items-center gap-2.5">
                <img src="{{ asset('images/logo-smk-bina-bangsa-180.webp') }}" alt="Logo SMK Bina Bangsa"
                     class="h-8 w-8 shrink-0 rounded-full object-contain ring-1 ring-garis dark:ring-garis-gelap" width="32" height="32">
                <span class="truncate text-sm font-semibold tracking-tight text-tinta dark:text-teks-gelap">BinaBangsa Pay</span>
            </a>

            <div class="flex shrink-0 items-center gap-2">
                <a href="{{ route('beranda') }}"
                   class="text-xs font-medium text-redup hover:text-tinta dark:text-redup-gelap dark:hover:text-teks-gelap">
                    Kembali ke beranda
                </a>
                <button type="button" data-tombol-tema aria-pressed="false" aria-label="Ganti tema terang atau gelap"
                        class="rounded-lg border border-garis px-2.5 py-1.5 text-xs font-medium text-redup hover:bg-latar-2 hover:text-tinta dark:border-garis-gelap dark:text-redup-gelap dark:hover:bg-gelap dark:hover:text-teks-gelap">
                    <span data-label-tema>Mode gelap</span>
                </button>
            </div>

        </div>
    </header>

    <main class="mx-auto w-full max-w-lg flex-1 px-4 py-8">

        <p class="inline-block rounded-full bg-red-700 px-3 py-1 text-[11px] font-bold uppercase tracking-widest text-white">
            Mock Gateway Lokal
        </p>
        <p class="mt-3 text-xs leading-relaxed text-redup dark:text-redup-gelap">
            Halaman ini memakai mock payment gateway lokal, bukan Midtrans atau Xendit. Tidak ada uang yang benar-benar berpindah.
        </p>

        <div class="mt-5">
            @yield('isi')
        </div>

    </main>

    {{-- Pesan sukses atau gagal dari controller (mis. nominal tidak cocok)
         tampil sebagai toast, sama seperti halaman admin. --}}
    @include('layouts.partials._toast')

    <footer class="border-t border-garis bg-white px-4 py-4 text-center text-xs text-redup dark:border-garis-gelap dark:bg-gelap-2 dark:text-redup-gelap">
        SMK Bina Bangsa, simulasi pembayaran Virtual Account
    </footer>

</body>
</html>