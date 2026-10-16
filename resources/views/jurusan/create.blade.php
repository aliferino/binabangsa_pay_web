@extends('layouts.app')

@section('judul', 'Tambah Jurusan')
@section('keterangan', 'Isi data jurusan baru')

@section('isi')

    <form method="POST" action="{{ route('jurusan.store') }}" class="max-w-2xl">
        @csrf

        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="space-y-4">

                <div>
                    <label for="kode_jurusan" class="mb-1 block text-sm font-medium text-slate-700">
                        Kode jurusan <span class="text-red-600">*</span>
                    </label>
                    <input type="text" name="kode_jurusan" id="kode_jurusan"
                           value="{{ old('kode_jurusan') }}" required
                           class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-teal-600 focus:outline-none focus:ring-2 focus:ring-teal-100">
                    @include('partials.error', ['nama' => 'kode_jurusan'])
                </div>

                <div>
                    <label for="nama_jurusan" class="mb-1 block text-sm font-medium text-slate-700">
                        Nama jurusan <span class="text-red-600">*</span>
                    </label>
                    <input type="text" name="nama_jurusan" id="nama_jurusan"
                           value="{{ old('nama_jurusan') }}" required
                           class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-teal-600 focus:outline-none focus:ring-2 focus:ring-teal-100">
                    @include('partials.error', ['nama' => 'nama_jurusan'])
                </div>

                <div>
                    <label for="keterangan" class="mb-1 block text-sm font-medium text-slate-700">Keterangan</label>
                    <textarea name="keterangan" id="keterangan" rows="3"
                              class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-teal-600 focus:outline-none focus:ring-2 focus:ring-teal-100">{{ old('keterangan') }}</textarea>
                    @include('partials.error', ['nama' => 'keterangan'])
                </div>
            </div>
        </div>

        <div class="mt-4 flex flex-wrap items-center gap-2">
            <button type="submit"
                    class="rounded-lg bg-teal-700 px-4 py-2 text-sm font-semibold text-white hover:bg-teal-800">
                Simpan
            </button>
            <a href="{{ route('jurusan.index') }}"
               class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                Batal
            </a>
        </div>
    </form>

@endsection
