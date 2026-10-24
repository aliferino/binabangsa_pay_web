@extends('layouts.app')

@section('judul', 'Data Siswa')
@section('keterangan', 'Daftar siswa beserta kelas dan tahun ajarannya')

@section('aksi')
    @if (auth()->user()->role === 'admin')
        <button type="button" data-modal-buka="tambah" data-modal-target="modal-siswa"
                class="rounded-lg bg-neon px-4 py-2 text-sm font-semibold text-tinta hover:bg-neon-tekan tombol-tekan">
            Tambah siswa
        </button>
    @endif
@endsection

@section('isi')

    @include('admin.students._table')

    @if (auth()->user()->role === 'admin')
        @include('admin.students._modal')
    @endif

@endsection
