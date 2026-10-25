{{--
    Kerangka halaman galat. Berdiri sendiri, tidak memakai layout induk,
    sebab halaman 500 bisa dipanggil saat layout itu sendiri yang rusak.

    Angka galat dibuat tiga dimensi dari tumpukan text-shadow satu arah
    (kiri atas ke kanan bawah), ditambah satu lapis tipis terang di sisi
    atas sebagai pantulan cahaya. Tidak ada gambar, tidak ada pustaka.

    Variabel: $kode, $judul, $pesan, $saran (opsional).
--}}
@php
    $kode = $kode ?? '404';
    $judul = $judul ?? 'Terjadi kesalahan';
    $pesan = $pesan ?? 'Permintaan Anda tidak bisa diproses.';
    $saran = $saran ?? null;

    // Warna tumpukan 3D: dari dekat permukaan huruf sampai ujung bayangan.
    // Hijau neon di permukaan, makin tua ke arah bayangan.
    $dalam = [154, 209, 58];    // #9ad13a, tepat di belakang huruf
    $luar = [74, 122, 10];      // #4a7a0a, ujung terjauh
    $langkah = 14;              // jumlah lapis, menentukan tebal dimensi

    $tumpukan = [];
    for ($i = 1; $i <= $langkah; $i++) {
        $t = $i / $langkah;
        $r = (int) round($dalam[0] + ($luar[0] - $dalam[0]) * $t);
        $g = (int) round($dalam[1] + ($luar[1] - $dalam[1]) * $t);
        $b = (int) round($dalam[2] + ($luar[2] - $dalam[2]) * $t);
        $tumpukan[] = sprintf('%dpx %dpx 0 rgb(%d,%d,%d)', $i, $i, $r, $g, $b);
    }
    // Bayangan lepas di ujung, supaya angka tidak mengambang.
    $tumpukan[] = '16px 18px 26px rgba(74,122,10,0.22)';
    $bayangan = implode(', ', $tumpukan);
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>{{ $kode }} | BinaBangsa Pay</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script>
        (function () {
            try {
                if (localStorage.getItem('tema') === 'dark') {
                    document.documentElement.classList.add('dark');
                }
            } catch (e) { }
        })();
    </script>
    <style>
        /*
            Angka 3D. Diekstrusi ke kanan bawah, dengan satu lapis tipis
            terang di kiri atas supaya sisi yang kena cahaya terbaca.
        */
        .angka-3d {
            font-variant-numeric: tabular-nums;
            font-weight: 800;
            letter-spacing: -0.04em;
            line-height: 0.85;
            /* Ukuran mengikuti lebar layar, bukan piksel tetap. Ditulis di sini,
               bukan lewat kelas Tailwind, sebab clamp() berkoma tidak selalu
               ikut terkompilasi. */
            font-size: clamp(5.5rem, 26vw, 13rem);
            color: #b8f048;
            text-shadow:
                -1px -1px 0 rgba(255, 255, 255, 0.85),
                {{ $bayangan }};
            user-select: none;
        }

        .dark .angka-3d {
            color: #b8f048;
            text-shadow:
                -1px -1px 0 rgba(255, 255, 255, 0.18),
                {{ $bayangan }};
        }
    </style>
</head>
<body class="flex min-h-[100dvh] flex-col bg-white font-sans text-[#111111] antialiased dark:bg-[#111111] dark:text-[#e8ecf1]">

    <div class="grain" aria-hidden="true"></div>

    <main class="mx-auto flex w-full max-w-3xl flex-1 flex-col items-center justify-center px-5 py-14 text-center sm:px-8">

        {{-- Angka 3D. Ukurannya mengikuti lebar layar, bukan piksel tetap.
             Kode galat sudah terbaca dari sini, tidak perlu label terpisah. --}}
        <p class="angka-3d" aria-hidden="true">{{ $kode }}</p>

        <h1 class="mt-10 text-2xl font-bold tracking-tight sm:text-3xl">{{ $judul }}</h1>

        <p class="mt-3 max-w-md text-sm leading-relaxed text-[#5b6659] dark:text-[#8b95a1]">{{ $pesan }}</p>

        @if ($saran)
            <div class="mt-6 w-full max-w-md rounded-xl border border-dashed border-[#d5ddc9] px-5 py-4 text-left dark:border-[#3a424e]">
                <p class="font-mono text-[10px] font-semibold uppercase tracking-[0.16em] text-[#5b6659] dark:text-[#8b95a1]">Yang bisa dilakukan</p>
                <p class="mt-2 text-sm leading-relaxed">{{ $saran }}</p>
            </div>
        @endif

        <div class="mt-12 flex flex-col gap-3 sm:flex-row">
            <a href="/"
               class="tombol-tekan inline-flex items-center justify-center rounded-lg bg-[#b8f048] px-5 py-2.5 text-sm font-semibold text-[#111111] hover:bg-[#a0d040] focus:outline-none focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#4a7a0a] dark:focus-visible:outline-[#b8f048]">
                Kembali ke beranda
            </a>
            <button type="button" onclick="location.reload()"
                    class="inline-flex items-center justify-center rounded-lg border border-[#d5ddc9] px-5 py-2.5 text-sm font-semibold transition-colors hover:border-[#b9c4ae] hover:bg-[#f6f8f4] focus:outline-none focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#4a7a0a] dark:border-[#3a424e] dark:hover:border-[#8b95a1] dark:hover:bg-[#171c21] dark:focus-visible:outline-[#b8f048]">
                Muat ulang
            </button>
        </div>

    </main>

    <footer class="border-t border-[#e2e8dd] px-4 py-6 text-center text-xs text-[#5b6659] dark:border-[#2a3038] dark:text-[#8b95a1]">
        SMK Bina Bangsa, Kabupaten Sidoarjo. Pembayaran SPP lewat virtual account.
    </footer>

</body>
</html>