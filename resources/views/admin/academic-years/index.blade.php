@extends('layouts.app')

@section('judul', 'Tahun Ajaran')
@section('keterangan', 'Daftar tahun ajaran. Hanya satu yang boleh berstatus aktif.')

@section('aksi')
    @if (auth()->user()->role === 'admin')
        <button type="button" data-modal-buka="tambah" data-modal-target="modal-tahun-ajaran"
                class="rounded-lg bg-teal-700 px-4 py-2 text-sm font-semibold text-white hover:bg-teal-800">
            Tambah tahun ajaran
        </button>
    @endif
@endsection

@section('isi')

    @include('admin.academic-years._table')

    @if (auth()->user()->role === 'admin')
        @include('admin.academic-years._modal')
        @include('partials._modal-script')
    @endif

@endsection
