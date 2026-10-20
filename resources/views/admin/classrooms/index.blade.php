@extends('layouts.app')

@section('judul', 'Data Kelas')
@section('keterangan', 'Daftar kelas beserta jurusan dan tahun ajarannya')

@section('aksi')
    @if (auth()->user()->role === 'admin')
        <button type="button" data-modal-buka="tambah" data-modal-target="modal-kelas"
                class="rounded-lg bg-teal-700 px-4 py-2 text-sm font-semibold text-white hover:bg-teal-800">
            Tambah kelas
        </button>
    @endif
@endsection

@section('isi')

    @include('admin.classrooms._table')

    @if (auth()->user()->role === 'admin')
        @include('admin.classrooms._modal')
        @include('partials._modal-script')
    @endif

@endsection
