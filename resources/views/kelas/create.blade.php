@extends('layouts.app')

@section('judul', 'Tambah Kelas')
@section('keterangan', 'Isi data kelas baru')

@section('isi')

    <form method="POST" action="{{ route('kelas.store') }}" class="max-w-2xl">
        @csrf

        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="space-y-4">

                <div>
                    <label for="nama_kelas" class="mb-1 block text-sm font-medium text-slate-700">
                        Nama kelas <span class="text-red-600">*</span>
                    </label>
                    <input type="text" name="nama_kelas" id="nama_kelas"
                           value="{{ old('nama_kelas') }}" required placeholder="Contoh: XI RPL 1"
                           class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-teal-600 focus:outline-none focus:ring-2 focus:ring-teal-100">
                    @include('partials.error', ['nama' => 'nama_kelas'])
                </div>

                <div>
                    <label for="tingkat" class="mb-1 block text-sm font-medium text-slate-700">
                        Tingkat <span class="text-red-600">*</span>
                    </label>
                    <input type="text" name="tingkat" id="tingkat"
                           value="{{ old('tingkat') }}" required placeholder="Contoh: XI"
                           class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-teal-600 focus:outline-none focus:ring-2 focus:ring-teal-100">
                    @include('partials.error', ['nama' => 'tingkat'])
                </div>

                <div>
                    <label for="id_jurusan" class="mb-1 block text-sm font-medium text-slate-700">
                        Jurusan <span class="text-red-600">*</span>
                    </label>
                    <select name="id_jurusan" id="id_jurusan" required
                            class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-teal-600 focus:outline-none focus:ring-2 focus:ring-teal-100">
                        <option value="">Pilih jurusan</option>
                        @foreach ($daftar_jurusan as $jurusan)
                            <option value="{{ $jurusan->id_jurusan }}" @selected(old('id_jurusan') == $jurusan->id_jurusan)>
                                {{ $jurusan->nama_jurusan }}
                            </option>
                        @endforeach
                    </select>
                    @include('partials.error', ['nama' => 'id_jurusan'])
                </div>

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
            </div>
        </div>

        <div class="mt-4 flex flex-wrap items-center gap-2">
            <button type="submit"
                    class="rounded-lg bg-teal-700 px-4 py-2 text-sm font-semibold text-white hover:bg-teal-800">
                Simpan
            </button>
            <a href="{{ route('kelas.index') }}"
               class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                Batal
            </a>
        </div>
    </form>

@endsection
