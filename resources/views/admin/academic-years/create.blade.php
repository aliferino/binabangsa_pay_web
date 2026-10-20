@extends('layouts.app')

@section('judul', 'Tambah Tahun Ajaran')
@section('keterangan', 'Isi data tahun ajaran baru')

@section('isi')

    <form method="POST" action="{{ route('tahun-ajaran.store') }}" class="max-w-2xl">
        @csrf

        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="space-y-4">

                <div>
                    <label for="tahun_ajaran" class="mb-1 block text-sm font-medium text-slate-700">
                        Tahun ajaran <span class="text-red-600">*</span>
                    </label>
                    <input type="text" name="tahun_ajaran" id="tahun_ajaran"
                           value="{{ old('tahun_ajaran') }}" required placeholder="Contoh: 2026/2027"
                           class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-teal-600 focus:outline-none focus:ring-2 focus:ring-teal-100">
                    @include('partials.error', ['nama' => 'tahun_ajaran'])
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label for="tanggal_mulai" class="mb-1 block text-sm font-medium text-slate-700">
                            Tanggal mulai <span class="text-red-600">*</span>
                        </label>
                        <input type="date" name="tanggal_mulai" id="tanggal_mulai"
                               value="{{ old('tanggal_mulai') }}" required
                               class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-teal-600 focus:outline-none focus:ring-2 focus:ring-teal-100">
                        @include('partials.error', ['nama' => 'tanggal_mulai'])
                    </div>

                    <div>
                        <label for="tanggal_selesai" class="mb-1 block text-sm font-medium text-slate-700">
                            Tanggal selesai <span class="text-red-600">*</span>
                        </label>
                        <input type="date" name="tanggal_selesai" id="tanggal_selesai"
                               value="{{ old('tanggal_selesai') }}" required
                               class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-teal-600 focus:outline-none focus:ring-2 focus:ring-teal-100">
                        @include('partials.error', ['nama' => 'tanggal_selesai'])
                    </div>
                </div>

                <div>
                    <label for="status" class="mb-1 block text-sm font-medium text-slate-700">
                        Status <span class="text-red-600">*</span>
                    </label>
                    <select name="status" id="status" required
                            class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-teal-600 focus:outline-none focus:ring-2 focus:ring-teal-100">
                        <option value="aktif" @selected(old('status') === 'aktif')>Aktif</option>
                        <option value="nonaktif" @selected(old('status') === 'nonaktif')>Nonaktif</option>
                    </select>
                    @include('partials.error', ['nama' => 'status'])
                    <p class="mt-1 text-xs text-slate-500">
                        Kalau dipilih aktif, tahun ajaran lain otomatis dinonaktifkan.
                    </p>
                </div>
            </div>
        </div>

        <div class="mt-4 flex flex-wrap items-center gap-2">
            <button type="submit"
                    class="rounded-lg bg-teal-700 px-4 py-2 text-sm font-semibold text-white hover:bg-teal-800">
                Simpan
            </button>
            <a href="{{ route('tahun-ajaran.index') }}"
               class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                Batal
            </a>
        </div>
    </form>

@endsection
