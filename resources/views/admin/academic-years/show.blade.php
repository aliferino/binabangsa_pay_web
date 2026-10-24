@extends('layouts.app')

@section('judul', 'Detail Tahun Ajaran')
@section('keterangan', 'Informasi tahun ajaran, kelas, dan tarifnya')

@section('aksi')
    @if (auth()->user()->role === 'admin')
        <a href="{{ route('tahun-ajaran.edit', $tahun_ajaran) }}" class="inline-flex h-8 w-8 items-center justify-center rounded-md border border-garis text-redup transition-colors hover:border-neon-teks hover:bg-latar-2 hover:text-tinta dark:border-garis-gelap dark:text-redup-gelap dark:hover:bg-gelap dark:hover:text-teks-gelap" title="Ubah" aria-label="Ubah"><svg class="h-[18px] w-[18px]" aria-hidden="true"><use href="#i-ubah"/></svg></a>
    @endif
    <a href="{{ route('tahun-ajaran.index') }}"
       class="inline-flex items-center gap-2 rounded-lg border border-garis bg-white px-4 py-2 text-sm font-medium text-tinta hover:bg-latar-2 dark:border-garis-gelap dark:bg-gelap-2 dark:text-teks-gelap dark:hover:bg-gelap">
        <svg class="h-[18px] w-[18px]" aria-hidden="true"><use href="#i-kembali"/></svg>
        Kembali
    </a>
@endsection

