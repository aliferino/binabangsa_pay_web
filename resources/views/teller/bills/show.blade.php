@extends('layouts.app')

@section('judul', 'Detail Tagihan')
@section('keterangan', 'Rincian satu tagihan SPP')

@section('aksi')
    @if (auth()->user()->role === 'admin')
        <a href="{{ route('tagihan-spp.edit', $tagihan) }}"
           class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
            Ubah
        </a>
    @endif
    <a href="{{ route('tagihan-spp.index') }}"
       class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
        Kembali
    </a>
@endsection

@section('isi')

    <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">

        {{-- Data siswa penanggung tagihan. --}}
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <h2 class="text-sm font-semibold text-slate-700">Siswa</h2>
            <dl class="mt-3 space-y-3 text-sm">
                <div>
                    <dt class="text-slate-500">Nama</dt>
                    <dd class="font-medium text-slate-900">{{ $tagihan->siswa?->nama ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500">NIS</dt>
                    <dd class="text-slate-700">{{ $tagihan->siswa?->nis ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500">Kelas</dt>
                    <dd class="text-slate-700">{{ $tagihan->siswa?->kelas?->nama_kelas ?? '-' }}</dd>
                </div>
            </dl>
        </div>

        {{-- Rincian tagihan. --}}
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <h2 class="text-sm font-semibold text-slate-700">Rincian tagihan</h2>
            <dl class="mt-3 space-y-3 text-sm">
                <div>
                    <dt class="text-slate-500">Periode</dt>
                    <dd class="font-medium text-slate-900">{{ $tagihan->namaBulan() }} {{ $tagihan->tahun }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500">Nominal</dt>
                    <dd class="text-lg font-bold text-slate-900">
                        Rp{{ number_format((float) $tagihan->nominal, 0, ',', '.') }}
                    </dd>
                </div>
                <div>
                    <dt class="text-slate-500">Tanggal jatuh tempo</dt>
                    <dd class="text-slate-700">{{ $tagihan->tanggal_jatuh_tempo?->format('d/m/Y') ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500">Status</dt>
                    <dd>@include('partials._status-badge', ['status' => $tagihan->status])</dd>
                </div>
                <div>
                    <dt class="text-slate-500">Tahun ajaran</dt>
                    <dd class="text-slate-700">{{ $tagihan->tahunAjaran?->tahun_ajaran ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500">Tarif acuan</dt>
                    <dd class="text-slate-700">
                        @if ($tagihan->tarif)
                            Rp{{ number_format((float) $tagihan->tarif->nominal, 0, ',', '.') }}
                            <span class="text-slate-500">{{ $tagihan->tarif->keterangan ? '- '.$tagihan->tarif->keterangan : '' }}</span>
                        @else
                            -
                        @endif
                    </dd>
                </div>
            </dl>

            @if ($tagihan->status !== 'lunas')
                <p class="mt-4 rounded-lg border border-dashed border-slate-300 bg-slate-50 px-3 py-2 text-xs text-slate-500">
                    Tanda lunas dan riwayat pembayaran belum bisa ditampilkan karena tabel
                    pembayaran belum dibuat.
                </p>
            @endif
        </div>
    </div>

@endsection
