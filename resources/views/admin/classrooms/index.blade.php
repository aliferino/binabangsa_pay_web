@extends('layouts.app')

@section('judul', 'Data Kelas')
@section('keterangan', 'Daftar kelas beserta jurusan dan tahun ajarannya')

@section('aksi')
    @if (auth()->user()->role === 'admin')
        <a href="{{ route('kelas.create') }}"
           class="rounded-lg bg-teal-700 px-4 py-2 text-sm font-semibold text-white hover:bg-teal-800">
            Tambah kelas
        </a>
    @endif
@endsection

@section('isi')

    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-5 py-3 font-semibold">Nama kelas</th>
                        <th class="px-5 py-3 font-semibold">Tingkat</th>
                        <th class="px-5 py-3 font-semibold">Jurusan</th>
                        <th class="px-5 py-3 font-semibold">Tahun ajaran</th>
                        <th class="px-5 py-3 font-semibold">Jumlah siswa</th>
                        <th class="px-5 py-3 text-right font-semibold">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($daftar_kelas as $kelas)
                        <tr class="hover:bg-slate-50">
                            <td class="px-5 py-3 font-medium text-slate-800">{{ $kelas->nama_kelas }}</td>
                            <td class="px-5 py-3 text-slate-600">{{ $kelas->tingkat }}</td>
                            <td class="px-5 py-3 text-slate-600">{{ $kelas->jurusan?->nama_jurusan ?? '-' }}</td>
                            <td class="px-5 py-3 text-slate-600">{{ $kelas->tahunAjaran?->tahun_ajaran ?? '-' }}</td>
                            <td class="px-5 py-3 text-slate-600">{{ $kelas->siswa_count }}</td>
                            <td class="px-5 py-3">
                                <div class="flex flex-wrap items-center justify-end gap-2">
                                    <a href="{{ route('kelas.show', $kelas) }}"
                                       class="rounded-md border border-slate-300 px-2.5 py-1 text-xs font-medium text-slate-700 hover:bg-slate-100">
                                        Detail
                                    </a>

                                    @if (auth()->user()->role === 'admin')
                                        <a href="{{ route('kelas.edit', $kelas) }}"
                                           class="rounded-md border border-slate-300 px-2.5 py-1 text-xs font-medium text-slate-700 hover:bg-slate-100">
                                            Ubah
                                        </a>

                                        <form method="POST" action="{{ route('kelas.destroy', $kelas) }}"
                                              onsubmit="return confirm('Hapus data kelas ini? Tindakan ini tidak bisa dibatalkan.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="rounded-md border border-red-300 px-2.5 py-1 text-xs font-medium text-red-700 hover:bg-red-50">
                                                Hapus
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-8 text-center text-slate-500">
                                Belum ada data kelas.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

@endsection