@section('isi')

    @php
        $isActive = $tahun_ajaran->status === 'aktif';
        $classCount = $tahun_ajaran->kelas->count();
        $rateCount = $tahun_ajaran->tarifSpp->count();
        $rateTotal = (float) $tahun_ajaran->tarifSpp->sum('nominal');
    @endphp

    <div class="grid grid-cols-1 gap-5 lg:grid-cols-3">

        {{-- Kartu identitas: nama tahun ajaran jadi judul besar. --}}
        <div class="rounded-xl border border-garis bg-white p-5 shadow-sm dark:border-garis-gelap dark:bg-gelap-2 lg:col-span-1">
            <div class="flex items-start justify-between gap-4">
                <div class="min-w-0">
                    <p class="text-xs uppercase tracking-wide text-redup dark:text-redup-gelap">Tahun ajaran</p>
                    <p class="mt-1 font-mono text-2xl font-bold tabular-nums text-tinta dark:text-teks-gelap">{{ $tahun_ajaran->tahun_ajaran }}</p>
                </div>
                <span class="inline-flex whitespace-nowrap rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $isActive ? 'bg-neon text-tinta' : 'bg-latar-2 text-redup dark:bg-gelap dark:text-redup-gelap' }}">
                    {{ ucfirst($tahun_ajaran->status) }}
                </span>
            </div>

            <dl class="mt-5 divide-y divide-garis border-t border-garis text-sm dark:divide-garis-gelap dark:border-garis-gelap">
                <div class="flex items-start justify-between gap-4 py-2.5">
                    <dt class="text-redup dark:text-redup-gelap">Tanggal mulai</dt>
                    <dd class="text-right font-medium text-tinta dark:text-teks-gelap">{{ $tahun_ajaran->tanggal_mulai?->format('d/m/Y') ?? '-' }}</dd>
                </div>
                <div class="flex items-start justify-between gap-4 py-2.5">
                    <dt class="text-redup dark:text-redup-gelap">Tanggal selesai</dt>
                    <dd class="text-right font-medium text-tinta dark:text-teks-gelap">{{ $tahun_ajaran->tanggal_selesai?->format('d/m/Y') ?? '-' }}</dd>
                </div>
            </dl>
        </div>

        <div class="space-y-5 lg:col-span-2">

            {{-- Ringkasan: jumlah kelas dan tarif, tetap tampil walau kosong. --}}
            <dl class="grid grid-cols-1 gap-px overflow-hidden rounded-xl border border-garis bg-garis sm:grid-cols-3 dark:border-garis-gelap dark:bg-garis-gelap">
                <div class="bg-white px-5 py-4 dark:bg-gelap-2">
                    <dd class="font-mono text-2xl font-bold tabular-nums text-tinta dark:text-teks-gelap">{{ $classCount }}</dd>
                    <dt class="mt-1 text-xs text-redup dark:text-redup-gelap">Jumlah kelas</dt>
                </div>
                <div class="bg-white px-5 py-4 dark:bg-gelap-2">
                    <dd class="font-mono text-2xl font-bold tabular-nums text-tinta dark:text-teks-gelap">{{ $rateCount }}</dd>
                    <dt class="mt-1 text-xs text-redup dark:text-redup-gelap">Jumlah tarif</dt>
                </div>
                <div class="bg-white px-5 py-4 dark:bg-gelap-2">
                    <dd class="font-mono text-2xl font-bold tabular-nums text-tinta dark:text-teks-gelap">Rp{{ number_format($rateTotal, 0, ',', '.') }}</dd>
                    <dt class="mt-1 text-xs text-redup dark:text-redup-gelap">Total tarif</dt>
                </div>
            </dl>

            {{-- Kelas pada tahun ajaran ini. --}}
            <div class="overflow-hidden rounded-xl border border-garis bg-white shadow-sm dark:border-garis-gelap dark:bg-gelap-2">
                <div class="border-b border-garis px-5 py-4 dark:border-garis-gelap">
                    <h2 class="text-sm font-semibold text-tinta dark:text-teks-gelap">Kelas</h2>
                    <p class="mt-1 text-xs text-redup dark:text-redup-gelap">Daftar kelas pada tahun ajaran ini.</p>
                </div>

                @if ($tahun_ajaran->kelas->isEmpty())
                    <p class="px-5 py-6 text-sm text-redup dark:text-redup-gelap">Belum ada kelas pada tahun ajaran ini.</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-garis text-sm dark:divide-garis-gelap">
                            <thead class="bg-latar-2 text-left text-xs uppercase tracking-wide text-redup dark:bg-gelap dark:text-redup-gelap">
                                <tr>
                                    <th class="px-5 py-3 font-semibold">Nama kelas</th>
                                    <th class="px-5 py-3 font-semibold">Tingkat</th>
                                    <th class="px-5 py-3 font-semibold">Jurusan</th>
                                    <th class="px-5 py-3 text-right font-semibold">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-garis dark:divide-garis-gelap">
                                @foreach ($tahun_ajaran->kelas as $kelas)
                                    <tr class="transition-colors hover:bg-latar-2 dark:hover:bg-gelap">
                                        <td class="px-5 py-3.5 font-medium text-tinta dark:text-teks-gelap">{{ $kelas->nama_kelas }}</td>
                                        <td class="px-5 py-3.5 text-redup dark:text-redup-gelap">{{ $kelas->tingkat }}</td>
                                        <td class="px-5 py-3.5 text-redup dark:text-redup-gelap">{{ $kelas->jurusan?->nama_jurusan ?? '-' }}</td>
                                        <td class="px-5 py-3.5 text-right">
                                            <a href="{{ route('kelas.show', $kelas) }}"
                                               class="inline-flex h-8 w-8 items-center justify-center rounded-md border border-garis text-redup transition-colors hover:border-neon-teks hover:bg-latar-2 hover:text-tinta dark:border-garis-gelap dark:text-redup-gelap dark:hover:bg-gelap dark:hover:text-teks-gelap"
                                               title="Lihat" aria-label="Lihat kelas {{ $kelas->nama_kelas }}">
                                                <svg class="h-[18px] w-[18px]" aria-hidden="true"><use href="#i-lihat"/></svg>
                                            </a>
                                        </td>
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
                    <p class="mt-1 text-xs text-redup dark:text-redup-gelap">Daftar tarif pada tahun ajaran ini.</p>
                </div>

                @if ($tahun_ajaran->tarifSpp->isEmpty())
                    <p class="px-5 py-6 text-sm text-redup dark:text-redup-gelap">Belum ada tarif pada tahun ajaran ini.</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-garis text-sm dark:divide-garis-gelap">
                            <thead class="bg-latar-2 text-left text-xs uppercase tracking-wide text-redup dark:bg-gelap dark:text-redup-gelap">
                                <tr>
                                    <th class="px-5 py-3 text-right font-semibold">Nominal</th>
                                    <th class="px-5 py-3 font-semibold">Keterangan</th>
                                    <th class="px-5 py-3 font-semibold">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-garis dark:divide-garis-gelap">
                                @foreach ($tahun_ajaran->tarifSpp as $tarif)
                                    <tr class="transition-colors hover:bg-latar-2 dark:hover:bg-gelap">
                                        <td class="px-5 py-3.5 text-right font-mono tabular-nums font-medium text-tinta dark:text-teks-gelap">
                                            Rp{{ number_format((float) $tarif->nominal, 0, ',', '.') }}
                                        </td>
                                        <td class="px-5 py-3.5 text-redup dark:text-redup-gelap">{{ $tarif->keterangan ?: '-' }}</td>
                                        <td class="px-5 py-3.5 text-redup dark:text-redup-gelap">{{ ucfirst($tarif->status) }}</td>
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