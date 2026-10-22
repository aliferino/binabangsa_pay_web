{{-- Halaman depan. Berdiri sendiri, tidak memakai layout induk karena
     pengunjung di sini belum masuk. Base light mode, dark mode opsional
     lewat toggle di navbar (tersimpan di localStorage, kunci "tema").

     Metode bayar mengikuti brief web LSP: VIRTUAL ACCOUNT saja.
     Tunai dan QRIS milik brief desktop, tidak boleh muncul di sini. --}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>BinaBangsa Pay | Pembayaran SPP SMK Bina Bangsa</title>
    <meta name="description" content="BinaBangsa Pay menerbitkan virtual account per siswa untuk pembayaran SPP SMK Bina Bangsa, mencocokkan transaksi secara otomatis, dan menyusun laporan tunggakan.">
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
<body class="min-h-screen bg-white font-sans text-[#111111] antialiased dark:bg-[#111111] dark:text-[#e8ecf1]">

    {{-- Tekstur butiran halus di atas segalanya, tidak bisa diklik. --}}
    <div class="grain" aria-hidden="true"></div>

    @include('partials._navigation-web')

    {{-- Hero. Di ponsel dan tablet visual tampil LEBIH DULU, teks menyusul,
         karena kartu tagihan langsung menjelaskan produknya. Di layar lebar
         susunannya dibalik jadi dua kolom: teks kiri, visual kanan. --}}
    <section class="relative overflow-hidden">
        <div class="mx-auto flex max-w-6xl flex-col gap-8 px-4 pb-14 pt-10 sm:px-6 sm:gap-10 lg:grid lg:grid-cols-[1.25fr_0.75fr] lg:items-center lg:gap-10 lg:pb-20 lg:pt-16">

            {{-- Visual: kartu tagihan SPP sebagai SVG inline.
                 Bukan three.js: halaman ini Blade + Vite tanpa React, dan
                 bundle three.js sekitar 600 KB tidak sepadan untuk satu
                 ilustrasi statis. SVG memberi hasil sama tanpa tambahan byte. --}}
            <div class="order-1 mx-auto w-full max-w-[19rem] sm:max-w-[22rem] lg:order-2 lg:mx-0 lg:max-w-none">
                <svg viewBox="0 0 420 372" role="img" aria-labelledby="judul-visual-hero"
                     class="mx-auto h-auto w-full lg:max-w-none">
                    <title id="judul-visual-hero">Kartu tagihan SPP dengan nomor virtual account dan tanda lunas</title>

                    {{-- Kartu tagihan, sedikit dimiringkan. --}}
                    <g transform="rotate(-3 205 185)">
                        <rect x="60" y="72" width="290" height="212" rx="18"
                              class="fill-white stroke-[#e2e8dd] dark:fill-[#1b2026] dark:stroke-[#2a3038]" stroke-width="1.5"/>

                        {{-- Kepala kartu. --}}
                        <text x="84" y="104" font-family="'Instrument Sans', sans-serif" font-size="10.5" font-weight="600"
                              letter-spacing="1.4" class="fill-[#5b6659] dark:fill-[#8b95a1]">SPP OKTOBER 2026</text>
                        <circle cx="326" cy="100" r="4" class="fill-[#b8f048]"/>
                        <line x1="84" y1="122" x2="326" y2="122" stroke-dasharray="3 4"
                              class="stroke-[#e2e8dd] dark:stroke-[#2a3038]" stroke-width="1.5"/>

                        {{-- Identitas siswa. --}}
                        <text x="84" y="145" font-family="'Instrument Sans', sans-serif" font-size="8.5"
                              letter-spacing="1.1" class="fill-[#5b6659] dark:fill-[#8b95a1]">NAMA</text>
                        <text x="150" y="145" font-family="'Instrument Sans', sans-serif" font-size="11.5" font-weight="500"
                              class="fill-[#111111] dark:fill-[#e8ecf1]">Aulia Rahma</text>

                        <text x="84" y="169" font-family="'Instrument Sans', sans-serif" font-size="8.5"
                              letter-spacing="1.1" class="fill-[#5b6659] dark:fill-[#8b95a1]">KELAS</text>
                        <text x="150" y="169" font-family="'Instrument Sans', sans-serif" font-size="11.5" font-weight="500"
                              class="fill-[#111111] dark:fill-[#e8ecf1]">XII RPL 1</text>

                        <text x="84" y="193" font-family="'Instrument Sans', sans-serif" font-size="8.5"
                              letter-spacing="1.1" class="fill-[#5b6659] dark:fill-[#8b95a1]">JURUSAN</text>
                        <text x="150" y="193" font-family="'Instrument Sans', sans-serif" font-size="11.5" font-weight="500"
                              class="fill-[#111111] dark:fill-[#e8ecf1]">Rekayasa Perangkat Lunak</text>

                        <line x1="84" y1="210" x2="326" y2="210" stroke-dasharray="3 4"
                              class="stroke-[#e2e8dd] dark:stroke-[#2a3038]" stroke-width="1.5"/>

                        {{-- Satu-satunya cara bayar: virtual account. --}}
                        <text x="84" y="231" font-family="'Instrument Sans', sans-serif" font-size="8.5"
                              letter-spacing="1.1" class="fill-[#5b6659] dark:fill-[#8b95a1]">VIRTUAL ACCOUNT</text>
                        <text x="84" y="256" font-family="ui-monospace, 'Cascadia Mono', monospace" font-size="17" font-weight="700"
                              letter-spacing="1" class="fill-[#111111] dark:fill-[#e8ecf1]">8800012345</text>

                        {{-- Penanda lima digit terakhir sama dengan NIS, aturan brief. --}}
                        <text x="84" y="272" font-family="'Instrument Sans', sans-serif" font-size="8.5"
                              class="fill-[#5b6659] dark:fill-[#8b95a1]">5 digit terakhir sama dengan NIS</text>
                    </g>

                    {{-- Stempel lunas, menempel di sudut kanan atas. --}}
                    <g transform="rotate(7 330 52)">
                        <rect x="278" y="36" width="104" height="32" rx="16" class="fill-[#b8f048]"/>
                        <text x="330" y="56" text-anchor="middle" font-family="'Instrument Sans', sans-serif"
                              font-size="11.5" font-weight="700" letter-spacing="1.6" class="fill-[#111111]">LUNAS</text>
                    </g>

                    {{-- Koin nominal, menimpa sudut kiri bawah kartu. --}}
                    <circle cx="58" cy="278" r="26" class="fill-[#b8f048]"/>
                    <text x="58" y="284" text-anchor="middle" font-family="'Instrument Sans', sans-serif"
                          font-size="13" font-weight="700" class="fill-[#111111]">Rp</text>

                    {{-- Penanda transfer, mengambang di kanan bawah. --}}
                    <g transform="translate(238 300)">
                        <rect x="0" y="0" width="152" height="34" rx="10"
                              class="fill-white stroke-[#e2e8dd] dark:fill-[#1b2026] dark:stroke-[#2a3038]" stroke-width="1.5"/>
                        <circle cx="18" cy="17" r="3.5" class="fill-[#b8f048]"/>
                        <text x="30" y="21" font-family="'Instrument Sans', sans-serif" font-size="10.5" font-weight="500"
                              class="fill-[#5b6659] dark:fill-[#8b95a1]">Transfer ke VA</text>
                    </g>
                </svg>
            </div>

            {{-- Teks. --}}
            <div class="order-2 lg:order-1">
                <span class="inline-flex items-center gap-2 rounded-full border border-[#e2e8dd] bg-[#f6f8f4] px-3 py-1 text-xs font-medium text-[#5b6659] dark:border-[#2a3038] dark:bg-[#171c21] dark:text-[#8b95a1]">
                    <span class="h-1.5 w-1.5 rounded-full bg-[#b8f048]"></span>
                    Pembayaran SPP daring
                </span>

                <h1 class="mt-5 text-[2rem] font-bold leading-[1.08] tracking-tight text-[#111111] sm:mt-6 sm:text-[2.75rem] lg:text-[3.4rem] dark:text-[#e8ecf1]">
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

        {{-- Angka sistem: satu strip ramping di bawah hero, bukan kartu seragam. --}}
        <div class="mx-auto max-w-6xl px-4 pb-12 sm:px-6">
            <dl class="grid grid-cols-2 gap-px overflow-hidden rounded-xl border border-[#e2e8dd] bg-[#e2e8dd] sm:grid-cols-4 dark:border-[#2a3038] dark:bg-[#2a3038]">
                <div class="bg-white px-4 py-4 sm:px-5 dark:bg-[#1b2026]">
                    <dd class="angka-hitung text-2xl font-bold text-[#111111] dark:text-[#e8ecf1]" data-angka="1" data-sufiks="">0</dd>
                    <dt class="mt-1 text-xs leading-snug text-[#5b6659] dark:text-[#8b95a1]">Virtual account per siswa</dt>
                </div>
                <div class="bg-white px-4 py-4 sm:px-5 dark:bg-[#1b2026]">
                    <dd class="angka-hitung text-2xl font-bold text-[#111111] dark:text-[#e8ecf1]" data-angka="12" data-sufiks=" bulan">0</dd>
                    <dt class="mt-1 text-xs leading-snug text-[#5b6659] dark:text-[#8b95a1]">Tagihan per tahun ajaran</dt>
                </div>
                <div class="bg-white px-4 py-4 sm:px-5 dark:bg-[#1b2026]">
                    <dd class="angka-hitung text-2xl font-bold text-[#111111] dark:text-[#e8ecf1]" data-angka="5" data-sufiks=" digit">0</dd>
                    <dt class="mt-1 text-xs leading-snug text-[#5b6659] dark:text-[#8b95a1]">Akhir VA sama dengan NIS</dt>
                </div>
                <div class="bg-white px-4 py-4 sm:px-5 dark:bg-[#1b2026]">
                    <dd class="angka-hitung text-2xl font-bold text-[#111111] dark:text-[#e8ecf1]" data-angka="3" data-sufiks=" status">0</dd>
                    <dt class="mt-1 text-xs leading-snug text-[#5b6659] dark:text-[#8b95a1]">Belum bayar, lunas, tunggakan</dt>
                </div>
            </dl>
        </div>
    </section>

    {{-- Alur. Hanya virtual account, sesuai brief web. --}}
    <section id="alur" class="border-t border-[#e2e8dd] dark:border-[#2a3038]">
        <div class="mx-auto max-w-6xl px-4 py-16 sm:px-6 sm:py-20">
            <h2 class="text-2xl font-bold tracking-tight text-[#111111] sm:text-3xl dark:text-[#e8ecf1]">Alur pembayaran</h2>
            <p class="mt-3 max-w-xl text-sm leading-relaxed text-[#5b6659] dark:text-[#8b95a1]">
                Tiga langkah, dari tagihan terbit sampai statusnya berubah lunas.
            </p>

            <ol class="mt-10 grid gap-x-8 gap-y-10 md:grid-cols-3">
                <li class="border-t-2 border-[#b8f048] pt-5">
                    <span class="text-xs font-semibold tracking-wider text-[#4a7a0a] dark:text-[#b8f048]">01</span>
                    <p class="mt-2 text-base font-semibold text-[#111111] dark:text-[#e8ecf1]">Tagihan terbit</p>
                    <p class="mt-2 text-sm leading-relaxed text-[#5b6659] dark:text-[#8b95a1]">Sekolah menetapkan tarif per jurusan dan angkatan, lalu tagihan bulanan dibuat untuk tiap siswa.</p>
                </li>
                <li class="border-t-2 border-[#b8f048] pt-5">
                    <span class="text-xs font-semibold tracking-wider text-[#4a7a0a] dark:text-[#b8f048]">02</span>
                    <p class="mt-2 text-base font-semibold text-[#111111] dark:text-[#e8ecf1]">Transfer ke virtual account</p>
                    <p class="mt-2 text-sm leading-relaxed text-[#5b6659] dark:text-[#8b95a1]">Siswa atau orang tua transfer dari bank mana pun ke nomor virtual account milik siswa. Tanpa perlu datang ke sekolah.</p>
                </li>
                <li class="border-t-2 border-[#b8f048] pt-5">
                    <span class="text-xs font-semibold tracking-wider text-[#4a7a0a] dark:text-[#b8f048]">03</span>
                    <p class="mt-2 text-base font-semibold text-[#111111] dark:text-[#e8ecf1]">Otomatis tercatat lunas</p>
                    <p class="mt-2 text-sm leading-relaxed text-[#5b6659] dark:text-[#8b95a1]">Sistem mencocokkan transaksi dengan tagihan, mengubah status jadi lunas, dan mencatat riwayatnya.</p>
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

    <script>
        (function () {
            var html = document.documentElement;

            // Toggle tema: class="dark" di <html>, pilihan di localStorage ("tema").
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

            // Satu interaksi halus: angka statistik menghitung naik saat masuk viewport.
            var angka = document.querySelectorAll('.angka-hitung');
            if ('IntersectionObserver' in window && angka.length) {
                var pengamat = new IntersectionObserver(function (entri) {
                    entri.forEach(function (e) {
                        if (!e.isIntersecting) return;
                        pengamat.unobserve(e.target);
                        var el = e.target;
                        var target = parseInt(el.getAttribute('data-angka'), 10) || 0;
                        var sufiks = el.getAttribute('data-sufiks') || '';
                        var mulai = performance.now();
                        function langkah(t) {
                            var p = Math.min((t - mulai) / 900, 1);
                            p = 1 - Math.pow(1 - p, 3);
                            el.textContent = Math.round(target * p) + sufiks;
                            if (p < 1) requestAnimationFrame(langkah);
                        }
                        requestAnimationFrame(langkah);
                    });
                }, { threshold: 0.6 });
                angka.forEach(function (el) { pengamat.observe(el); });
            } else {
                angka.forEach(function (el) {
                    el.textContent = (el.getAttribute('data-angka') || '0') + (el.getAttribute('data-sufiks') || '');
                });
            }
        })();
    </script>
</body>
</html>