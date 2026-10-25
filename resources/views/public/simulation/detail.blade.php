@extends('public.simulation._layout')

@php $judul = 'Detail pembayaran'; @endphp

@section('isi')

    {{-- Peringatan jujur: pembayaran di halaman ini tidak pernah sampai ke
         Midtrans, jadi jangan sampai siswa mengira transaksinya tercatat di
         dashboard Midtrans. Merah, bukan neon, supaya terbaca sebagai
         peringatan dan tidak tertukar dengan tombol aksi. --}}
    <div class="mb-4 rounded-xl border border-red-300 bg-red-50 px-4 py-3 dark:border-red-900 dark:bg-red-950/40">
        <p class="text-[11px] font-bold uppercase tracking-widest text-red-700 dark:text-red-400">Simulasi lokal</p>
        <p class="mt-1 text-xs leading-relaxed text-red-700 dark:text-red-400">
            Simulasi lokal. Transaksi ini tidak muncul di dashboard Midtrans. Hanya dicatat di aplikasi ini.
        </p>
    </div>

    @php
        $rupiah = fn ($n) => 'Rp'.number_format((float) $n, 0, ',', '.');
    @endphp

    {{-- Kartu pemilik. Netral, tanpa nama bank dan tanpa logo bank. --}}
    <div class="rounded-xl border border-garis bg-white px-5 py-4 dark:border-garis-gelap dark:bg-gelap-2">
        <p class="text-[11px] font-medium text-redup dark:text-redup-gelap">Virtual Account</p>
        <p class="mt-1 font-mono text-2xl font-bold tracking-wide text-tinta dark:text-teks-gelap">@include('layouts.partials._nomor-va', ['nomor' => $va->nomor])</p>
        <p class="mt-2 text-sm text-tinta dark:text-teks-gelap">{{ $va->nama }}</p>
        <p class="mt-0.5 text-[11px] text-redup dark:text-redup-gelap">{{ $va->nis }} &middot; {{ $va->kelas }}</p>
    </div>

    {{-- Tagihan yang akan dilunasi: selalu yang tertua. --}}
    <div class="mt-4 rounded-xl border border-garis bg-white px-5 py-4 dark:border-garis-gelap dark:bg-gelap-2">

        @if ($tagihan)

            <p class="text-[11px] font-medium text-redup dark:text-redup-gelap">Tagihan tertua</p>
            <p class="mt-1 text-sm text-tinta dark:text-teks-gelap">{{ $tagihan->namaBulan() }} {{ $tagihan->tahun }}</p>
            <p class="mt-2 text-3xl font-bold tabular-nums tracking-tight text-tinta dark:text-teks-gelap">{{ $rupiah($tagihan->nominal) }}</p>
            <p class="mt-1 text-[11px] text-redup dark:text-redup-gelap">
                Jatuh tempo {{ $tagihan->tanggal_jatuh_tempo?->format('d/m/Y') ?? 'belum ditetapkan' }}
            </p>

        @else

            <p class="text-sm text-tinta dark:text-teks-gelap">Semua tagihan sudah lunas.</p>

        @endif

    </div>

    {{-- Form bayar. Nominal sudah terisi tagihan tertua, tetapi boleh diubah. --}}
    @if ($tagihan)

        <form method="POST" action="{{ route('simulasi.bayar', $va->nomor) }}"
              class="mt-4 rounded-xl border border-garis bg-white px-5 py-4 dark:border-garis-gelap dark:bg-gelap-2">
            @csrf

            <label for="nominal" class="text-[11px] font-medium text-redup dark:text-redup-gelap">Nominal</label>

            <input type="number" id="nominal" name="nominal" min="1" step="1" inputmode="numeric"
                   value="{{ old('nominal', $nominal) }}"
                   class="mt-1.5 w-full rounded-lg border border-garis bg-white px-3 py-2 font-mono text-sm text-tinta focus:outline-none focus-visible:outline-2 focus-visible:outline-offset-1 focus-visible:outline-neon-teks dark:border-garis-gelap dark:bg-gelap dark:text-teks-gelap dark:focus-visible:outline-neon">

            @error('nominal')
                <p class="mt-2 text-xs font-medium text-red-700 dark:text-red-400">{{ $message }}</p>
            @enderror

            <button type="submit"
                    class="tombol-tekan mt-3 w-full rounded-lg bg-neon px-4 py-3 text-sm font-semibold text-tinta hover:bg-neon-tekan">
                Bayar sekarang
            </button>

            <p class="mt-2 text-[11px] leading-relaxed text-redup dark:text-redup-gelap">
                Nominal sudah terisi tagihan tertua. Bisa diubah untuk latihan, tetapi tagihan yang dilunasi tetap yang tertua.
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