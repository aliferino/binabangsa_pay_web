@extends('layouts.app')

@section('judul', 'Tambah Pengguna')
@section('keterangan', 'Buat akun baru')

@section('isi')

    <form method="POST" action="{{ route('user.store') }}" class="max-w-2xl">
        @csrf

        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="space-y-4">

                <div>
                    <label for="nama" class="mb-1 block text-sm font-medium text-slate-700">
                        Nama <span class="text-red-600">*</span>
                    </label>
                    <input type="text" name="nama" id="nama" value="{{ old('nama') }}" required
                           class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-teal-600 focus:outline-none focus:ring-2 focus:ring-teal-100">
                    @include('partials.error', ['nama' => 'nama'])
                </div>

                <div>
                    <label for="username" class="mb-1 block text-sm font-medium text-slate-700">
                        Username <span class="text-red-600">*</span>
                    </label>
                    <input type="text" name="username" id="username" value="{{ old('username') }}" required
                           autocomplete="off"
                           class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-teal-600 focus:outline-none focus:ring-2 focus:ring-teal-100">
                    @include('partials.error', ['nama' => 'username'])
                </div>

                <div>
                    <label for="password" class="mb-1 block text-sm font-medium text-slate-700">
                        Password <span class="text-red-600">*</span>
                    </label>
                    <input type="password" name="password" id="password" required
                           autocomplete="new-password"
                           class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-teal-600 focus:outline-none focus:ring-2 focus:ring-teal-100">
                    @include('partials.error', ['nama' => 'password'])
                    <p class="mt-1 text-xs text-slate-500">Minimal 6 karakter.</p>
                </div>

                <div>
                    <label for="role" class="mb-1 block text-sm font-medium text-slate-700">
                        Role <span class="text-red-600">*</span>
                    </label>
                    <select name="role" id="role" required
                            class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-teal-600 focus:outline-none focus:ring-2 focus:ring-teal-100">
                        <option value="">Pilih role</option>
                        <option value="admin" @selected(old('role') === 'admin')>Administrator</option>
                        <option value="kepala_sekolah" @selected(old('role') === 'kepala_sekolah')>Kepala Sekolah</option>
                        <option value="teller" @selected(old('role') === 'teller')>Teller</option>
                        <option value="siswa" @selected(old('role') === 'siswa')>Siswa</option>
                    </select>
                    @include('partials.error', ['nama' => 'role'])
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
            <a href="{{ route('user.index') }}"
               class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                Batal
            </a>
        </div>
    </form>

@endsection
