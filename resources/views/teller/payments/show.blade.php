@extends('layouts.app')

@section('judul', 'Detail Pembayaran')
@section('keterangan', 'Rincian satu catatan pembayaran SPP')

@section('aksi')
    <a href="{{ route('teller.payments.index') }}"
       class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
        Kembali
    </a>
@endsection

@section('isi')

    <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">

        {{-- Data siswa. --}}
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <h2 class="text-sm font-semibold text-slate-700">Siswa</h2>
            <dl class="mt-3 space-y-3 text-sm">
                <div>
                    <dt class="text-slate-500">Nama</dt>
                    <dd class="font-medium text-slate-900">{{ $pembayaran->siswa?->nama ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500">NIS</dt>
                    <dd class="text-slate-700">{{ $pembayaran->siswa?->nis ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500">Kelas</dt>
                    <dd class="text-slate-700">{{ $pembayaran->siswa?->kelas?->nama_kelas ?? '-' }}</dd>
                </div>
            </dl>
        </div>

        {{-- Rincian pembayaran. --}}
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <h2 class="text-sm font-semibold text-slate-700">Rincian pembayaran</h2>
            <dl class="mt-3 space-y-3 text-sm">
                <div>
                    <dt class="text-slate-500">Nomor transaksi</dt>
                    <dd class="font-mono font-medium text-slate-900">{{ $pembayaran->nomor_transaksi }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500">Nominal</dt>
                    <dd class="text-lg font-bold text-slate-900">
                        Rp{{ number_format((float) $pembayaran->nominal_pembayaran, 0, ',', '.') }}
                    </dd>
                </div>
                <div>
                    <dt class="text-slate-500">Metode pembayaran</dt>
                    <dd class="text-slate-700">
                        @php
                            $labelMetode = [
                                'tunai' => 'Tunai',
                                'qris' => 'QRIS',
                                'virtual_account' => 'Virtual Account',
                            ];
                        @endphp
                        {{ $labelMetode[$pembayaran->metode_pembayaran] ?? $pembayaran->metode_pembayaran }}
                    </dd>
                </div>
                <div>
                    <dt class="text-slate-500">Tanggal</dt>
                    <dd class="text-slate-700">{{ $pembayaran->created_at?->format('d/m/Y H:i') ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500">Status</dt>
                    <dd>
                        @php
                            $petaStatus = [
                                'berhasil' => ['label' => 'Berhasil', 'kelas' => 'bg-emerald-100 text-emerald-800'],
                                'gagal' => ['label' => 'Gagal', 'kelas' => 'bg-red-100 text-red-800'],
                                'dibatalkan' => ['label' => 'Dibatalkan', 'kelas' => 'bg-slate-100 text-slate-700'],
                            ];
                            $dataStatus = $petaStatus[$pembayaran->status] ?? ['label' => $pembayaran->status, 'kelas' => 'bg-slate-100 text-slate-700'];
                        @endphp
                        <span class="inline-flex whitespace-nowrap rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $dataStatus['kelas'] }}">
                            {{ $dataStatus['label'] }}
                        </span>
                    </dd>
                </div>
                <div>
                    <dt class="text-slate-500">Dicatat oleh</dt>
                    <dd class="text-slate-700">{{ $pembayaran->user?->nama ?? '-' }}</dd>
                </div>
                @if ($pembayaran->nomor_referensi)
                    <div>
                        <dt class="text-slate-500">Nomor referensi</dt>
                        <dd class="font-mono text-slate-700">{{ $pembayaran->nomor_referensi }}</dd>
                    </div>
                @endif
                @if ($pembayaran->keterangan)
                    <div>
                        <dt class="text-slate-500">Keterangan</dt>
                        <dd class="text-slate-700">{{ $pembayaran->keterangan }}</dd>
                    </div>
                @endif
                @if ($pembayaran->tagihanSpp)
                    <div>
                        <dt class="text-slate-500">Tagihan terkait</dt>
                        <dd class="text-slate-700">
                            {{ $pembayaran->tagihanSpp->namaBulan() }} {{ $pembayaran->tagihanSpp->tahun }}
                        </dd>
                    </div>
                @endif
            </dl>
        </div>
    </div>

@endsection
