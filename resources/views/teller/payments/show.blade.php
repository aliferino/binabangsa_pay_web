@extends('layouts.app')

@section('judul', 'Detail Pembayaran')
@section('keterangan', 'Rincian satu catatan pembayaran SPP')

@section('aksi')
    <a href="{{ route('teller.payments.index') }}"
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
            'berhasil' => ['label' => 'Berhasil', 'class' => 'bg-neon text-tinta'],
            'gagal' => ['label' => 'Gagal', 'class' => 'bg-red-100 text-red-800'],
            'dibatalkan' => ['label' => 'Dibatalkan', 'class' => 'bg-latar-2 text-redup'],
        ];
        $statusData = $statusMap[$pembayaran->status] ?? ['label' => $pembayaran->status, 'class' => 'bg-latar-2 text-redup'];
    @endphp

    <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">

        {{-- Kartu identitas: siswa yang membayar. --}}
        <div class="rounded-xl border border-garis bg-white p-5 shadow-sm dark:border-garis-gelap dark:bg-gelap-2">
            <div class="flex items-start gap-4">
                <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-neon text-base font-bold text-tinta">
                    {{ mb_substr($pembayaran->siswa?->nama ?? 'S', 0, 1) }}
                </span>
                <div class="min-w-0">
                    <p class="truncate text-base font-semibold text-tinta dark:text-teks-gelap">{{ $pembayaran->siswa?->nama ?? 'Siswa terhapus' }}</p>
                    <p class="mt-0.5 font-mono text-xs text-redup dark:text-redup-gelap">NIS {{ $pembayaran->siswa?->nis ?? '-' }}</p>
                    <p class="mt-1 text-xs text-redup dark:text-redup-gelap">{{ $pembayaran->siswa?->kelas?->nama_kelas ?? '-' }}</p>
                </div>
            </div>

            @if ($pembayaran->siswa)
                <dl class="mt-5 divide-y divide-garis border-t border-garis text-sm dark:divide-garis-gelap dark:border-garis-gelap">
                    <div class="flex items-start justify-between gap-4 py-2.5">
                        <dt class="text-redup dark:text-redup-gelap">Kelas</dt>
                        <dd class="text-right font-medium text-tinta dark:text-teks-gelap">{{ $pembayaran->siswa->kelas?->nama_kelas ?? '-' }}</dd>
                    </div>
                </dl>
            @endif
        </div>

        {{-- Rincian pembayaran: nominal jadi angka utama. --}}
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
                <div class="flex items-start justify-between gap-4 py-2.5">
                    <dt class="text-redup dark:text-redup-gelap">Dicatat oleh</dt>
                    <dd class="text-right font-medium text-tinta dark:text-teks-gelap">{{ $pembayaran->user?->nama ?? '-' }}</dd>
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

            @if ($pembayaran->tagihanSpp)
                <a href="{{ route('teller.bills.show', $pembayaran->tagihanSpp) }}"
                   class="mt-4 inline-flex items-center gap-2 rounded-lg border border-garis bg-white px-3 py-2 text-sm font-medium text-tinta transition-colors hover:border-neon-teks hover:bg-latar-2 dark:border-garis-gelap dark:bg-gelap-2 dark:text-teks-gelap dark:hover:bg-gelap">
                    <svg class="h-[18px] w-[18px]" aria-hidden="true"><use href="#i-tagihan"/></svg>
                    Lihat tagihan terkait
                </a>
            @endif
        </div>
    </div>

@endsection