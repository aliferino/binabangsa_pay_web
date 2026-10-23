{{-- Halaman login. Berdiri sendiri, tidak memakai layout induk.
     Palet dan toggle tema mengikuti halaman depan: light default,
     dark opsional lewat localStorage dengan kunci "tema". --}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Masuk | BinaBangsa Pay</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
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

    @include('layouts.partials._navigation-web')

    {{-- Dua kolom di layar lebar: keterangan di kiri, form di kanan.
         Di ponsel dan tablet hanya form yang tampil, keterangannya disembunyikan
         supaya pengguna langsung sampai ke kolom isian. --}}
    <main class="mx-auto grid w-full max-w-6xl flex-1 content-center gap-12 px-4 py-10 sm:px-6 sm:py-14 lg:content-center lg:grid-cols-[1fr_0.85fr] lg:items-start lg:gap-16 lg:py-20">

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

            {{-- Animasi kecil: uang berjalan dari kartu VA ke status lunas.
                 CSS keyframes saja, tanpa pustaka. Berhenti sendiri kalau
                 pengguna memilih kurangi gerak lewat prefers-reduced-motion. --}}
            <div class="mt-10 max-w-md overflow-hidden rounded-xl border border-[#e2e8dd] bg-[#f6f8f4] dark:border-[#2a3038] dark:bg-[#171c21]">
                <svg viewBox="0 18 340 76" class="h-auto w-full" role="img" aria-labelledby="judul-animasi">
                    <title id="judul-animasi">Transfer masuk ke virtual account lalu status tagihan berubah lunas</title>

                    {{-- Kartu VA sumber. --}}
                    <g class="fill-white stroke-[#d5ddc9] dark:fill-[#1b2026] dark:stroke-[#3a424e]">
                        <rect x="16" y="34" width="112" height="52" rx="8" fill="inherit" stroke-width="1.2"/>
                    </g>
                    <text x="28" y="55" font-family="'Instrument Sans', sans-serif" font-size="7.5" letter-spacing="0.9"
                          class="fill-[#5b6659] dark:fill-[#8b95a1]">VIRTUAL ACCOUNT</text>
                    <text x="28" y="72" font-family="ui-monospace, monospace" font-size="10" font-weight="700"
                          class="fill-[#111111] dark:fill-[#e8ecf1]">8800012345</text>

                    {{-- Jalur transfer: garis putus-putus yang mengalir. --}}
                    <line x1="132" y1="60" x2="208" y2="60" stroke-width="1.4" stroke-dasharray="4 4"
                          class="anim-alir stroke-[#b8f048]"/>

                    {{-- Koin yang berjalan di sepanjang jalur. --}}
                    <circle r="4" cy="60" class="anim-koin fill-[#b8f048]"/>

                    {{-- Status tujuan: berdenyut lalu menetap. --}}
                    <g class="anim-stempel">
                        <rect x="212" y="42" width="112" height="36" rx="18" class="fill-[#b8f048]"/>
                        <path d="M228 60.5l5 5 11-11" fill="none" stroke="#111111" stroke-width="2.4"
                              stroke-linecap="round" stroke-linejoin="round" class="anim-centang"/>
                        <text x="252" y="64" font-family="'Instrument Sans', sans-serif" font-size="10" font-weight="700"
                              letter-spacing="1.2" class="fill-[#111111]">LUNAS</text>
                    </g>
                </svg>
            </div>

        </div>

        <div class="w-full max-w-sm justify-self-center lg:justify-self-end">

            <div class="mb-6 lg:hidden">
                <h1 class="text-2xl font-bold tracking-tight">Masuk ke akun</h1>
                <p class="mt-1.5 text-sm text-[#5b6659] dark:text-[#8b95a1]">Sistem Pembayaran SPP SMK Bina Bangsa</p>
            </div>

            {{-- Kartu form mengikuti motif kartu tagihan di halaman depan: kepala
                 bergaris putus-putus, label monospace huruf besar, dan satu titik
                 neon. Bukan kartu putih polos dengan dua kotak isian. --}}
            <div class="overflow-hidden rounded-xl border border-[#d5ddc9] bg-white dark:border-[#3a424e] dark:bg-[#1b2026]">

                <div class="flex items-center justify-between border-b border-dashed border-[#d5ddc9] px-6 py-3 dark:border-[#3a424e]">
                    <span class="font-mono text-[10px] font-semibold uppercase tracking-[0.16em] text-[#5b6659] dark:text-[#8b95a1]">Akses akun</span>
                    <span class="h-1.5 w-1.5 rounded-full bg-[#b8f048]"></span>
                </div>

                <div class="p-6">

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
                            <label for="username" class="mb-1.5 block font-mono text-[10px] font-semibold uppercase tracking-[0.16em] text-[#5b6659] dark:text-[#8b95a1]">Username</label>
                            <input type="text" name="username" id="username"
                                   value="{{ old('username') }}"
                                   required autofocus autocomplete="username"
                                   placeholder="NIS atau username"
                                   class="h-11 w-full rounded-lg border bg-white px-3 font-mono text-sm transition-colors duration-150 placeholder:font-sans placeholder:text-[#8b95a1] border-[#d5ddc9] dark:border-[#6b7784] dark:bg-[#111111] hover:border-[#b9c4ae] dark:hover:border-[#8b95a1] focus:border-[#4a7a0a] focus:shadow-[0_0_0_3px_rgba(74,122,10,0.12)] focus:outline-none dark:focus:border-[#b8f048] dark:focus:shadow-[0_0_0_3px_rgba(184,240,72,0.16)]">
                            @error('username')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="password" class="mb-1.5 block font-mono text-[10px] font-semibold uppercase tracking-[0.16em] text-[#5b6659] dark:text-[#8b95a1]">Password</label>
                            <div class="relative">
                                <input type="password" name="password" id="password"
                                required autocomplete="current-password"
                                class="h-11 w-full rounded-lg border bg-white pl-3 pr-11 font-mono text-sm transition-colors duration-150 placeholder:font-sans placeholder:text-[#8b95a1] border-[#d5ddc9] dark:border-[#6b7784] dark:bg-[#111111] hover:border-[#b9c4ae] dark:hover:border-[#8b95a1] focus:border-[#4a7a0a] focus:shadow-[0_0_0_3px_rgba(74,122,10,0.12)] focus:outline-none dark:focus:border-[#b8f048] dark:focus:shadow-[0_0_0_3px_rgba(184,240,72,0.16)]">
                                <button type="button" id="tombol-lihat"
                                aria-pressed="false" aria-controls="password" aria-label="Tampilkan password"
                                class="absolute inset-y-0 right-0 flex w-10 items-center justify-center rounded-r-lg text-[#5b6659] transition hover:text-[#4a7a0a] focus:outline-none focus-visible:ring-1 focus-visible:ring-inset focus-visible:ring-[#4a7a0a] dark:text-[#8b95a1] dark:hover:text-[#b8f048] dark:focus-visible:ring-[#b8f048]">
                                {{-- Mata terbuka: tampil selama password tersembunyi. --}}
                                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true" class="mata-terbuka h-[18px] w-[18px]">
                                <path d="M2.6 12S6 5.6 12 5.6 21.4 12 21.4 12 18 18.4 12 18.4 2.6 12 2.6 12Z" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                                <circle cx="12" cy="12" r="2.9" stroke="currentColor" stroke-width="1.6"/>
                                </svg>
                                {{-- Mata dicoret: tampil selama password terlihat. --}}
                                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true" class="mata-tertutup hidden h-[18px] w-[18px]">
                                <path d="M2.6 12S6 5.6 12 5.6 21.4 12 21.4 12 18 18.4 12 18.4 2.6 12 2.6 12Z" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                                <circle cx="12" cy="12" r="2.9" stroke="currentColor" stroke-width="1.6"/>
                                <path d="M3.6 3.6l16.8 16.8" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
                                </svg>
                                </button>
                            </div>
                            @error('password')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <button type="submit"
                                class="tombol-tekan flex w-full items-center justify-center rounded-lg bg-[#b8f048] px-4 py-2.5 text-sm font-semibold text-[#111111] hover:bg-[#a0d040] focus:outline-none focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#4a7a0a] dark:focus-visible:outline-[#b8f048]">
                            Masuk
                        </button>
                    </form>
                </div>
            </div>

            <p class="mt-5 text-center text-xs leading-relaxed text-[#5b6659] dark:text-[#8b95a1]">
                Akun dibuatkan oleh sekolah.
                <a href="{{ route('beranda') }}" class="font-medium underline decoration-[#e2e8dd] underline-offset-4 transition hover:text-[#4a7a0a] hover:decoration-[#4a7a0a] dark:decoration-[#2a3038] dark:hover:text-[#b8f048] dark:hover:decoration-[#b8f048]">Kembali ke beranda</a>
            </p>
        </div>
    </main>

    <footer class="border-t border-[#e2e8dd] bg-white px-4 py-6 text-center text-xs text-[#5b6659] sm:px-6 dark:border-[#2a3038] dark:bg-[#111111] dark:text-[#8b95a1]">
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

            // Tombol mata: ganti ikon dan jenis input, tanpa menulis teks.
            var lihat = document.getElementById('tombol-lihat');
            var sandi = document.getElementById('password');

            if (lihat && sandi) {
                var mataBuka = lihat.querySelector('.mata-terbuka');
                var mataTutup = lihat.querySelector('.mata-tertutup');

                lihat.addEventListener('click', function () {
                    var sedangTerlihat = sandi.type === 'text';
                    sandi.type = sedangTerlihat ? 'password' : 'text';
                    mataBuka.classList.toggle('hidden', sedangTerlihat === false);
                    mataTutup.classList.toggle('hidden', sedangTerlihat === true);
                    lihat.setAttribute('aria-pressed', sedangTerlihat ? 'false' : 'true');
                    lihat.setAttribute('aria-label', sedangTerlihat ? 'Tampilkan password' : 'Sembunyikan password');
                });
            }
        })();
    </script>
</body>
</html>