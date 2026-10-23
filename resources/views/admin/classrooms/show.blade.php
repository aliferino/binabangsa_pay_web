@extends('layouts.app')

@section('judul', 'Detail Kelas')
@section('keterangan', 'Informasi kelas dan siswa di dalamnya')

@section('aksi')
    @if (auth()->user()->role === 'admin')
        <a href="{{ route('kelas.edit', $kelas) }}"
           class="rounded-lg border border-garis bg-white px-4 py-2 text-sm font-medium text-tinta hover:bg-latar-2 dark:border-garis-gelap dark:bg-gelap-2 dark:text-teks-gelap dark:hover:bg-gelap">
            Ubah
        </a>
    @endif
    <a href="{{ route('kelas.index') }}"
       class="rounded-lg border border-garis bg-white px-4 py-2 text-sm font-medium text-tinta hover:bg-latar-2 dark:border-garis-gelap dark:bg-gelap-2 dark:text-teks-gelap dark:hover:bg-gelap">
        Kembali
    </a>
@endsection

@section('isi')

    <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">

        {{-- Data kelas. --}}
        <div class="rounded-xl border border-garis bg-white p-5 shadow-sm dark:border-garis-gelap dark:bg-gelap-2 lg:col-span-1">
            <h2 class="text-sm font-semibold text-tinta dark:text-teks-gelap">Data kelas</h2>
            <dl class="mt-3 space-y-3 text-sm">
                <div>
                    <dt class="text-redup dark:text-redup-gelap">Nama kelas</dt>
                    <dd class="font-medium text-tinta dark:text-teks-gelap">{{ $kelas->nama_kelas }}</dd>
                </div>
                <div>
                    <dt class="text-redup dark:text-redup-gelap">Tingkat</dt>
                    <dd class="font-medium text-tinta dark:text-teks-gelap">{{ $kelas->tingkat }}</dd>
                </div>
                <div>
                    <dt class="text-redup dark:text-redup-gelap">Jurusan</dt>
                    <dd class="text-redup dark:text-redup-gelap">{{ $kelas->jurusan?->nama_jurusan ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="text-redup dark:text-redup-gelap">Tahun ajaran</dt>
                    <dd class="text-redup dark:text-redup-gelap">{{ $kelas->tahunAjaran?->tahun_ajaran ?? '-' }}</dd>
                </div>
            </dl>
        </div>

        {{-- Daftar siswa di kelas ini. --}}
        <div class="overflow-hidden rounded-xl border border-garis bg-white shadow-sm dark:border-garis-gelap dark:bg-gelap-2 lg:col-span-2">
            <div class="border-b border-garis px-5 py-4 dark:border-garis-gelap">
                <h2 class="text-sm font-semibold text-tinta dark:text-teks-gelap">Siswa di kelas ini</h2>
                <p class="mt-1 text-xs text-redup dark:text-redup-gelap">
                    Total {{ $kelas->siswa->count() }} siswa.
                </p>
            </div>

            @if ($kelas->siswa->isEmpty())
                <p class="px-5 py-6 text-sm text-redup dark:text-redup-gelap">Belum ada siswa di kelas ini.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-garis dark:divide-garis-gelap text-sm">
                        <thead class="bg-latar-2 text-left text-xs uppercase tracking-wide text-redup dark:bg-gelap dark:text-redup-gelap">
                            <tr>
                                <th class="px-5 py-3 font-semibold">NIS</th>
                                <th class="px-5 py-3 font-semibold">Nama</th>
                                <th class="px-5 py-3 font-semibold">Jenis kelamin</th>
                                <th class="px-5 py-3 font-semibold">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-garis dark:divide-garis-gelap">
                            @foreach ($kelas->siswa as $siswa)
                                <tr class="hover:bg-latar-2 dark:hover:bg-gelap">
                                    <td class="px-5 py-3 font-medium text-tinta dark:text-teks-gelap">{{ $siswa->nis }}</td>
                                    <td class="px-5 py-3 text-tinta dark:text-teks-gelap">{{ $siswa->nama }}</td>
                                    <td class="px-5 py-3 text-redup dark:text-redup-gelap">
                                        {{ $siswa->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }}
                                    </td>
                                    <td class="px-5 py-3 text-redup dark:text-redup-gelap">{{ ucfirst($siswa->status) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

@endsection
