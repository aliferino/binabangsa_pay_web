@extends('layouts.app')

@section('judul', 'Data Jurusan')
@section('keterangan', 'Daftar jurusan di SMK Bina Bangsa')

@section('aksi')
    @if (auth()->user()->role === 'admin')
        <a href="{{ route('jurusan.create') }}"
           class="rounded-lg bg-teal-700 px-4 py-2 text-sm font-semibold text-white hover:bg-teal-800">
            Tambah jurusan
        </a>
    @endif
@endsection

@section('isi')

    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-5 py-3 font-semibold">Kode</th>
                        <th class="px-5 py-3 font-semibold">Nama jurusan</th>
                        <th class="px-5 py-3 font-semibold">Keterangan</th>
                        <th class="px-5 py-3 font-semibold">Jumlah kelas</th>
                        <th class="px-5 py-3 text-right font-semibold">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($daftar_jurusan as $jurusan)
                        <tr class="hover:bg-slate-50">
                            <td class="px-5 py-3 font-medium text-slate-800">{{ $jurusan->kode_jurusan }}</td>
                            <td class="px-5 py-3 text-slate-800">{{ $jurusan->nama_jurusan }}</td>
                            <td class="px-5 py-3 text-slate-600">{{ $jurusan->keterangan ?: '-' }}</td>
                            <td class="px-5 py-3 text-slate-600">{{ $jurusan->kelas_count }}</td>
                            <td class="px-5 py-3">
                                <div class="flex flex-wrap items-center justify-end gap-2">
                                    <a href="{{ route('jurusan.show', $jurusan) }}"
                                       class="rounded-md border border-slate-300 px-2.5 py-1 text-xs font-medium text-slate-700 hover:bg-slate-100">
                                        Detail
                                    </a>

                                    @if (auth()->user()->role === 'admin')
                                        <a href="{{ route('jurusan.edit', $jurusan) }}"
                                           class="rounded-md border border-slate-300 px-2.5 py-1 text-xs font-medium text-slate-700 hover:bg-slate-100">
                                            Ubah
                                        </a>

                                        <form method="POST" action="{{ route('jurusan.destroy', $jurusan) }}"
                                              onsubmit="return confirm('Hapus data jurusan ini? Tindakan ini tidak bisa dibatalkan.')">
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
                            <td colspan="5" class="px-5 py-8 text-center text-slate-500">
                                Belum ada data jurusan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

@endsection
