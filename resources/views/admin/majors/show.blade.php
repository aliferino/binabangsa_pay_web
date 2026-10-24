@extends('layouts.app')

@section('judul', 'Detail Jurusan')
@section('keterangan', 'Informasi jurusan dan kelas di dalamnya')

@section('aksi')
    @if (auth()->user()->role === 'admin')
        <a href="{{ route('jurusan.edit', $jurusan) }}" class="inline-flex h-8 w-8 items-center justify-center rounded-md border border-garis text-redup transition-colors hover:border-neon-teks hover:bg-latar-2 hover:text-tinta dark:border-garis-gelap dark:text-redup-gelap dark:hover:bg-gelap dark:hover:text-teks-gelap" title="Ubah" aria-label="Ubah"><svg class="h-[18px] w-[18px]" aria-hidden="true"><use href="#i-ubah"/></svg></a>
    @endif
    <a href="{{ route('jurusan.index') }}"
       class="inline-flex items-center gap-2 rounded-lg border border-garis bg-white px-4 py-2 text-sm font-medium text-tinta hover:bg-latar-2 dark:border-garis-gelap dark:bg-gelap-2 dark:text-teks-gelap dark:hover:bg-gelap">
        <svg class="h-[18px] w-[18px]" aria-hidden="true"><use href="#i-kembali"/></svg>
        Kembali
    </a>
@endsection

@section('isi')

    @php
        $classCount = $jurusan->kelas->count();
        $studentCount = $jurusan->kelas->sum(fn ($item) => $item->siswa->count());
    @endphp

    <div class="grid grid-cols-1 gap-5 lg:grid-cols-3">

        {{-- Kartu identitas: kode jurusan jadi penanda utama. --}}
        <div class="rounded-xl border border-garis bg-white p-5 shadow-sm dark:border-garis-gelap dark:bg-gelap-2 lg:col-span-1">
            <div class="flex items-start gap-4">
                <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-neon font-mono text-sm font-bold text-tinta">
                    {{ $jurusan->kode_jurusan }}
                </span>
                <div class="min-w-0">
                    <p class="truncate text-base font-semibold text-tinta dark:text-teks-gelap">{{ $jurusan->nama_jurusan }}</p>
                    <p class="mt-0.5 text-xs text-redup dark:text-redup-gelap">Kode {{ $jurusan->kode_jurusan }}</p>
                </div>
            </div>

            <dl class="mt-5 divide-y divide-garis border-t border-garis text-sm dark:divide-garis-gelap dark:border-garis-gelap">
                <div class="flex items-start justify-between gap-4 py-2.5">
                    <dt class="text-redup dark:text-redup-gelap">Kode jurusan</dt>
                    <dd class="text-right font-mono font-medium text-tinta dark:text-teks-gelap">{{ $jurusan->kode_jurusan }}</dd>
                </div>
                <div class="flex items-start justify-between gap-4 py-2.5">
                    <dt class="text-redup dark:text-redup-gelap">Nama jurusan</dt>
                    <dd class="text-right font-medium text-tinta dark:text-teks-gelap">{{ $jurusan->nama_jurusan }}</dd>
                </div>
                <div class="flex items-start justify-between gap-4 py-2.5">
                    <dt class="text-redup dark:text-redup-gelap">Keterangan</dt>
                    <dd class="text-right font-medium text-tinta dark:text-teks-gelap">{{ $jurusan->keterangan ?: '-' }}</dd>
                </div>
            </dl>
        </div>

        <div class="space-y-5 lg:col-span-2">

            {{-- Ringkasan, tetap tampil walau kosong. --}}
            <dl class="grid grid-cols-1 gap-px overflow-hidden rounded-xl border border-garis bg-garis sm:grid-cols-2 dark:border-garis-gelap dark:bg-garis-gelap">
                <div class="bg-white px-5 py-4 dark:bg-gelap-2">
                    <dd class="font-mono text-2xl font-bold tabular-nums text-tinta dark:text-teks-gelap">{{ $classCount }}</dd>
                    <dt class="mt-1 text-xs text-redup dark:text-redup-gelap">Jumlah kelas</dt>
                </div>
                <div class="bg-white px-5 py-4 dark:bg-gelap-2">
                    <dd class="font-mono text-2xl font-bold tabular-nums text-tinta dark:text-teks-gelap">{{ $studentCount }}</dd>
                    <dt class="mt-1 text-xs text-redup dark:text-redup-gelap">Jumlah siswa</dt>
                </div>
            </dl>

            {{-- Daftar kelas di jurusan ini. --}}
            <div class="overflow-hidden rounded-xl border border-garis bg-white shadow-sm dark:border-garis-gelap dark:bg-gelap-2">
                <div class="border-b border-garis px-5 py-4 dark:border-garis-gelap">
                    <h2 class="text-sm font-semibold text-tinta dark:text-teks-gelap">Kelas di jurusan ini</h2>
                    <p class="mt-1 text-xs text-redup dark:text-redup-gelap">Daftar kelas yang termasuk jurusan ini.</p>
                </div>

                @if ($jurusan->kelas->isEmpty())
                    <p class="px-5 py-6 text-sm text-redup dark:text-redup-gelap">Belum ada kelas di jurusan ini.</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-garis text-sm dark:divide-garis-gelap">
                            <thead class="bg-latar-2 text-left text-xs uppercase tracking-wide text-redup dark:bg-gelap dark:text-redup-gelap">
                                <tr>
                                    <th class="px-5 py-3 font-semibold">Nama kelas</th>
                                    <th class="px-5 py-3 font-semibold">Tingkat</th>
                                    <th class="px-5 py-3 font-semibold">Tahun ajaran</th>
                                    <th class="px-5 py-3 text-right font-semibold">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-garis dark:divide-garis-gelap">
                                @foreach ($jurusan->kelas as $kelas)
                                    <tr class="transition-colors hover:bg-latar-2 dark:hover:bg-gelap">
                                        <td class="px-5 py-3.5 font-medium text-tinta dark:text-teks-gelap">{{ $kelas->nama_kelas }}</td>
                                        <td class="px-5 py-3.5 text-redup dark:text-redup-gelap">{{ $kelas->tingkat }}</td>
                                        <td class="px-5 py-3.5 text-redup dark:text-redup-gelap">{{ $kelas->tahunAjaran?->tahun_ajaran ?? '-' }}</td>
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
        </div>
    </div>

@endsection