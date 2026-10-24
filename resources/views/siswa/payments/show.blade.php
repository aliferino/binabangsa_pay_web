@extends('layouts.app')

@section('judul', 'Detail Pembayaran')
@section('keterangan', 'Rincian satu pembayaran SPP Anda')

@section('aksi')
    <a href="{{ route('siswa.payments.index') }}"
       class="inline-flex items-center gap-2 rounded-lg border border-garis bg-white px-4 py-2 text-sm font-medium text-tinta hover:bg-latar-2 dark:border-garis-gelap dark:bg-gelap-2 dark:text-teks-gelap dark:hover:bg-gelap">
        <svg class="h-[18px] w-[18px]" aria-hidden="true"><use href="#i-kembali"/></svg>
        Kembali
    </a>
@endsection

@section('isi')

    @php
        $methodLabels = [
            'tunai' => 'Tunai',
            'qris' => 'QRIS',
            'virtual_account' => 'Virtual Account',
        ];
        $statusMap = [
            'berhasil' => ['label' => 'Berhasil', 'class' => 'bg-emerald-100 text-emerald-800'],
            'gagal' => ['label' => 'Gagal', 'class' => 'bg-red-100 text-red-800'],
            'dibatalkan' => ['label' => 'Dibatalkan', 'class' => 'bg-latar-2 text-redup'],
        ];
        $statusData = $statusMap[$pembayaran->status] ?? ['label' => $pembayaran->status, 'class' => 'bg-latar-2 text-redup'];
    @endphp

    <div class="max-w-2xl space-y-5">

        {{-- Nominal jadi angka utama, status menempel di kanan. --}}
        <div class="rounded-xl border border-garis bg-white p-5 shadow-sm dark:border-garis-gelap dark:bg-gelap-2">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-xs uppercase tracking-wide text-redup dark:text-redup-gelap">Nominal dibayar</p>
                    <p class="mt-1 font-mono text-2xl font-bold tabular-nums text-tinta dark:text-teks-gelap">
                        Rp{{ number_format((float) $pembayaran->nominal_pembayaran, 0, ',', '.') }}
                    </p>
                </div>
                <span class="inline-flex whitespace-nowrap rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $statusData['class'] }}">
                    {{ $statusData['label'] }}
                </span>
            </div>

            <dl class="mt-5 divide-y divide-garis border-t border-garis text-sm dark:divide-garis-gelap dark:border-garis-gelap">
                <div class="flex items-start justify-between gap-4 py-2.5">
                    <dt class="text-redup dark:text-redup-gelap">Nomor transaksi</dt>
                    <dd class="text-right font-mono font-medium text-tinta dark:text-teks-gelap">{{ $pembayaran->nomor_transaksi }}</dd>
                </div>
                <div class="flex items-start justify-between gap-4 py-2.5">
                    <dt class="text-redup dark:text-redup-gelap">Metode pembayaran</dt>
                    <dd class="text-right font-medium text-tinta dark:text-teks-gelap">{{ $methodLabels[$pembayaran->metode_pembayaran] ?? $pembayaran->metode_pembayaran }}</dd>
                </div>
                <div class="flex items-start justify-between gap-4 py-2.5">
                    <dt class="text-redup dark:text-redup-gelap">Tanggal</dt>
                    <dd class="text-right font-medium text-tinta dark:text-teks-gelap">{{ $pembayaran->created_at?->format('d/m/Y H:i') ?? '-' }}</dd>
                </div>
                @if ($pembayaran->nomor_referensi)
                    <div class="flex items-start justify-between gap-4 py-2.5">
                        <dt class="text-redup dark:text-redup-gelap">Nomor referensi</dt>
                        <dd class="text-right font-mono font-medium text-tinta dark:text-teks-gelap">{{ $pembayaran->nomor_referensi }}</dd>
                    </div>
                @endif
                @if ($pembayaran->tagihanSpp)
                    <div class="flex items-start justify-between gap-4 py-2.5">
                        <dt class="text-redup dark:text-redup-gelap">Tagihan terkait</dt>
                        <dd class="text-right font-medium text-tinta dark:text-teks-gelap">
                            {{ $pembayaran->tagihanSpp->namaBulan() }} {{ $pembayaran->tagihanSpp->tahun }}
                        </dd>
                    </div>
                @endif
                @if ($pembayaran->keterangan)
                    <div class="flex items-start justify-between gap-4 py-2.5">
                        <dt class="text-redup dark:text-redup-gelap">Keterangan</dt>
                        <dd class="text-right font-medium text-tinta dark:text-teks-gelap">{{ $pembayaran->keterangan }}</dd>
                    </div>
                @endif
            </dl>
        </div>

        <a href="{{ route('siswa.payments.index') }}"
           class="inline-flex items-center gap-2 rounded-lg border border-garis bg-white px-4 py-2 text-sm font-medium text-tinta transition-colors hover:border-neon-teks hover:bg-latar-2 dark:border-garis-gelap dark:bg-gelap-2 dark:text-teks-gelap dark:hover:bg-gelap">
            <svg class="h-[18px] w-[18px]" aria-hidden="true"><use href="#i-kembali"/></svg>
            Kembali ke riwayat pembayaran
        </a>
    </div>

@endsection