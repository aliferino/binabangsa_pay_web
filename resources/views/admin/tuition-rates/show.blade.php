@extends('layouts.app')

@section('judul', 'Detail Tarif SPP')
@section('keterangan', 'Informasi tarif SPP')

@section('aksi')
    @if (auth()->user()->role === 'admin')
        <a href="{{ route('tarif-spp.edit', $tarif) }}"
           class="rounded-lg border border-garis bg-white px-4 py-2 text-sm font-medium text-tinta hover:bg-latar-2 dark:border-garis-gelap dark:bg-gelap-2 dark:text-teks-gelap dark:hover:bg-gelap">
            Ubah
        </a>
    @endif
    <a href="{{ route('tarif-spp.index') }}"
       class="rounded-lg border border-garis bg-white px-4 py-2 text-sm font-medium text-tinta hover:bg-latar-2 dark:border-garis-gelap dark:bg-gelap-2 dark:text-teks-gelap dark:hover:bg-gelap">
        Kembali
    </a>
@endsection

@section('isi')

    <div class="max-w-2xl rounded-xl border border-garis bg-white p-5 shadow-sm dark:border-garis-gelap dark:bg-gelap-2">
        <h2 class="text-sm font-semibold text-tinta dark:text-teks-gelap">Data tarif</h2>
        <dl class="mt-3 space-y-3 text-sm">
            <div>
                <dt class="text-redup dark:text-redup-gelap">Tahun ajaran</dt>
                <dd class="font-medium text-tinta dark:text-teks-gelap">{{ $tarif->tahunAjaran?->tahun_ajaran ?? '-' }}</dd>
            </div>
            <div>
                <dt class="text-redup dark:text-redup-gelap">Nominal</dt>
                <dd class="text-lg font-bold text-tinta dark:text-teks-gelap">
                    Rp{{ number_format((float) $tarif->nominal, 0, ',', '.') }}
                </dd>
            </div>
            <div>
                <dt class="text-redup dark:text-redup-gelap">Keterangan</dt>
                <dd class="text-tinta dark:text-teks-gelap">{{ $tarif->keterangan ?: '-' }}</dd>
            </div>
            <div>
                <dt class="text-redup dark:text-redup-gelap">Status</dt>
                <dd>
                    @if ($tarif->status === 'aktif')
                        <span class="inline-flex rounded-full bg-neon px-2.5 py-0.5 text-xs font-semibold text-tinta">Aktif</span>
                    @else
                        <span class="inline-flex rounded-full bg-latar-2 px-2.5 py-0.5 text-xs font-semibold text-redup dark:bg-gelap dark:text-redup-gelap">Nonaktif</span>
                    @endif
                </dd>
            </div>
        </dl>
    </div>

@endsection
