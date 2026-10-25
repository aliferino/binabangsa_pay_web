{{--
    Pengaturan integrasi gateway. Empat bagian: status kredensial (baca saja),
    tunnel untuk memberi Midtrans alamat publik, uji notifikasi masuk, dan
    kunci API yang bisa diedit.

    Semua nilai datang dari controller. Nama variabel jangan diubah.
--}}
@extends('layouts.app')

@section('judul', 'Integrasi Gateway')
@section('keterangan', 'Kelola koneksi payment gateway dan uji notifikasi masuk.')

@section('isi')

    @php
        $selKredensial = 'bg-white px-5 py-4 dark:bg-gelap-2';
        $kelasLabel = 'text-[11px] font-medium text-redup dark:text-redup-gelap';
        $kelasBantuan = 'mt-1 text-[11px] leading-relaxed text-redup dark:text-redup-gelap';
        $kelasIsian = 'mt-1.5 w-full rounded-lg border border-garis bg-white px-3 py-2.5 font-mono text-sm text-tinta outline-none focus:border-neon-teks dark:border-garis-gelap dark:bg-gelap dark:text-teks-gelap dark:focus:border-neon';

        $kedaluwarsa = ! empty($tunnel['kedaluwarsa']) ? \Carbon\Carbon::parse($tunnel['kedaluwarsa']) : null;
        $sudahLewat = $kedaluwarsa && $kedaluwarsa->isPast();

        // WINA menambah penyimpanan kunci dalam berkas JSON. Variabelnya dipakai
        // dengan nilai cadangan supaya halaman ini tetap tampil walau controller
        // belum mengirimnya.
        $sumberKunci = $sumberKunci ?? 'env';
        $jsonPratinjau = $jsonPratinjau ?? [];

        // Tombol kunci hanya digambar kalau rutenya sudah ada. Tanpa ini,
        // halaman gagal total karena route() melempar galat. Hapus penjagaan
        // ini setelah WINA mendaftarkan integrasi.kunci.simpan dan .reset.
        $adaRouteKunci = \Illuminate\Support\Facades\Route::has('integrasi.kunci.simpan')
            && \Illuminate\Support\Facades\Route::has('integrasi.kunci.reset');
    @endphp

    {{-- Bagian A: status kredensial. Baca saja, tidak ada yang bisa diubah dari sini. --}}
    <section class="mb-5 overflow-hidden rounded-xl border border-garis bg-white dark:border-garis-gelap dark:bg-gelap-2">

        <div class="grid gap-px bg-garis sm:grid-cols-3 dark:bg-garis-gelap">

            <div class="{{ $selKredensial }}">
                <p class="text-[11px] font-medium text-redup dark:text-redup-gelap">Midtrans</p>
                <p class="mt-1 text-base font-bold tracking-tight {{ $midtransSiap ? 'text-neon-teks dark:text-neon' : 'text-red-700 dark:text-red-400' }}">
                    {{ $midtransSiap ? 'Siap' : 'Belum diisi' }}
                </p>
                <p class="mt-1 font-mono text-[11px] leading-relaxed text-redup dark:text-redup-gelap">{{ $sandiTersamar }}</p>
                <p class="mt-0.5 text-[11px] text-redup dark:text-redup-gelap">{{ $midtransProduksi ? 'Produksi' : 'Sandbox' }}</p>
            </div>

            <div class="{{ $selKredensial }}">
                <p class="text-[11px] font-medium text-redup dark:text-redup-gelap">Xendit</p>
                <p class="mt-1 text-base font-bold tracking-tight {{ $xenditSiap ? 'text-neon-teks dark:text-neon' : 'text-red-700 dark:text-red-400' }}">
                    {{ $xenditSiap ? 'Siap' : 'Belum diisi' }}
                </p>
                <p class="mt-0.5 text-[11px] text-redup dark:text-redup-gelap">jalur cadangan</p>
            </div>

            <div class="{{ $selKredensial }}">
                <p class="text-[11px] font-medium text-redup dark:text-redup-gelap">Mode</p>
                <p class="mt-1 text-base font-bold tracking-tight text-tinta dark:text-teks-gelap">
                    {{ $midtransProduksi ? 'Produksi' : 'Sandbox' }}
                </p>
                <p class="mt-0.5 text-[11px] leading-snug text-redup dark:text-redup-gelap">notifikasi Midtrans tidak diverifikasi ulang ke server</p>
            </div>

        </div>
    </section>

    <p class="mb-5 text-xs text-redup dark:text-redup-gelap">
        Kunci disimpan di <span class="font-mono">storage/app/integration.json</span>, bukan di database, dan tidak pernah ditampilkan penuh di halaman ini. Berkas itu tidak ikut masuk git.
    </p>

    {{-- Bagian B: tunnel. Bagian terpenting, karena tanpa alamat publik
         Midtrans tidak bisa mengirim notifikasi ke localhost. --}}
    <section class="mb-5 rounded-xl border border-garis bg-white dark:border-garis-gelap dark:bg-gelap-2">

        <div class="border-b border-garis px-5 py-4 dark:border-garis-gelap">
            <h2 class="text-sm font-semibold text-tinta dark:text-teks-gelap">Tunnel</h2>

            @if ($tunnel)
                <p class="mt-1 flex items-center gap-2 text-xs text-redup dark:text-redup-gelap">
                    <span class="h-2 w-2 shrink-0 rounded-full bg-neon" aria-hidden="true"></span>
                    Tunnel aktif
                </p>
            @else
                <p class="mt-1 text-xs leading-relaxed text-redup dark:text-redup-gelap">
                    Midtrans tidak bisa menghubungi localhost. Nyalakan tunnel supaya aplikasi punya alamat publik.
                </p>
            @endif
        </div>

        <div class="px-5 py-4">

            @if ($tunnel)

                <p class="text-[11px] font-medium text-redup dark:text-redup-gelap">URL callback</p>

                <div class="mt-1.5 flex flex-col gap-2 sm:flex-row sm:items-center">
                    <p class="min-w-0 flex-1 overflow-x-auto rounded-lg bg-latar-2 px-3 py-2.5 font-mono text-sm text-tinta dark:bg-gelap dark:text-teks-gelap">{{ $urlCallback }}</p>
                    <button type="button" data-salin="{{ $urlCallback }}"
                            class="shrink-0 rounded-lg border border-garis px-4 py-2 text-sm font-semibold text-tinta hover:bg-latar-2 dark:border-garis-gelap dark:text-teks-gelap dark:hover:bg-gelap">
                        Salin
                    </button>
                </div>

                <p class="mt-1.5 text-xs {{ $sudahLewat ? 'text-red-700 dark:text-red-400' : 'text-redup dark:text-redup-gelap' }}">
                    @if ($sudahLewat)
                        Sudah kedaluwarsa, nyalakan ulang.
                    @else
                        Berlaku sampai {{ $kedaluwarsa->format('H:i') }}.
                    @endif
                </p>

                <form method="POST" action="{{ route('integrasi.tunnel.matikan') }}" class="mt-4">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            class="rounded-lg border border-garis bg-white px-4 py-2 text-sm font-semibold text-tinta hover:bg-latar-2 dark:border-garis-gelap dark:bg-gelap-2 dark:text-teks-gelap dark:hover:bg-gelap">
                        Matikan tunnel
                    </button>
                </form>

                <ol class="mt-4 list-decimal space-y-1 pl-4 text-xs leading-relaxed text-redup dark:text-redup-gelap">
                    <li>Salin URL di atas</li>
                    <li>Buka dashboard Midtrans, isi di Settings, Payment, Notification URL</li>
                    <li>Klik "Uji notifikasi" di bagian bawah halaman ini</li>
                </ol>

            @else

                <form method="POST" action="{{ route('integrasi.tunnel.nyalakan') }}">
                    @csrf
                    <button type="submit"
                            class="tombol-tekan rounded-lg bg-neon px-4 py-2 text-sm font-semibold text-tinta hover:bg-neon-tekan">
                        Nyalakan tunnel
                    </button>
                </form>

                <div class="mt-3 overflow-x-auto rounded-lg bg-latar-2 px-3 py-2.5 dark:bg-gelap">
                    <p class="font-mono text-xs text-redup dark:text-redup-gelap">https://xxxx-xxx-xxx-xxx.free.pinggy.net/callback/midtrans</p>
                </div>

            @endif

        </div>
    </section>

    {{-- Bagian C: uji notifikasi. Hanya masuk akal di sandbox, jadi tombolnya
         dimatikan saat mode produksi. --}}
    <section class="mb-5 rounded-xl border border-garis bg-white px-5 py-4 dark:border-garis-gelap dark:bg-gelap-2">

        <h2 class="text-sm font-semibold text-tinta dark:text-teks-gelap">Uji notifikasi</h2>
        <p class="mt-1 text-xs leading-relaxed text-redup dark:text-redup-gelap">
            Kirim notifikasi pembayaran tiruan ke aplikasi sendiri, tanpa membuka dashboard Midtrans.
            Satu transaksi pending akan ditandai berhasil dan tagihannya lunas.
        </p>

        @unless ($midtransProduksi)
            <p class="mt-2 text-xs font-medium text-red-700 dark:text-red-400">
                Hanya untuk sandbox. Jangan dipakai di mode produksi.
            </p>
        @endunless

        <form method="POST" action="{{ route('integrasi.uji') }}" class="mt-3"
              data-konfirmasi="Kirim notifikasi uji sekarang? Satu transaksi pending akan ditandai berhasil dan tagihannya jadi lunas."
              data-konfirmasi-jenis="simpan">
            @csrf
            <button type="submit"
                    @if ($midtransProduksi) disabled @endif
                    class="tombol-tekan rounded-lg bg-neon px-4 py-2 text-sm font-semibold text-tinta hover:bg-neon-tekan @if ($midtransProduksi) cursor-not-allowed opacity-50 @endif">
                Kirim notifikasi uji
            </button>
        </form>

    </section>

    {{-- Bagian D: kunci API. Formnya ada di modal, bukan di halaman, supaya
         halaman ini tetap ringkas. Nilai penuh tidak pernah dikirim ke sini. --}}
    <section class="mb-5 rounded-xl border border-garis bg-white px-5 py-4 dark:border-garis-gelap dark:bg-gelap-2">

        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <h2 class="text-sm font-semibold text-tinta dark:text-teks-gelap">Kunci API</h2>
                <p class="mt-1 flex items-center gap-2 text-xs text-redup dark:text-redup-gelap">
                    <span class="h-2 w-2 shrink-0 rounded-full {{ $sumberKunci === 'berkas' ? 'bg-neon' : 'bg-garis dark:bg-garis-gelap' }}" aria-hidden="true"></span>
                    Sumber saat ini: {{ $sumberKunci === 'berkas' ? 'berkas' : 'bawaan .env' }}
                </p>
                <p class="mt-1 font-mono text-[11px] text-redup dark:text-redup-gelap">{{ $sandiTersamar }}</p>
            </div>

            @if ($adaRouteKunci)
                <button type="button" data-modal-buka="tambah" data-modal-target="modal-kunci"
                        class="tombol-tekan rounded-lg bg-neon px-4 py-2 text-sm font-semibold text-tinta hover:bg-neon-tekan">
                    Ubah kunci
                </button>
            @else
                <p class="text-xs font-medium text-redup dark:text-redup-gelap">
                    Rute penyimpanan kunci belum terdaftar.
                </p>
            @endif
        </div>

        <p class="mt-3 text-xs leading-relaxed text-redup dark:text-redup-gelap">
            Kunci disimpan di berkas terpisah, bukan di database. Perubahan langsung dipakai
            tanpa mengubah berkas <span class="font-mono">.env</span>.
        </p>

        <details class="mt-4 rounded-lg border border-garis dark:border-garis-gelap">
            <summary class="cursor-pointer px-4 py-2.5 text-xs font-semibold text-tinta dark:text-teks-gelap">
                Pratinjau JSON berkas pengaturan
            </summary>
            <pre class="overflow-x-auto border-t border-garis px-4 py-3 font-mono text-[11px] leading-relaxed text-redup dark:border-garis-gelap dark:text-redup-gelap">{{ json_encode($jsonPratinjau, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
        </details>

    </section>

    @include('admin.integration._modal-kunci')

    {{-- Tombol salin. Ditulis di sini, bukan di app.js, karena hanya halaman ini
         yang memakainya. Fallback execCommand dipakai kalau navigator.clipboard
         tidak ada, misalnya saat halaman dibuka lewat http biasa. --}}
    <script>
        (function () {
            document.querySelectorAll('[data-salin]').forEach(function (tombol) {
                tombol.addEventListener('click', function () {
                    var teks = tombol.getAttribute('data-salin');
                    var asli = tombol.textContent;

                    function berhasil() {
                        tombol.textContent = 'Tersalin';
                        setTimeout(function () {
                            tombol.textContent = asli;
                        }, 2000);
                    }

                    if (navigator.clipboard && navigator.clipboard.writeText) {
                        navigator.clipboard.writeText(teks).then(berhasil, function () {
                            salinLama(teks, berhasil);
                        });

                        return;
                    }

                    salinLama(teks, berhasil);
                });
            });

            function salinLama(teks, berhasil) {
                var area = document.createElement('textarea');
                area.value = teks;
                area.setAttribute('readonly', '');
                area.style.position = 'fixed';
                area.style.top = '-1000px';
                document.body.appendChild(area);
                area.select();

                try {
                    document.execCommand('copy');
                    berhasil();
                } catch (e) {
                    // Tidak ada yang bisa dilakukan kalau peramban menolak.
                }

                document.body.removeChild(area);
            }
        })();
    </script>

@endsection[H[2J[3J