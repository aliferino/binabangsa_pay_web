@extends('public.simulation._layout')

@php $judul = 'Detail pembayaran'; @endphp

@section('isi')

    {{-- Peringatan jujur: seluruh alur di halaman ini memakai mock gateway
         lokal, tidak pernah sampai ke Midtrans atau Xendit. Merah, bukan
         neon, supaya terbaca sebagai peringatan dan tidak tertukar dengan
         tombol aksi. --}}
    <div class="mb-4 rounded-xl border border-red-300 bg-red-50 px-4 py-3 dark:border-red-900 dark:bg-red-950/40">
        <p class="text-[11px] font-bold uppercase tracking-widest text-red-700 dark:text-red-400">Mock gateway lokal</p>
        <p class="mt-1 text-xs leading-relaxed text-red-700 dark:text-red-400">
            Transaksi ini tidak muncul di dashboard Midtrans maupun Xendit. Callback dikirim oleh mock lokal ke aplikasi ini sendiri.
        </p>
    </div>

    @php
        $rupiah = fn ($n) => 'Rp'.number_format((float) $n, 0, ',', '.');

        $lunas = $tagihan === null;

        // Setelah tagihan lunas, controller tidak lagi menemukan transaksi
        // pending, jadi tahapnya kembali terbaca 'generate'. Di halaman detail
        // itu menyesatkan, karena pembayarannya sudah selesai. Jadi kalau
        // tagihan sudah lunas, tahapnya dipaksa 'berhasil'.
        if ($lunas) {
            $tahap = 'berhasil';
        }
    @endphp

    {{-- Empat tahap wajib terlihat asesor. Ditulis satu per satu, bukan
         tersembunyi di balik satu tombol. --}}
    <div class="rounded-xl border border-garis bg-white px-5 py-4 dark:border-garis-gelap dark:bg-gelap-2">
        <p class="text-[11px] font-medium text-redup dark:text-redup-gelap">Alur pembayaran</p>
        <div class="mt-3">
            @include('public.simulation._tahap', ['tahap' => $tahap, 'tahapDaftar' => $tahapDaftar])
        </div>
    </div>

    {{-- Tahap 3: nomor Virtual Account hasil pencocokan. --}}
    <div class="mt-4 rounded-xl border border-garis bg-white px-5 py-4 dark:border-garis-gelap dark:bg-gelap-2">
        <p class="text-[11px] font-medium text-redup dark:text-redup-gelap">Virtual Account</p>
        <p class="mt-1 font-mono text-2xl font-bold tracking-wide text-tinta dark:text-teks-gelap">@include('layouts.partials._nomor-va', ['nomor' => $va->nomor])</p>
        <p class="mt-2 text-sm text-tinta dark:text-teks-gelap">{{ $va->nama }}</p>
        <p class="mt-0.5 text-[11px] text-redup dark:text-redup-gelap">{{ $va->nis }} &middot; {{ $va->kelas }}</p>
    </div>

    {{-- Tagihan yang akan dilunasi: selalu yang tertua. Status belum lunas
         sampai callback mock masuk. --}}
    <div class="mt-4 rounded-xl border border-garis bg-white px-5 py-4 dark:border-garis-gelap dark:bg-gelap-2">

        @if ($tagihan)

            <div class="flex items-start justify-between gap-3">
                <p class="text-[11px] font-medium text-redup dark:text-redup-gelap">Tagihan tertua</p>
                <span class="shrink-0 rounded-full bg-latar-2 px-2.5 py-1 text-[10px] font-bold uppercase tracking-widest text-redup dark:bg-gelap dark:text-redup-gelap">Belum bayar</span>
            </div>
            <p class="mt-1 text-sm text-tinta dark:text-teks-gelap">{{ $tagihan->namaBulan() }} {{ $tagihan->tahun }}</p>
            <p class="mt-2 text-3xl font-bold tabular-nums tracking-tight text-tinta dark:text-teks-gelap">{{ $rupiah($tagihan->nominal) }}</p>
            <p class="mt-1 text-[11px] text-redup dark:text-redup-gelap">
                Jatuh tempo {{ $tagihan->tanggal_jatuh_tempo?->format('d/m/Y') ?? 'belum ditetapkan' }}
            </p>

        @else

            <div class="flex items-start justify-between gap-3">
                <p class="text-sm text-tinta dark:text-teks-gelap">Semua tagihan sudah lunas.</p>
                <span class="shrink-0 rounded-full bg-neon px-2.5 py-1 text-[10px] font-bold uppercase tracking-widest text-tinta">Lunas</span>
            </div>

        @endif

    </div>

    @if ($tagihan)

        {{-- Tahap 1, 2, dan 4 sekaligus: menekan tombol ini membuat transaksi,
             mengirimnya ke mock gateway, lalu menjalankan pembayaran. Status
             tagihan baru berubah setelah callback masuk, bukan karena tombol. --}}
        <form method="POST" action="{{ route('simulasi.bayar', $va->nomor) }}"
              class="mt-4 rounded-xl border border-garis bg-white px-5 py-4 dark:border-garis-gelap dark:bg-gelap-2">
            @csrf

            <label for="nominal" class="block text-[11px] font-medium text-redup dark:text-redup-gelap">Nominal</label>

            <input type="number" id="nominal" name="nominal" min="1" step="1" inputmode="numeric"
                   value="{{ old('nominal', $nominal) }}"
                   @error('nominal') aria-invalid="true" aria-describedby="galat-nominal" @enderror
                   class="mt-1.5 w-full rounded-lg border border-garis bg-white px-3 py-2 font-mono text-sm text-tinta focus:outline-none focus-visible:outline-2 focus-visible:outline-offset-1 focus-visible:outline-neon-teks dark:border-garis-gelap dark:bg-gelap dark:text-teks-gelap dark:focus-visible:outline-neon">

            @error('nominal')
                <p id="galat-nominal" class="mt-2 text-xs font-medium text-red-700 dark:text-red-400">{{ $message }}</p>
            @enderror

            <button type="submit"
                    class="border border-transparent tombol-tekan mt-3 w-full rounded-lg bg-neon px-4 py-3 text-sm font-semibold text-tinta hover:bg-neon-tekan">
                Bayar sekarang
            </button>

            <p class="mt-2 text-[11px] leading-relaxed text-redup dark:text-redup-gelap">
                Nominal wajib sama persis dengan tagihan. Setelah ditekan, mock gateway mengirim callback ke aplikasi ini, baru tagihan berubah lunas.
            </p>
        </form>

    @endif

    {{-- Tiga transaksi terakhir, kalau ada. --}}
    @if ($riwayat->isNotEmpty())

        <p class="mt-5 text-[11px] font-medium text-redup dark:text-redup-gelap">Riwayat terakhir</p>

        <div class="mt-2 overflow-hidden rounded-xl border border-garis bg-white dark:border-garis-gelap dark:bg-gelap-2">
            <dl class="divide-y divide-garis dark:divide-garis-gelap">
                @foreach ($riwayat as $baris)
                    <div class="flex items-center justify-between gap-3 px-5 py-2.5">
                        <div class="min-w-0">
                            <dt class="text-xs text-tinta dark:text-teks-gelap">{{ $baris->tanggal?->format('d/m/Y H:i') ?? '-' }}</dt>
                            <dd class="mt-0.5 text-[11px] text-redup dark:text-redup-gelap">{{ ucfirst(str_replace('_', ' ', (string) $baris->status)) }}</dd>
                        </div>
                        <dd class="shrink-0 font-mono text-xs font-semibold tabular-nums text-tinta dark:text-teks-gelap">{{ $rupiah($baris->nominal) }}</dd>
                    </div>
                @endforeach
            </dl>
        </div>

    @endif

@endsection
