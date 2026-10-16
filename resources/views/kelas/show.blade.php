@extends('layouts.app')

@section('judul', 'Detail Kelas')
@section('keterangan', 'Informasi kelas dan siswa di dalamnya')

@section('aksi')
    @if (auth()->user()->role === 'admin')
        <a href="{{ route('kelas.edit', $kelas) }}"
           class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
            Ubah
        </a>
    @endif
    <a href="{{ route('kelas.index') }}"
       class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
        Kembali
    </a>
@endsection

@section('isi')

    <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">

        {{-- Data kelas. --}}
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm lg:col-span-1">
            <h2 class="text-sm font-semibold text-slate-700">Data kelas</h2>
            <dl class="mt-3 space-y-3 text-sm">
                <div>
                    <dt class="text-slate-500">Nama kelas</dt>
                    <dd class="font-medium text-slate-900">{{ $kelas->nama_kelas }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500">Tingkat</dt>
                    <dd class="font-medium text-slate-900">{{ $kelas->tingkat }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500">Jurusan</dt>
                    <dd class="text-slate-700">{{ $kelas->jurusan?->nama_jurusan ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500">Tahun ajaran</dt>
                    <dd class="text-slate-700">{{ $kelas->tahunAjaran?->tahun_ajaran ?? '-' }}</dd>
                </div>
            </dl>
        </div>

        {{-- Daftar siswa di kelas ini. --}}
        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm lg:col-span-2">
            <div class="border-b border-slate-200 px-5 py-4">
                <h2 class="text-sm font-semibold text-slate-700">Siswa di kelas ini</h2>
                <p class="mt-1 text-xs text-slate-500">
                    Total {{ $kelas->siswa->count() }} siswa.
                </p>
            </div>

            @if ($kelas->siswa->isEmpty())
                <p class="px-5 py-6 text-sm text-slate-500">Belum ada siswa di kelas ini.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                            <tr>
                                <th class="px-5 py-3 font-semibold">NIS</th>
                                <th class="px-5 py-3 font-semibold">Nama</th>
                                <th class="px-5 py-3 font-semibold">Jenis kelamin</th>
                                <th class="px-5 py-3 font-semibold">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($kelas->siswa as $siswa)
                                <tr class="hover:bg-slate-50">
                                    <td class="px-5 py-3 font-medium text-slate-800">{{ $siswa->nis }}</td>
                                    <td class="px-5 py-3 text-slate-800">{{ $siswa->nama }}</td>
                                    <td class="px-5 py-3 text-slate-600">
                                        {{ $siswa->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }}
                                    </td>
                                    <td class="px-5 py-3 text-slate-600">{{ ucfirst($siswa->status) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

@endsection
