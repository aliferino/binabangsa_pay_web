@extends('layouts.app')

@section('judul', 'Detail Tagihan')
@section('keterangan', 'Rincian satu tagihan SPP')

@section('aksi')
    @if (auth()->user()->role === 'admin')
        <a href="{{ route('tagihan-spp.edit', $tagihan) }}" class="inline-flex h-8 w-8 items-center justify-center rounded-md border border-garis text-redup transition-colors hover:border-neon-teks hover:bg-latar-2 hover:text-tinta dark:border-garis-gelap dark:text-redup-gelap dark:hover:bg-gelap dark:hover:text-teks-gelap" title="Ubah" aria-label="Ubah"><svg class="h-[18px] w-[18px]" aria-hidden="true"><use href="#i-ubah"/></svg></a>
    @endif
    <a href="{{ auth()->user()->role === 'teller' ? route('teller.bills.index') : route('tagihan-spp.index') }}"
       class="inline-flex items-center gap-2 rounded-lg border border-garis bg-white px-4 py-2 text-sm font-medium text-tinta hover:bg-latar-2 dark:border-garis-gelap dark:bg-gelap-2 dark:text-teks-gelap dark:hover:bg-gelap">
        <svg class="h-[18px] w-[18px]" aria-hidden="true"><use href="#i-kembali"/></svg>
        Kembali
    </a>
@endsection

@section('isi')

    <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">

        {{-- Kartu identitas: siswa penanggung tagihan. --}}
        <div class="rounded-xl border border-garis bg-white p-5 shadow-sm dark:border-garis-gelap dark:bg-gelap-2">
            <div class="flex items-start gap-4">
                <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-neon text-base font-bold text-tinta">
                    {{ mb_substr($tagihan->siswa?->nama ?? 'S', 0, 1) }}
                </span>
                <div class="min-w-0">
                    <p class="truncate text-base font-semibold text-tinta dark:text-teks-gelap">{{ $tagihan->siswa?->nama ?? 'Siswa terhapus' }}</p>
                    <p class="mt-0.5 font-mono text-xs text-redup dark:text-redup-gelap">NIS {{ $tagihan->siswa?->nis ?? '-' }}</p>
                    <p class="mt-1 text-xs text-redup dark:text-redup-gelap">{{ $tagihan->siswa?->kelas?->nama_kelas ?? '-' }}</p>
                </div>
            </div>

            <dl class="mt-5 divide-y divide-garis border-t border-garis text-sm dark:divide-garis-gelap dark:border-garis-gelap">
                <div class="flex items-start justify-between gap-4 py-2.5">
                    <dt class="text-redup dark:text-redup-gelap">Nomor induk siswa</dt>
                    <dd class="text-right font-mono font-medium text-tinta dark:text-teks-gelap">{{ $tagihan->siswa?->nis ?? '-' }}</dd>
                </div>
                <div class="flex items-start justify-between gap-4 py-2.5">
                    <dt class="text-redup dark:text-redup-gelap">Kelas</dt>
                    <dd class="text-right font-medium text-tinta dark:text-teks-gelap">{{ $tagihan->siswa?->kelas?->nama_kelas ?? '-' }}</dd>
                </div>
                <div class="flex items-start justify-between gap-4 py-2.5">
                    <dt class="text-redup dark:text-redup-gelap">Tahun ajaran</dt>
                    <dd class="text-right font-medium text-tinta dark:text-teks-gelap">{{ $tagihan->tahunAjaran?->tahun_ajaran ?? '-' }}</dd>
                </div>
            </dl>
        </div>

        {{-- Rincian tagihan: nominal jadi angka utama. --}}
        <div class="rounded-xl border border-garis bg-white p-5 shadow-sm dark:border-garis-gelap dark:bg-gelap-2">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-xs uppercase tracking-wide text-redup dark:text-redup-gelap">Nominal tagihan</p>
                    <p class="mt-1 font-mono text-2xl font-bold tabular-nums text-tinta dark:text-teks-gelap">
                        Rp{{ number_format((float) $tagihan->nominal, 0, ',', '.') }}
                    </p>
                </div>
                @include('layouts.partials._status-badge', ['tagihan' => $tagihan])
            </div>

            <dl class="mt-5 divide-y divide-garis border-t border-garis text-sm dark:divide-garis-gelap dark:border-garis-gelap">
                <div class="flex items-start justify-between gap-4 py-2.5">
                    <dt class="text-redup dark:text-redup-gelap">Periode</dt>
                    <dd class="text-right font-medium text-tinta dark:text-teks-gelap">{{ $tagihan->namaBulan() }} {{ $tagihan->tahun }}</dd>
                </div>
                <div class="flex items-start justify-between gap-4 py-2.5">
                    <dt class="text-redup dark:text-redup-gelap">Tanggal jatuh tempo</dt>
                    <dd class="text-right font-medium text-tinta dark:text-teks-gelap">{{ $tagihan->tanggal_jatuh_tempo?->format('d/m/Y') ?? '-' }}</dd>
                </div>
                <div class="flex items-start justify-between gap-4 py-2.5">
                    <dt class="text-redup dark:text-redup-gelap">Tarif acuan</dt>
                    <dd class="text-right font-medium text-tinta dark:text-teks-gelap">
                        @if ($tagihan->tarif)
                            <span class="font-mono tabular-nums">Rp{{ number_format((float) $tagihan->tarif->nominal, 0, ',', '.') }}</span>
                            @if ($tagihan->tarif->keterangan)
                                <span class="block text-xs font-normal text-redup dark:text-redup-gelap">{{ $tagihan->tarif->keterangan }}</span>
                            @endif
                        @else
                            -
                        @endif
                    </dd>
                </div>
            </dl>

            @if ($tagihan->status !== 'lunas')
                <p class="mt-4 flex items-start gap-2 rounded-lg border border-garis bg-latar-2 px-3 py-2.5 text-xs leading-relaxed text-redup dark:border-garis-gelap dark:bg-gelap dark:text-redup-gelap">
                    <svg class="mt-0.5 h-[18px] w-[18px] shrink-0" aria-hidden="true"><use href="#i-peringatan"/></svg>
                    <span>Tanda lunas dan riwayat pembayaran belum bisa ditampilkan karena tabel pembayaran belum dibuat.</span>
                </p>
            @endif
        </div>
    </div>

@endsection