@extends('layouts.app')

@section('judul', 'Data Pengguna')
@section('keterangan', 'Akun yang bisa masuk ke aplikasi')

@section('aksi')
    <button type="button" data-modal-buka="tambah" data-modal-target="modal-pengguna"
            class="rounded-lg bg-teal-700 px-4 py-2 text-sm font-semibold text-white hover:bg-teal-800">
        Tambah pengguna
    </button>
@endsection

@section('isi')

    @include('admin.users._table')
    @include('admin.users._modal')
    @include('partials._modal-script')

@endsection
