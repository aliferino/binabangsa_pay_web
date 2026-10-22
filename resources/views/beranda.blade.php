{{-- Halaman depan. Berdiri sendiri, tidak memakai layout induk karena
     pengunjung di sini belum masuk. --}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>BinaBangsa Pay | Sistem Pembayaran SPP SMK Bina Bangsa</title>
    <meta name="description" content="BinaBangsa Pay mencatat pembayaran SPP tunai dan QRIS, menerbitkan virtual account, dan menyusun laporan tunggakan untuk SMK Bina Bangsa.">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-white font-sans text-slate-800 antialiased">

    {{-- Bilah atas: merek di kiri, tombol masuk di kanan. --}}
    <header class="sticky top-0 z-30 border-b border-slate-200 bg-white/90 backdrop-blur">
        <div class="mx-auto flex max-w-6xl items-center justify-between px-4 py-3 sm:px-6">
            <a href="{{ route('beranda') }}" class="flex items-center gap-2.5">
                <img src="{{ asset('images/logo-smk-bina-bangsa-180.webp') }}"
                     alt="Logo SMK Bina Bangsa" width="36" height="36"
                     class="h-9 w-9 rounded-full ring-1 ring-slate-200">
                <span class="text-sm font-semibold tracking-tight text-slate-900">BinaBangsa Pay</span>
            </a>
            <a href="{{ route('login') }}"
               class="rounded-lg bg-teal-700 px-4 py-1.5 text-sm font-semibold text-white transition hover:bg-teal-800 focus:outline-none focus:ring-2 focus:ring-teal-300">
                Masuk
            </a>
        </div>
    </header>

    {{-- Hero. --}}
    <section class="mx-auto grid max-w-6xl items-center gap-12 px-4 pb-20 pt-14 sm:px-6 lg:grid-cols-[1.05fr_0.95fr] lg:gap-16 lg:pb-24 lg:pt-20">
        <div>
            <span class="inline-flex items-center gap-2 rounded-full border border-teal-100 bg-teal-50 px-3 py-1 text-xs font-medium text-teal-800">
                <span class="h-1.5 w-1.5 rounded-full bg-teal-600"></span>
                Sistem Pembayaran SPP
            </span>

            <h1 class="mt-6 text-4xl font-bold leading-[1.08] tracking-tight text-slate-900 sm:text-5xl">
                SPP sekolah rapi,
                <span class="text-teal-700">tercatat, dan bisa ditelusuri</span>.
            </h1>

            <p class="mt-6 max-w-lg text-base leading-relaxed text-slate-600">
                BinaBangsa Pay mencatat pembayaran tunai dan QRIS, menerbitkan virtual account
                per siswa, dan menyusun laporan tunggakan. Satu tempat untuk admin, teller,
                kepala sekolah, dan siswa.
            </p>

            <div class="mt-9 flex flex-wrap items-center gap-x-6 gap-y-3">
                <a href="{{ route('login') }}"
                   class="inline-flex items-center gap-2 rounded-lg bg-teal-700 px-6 py-3 text-sm font-semibold text-white transition hover:bg-teal-800 focus:outline-none focus:ring-2 focus:ring-teal-300">
                    Masuk ke akun
                    <svg viewBox="0 0 16 16" fill="none" aria-hidden="true" class="h-4 w-4">
                        <path d="M3 8h9.5M9 4.5 12.5 8 9 11.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </a>
                <a href="#alur" class="inline-flex items-center gap-1.5 text-sm font-medium text-slate-700 underline decoration-slate-300 underline-offset-4 transition hover:text-teal-700 hover:decoration-teal-700">
                    Lihat cara kerjanya
                    <svg viewBox="0 0 16 16" fill="none" aria-hidden="true" class="h-3.5 w-3.5">
                        <path d="M8 3.5v9M4.5 9 8 12.5 11.5 9" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </a>
            </div>

            <dl class="mt-11 grid max-w-lg grid-cols-3 gap-6 border-t border-slate-200 pt-6">
                <div>
                    <dt class="text-xs leading-snug text-slate-500">Metode bayar</dt>
                    <dd class="mt-1 text-sm font-semibold text-slate-900">Tunai &amp; QRIS</dd>
                </div>
                <div>
                    <dt class="text-xs leading-snug text-slate-500">Virtual account</dt>
                    <dd class="mt-1 text-sm font-semibold text-slate-900">Per siswa</dd>
                </div>
                <div>
                    <dt class="text-xs leading-snug text-slate-500">Tunggakan</dt>
                    <dd class="mt-1 text-sm font-semibold text-slate-900">Terpantau</dd>
                </div>
            </dl>
        </div>

        {{-- Lambang sekolah. Satu cincin tipis supaya tidak mengambang. --}}
        <div class="flex justify-center lg:justify-end">
            <div class="rounded-full border border-slate-200 p-6 sm:p-8">
                <img src="{{ asset('images/logo-smk-bina-bangsa.webp') }}"
                     alt="Lambang SMK Bina Bangsa, Kabupaten Sidoarjo"
                     width="512" height="512"
                     class="h-56 w-56 rounded-full ring-1 ring-slate-200 sm:h-64 sm:w-64 lg:h-72 lg:w-72">
            </div>
        </div>
    </section>

    {{-- Peran pengguna. --}}
    <section class="border-t border-slate-200 bg-slate-50">
        <div class="mx-auto max-w-6xl px-4 py-20 sm:px-6">
            <h2 class="text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">Satu sistem, empat peran</h2>
            <p class="mt-3 max-w-xl text-sm leading-relaxed text-slate-600">
                Setiap peran hanya melihat menu yang menjadi haknya.
            </p>

            <div class="mt-10 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-xl border border-slate-200 bg-white p-5">
                    <p class="text-sm font-semibold text-slate-900">Admin</p>
                    <p class="mt-2 text-sm leading-relaxed text-slate-600">Kelola siswa, kelas, jurusan, tarif SPP, dan akun pengguna.</p>
                </div>
                <div class="rounded-xl border border-slate-200 bg-white p-5">
                    <p class="text-sm font-semibold text-slate-900">Teller</p>
                    <p class="mt-2 text-sm leading-relaxed text-slate-600">Catat pembayaran tunai dan QRIS, terbitkan virtual account.</p>
                </div>
                <div class="rounded-xl border border-slate-200 bg-white p-5">
                    <p class="text-sm font-semibold text-slate-900">Kepala sekolah</p>
                    <p class="mt-2 text-sm leading-relaxed text-slate-600">Pantau laporan pembayaran dan tunggakan tanpa bisa mengubah data.</p>
                </div>
                <div class="rounded-xl border border-slate-200 bg-white p-5">
                    <p class="text-sm font-semibold text-slate-900">Siswa</p>
                    <p class="mt-2 text-sm leading-relaxed text-slate-600">Lihat tagihan, riwayat pembayaran, dan status virtual account sendiri.</p>
                </div>
            </div>
        </div>
    </section>

    {{-- Alur. --}}
    <section id="alur" class="border-t border-slate-200">
        <div class="mx-auto max-w-6xl px-4 py-20 sm:px-6">
            <h2 class="text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">Alur pembayaran</h2>
            <p class="mt-3 max-w-xl text-sm leading-relaxed text-slate-600">
                Tiga langkah, dari tagihan terbit sampai tercatat lunas.
            </p>

            <ol class="mt-10 grid gap-x-8 gap-y-10 md:grid-cols-3">
                <li class="border-t-2 border-teal-700 pt-5">
                    <span class="text-xs font-semibold tracking-wider text-teal-700">01</span>
                    <p class="mt-2 text-base font-semibold text-slate-900">Tagihan terbit</p>
                    <p class="mt-2 text-sm leading-relaxed text-slate-600">Admin menetapkan tarif per jurusan dan angkatan, lalu tagihan dibuat untuk tiap siswa.</p>
                </li>
                <li class="border-t-2 border-teal-700 pt-5">
                    <span class="text-xs font-semibold tracking-wider text-teal-700">02</span>
                    <p class="mt-2 text-base font-semibold text-slate-900">Siswa membayar</p>
                    <p class="mt-2 text-sm leading-relaxed text-slate-600">Bayar tunai di teller, pindai QRIS, atau transfer ke virtual account miliknya.</p>
                </li>
                <li class="border-t-2 border-teal-700 pt-5">
                    <span class="text-xs font-semibold tracking-wider text-teal-700">03</span>
                    <p class="mt-2 text-base font-semibold text-slate-900">Tercatat dan dilaporkan</p>
                    <p class="mt-2 text-sm leading-relaxed text-slate-600">Pembayaran masuk ke riwayat, sisa tunggakan diperbarui, laporan siap dicetak.</p>
                </li>
            </ol>
        </div>
    </section>

    {{-- Ajakan masuk. --}}
    <section class="border-t border-slate-200 bg-slate-50">
        <div class="mx-auto max-w-6xl px-4 py-16 text-center sm:px-6">
            <h2 class="text-2xl font-bold tracking-tight text-slate-900">Sudah punya akun?</h2>
            <p class="mx-auto mt-3 max-w-md text-sm leading-relaxed text-slate-600">
                Akun dibuatkan oleh admin sekolah. Hubungi bagian administrasi kalau belum menerima akses.
            </p>
            <a href="{{ route('login') }}"
               class="mt-7 inline-block rounded-lg bg-teal-700 px-7 py-3 text-sm font-semibold text-white transition hover:bg-teal-800 focus:outline-none focus:ring-2 focus:ring-teal-300">
                Masuk
            </a>
        </div>
    </section>

    <footer class="border-t border-slate-200 bg-white px-4 py-6 text-center text-xs text-slate-500 sm:px-6">
        SMK Bina Bangsa, Kabupaten Sidoarjo. Sistem Pembayaran SPP.
    </footer>
</body>
</html>