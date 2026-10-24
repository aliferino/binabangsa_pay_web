@extends('layouts.app')

@section('judul', 'Data Kelas')
@section('keterangan', 'Daftar kelas beserta jurusan dan tahun ajarannya')

@section('aksi')
    @if (auth()->user()->role === 'admin')
        <button type="button" data-modal-buka="tambah" data-modal-target="modal-kelas"
                class="rounded-lg bg-neon px-4 py-2 text-sm font-semibold text-tinta hover:bg-neon-tekan tombol-tekan">
            Tambah kelas
        </button>
    @endif
@endsection

@section('isi')

    @include('admin.classrooms._table')

    @if (auth()->user()->role === 'admin')
        @include('admin.classrooms._modal')
    @endif

@endsection
