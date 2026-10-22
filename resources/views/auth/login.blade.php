{{-- Halaman login. Berdiri sendiri, tidak memakai layout induk.
     Palet dan toggle tema mengikuti halaman depan: light default,
     dark opsional lewat localStorage dengan kunci "tema". --}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Masuk | BinaBangsa Pay</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    {{-- Pasang tema tersimpan SEBELUM body dilukis supaya tidak ada flash. --}}
    <script>
        (function () {
            try {
                if (localStorage.getItem('tema') === 'dark') {
                    document.documentElement.classList.add('dark');
                }
            } catch (e) { /* localStorage bisa terblokir; tetap light. */ }
        })();
    </script>
</head>
<body class="flex min-h-screen flex-col bg-white font-sans text-[#111111] antialiased dark:bg-[#111111] dark:text-[#e8ecf1]">

    <div class="grain" aria-hidden="true"></div>

    {{-- Bilah atas tipis: kembali ke beranda di kiri, toggle tema di kanan. --}}
    <header class="border-b border-[#e2e8dd] dark:border-[#2a3038]">
        <div class="mx-auto flex max-w-6xl items-center justify-between px-4 py-3 sm:px-6">
            <a href="{{ route('beranda') }}" class="flex items-center gap-2.5">
                <img src="{{ asset('images/logo-smk-bina-bangsa-180.webp') }}"
                     alt="Logo SMK Bina Bangsa" width="28" height="28"
                     class="h-7 w-7 rounded-full ring-1 ring-[#e2e8dd] dark:ring-[#2a3038]">
                <span class="text-sm font-semibold tracking-tight">BinaBangsa Pay</span>
            </a>
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
        </div>
    </header>

    {{-- Dua kolom di layar lebar: keterangan di kiri, form di kanan.
         Di ponsel dan tablet hanya form yang tampil, keterangannya disembunyikan
         supaya pengguna langsung sampai ke kolom isian. --}}
    <main class="mx-auto grid w-full max-w-6xl flex-1 gap-12 px-4 py-10 sm:px-6 sm:py-14 lg:grid-cols-[1fr_0.85fr] lg:content-center lg:items-start lg:gap-16 lg:py-20">

        <div class="hidden lg:block">
            <span class="inline-flex items-center gap-2 rounded-full border border-[#e2e8dd] bg-[#f6f8f4] px-3 py-1 text-xs font-medium text-[#5b6659] dark:border-[#2a3038] dark:bg-[#171c21] dark:text-[#8b95a1]">
                <span class="h-1.5 w-1.5 rounded-full bg-[#b8f048]"></span>
                Pembayaran SPP daring
            </span>

            <h1 class="mt-6 text-[2.75rem] font-bold leading-[1.08] tracking-tight">
                Masuk untuk melihat
                <span class="text-[#4a7a0a] dark:text-[#b8f048]">tagihan dan riwayat</span>.
            </h1>

            <p class="mt-6 max-w-md text-base leading-relaxed text-[#5b6659] dark:text-[#8b95a1]">
                Satu akun untuk melihat virtual account, status tagihan per bulan,
                dan riwayat pembayaran SPP.
            </p>

            <dl class="mt-10 max-w-md space-y-4 border-t border-[#e2e8dd] pt-6 dark:border-[#2a3038]">
                <div class="flex gap-3">
                    <span class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-[#b8f048]"></span>
                    <div>
                        <dt class="text-sm font-semibold">Virtual account pribadi</dt>
                        <dd class="mt-0.5 text-sm leading-relaxed text-[#5b6659] dark:text-[#8b95a1]">Nomornya hanya untuk satu siswa, tidak bisa tertukar.</dd>
                    </div>
                </div>
                <div class="flex gap-3">
                    <span class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-[#b8f048]"></span>
                    <div>
                        <dt class="text-sm font-semibold">Status tagihan jelas</dt>
                        <dd class="mt-0.5 text-sm leading-relaxed text-[#5b6659] dark:text-[#8b95a1]">Belum bayar, lunas, atau tunggakan, per bulan.</dd>
                    </div>
                </div>
                <div class="flex gap-3">
                    <span class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-[#b8f048]"></span>
                    <div>
                        <dt class="text-sm font-semibold">Tanpa kirim bukti transfer</dt>
                        <dd class="mt-0.5 text-sm leading-relaxed text-[#5b6659] dark:text-[#8b95a1]">Sistem mencocokkan transaksi sendiri.</dd>
                    </div>
                </div>
            </dl>
        </div>

        <div class="w-full max-w-sm justify-self-center lg:justify-self-end">

            <div class="mb-6 lg:hidden">
                <h1 class="text-2xl font-bold tracking-tight">Masuk ke akun</h1>
                <p class="mt-1.5 text-sm text-[#5b6659] dark:text-[#8b95a1]">Sistem Pembayaran SPP SMK Bina Bangsa</p>
            </div>

            <div class="rounded-xl border border-[#d5ddc9] bg-white p-6 shadow-sm dark:border-[#3a424e] dark:bg-[#1b2026] dark:shadow-none">

                <h2 class="hidden text-lg font-bold tracking-tight lg:block">Masuk ke akun</h2>

                {{-- Pesan gagal dari validasi login. --}}
                @if ($errors->any())
                    <div class="mt-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 dark:border-red-900 dark:bg-red-950 dark:text-red-200" role="alert">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('login.proses') }}" class="mt-5 space-y-4">
                    @csrf

                    <div>
                        <label for="username" class="mb-1.5 block text-sm font-medium">Username</label>
                        <input type="text" name="username" id="username"
                               value="{{ old('username') }}"
                               required autofocus autocomplete="username"
                               class="w-full rounded-lg border border-[#d5ddc9] bg-white px-3 py-2.5 text-sm transition placeholder:text-[#8b95a1] focus:border-[#4a7a0a] focus:outline-none focus:ring-2 focus:ring-[#b8f048]/40 dark:border-[#6b7784] dark:bg-[#111111] dark:focus:border-[#b8f048]">
                        @error('username')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password" class="mb-1.5 block text-sm font-medium">Password</label>
                        <input type="password" name="password" id="password"
                               required autocomplete="current-password"
                               class="w-full rounded-lg border border-[#d5ddc9] bg-white px-3 py-2.5 text-sm transition placeholder:text-[#8b95a1] focus:border-[#4a7a0a] focus:outline-none focus:ring-2 focus:ring-[#b8f048]/40 dark:border-[#6b7784] dark:bg-[#111111] dark:focus:border-[#b8f048]">
                        @error('password')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <button type="submit"
                            class="w-full rounded-lg bg-[#b8f048] px-4 py-2.5 text-sm font-semibold text-[#111111] transition hover:bg-[#a0d040] focus:outline-none focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#4a7a0a] dark:focus-visible:outline-[#b8f048]">
                        Masuk
                    </button>
                </form>
            </div>

            <p class="mt-5 text-center text-xs leading-relaxed text-[#5b6659] lg:text-left dark:text-[#8b95a1]">
                Akun dibuatkan oleh sekolah.
                <a href="{{ route('beranda') }}" class="font-medium underline decoration-[#e2e8dd] underline-offset-4 transition hover:text-[#4a7a0a] hover:decoration-[#4a7a0a] dark:decoration-[#2a3038] dark:hover:text-[#b8f048] dark:hover:decoration-[#b8f048]">Kembali ke beranda</a>
            </p>
        </div>
    </main>

    <footer class="border-t border-[#e2e8dd] px-4 py-6 text-center text-xs text-[#5b6659] sm:px-6 dark:border-[#2a3038] dark:text-[#8b95a1]">
        SMK Bina Bangsa, Kabupaten Sidoarjo. Pembayaran SPP lewat virtual account.
    </footer>

    <script>
        (function () {
            var html = document.documentElement;
            var tombol = document.getElementById('tombol-tema');

            function terapkan(gelap) {
                html.classList.toggle('dark', gelap);
                if (tombol) tombol.setAttribute('aria-pressed', gelap ? 'true' : 'false');
            }

            terapkan(html.classList.contains('dark'));

            if (tombol) {
                tombol.addEventListener('click', function () {
                    var gelap = !html.classList.contains('dark');
                    terapkan(gelap);
                    try { localStorage.setItem('tema', gelap ? 'dark' : 'light'); } catch (e) { }
                });
            }
        })();
    </script>
</body>
</html>