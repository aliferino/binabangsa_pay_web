{{-- Halaman depan. Berdiri sendiri, tidak memakai layout induk karena
     pengunjung di sini belum masuk. Base light mode, dark mode opsional
     lewat toggle di navbar (tersimpan di localStorage, kunci "tema").

     Metode bayar mengikuti brief web LSP: VIRTUAL ACCOUNT saja.
     Tunai dan QRIS milik brief desktop, tidak boleh muncul di sini.

     Adegan 3D di hero dimuat dari resources/js/hero.js. Kalau peramban tidak
     sanggup WebGL, kanvasnya kosong dan kartu virtual account tetap tampil. --}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>BinaBangsa Pay | Pembayaran SPP SMK Bina Bangsa</title>
    <meta name="description" content="BinaBangsa Pay menerbitkan virtual account per siswa untuk pembayaran SPP SMK Bina Bangsa, mencocokkan transaksi secara otomatis, dan menyimpan riwayat pembayaran.">
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/hero.js', 'resources/js/landing.js'])

    @include('layouts.partials._transisi')
    @include('layouts.partials._tema')
</head>
<body class="min-h-screen bg-white font-sans text-[#111111] antialiased dark:bg-[#111111] dark:text-[#e8ecf1]">

    {{-- Tekstur butiran halus di atas segalanya, tidak bisa diklik. --}}
    <div class="grain" aria-hidden="true"></div>

    @include('layouts.partials._navigation-web')

    {{-- Hero. Setinggi satu layar dikurangi bilah atas, jadi tulisan dan visual
         pas di tengah dan section berikutnya tidak mengintip. Di ponsel visual
         tampil LEBIH DULU, teks menyusul; di layar lebar teks kiri, visual kanan. --}}
    <section class="relative overflow-hidden">
        <div class="mx-auto flex min-h-[calc(100svh-4rem)] max-w-6xl flex-col justify-center gap-8 px-4 py-10 sm:gap-10 sm:px-6 lg:grid lg:grid-cols-[1.15fr_0.85fr] lg:items-center lg:gap-12 lg:py-12">

            {{-- Visual: kartu siswa tiga dimensi yang mengikuti kursor. Klik
                 kartunya memercikkan koin neon ke luar lalu jatuh ke bawah.
                 Digambar three.js. SVG di bawahnya cuma cadangan kalau peramban
                 tidak sanggup WebGL, dan disembunyikan begitu adegan jalan. --}}
            <div class="order-1 mx-auto w-full max-w-[30rem] lg:order-2 lg:mx-0 lg:max-w-none">
                <div class="relative aspect-[16/10] w-full">
                    <canvas id="hero-3d" aria-label="Kartu siswa tiga dimensi, klik untuk memercikkan koin"
                            class="absolute inset-0 h-full w-full"></canvas>

                    {{-- Cadangan kalau WebGL tidak tersedia: kartu yang sama
                         digambar sebagai SVG statis. --}}
                    <svg id="hero-cadangan" viewBox="0 0 420 300" role="img" aria-labelledby="judul-visual-hero"
                         class="pointer-events-none absolute inset-0 h-full w-full">
                        <title id="judul-visual-hero">Kartu siswa dengan nomor virtual account</title>

                        <g transform="rotate(-2 210 150)">
                            <defs>
                                <linearGradient id="neonKartu" x1="0" y1="0" x2="1" y2="1">
                                    <stop offset="0" stop-color="#d8f878"/>
                                    <stop offset="0.5" stop-color="#b8f048"/>
                                    <stop offset="1" stop-color="#9ad42c"/>
                                </linearGradient>
                                <clipPath id="potongKartu">
                                    <rect x="66" y="44" width="288" height="200" rx="18"/>
                                </clipPath>
                                <linearGradient id="pitaNeon" x1="0" y1="0" x2="1" y2="0">
                                    <stop offset="0" stop-color="#ffffff" stop-opacity="0"/>
                                    <stop offset="0.45" stop-color="#ffffff" stop-opacity="0.34"/>
                                    <stop offset="1" stop-color="#ffffff" stop-opacity="0.17"/>
                                </linearGradient>
                            </defs>

                            <rect x="66" y="44" width="288" height="200" rx="18" fill="url(#neonKartu)"/>

                            <g clip-path="url(#potongKartu)">
                                <path d="M40 210C120 140 170 250 250 205 300 178 320 110 370 88L370 148C320 170 300 238 250 265 170 310 120 200 40 270Z"
                                      fill="url(#pitaNeon)"/>
                                <path d="M40 140C120 70 170 180 250 135 300 108 320 40 370 18L370 58C320 80 300 148 250 175 170 220 120 110 40 200Z"
                                      fill="url(#pitaNeon)" opacity="0.6"/>
                            </g>

                            <path d="M84 109a26 26 0 0 1 0 -33M96 115a42 42 0 0 1 0 -44M108 121a58 58 0 0 1 0 -55M120 127a74 74 0 0 1 0 -66"
                                  fill="none" stroke="#111111" stroke-width="1.8" stroke-linecap="round" opacity="0.85"/>

                            <g transform="translate(302 73) scale(0.333)">
                                <path d="M37.60 8.02 C45.51 8.02 53.42 8.02 61.56 8.02 C61.56 8.91 61.56 9.81 61.56 10.73 C61.45 10.74 61.45 10.74 60.86 10.78 C57.20 11.11 57.20 11.11 55.94 12.19 C55.00 13.45 54.76 14.84 54.48 16.35 C54.60 16.35 54.71 16.35 54.84 16.34 C60.67 16.16 67.25 16.83 71.85 20.81 C74.05 22.92 75.46 25.26 75.58 28.36 C75.59 28.68 75.59 29.01 75.59 29.34 C75.58 29.51 75.58 29.68 75.58 29.86 C75.56 31.26 75.36 32.52 74.90 33.85 C74.83 34.06 74.76 34.26 74.70 34.47 C74.04 36.26 72.99 37.62 71.77 39.06 C71.66 39.20 71.54 39.34 71.42 39.48 C69.81 41.26 67.49 42.50 65.36 43.57 C65.22 43.64 65.08 43.71 64.94 43.78 C64.33 44.08 63.91 44.27 63.23 44.27 C63.23 44.41 63.23 44.55 63.23 44.69 C63.40 44.71 63.57 44.73 63.74 44.75 C64.64 44.93 65.42 45.26 66.25 45.64 C66.41 45.71 66.58 45.79 66.75 45.86 C72.36 48.44 77.44 52.33 79.69 58.23 C80.98 62.17 80.76 66.52 79.06 70.31 C76.87 74.54 73.35 77.39 69.06 79.27 C68.95 79.32 68.95 79.32 68.38 79.58 C67.12 80.10 65.85 80.46 64.52 80.77 C64.38 80.80 64.24 80.83 64.09 80.87 C61.15 81.49 58.25 81.62 55.26 81.61 C54.87 81.61 54.48 81.61 54.09 81.61 C53.04 81.61 51.99 81.61 50.94 81.60 C49.84 81.60 48.74 81.60 47.64 81.60 C45.56 81.60 43.48 81.60 41.40 81.59 C39.03 81.59 36.66 81.59 34.29 81.58 C29.43 81.58 24.56 81.57 19.69 81.56 C19.69 80.60 19.69 79.64 19.69 78.65 C19.96 78.62 20.24 78.60 20.53 78.58 C25.60 78.13 25.60 78.13 27.10 76.70 C28.26 75.32 28.26 73.58 28.26 71.87 C28.26 71.67 28.26 71.47 28.26 71.26 C28.26 70.59 28.26 69.91 28.26 69.24 C28.26 68.76 28.26 68.27 28.27 67.79 C28.27 66.48 28.27 65.17 28.27 63.85 C28.27 63.03 28.27 62.21 28.27 61.39 C28.28 59.12 28.28 56.85 28.28 54.57 C28.28 54.43 28.28 54.28 28.28 54.13 C28.28 53.99 28.28 53.84 28.28 53.69 C28.28 53.40 28.28 53.10 28.28 52.81 C28.28 52.73 28.28 52.73 28.28 52.36 C28.28 49.99 28.28 47.62 28.29 45.25 C28.29 42.81 28.30 40.37 28.30 37.94 C28.30 36.57 28.30 35.20 28.30 33.83 C28.31 32.55 28.31 31.26 28.30 29.97 C28.30 29.50 28.30 29.03 28.31 28.56 C28.31 27.91 28.31 27.27 28.31 26.62 C28.31 26.44 28.31 26.25 28.31 26.06 C28.30 24.13 27.90 22.00 26.56 20.52 C24.69 19.09 21.92 19.19 19.69 19.06 C19.69 18.17 19.69 17.27 19.69 16.35 C27.94 16.35 36.19 16.35 44.69 16.35 C44.12 12.95 44.12 12.95 43.01 11.71 C41.35 10.75 39.48 10.63 37.60 10.52 C37.60 9.70 37.60 8.87 37.60 8.02 Z  M39.86 19.45 C39.93 19.45 39.93 19.45 40.30 19.45 C40.80 19.44 41.29 19.44 41.79 19.44 C42.14 19.44 42.48 19.44 42.83 19.44 C43.56 19.44 44.28 19.44 45.01 19.44 C45.93 19.44 46.86 19.44 47.78 19.43 C48.50 19.43 49.21 19.43 49.93 19.43 C50.27 19.43 50.61 19.43 50.95 19.43 C52.74 19.42 54.51 19.47 56.29 19.66 C56.36 19.67 56.36 19.67 56.75 19.71 C59.63 20.05 62.20 21.26 64.05 23.54 C65.95 26.21 66.50 29.23 66.05 32.47 C65.36 35.91 63.48 38.44 60.69 40.48 C58.77 41.74 56.76 42.69 54.48 43.02 C54.48 36.08 54.48 29.13 54.48 21.98 C51.25 21.98 48.02 21.98 44.69 21.98 C44.69 29.47 44.69 36.97 44.69 44.69 C43.73 44.76 42.76 44.82 41.77 44.90 C41.29 46.00 40.81 47.10 40.31 48.23 C44.48 47.93 48.63 47.58 52.77 46.97 C52.88 46.96 52.88 46.96 53.42 46.88 C53.86 46.80 54.29 46.72 54.72 46.62 C56.26 46.29 57.10 46.39 58.42 47.24 C58.58 47.34 58.74 47.43 58.91 47.53 C60.36 48.42 61.72 49.42 63.02 50.52 C63.12 50.60 63.12 50.60 63.62 51.02 C67.15 54.06 69.49 58.17 70.10 62.81 C70.35 66.56 69.85 69.61 67.34 72.53 C64.65 75.36 60.86 77.15 56.98 77.60 C56.15 77.60 55.33 77.60 54.48 77.60 C54.41 68.46 54.34 59.32 54.27 49.90 C53.38 50.03 53.38 50.03 48.85 50.73 C48.17 50.76 48.17 50.76 44.69 50.94 C44.69 59.74 44.69 68.54 44.69 77.60 C42.69 77.60 41.29 77.32 39.83 75.91 C37.61 73.45 38.17 69.60 38.18 66.52 C38.18 66.01 38.18 65.50 38.18 64.99 C38.18 63.75 38.18 62.51 38.18 61.27 C38.18 59.83 38.18 58.38 38.18 56.94 C38.18 54.37 38.18 51.80 38.19 49.22 C38.19 46.72 38.19 44.23 38.19 41.73 C38.19 41.58 38.19 41.42 38.19 41.26 C38.19 41.11 38.19 40.95 38.19 40.80 C38.19 37.95 38.19 35.11 38.19 32.26 C38.19 31.25 38.19 30.25 38.19 29.24 C38.19 27.89 38.20 26.54 38.20 25.18 C38.20 24.69 38.20 24.19 38.20 23.69 C38.20 23.02 38.20 22.34 38.20 21.67 C38.20 21.47 38.20 21.27 38.20 21.06 C38.21 19.44 38.26 19.45 39.86 19.45 Z  M45.28 83.83 C45.37 83.83 45.37 83.83 45.80 83.83 C45.90 83.83 45.90 83.83 46.38 83.83 C46.59 83.83 46.80 83.83 47.01 83.83 C47.22 83.83 47.44 83.83 47.66 83.83 C48.34 83.84 49.02 83.84 49.70 83.84 C50.16 83.84 50.62 83.84 51.08 83.84 C52.21 83.85 53.35 83.85 54.48 83.85 C54.50 83.95 54.50 83.95 54.60 84.41 C54.65 84.66 54.71 84.90 54.76 85.15 C54.79 85.27 54.79 85.27 54.92 85.88 C55.23 87.02 55.71 87.88 56.63 88.62 C58.24 89.30 59.83 89.37 61.56 89.48 C61.56 90.37 61.56 91.27 61.56 92.19 C53.66 92.19 45.75 92.19 37.60 92.19 C37.60 91.36 37.60 90.54 37.60 89.69 C37.84 89.67 38.07 89.65 38.31 89.63 C42.16 89.26 42.16 89.26 43.52 87.97 C44.27 86.70 44.53 85.52 44.69 84.06 C44.90 83.85 44.90 83.85 45.28 83.83 Z" fill="#111111" fill-rule="evenodd"/>
                            </g>

                            <text x="86" y="166" font-family="ui-monospace, 'Cascadia Mono', monospace" font-size="22"
                                  font-weight="700" letter-spacing="1.6" class="fill-[#111111]">7770 1001 3333</text>

                            <text x="86" y="200" font-family="'Instrument Sans', sans-serif" font-size="6"
                                  letter-spacing="1" fill="#111111" opacity="0.62">NAMA PEMILIK</text>
                            <text x="86" y="216" font-family="'Instrument Sans', sans-serif" font-size="12" font-weight="700"
                                  class="fill-[#111111]">Jin Kazama</text>

                            <text x="232" y="200" font-family="'Instrument Sans', sans-serif" font-size="6"
                                  letter-spacing="1" fill="#111111" opacity="0.62">KELAS</text>
                            <text x="232" y="216" font-family="'Instrument Sans', sans-serif" font-size="12" font-weight="700"
                                  class="fill-[#111111]">XII RPL 1</text>

                            <rect x="302" y="147" width="30" height="22" rx="4" fill="#f2f6ea" stroke="#111111" stroke-width="1.4"/>
                            <path d="M302 158h30M312 147v22M322 147v22" stroke="#111111" stroke-width="0.9"/>
                        </g>

                        <g transform="rotate(6 330 38)">
                            <rect x="280" y="22" width="100" height="30" rx="15" class="fill-[#111111]"/>
                            <text x="330" y="41" text-anchor="middle" font-family="'Instrument Sans', sans-serif"
                                  font-size="11" font-weight="700" letter-spacing="1.6" class="fill-[#b8f048]">LUNAS</text>
                        </g>
                    </svg>
                </div>
            </div>

            {{-- Teks. --}}
            <div class="order-2 lg:order-1">
                <h1 class="text-[2rem] font-bold leading-[1.08] tracking-tight text-[#111111] sm:text-[2.75rem] lg:text-[3.4rem] dark:text-[#e8ecf1]">
                    Bayar SPP dari mana saja,
                    <span class="text-[#4a7a0a] dark:text-[#b8f048]">tanpa antre di sekolah</span>.
                </h1>

                <p class="mt-5 max-w-xl text-[15px] leading-relaxed text-[#5b6659] sm:mt-6 sm:text-base dark:text-[#8b95a1]">
                    Setiap siswa punya satu virtual account. Transfer dari bank mana pun,
                    sistem mencocokkan sendiri, dan status tagihan langsung berubah.
                    Tidak perlu kirim bukti transfer.
                </p>

                <div class="mt-7 flex flex-wrap items-center gap-x-6 gap-y-3 sm:mt-9">
                    <a href="{{ route('login') }}"
                       class="tombol-tekan inline-flex items-center gap-2 rounded-lg bg-[#b8f048] px-6 py-3 text-sm font-semibold text-[#111111] hover:bg-[#a0d040] focus:outline-none focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#4a7a0a] dark:focus-visible:outline-[#b8f048]">
                        Masuk ke akun
                        <svg viewBox="0 0 16 16" fill="none" aria-hidden="true" class="h-4 w-4">
                            <path d="M3 8h9.5M9 4.5 12.5 8 9 11.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </a>
                    <a href="#alur" class="inline-flex items-center gap-1.5 text-sm font-medium text-[#111111] underline decoration-[#e2e8dd] underline-offset-4 transition hover:text-[#4a7a0a] hover:decoration-[#4a7a0a] dark:text-[#e8ecf1] dark:decoration-[#2a3038] dark:hover:text-[#b8f048] dark:hover:decoration-[#b8f048]">
                        Lihat cara kerjanya
                        <svg viewBox="0 0 16 16" fill="none" aria-hidden="true" class="h-3.5 w-3.5">
                            <path d="M8 3.5v9M4.5 9 8 12.5 11.5 9" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </section>

    {{-- Alur. Satu aliran bernomor di atas garis putus-putus, bukan tiga kartu
         seragam. Hanya virtual account, sesuai brief web. --}}
    <section id="alur" class="border-t border-[#e2e8dd] dark:border-[#2a3038]">
        <div class="mx-auto max-w-6xl px-4 py-16 sm:px-6 sm:py-20">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                <h2 class="text-2xl font-bold tracking-tight text-[#111111] sm:text-3xl dark:text-[#e8ecf1]">Alur pembayaran</h2>
                <p class="text-sm text-[#5b6659] sm:text-right dark:text-[#8b95a1]">
                    Tagihan terbit sampai lunas, tanpa kirim bukti transfer.
                </p>
            </div>

            <ol class="mt-10 grid gap-y-8 sm:grid-cols-3 sm:gap-x-10">
                <li class="relative border-t border-dashed border-[#d5ddc9] pt-4 dark:border-[#3a424e]">
                    <span class="absolute -top-2.5 left-0 bg-white pr-2 font-mono text-xs font-semibold tracking-widest text-[#4a7a0a] dark:bg-[#111111] dark:text-[#b8f048]">01</span>
                    <p class="text-base font-semibold text-[#111111] dark:text-[#e8ecf1]">Tagihan terbit</p>
                    <p class="mt-1.5 text-sm leading-relaxed text-[#5b6659] dark:text-[#8b95a1]">Sekolah menetapkan tarif per jurusan dan angkatan, tagihan bulanan dibuat untuk tiap siswa.</p>
                </li>
                <li class="relative border-t border-dashed border-[#d5ddc9] pt-4 dark:border-[#3a424e]">
                    <span class="absolute -top-2.5 left-0 bg-white pr-2 font-mono text-xs font-semibold tracking-widest text-[#4a7a0a] dark:bg-[#111111] dark:text-[#b8f048]">02</span>
                    <p class="text-base font-semibold text-[#111111] dark:text-[#e8ecf1]">Transfer ke virtual account</p>
                    <p class="mt-1.5 text-sm leading-relaxed text-[#5b6659] dark:text-[#8b95a1]">Dari bank mana pun ke nomor milik siswa. Tidak perlu datang ke sekolah.</p>
                </li>
                <li class="relative border-t border-dashed border-[#d5ddc9] pt-4 dark:border-[#3a424e]">
                    <span class="absolute -top-2.5 left-0 bg-white pr-2 font-mono text-xs font-semibold tracking-widest text-[#4a7a0a] dark:bg-[#111111] dark:text-[#b8f048]">03</span>
                    <p class="text-base font-semibold text-[#111111] dark:text-[#e8ecf1]">Otomatis tercatat lunas</p>
                    <p class="mt-1.5 text-sm leading-relaxed text-[#5b6659] dark:text-[#8b95a1]">Sistem mencocokkan transaksi dengan tagihan, status berubah, riwayat tersimpan.</p>
                </li>
            </ol>
        </div>
    </section>

    {{-- Ajakan masuk. --}}
    <section class="border-t border-[#e2e8dd] bg-[#f6f8f4] dark:border-[#2a3038] dark:bg-[#171c21]">
        <div class="mx-auto max-w-6xl px-4 py-14 text-center sm:px-6 sm:py-16">
            <h2 class="text-2xl font-bold tracking-tight text-[#111111] dark:text-[#e8ecf1]">Sudah punya akun?</h2>
            <p class="mx-auto mt-3 max-w-md text-sm leading-relaxed text-[#5b6659] dark:text-[#8b95a1]">
                Akun dan virtual account dibuatkan oleh sekolah. Hubungi bagian administrasi kalau belum menerima akses.
            </p>
            <a href="{{ route('login') }}"
               class="tombol-tekan mt-7 inline-block rounded-lg bg-[#b8f048] px-7 py-3 text-sm font-semibold text-[#111111] hover:bg-[#a0d040] focus:outline-none focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#4a7a0a] dark:focus-visible:outline-[#b8f048]">
                Masuk
            </a>
        </div>
    </section>

    <footer class="border-t border-[#e2e8dd] bg-white px-4 py-6 text-center text-xs text-[#5b6659] sm:px-6 dark:border-[#2a3038] dark:bg-[#111111] dark:text-[#8b95a1]">
        SMK Bina Bangsa, Kabupaten Sidoarjo. Pembayaran SPP lewat virtual account.
    </footer>

</body>
</html>