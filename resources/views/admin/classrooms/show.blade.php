@extends('layouts.app')

@section('judul', 'Detail Kelas')
@section('keterangan', 'Informasi kelas dan siswa di dalamnya')

@section('aksi')
    @if (auth()->user()->role === 'admin')
        <a href="{{ route('kelas.edit', $kelas) }}" class="inline-flex h-8 w-8 items-center justify-center rounded-md border border-garis text-redup transition-colors hover:border-neon-teks hover:bg-latar-2 hover:text-tinta dark:border-garis-gelap dark:text-redup-gelap dark:hover:bg-gelap dark:hover:text-teks-gelap" title="Ubah" aria-label="Ubah"><svg class="h-[18px] w-[18px]" aria-hidden="true"><use href="#i-ubah"/></svg></a>
    @endif
    <a href="{{ route('kelas.index') }}"
       class="inline-flex items-center gap-2 rounded-lg border border-garis bg-white px-4 py-2 text-sm font-medium text-tinta hover:bg-latar-2 dark:border-garis-gelap dark:bg-gelap-2 dark:text-teks-gelap dark:hover:bg-gelap">
        <svg class="h-[18px] w-[18px]" aria-hidden="true"><use href="#i-kembali"/></svg>
        Kembali
    </a>
@endsection

@section('isi')

    @php
        $studentCount = $kelas->siswa->count();
        $activeCount = $kelas->siswa->where('status', 'aktif')->count();
    @endphp

    <div class="grid grid-cols-1 gap-5 lg:grid-cols-3">

        {{-- Kartu identitas: nama kelas jadi judul besar. --}}
        <div class="rounded-xl border border-garis bg-white p-5 shadow-sm dark:border-garis-gelap dark:bg-gelap-2 lg:col-span-1">
            <p class="text-xs uppercase tracking-wide text-redup dark:text-redup-gelap">Nama kelas</p>
            <p class="mt-1 text-2xl font-bold text-tinta dark:text-teks-gelap">{{ $kelas->nama_kelas }}</p>

            <dl class="mt-5 divide-y divide-garis border-t border-garis text-sm dark:divide-garis-gelap dark:border-garis-gelap">
                <div class="flex items-start justify-between gap-4 py-2.5">
                    <dt class="text-redup dark:text-redup-gelap">Tingkat</dt>
                    <dd class="text-right font-medium text-tinta dark:text-teks-gelap">{{ $kelas->tingkat }}</dd>
                </div>
                <div class="flex items-start justify-between gap-4 py-2.5">
                    <dt class="text-redup dark:text-redup-gelap">Jurusan</dt>
                    <dd class="text-right font-medium text-tinta dark:text-teks-gelap">{{ $kelas->jurusan?->nama_jurusan ?? '-' }}</dd>
                </div>
                <div class="flex items-start justify-between gap-4 py-2.5">
                    <dt class="text-redup dark:text-redup-gelap">Tahun ajaran</dt>
                    <dd class="text-right font-medium text-tinta dark:text-teks-gelap">{{ $kelas->tahunAjaran?->tahun_ajaran ?? '-' }}</dd>
                </div>
            </dl>
        </div>

        <div class="space-y-5 lg:col-span-2">

            {{-- Ringkasan siswa, tetap tampil walau kosong. --}}
            <dl class="grid grid-cols-1 gap-px overflow-hidden rounded-xl border border-garis bg-garis sm:grid-cols-2 dark:border-garis-gelap dark:bg-garis-gelap">
                <div class="bg-white px-5 py-4 dark:bg-gelap-2">
                    <dd class="font-mono text-2xl font-bold tabular-nums text-tinta dark:text-teks-gelap">{{ $studentCount }}</dd>
                    <dt class="mt-1 text-xs text-redup dark:text-redup-gelap">Jumlah siswa</dt>
                </div>
                <div class="bg-white px-5 py-4 dark:bg-gelap-2">
                    <dd class="font-mono text-2xl font-bold tabular-nums {{ $activeCount > 0 ? 'text-neon-teks dark:text-neon' : 'text-tinta dark:text-teks-gelap' }}">{{ $activeCount }}</dd>
                    <dt class="mt-1 text-xs text-redup dark:text-redup-gelap">Siswa aktif</dt>
                </div>
            </dl>

            {{-- Daftar siswa di kelas ini. --}}
            <div class="overflow-hidden rounded-xl border border-garis bg-white shadow-sm dark:border-garis-gelap dark:bg-gelap-2">
                <div class="border-b border-garis px-5 py-4 dark:border-garis-gelap">
                    <h2 class="text-sm font-semibold text-tinta dark:text-teks-gelap">Siswa di kelas ini</h2>
                    <p class="mt-1 text-xs text-redup dark:text-redup-gelap">Daftar siswa yang terdaftar di kelas ini.</p>
                </div>

                @if ($kelas->siswa->isEmpty())
                    <p class="px-5 py-6 text-sm text-redup dark:text-redup-gelap">Belum ada siswa di kelas ini.</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-garis text-sm dark:divide-garis-gelap">
                            <thead class="bg-latar-2 text-left text-xs uppercase tracking-wide text-redup dark:bg-gelap dark:text-redup-gelap">
                                <tr>
                                    <th class="px-5 py-3 font-semibold">NIS</th>
                                    <th class="px-5 py-3 font-semibold">Nama</th>
                                    <th class="px-5 py-3 font-semibold">Jenis kelamin</th>
                                    <th class="px-5 py-3 font-semibold">Status</th>
                                    <th class="px-5 py-3 text-right font-semibold">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-garis dark:divide-garis-gelap">
                                @foreach ($kelas->siswa as $siswa)
                                    <tr class="transition-colors hover:bg-latar-2 dark:hover:bg-gelap">
                                        <td class="px-5 py-3.5 font-mono tabular-nums text-redup dark:text-redup-gelap">{{ $siswa->nis }}</td>
                                        <td class="px-5 py-3.5 font-medium text-tinta dark:text-teks-gelap">{{ $siswa->nama }}</td>
                                        <td class="px-5 py-3.5 text-redup dark:text-redup-gelap">
                                            {{ $siswa->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }}
                                        </td>
                                        <td class="px-5 py-3.5 text-redup dark:text-redup-gelap">{{ ucfirst($siswa->status) }}</td>
                                        <td class="px-5 py-3.5 text-right">
                                            <a href="{{ route('siswa.show', $siswa) }}"
                                               class="inline-flex h-8 w-8 items-center justify-center rounded-md border border-garis text-redup transition-colors hover:border-neon-teks hover:bg-latar-2 hover:text-tinta dark:border-garis-gelap dark:text-redup-gelap dark:hover:bg-gelap dark:hover:text-teks-gelap"
                                               title="Lihat" aria-label="Lihat detail {{ $siswa->nama }}">
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