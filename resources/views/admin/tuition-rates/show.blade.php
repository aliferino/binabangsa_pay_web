@extends('layouts.app')

@section('judul', 'Detail Tarif SPP')
@section('keterangan', 'Informasi tarif SPP')

@section('aksi')
    @if (auth()->user()->role === 'admin')
        <a href="{{ route('tarif-spp.edit', $tarif) }}"
           class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
            Ubah
        </a>
    @endif
    <a href="{{ route('tarif-spp.index') }}"
       class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
        Kembali
    </a>
@endsection

@section('isi')

    <div class="max-w-2xl rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
        <h2 class="text-sm font-semibold text-slate-700">Data tarif</h2>
        <dl class="mt-3 space-y-3 text-sm">
            <div>
                <dt class="text-slate-500">Tahun ajaran</dt>
                <dd class="font-medium text-slate-900">{{ $tarif->tahunAjaran?->tahun_ajaran ?? '-' }}</dd>
            </div>
            <div>
                <dt class="text-slate-500">Nominal</dt>
                <dd class="text-lg font-bold text-slate-900">
                    Rp{{ number_format((float) $tarif->nominal, 0, ',', '.') }}
                </dd>
            </div>
            <div>
                <dt class="text-slate-500">Keterangan</dt>
                <dd class="text-slate-700">{{ $tarif->keterangan ?: '-' }}</dd>
            </div>
            <div>
                <dt class="text-slate-500">Status</dt>
                <dd>
                    @if ($tarif->status === 'aktif')
                        <span class="inline-flex rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-semibold text-emerald-800">Aktif</span>
                    @else
                        <span class="inline-flex rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-semibold text-slate-700">Nonaktif</span>
                    @endif
                </dd>
            </div>
        </dl>
    </div>

@endsection
