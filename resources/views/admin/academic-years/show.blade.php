@extends('layouts.app')

@section('judul', 'Detail Tahun Ajaran')
@section('keterangan', 'Informasi tahun ajaran, kelas, dan tarifnya')

@section('aksi')
    @if (auth()->user()->role === 'admin')
        <a href="{{ route('tahun-ajaran.edit', $tahun_ajaran) }}"
           class="rounded-lg border border-garis bg-white px-4 py-2 text-sm font-medium text-tinta hover:bg-latar-2 dark:border-garis-gelap dark:bg-gelap-2 dark:text-teks-gelap dark:hover:bg-gelap">
            Ubah
        </a>
    @endif
    <a href="{{ route('tahun-ajaran.index') }}"
       class="rounded-lg border border-garis bg-white px-4 py-2 text-sm font-medium text-tinta hover:bg-latar-2 dark:border-garis-gelap dark:bg-gelap-2 dark:text-teks-gelap dark:hover:bg-gelap">
        Kembali
    </a>
@endsection

@section('isi')

    <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">

        {{-- Data tahun ajaran. --}}
        <div class="rounded-xl border border-garis bg-white p-5 shadow-sm dark:border-garis-gelap dark:bg-gelap-2 lg:col-span-1">
            <h2 class="text-sm font-semibold text-tinta dark:text-teks-gelap">Data tahun ajaran</h2>
            <dl class="mt-3 space-y-3 text-sm">
                <div>
                    <dt class="text-redup dark:text-redup-gelap">Tahun ajaran</dt>
                    <dd class="font-medium text-tinta dark:text-teks-gelap">{{ $tahun_ajaran->tahun_ajaran }}</dd>
                </div>
                <div>
                    <dt class="text-redup dark:text-redup-gelap">Tanggal mulai</dt>
                    <dd class="text-redup dark:text-redup-gelap">{{ $tahun_ajaran->tanggal_mulai?->format('d/m/Y') ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="text-redup dark:text-redup-gelap">Tanggal selesai</dt>
                    <dd class="text-redup dark:text-redup-gelap">{{ $tahun_ajaran->tanggal_selesai?->format('d/m/Y') ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="text-redup dark:text-redup-gelap">Status</dt>
                    <dd>
                        @if ($tahun_ajaran->status === 'aktif')
                            <span class="inline-flex rounded-full bg-neon px-2.5 py-0.5 text-xs font-semibold text-tinta">Aktif</span>
                        @else
                            <span class="inline-flex rounded-full bg-latar-2 px-2.5 py-0.5 text-xs font-semibold text-redup dark:bg-gelap dark:text-redup-gelap">Nonaktif</span>
                        @endif
                    </dd>
                </div>
            </dl>
        </div>

        <div class="space-y-4 lg:col-span-2">

            {{-- Kelas pada tahun ajaran ini. --}}
            <div class="overflow-hidden rounded-xl border border-garis bg-white shadow-sm dark:border-garis-gelap dark:bg-gelap-2">
                <div class="border-b border-garis px-5 py-4 dark:border-garis-gelap">
                    <h2 class="text-sm font-semibold text-tinta dark:text-teks-gelap">Kelas</h2>
                    <p class="mt-1 text-xs text-redup dark:text-redup-gelap">Total {{ $tahun_ajaran->kelas->count() }} kelas.</p>
                </div>

                @if ($tahun_ajaran->kelas->isEmpty())
                    <p class="px-5 py-6 text-sm text-redup dark:text-redup-gelap">Belum ada kelas pada tahun ajaran ini.</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-garis dark:divide-garis-gelap text-sm">
                            <thead class="bg-latar-2 text-left text-xs uppercase tracking-wide text-redup dark:bg-gelap dark:text-redup-gelap">
                                <tr>
                                    <th class="px-5 py-3 font-semibold">Nama kelas</th>
                                    <th class="px-5 py-3 font-semibold">Tingkat</th>
                                    <th class="px-5 py-3 font-semibold">Jurusan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-garis dark:divide-garis-gelap">
                                @foreach ($tahun_ajaran->kelas as $kelas)
                                    <tr class="hover:bg-latar-2 dark:hover:bg-gelap">
                                        <td class="px-5 py-3 font-medium text-tinta dark:text-teks-gelap">{{ $kelas->nama_kelas }}</td>
                                        <td class="px-5 py-3 text-redup dark:text-redup-gelap">{{ $kelas->tingkat }}</td>
                                        <td class="px-5 py-3 text-redup dark:text-redup-gelap">{{ $kelas->jurusan?->nama_jurusan ?? '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            {{-- Tarif SPP pada tahun ajaran ini. --}}
            <div class="overflow-hidden rounded-xl border border-garis bg-white shadow-sm dark:border-garis-gelap dark:bg-gelap-2">
                <div class="border-b border-garis px-5 py-4 dark:border-garis-gelap">
                    <h2 class="text-sm font-semibold text-tinta dark:text-teks-gelap">Tarif SPP</h2>
                    <p class="mt-1 text-xs text-redup dark:text-redup-gelap">Total {{ $tahun_ajaran->tarifSpp->count() }} tarif.</p>
                </div>

                @if ($tahun_ajaran->tarifSpp->isEmpty())
                    <p class="px-5 py-6 text-sm text-redup dark:text-redup-gelap">Belum ada tarif pada tahun ajaran ini.</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-garis dark:divide-garis-gelap text-sm">
                            <thead class="bg-latar-2 text-left text-xs uppercase tracking-wide text-redup dark:bg-gelap dark:text-redup-gelap">
                                <tr>
                                    <th class="px-5 py-3 font-semibold">Nominal</th>
                                    <th class="px-5 py-3 font-semibold">Keterangan</th>
                                    <th class="px-5 py-3 font-semibold">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-garis dark:divide-garis-gelap">
                                @foreach ($tahun_ajaran->tarifSpp as $tarif)
                                    <tr class="hover:bg-latar-2 dark:hover:bg-gelap">
                                        <td class="px-5 py-3 font-medium text-tinta dark:text-teks-gelap">
                                            Rp{{ number_format((float) $tarif->nominal, 0, ',', '.') }}
                                        </td>
                                        <td class="px-5 py-3 text-redup dark:text-redup-gelap">{{ $tarif->keterangan ?: '-' }}</td>
                                        <td class="px-5 py-3 text-redup dark:text-redup-gelap">{{ ucfirst($tarif->status) }}</td>
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
