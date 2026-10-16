@extends('layouts.app')

@section('judul', 'Detail Tahun Ajaran')
@section('keterangan', 'Informasi tahun ajaran, kelas, dan tarifnya')

@section('aksi')
    @if (auth()->user()->role === 'admin')
        <a href="{{ route('tahun-ajaran.edit', $tahun_ajaran) }}"
           class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
            Ubah
        </a>
    @endif
    <a href="{{ route('tahun-ajaran.index') }}"
       class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
        Kembali
    </a>
@endsection

@section('isi')

    <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">

        {{-- Data tahun ajaran. --}}
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm lg:col-span-1">
            <h2 class="text-sm font-semibold text-slate-700">Data tahun ajaran</h2>
            <dl class="mt-3 space-y-3 text-sm">
                <div>
                    <dt class="text-slate-500">Tahun ajaran</dt>
                    <dd class="font-medium text-slate-900">{{ $tahun_ajaran->tahun_ajaran }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500">Tanggal mulai</dt>
                    <dd class="text-slate-700">{{ $tahun_ajaran->tanggal_mulai?->format('d/m/Y') ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500">Tanggal selesai</dt>
                    <dd class="text-slate-700">{{ $tahun_ajaran->tanggal_selesai?->format('d/m/Y') ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500">Status</dt>
                    <dd>
                        @if ($tahun_ajaran->status === 'aktif')
                            <span class="inline-flex rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-semibold text-emerald-800">Aktif</span>
                        @else
                            <span class="inline-flex rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-semibold text-slate-700">Nonaktif</span>
                        @endif
                    </dd>
                </div>
            </dl>
        </div>

        <div class="space-y-4 lg:col-span-2">

            {{-- Kelas pada tahun ajaran ini. --}}
            <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-200 px-5 py-4">
                    <h2 class="text-sm font-semibold text-slate-700">Kelas</h2>
                    <p class="mt-1 text-xs text-slate-500">Total {{ $tahun_ajaran->kelas->count() }} kelas.</p>
                </div>

                @if ($tahun_ajaran->kelas->isEmpty())
                    <p class="px-5 py-6 text-sm text-slate-500">Belum ada kelas pada tahun ajaran ini.</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200 text-sm">
                            <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                                <tr>
                                    <th class="px-5 py-3 font-semibold">Nama kelas</th>
                                    <th class="px-5 py-3 font-semibold">Tingkat</th>
                                    <th class="px-5 py-3 font-semibold">Jurusan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach ($tahun_ajaran->kelas as $kelas)
                                    <tr class="hover:bg-slate-50">
                                        <td class="px-5 py-3 font-medium text-slate-800">{{ $kelas->nama_kelas }}</td>
                                        <td class="px-5 py-3 text-slate-600">{{ $kelas->tingkat }}</td>
                                        <td class="px-5 py-3 text-slate-600">{{ $kelas->jurusan?->nama_jurusan ?? '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            {{-- Tarif SPP pada tahun ajaran ini. --}}
            <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-200 px-5 py-4">
                    <h2 class="text-sm font-semibold text-slate-700">Tarif SPP</h2>
                    <p class="mt-1 text-xs text-slate-500">Total {{ $tahun_ajaran->tarifSpp->count() }} tarif.</p>
                </div>

                @if ($tahun_ajaran->tarifSpp->isEmpty())
                    <p class="px-5 py-6 text-sm text-slate-500">Belum ada tarif pada tahun ajaran ini.</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200 text-sm">
                            <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                                <tr>
                                    <th class="px-5 py-3 font-semibold">Nominal</th>
                                    <th class="px-5 py-3 font-semibold">Keterangan</th>
                                    <th class="px-5 py-3 font-semibold">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach ($tahun_ajaran->tarifSpp as $tarif)
                                    <tr class="hover:bg-slate-50">
                                        <td class="px-5 py-3 font-medium text-slate-800">
                                            Rp{{ number_format((float) $tarif->nominal, 0, ',', '.') }}
                                        </td>
                                        <td class="px-5 py-3 text-slate-600">{{ $tarif->keterangan ?: '-' }}</td>
                                        <td class="px-5 py-3 text-slate-600">{{ ucfirst($tarif->status) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>

@endsection
