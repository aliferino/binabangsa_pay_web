@extends('layouts.app')

@section('judul', 'Detail Jurusan')
@section('keterangan', 'Informasi jurusan dan kelas di dalamnya')

@section('aksi')
    @if (auth()->user()->role === 'admin')
        <a href="{{ route('jurusan.edit', $jurusan) }}"
           class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
            Ubah
        </a>
    @endif
    <a href="{{ route('jurusan.index') }}"
       class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
        Kembali
    </a>
@endsection

@section('isi')

    <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">

        {{-- Data jurusan. --}}
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm lg:col-span-1">
            <h2 class="text-sm font-semibold text-slate-700">Data jurusan</h2>
            <dl class="mt-3 space-y-3 text-sm">
                <div>
                    <dt class="text-slate-500">Kode jurusan</dt>
                    <dd class="font-medium text-slate-900">{{ $jurusan->kode_jurusan }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500">Nama jurusan</dt>
                    <dd class="font-medium text-slate-900">{{ $jurusan->nama_jurusan }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500">Keterangan</dt>
                    <dd class="text-slate-700">{{ $jurusan->keterangan ?: '-' }}</dd>
                </div>
            </dl>
        </div>

        {{-- Daftar kelas di jurusan ini. --}}
        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm lg:col-span-2">
            <div class="border-b border-slate-200 px-5 py-4">
                <h2 class="text-sm font-semibold text-slate-700">Kelas di jurusan ini</h2>
                <p class="mt-1 text-xs text-slate-500">
                    Total {{ $jurusan->kelas->count() }} kelas.
                </p>
            </div>

            @if ($jurusan->kelas->isEmpty())
                <p class="px-5 py-6 text-sm text-slate-500">Belum ada kelas di jurusan ini.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                            <tr>
                                <th class="px-5 py-3 font-semibold">Nama kelas</th>
                                <th class="px-5 py-3 font-semibold">Tingkat</th>
                                <th class="px-5 py-3 font-semibold">Tahun ajaran</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($jurusan->kelas as $kelas)
                                <tr class="hover:bg-slate-50">
                                    <td class="px-5 py-3 font-medium text-slate-800">{{ $kelas->nama_kelas }}</td>
                                    <td class="px-5 py-3 text-slate-600">{{ $kelas->tingkat }}</td>
                                    <td class="px-5 py-3 text-slate-600">{{ $kelas->tahunAjaran?->tahun_ajaran ?? '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

@endsection
