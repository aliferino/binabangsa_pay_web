@extends('layouts.app')

@section('judul', 'Data Jurusan')
@section('keterangan', 'Daftar jurusan di SMK Bina Bangsa')

@section('aksi')
    @if (auth()->user()->role === 'admin')
        <button type="button" data-modal-buka="tambah" data-modal-target="modal-jurusan"
                class="rounded-lg bg-neon px-4 py-2 text-sm font-semibold text-tinta hover:bg-neon-tekan tombol-tekan">
            Tambah jurusan
        </button>
    @endif
@endsection

@section('isi')

    @include('admin.majors._table')

    @if (auth()->user()->role === 'admin')
        @include('admin.majors._modal')
    @endif

@endsection
