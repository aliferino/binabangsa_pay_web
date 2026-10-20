@extends('layouts.app')

@section('judul', 'Tambah Tarif SPP')
@section('keterangan', 'Isi data tarif SPP baru')

@section('isi')

    <form method="POST" action="{{ route('tarif-spp.store') }}" class="max-w-2xl">
        @csrf

        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="space-y-4">

                <div>
                    <label for="id_tahun_ajaran" class="mb-1 block text-sm font-medium text-slate-700">
                        Tahun ajaran <span class="text-red-600">*</span>
                    </label>
                    <select name="id_tahun_ajaran" id="id_tahun_ajaran" required
                            class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-teal-600 focus:outline-none focus:ring-2 focus:ring-teal-100">
                        <option value="">Pilih tahun ajaran</option>
                        @foreach ($daftar_tahun_ajaran as $tahun)
                            <option value="{{ $tahun->id_tahun_ajaran }}" @selected(old('id_tahun_ajaran') == $tahun->id_tahun_ajaran)>
                                {{ $tahun->tahun_ajaran }}
                            </option>
                        @endforeach
                    </select>
                    @include('partials.error', ['nama' => 'id_tahun_ajaran'])
                </div>

                <div>
                    <label for="nominal" class="mb-1 block text-sm font-medium text-slate-700">
                        Nominal <span class="text-red-600">*</span>
                    </label>
                    <input type="number" name="nominal" id="nominal" min="1" step="1000"
                           value="{{ old('nominal') }}" required placeholder="Contoh: 350000"
                           class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-teal-600 focus:outline-none focus:ring-2 focus:ring-teal-100">
                    @include('partials.error', ['nama' => 'nominal'])
                    <p class="mt-1 text-xs text-slate-500">Isi angka saja, tanpa titik atau tanda rupiah.</p>
                </div>

                <div>
                    <label for="keterangan" class="mb-1 block text-sm font-medium text-slate-700">Keterangan</label>
                    <textarea name="keterangan" id="keterangan" rows="3"
                              class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-teal-600 focus:outline-none focus:ring-2 focus:ring-teal-100">{{ old('keterangan') }}</textarea>
                    @include('partials.error', ['nama' => 'keterangan'])
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
                </div>
            </div>
        </div>

        <div class="mt-4 flex flex-wrap items-center gap-2">
            <button type="submit"
                    class="rounded-lg bg-teal-700 px-4 py-2 text-sm font-semibold text-white hover:bg-teal-800">
                Simpan
            </button>
            <a href="{{ route('tarif-spp.index') }}"
               class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                Batal
            </a>
        </div>
    </form>

@endsection
