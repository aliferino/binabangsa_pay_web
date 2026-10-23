@extends('layouts.app')

@section('judul', 'Detail Jurusan')
@section('keterangan', 'Informasi jurusan dan kelas di dalamnya')

@section('aksi')
    @if (auth()->user()->role === 'admin')
<a href="{{ route('jurusan.edit', $jurusan) }}" class="inline-flex h-8 w-8 items-center justify-center rounded-md border border-garis text-redup transition-colors hover:border-neon-teks hover:bg-latar-2 hover:text-tinta dark:border-garis-gelap dark:text-redup-gelap dark:hover:bg-gelap dark:hover:text-teks-gelap" title="Ubah" aria-label="Ubah"><svg class="h-[18px] w-[18px]" aria-hidden="true"><use href="#i-ubah"/></svg>
        </a>
    @endif
    <a href="{{ route('jurusan.index') }}"
       class="rounded-lg border border-garis bg-white px-4 py-2 text-sm font-medium text-tinta hover:bg-latar-2 dark:border-garis-gelap dark:bg-gelap-2 dark:text-teks-gelap dark:hover:bg-gelap">
        Kembali
    </a>
@endsection

@section('isi')

    <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">

        {{-- Data jurusan. --}}
        <div class="rounded-xl border border-garis bg-white p-5 shadow-sm dark:border-garis-gelap dark:bg-gelap-2 lg:col-span-1">
            <h2 class="text-sm font-semibold text-tinta dark:text-teks-gelap">Data jurusan</h2>
            <dl class="mt-3 space-y-3 text-sm">
                <div>
                    <dt class="text-redup dark:text-redup-gelap">Kode jurusan</dt>
                    <dd class="font-medium text-tinta dark:text-teks-gelap">{{ $jurusan->kode_jurusan }}</dd>
                </div>
                <div>
                    <dt class="text-redup dark:text-redup-gelap">Nama jurusan</dt>
                    <dd class="font-medium text-tinta dark:text-teks-gelap">{{ $jurusan->nama_jurusan }}</dd>
                </div>
                <div>
                    <dt class="text-redup dark:text-redup-gelap">Keterangan</dt>
                    <dd class="text-redup dark:text-redup-gelap">{{ $jurusan->keterangan ?: '-' }}</dd>
                </div>
            </dl>
        </div>

        {{-- Daftar kelas di jurusan ini. --}}
        <div class="overflow-hidden rounded-xl border border-garis bg-white shadow-sm dark:border-garis-gelap dark:bg-gelap-2 lg:col-span-2">
            <div class="border-b border-garis px-5 py-4 dark:border-garis-gelap">
                <h2 class="text-sm font-semibold text-tinta dark:text-teks-gelap">Kelas di jurusan ini</h2>
                <p class="mt-1 text-xs text-redup dark:text-redup-gelap">
                    Total {{ $jurusan->kelas->count() }} kelas.
                </p>
            </div>

            @if ($jurusan->kelas->isEmpty())
                <p class="px-5 py-6 text-sm text-redup dark:text-redup-gelap">Belum ada kelas di jurusan ini.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-garis dark:divide-garis-gelap text-sm">
                        <thead class="bg-latar-2 text-left text-xs uppercase tracking-wide text-redup dark:bg-gelap dark:text-redup-gelap">
                            <tr>
                                <th class="px-5 py-3 font-semibold">Nama kelas</th>
                                <th class="px-5 py-3 font-semibold">Tingkat</th>
                                <th class="px-5 py-3 font-semibold">Tahun ajaran</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-garis dark:divide-garis-gelap">
                            @foreach ($jurusan->kelas as $kelas)
                                <tr class="hover:bg-latar-2 dark:hover:bg-gelap">
                                    <td class="px-5 py-3 font-medium text-tinta dark:text-teks-gelap">{{ $kelas->nama_kelas }}</td>
                                    <td class="px-5 py-3 text-redup dark:text-redup-gelap">{{ $kelas->tingkat }}</td>
                                    <td class="px-5 py-3 text-redup dark:text-redup-gelap">{{ $kelas->tahunAjaran?->tahun_ajaran ?? '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

@endsection
