@extends('layouts.app')

@section('judul', 'Ubah Siswa')
@section('keterangan', 'Perbarui data siswa')

@section('isi')

    <form method="POST" action="{{ route('siswa.update', $siswa) }}">
        @csrf
        @method('PUT')

        @include('siswa.form')

        <div class="mt-4 flex flex-wrap items-center gap-2">
            <button type="submit"
                    class="rounded-lg bg-teal-700 px-4 py-2 text-sm font-semibold text-white hover:bg-teal-800">
                Simpan perubahan
            </button>
            <a href="{{ route('siswa.index') }}"
               class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                Batal
            </a>
        </div>
    </form>

@endsection
