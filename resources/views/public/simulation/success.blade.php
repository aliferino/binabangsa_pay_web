@extends('public.simulation._layout')

@php $judul = 'Pembayaran diterima'; @endphp

@section('isi')

    @php
        $rupiah = fn ($n) => 'Rp'.number_format((float) $n, 0, ',', '.');

        $baris = [
            'Nomor transaksi' => $pembayaran->nomor_transaksi,
            'Nama' => $pembayaran->nama,
            'Nominal' => $rupiah($pembayaran->nominal),
            'Waktu' => $pembayaran->waktu?->format('d/m/Y H:i') ?? '-',
            'Status' => ucfirst(str_replace('_', ' ', (string) $pembayaran->status)),
            'Sumber' => 'Callback mock gateway lokal',
        ];
    @endphp

    {{-- Struk: ikon centang, ringkasan, lalu daftar kunci dan nilai bergaris. --}}
    <div class="rounded-xl border border-garis bg-white px-5 py-6 text-center dark:border-garis-gelap dark:bg-gelap-2">

        <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-neon" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none" class="h-6 w-6 text-tinta">
                <path d="M5 12.5 9.5 17 19 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </span>

        <h1 class="mt-4 text-lg font-bold tracking-tight text-tinta dark:text-teks-gelap">Pembayaran diterima</h1>
        <p class="mt-1 text-xs text-redup dark:text-redup-gelap">Tagihan berubah lunas setelah callback masuk</p>
    </div>

    {{-- Peringatan jujur, ditempatkan tepat di bawah struk supaya terbaca
         sebelum siswa menutup halaman. --}}
    <div class="mt-4 rounded-xl border border-red-300 bg-red-50 px-4 py-3 dark:border-red-900 dark:bg-red-950/40">
        <p class="text-[11px] font-bold uppercase tracking-widest text-red-700 dark:text-red-400">Mock gateway lokal</p>
        <p class="mt-1 text-xs leading-relaxed text-red-700 dark:text-red-400">
            Transaksi ini tidak muncul di dashboard Midtrans maupun Xendit. Callback dikirim oleh mock lokal ke aplikasi ini sendiri.
        </p>
    </div>

    {{-- Kelima tahap sudah terlewati. Ditampilkan penuh supaya asesor bisa
         membandingkan halaman sebelum dan sesudah callback. --}}
    <div class="mt-4 rounded-xl border border-garis bg-white px-5 py-4 dark:border-garis-gelap dark:bg-gelap-2">
        <p class="text-[11px] font-medium text-redup dark:text-redup-gelap">Alur pembayaran</p>
        <div class="mt-3">
            @include('public.simulation._tahap', ['tahap' => 'berhasil'])
        </div>
    </div>

    <dl class="mt-4 divide-y divide-garis overflow-hidden rounded-xl border border-garis bg-white dark:divide-garis-gelap dark:border-garis-gelap dark:bg-gelap-2">
        @foreach ($baris as $label => $nilai)
            <div class="flex items-start justify-between gap-3 px-5 py-3">
                <dt class="shrink-0 text-[11px] font-medium text-redup dark:text-redup-gelap">{{ $label }}</dt>
                <dd class="text-right text-xs text-tinta dark:text-teks-gelap {{ $label === 'Nominal' ? 'font-mono text-base font-bold tabular-nums tracking-tight' : '' }}">{{ $nilai }}</dd>
            </div>
        @endforeach
    </dl>

    <a href="{{ route('simulasi.bukti', $pembayaran->nomor_transaksi) }}" download
       class="tombol-tekan mt-4 block w-full rounded-lg bg-neon px-4 py-3 text-center text-sm font-semibold text-tinta hover:bg-neon-tekan">
        Simpan bukti transfer (PNG)
    </a>

    <a href="{{ route('beranda') }}"
       class="tombol-tekan mt-2 block w-full rounded-lg border border-garis px-4 py-3 text-center text-sm font-semibold text-tinta hover:bg-latar-2 dark:border-garis-gelap dark:text-teks-gelap dark:hover:bg-gelap">
        Selesai
    </a>

@endsection
