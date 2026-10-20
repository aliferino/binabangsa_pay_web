@extends('layouts.app')

@section('judul', 'Tarif SPP')
@section('keterangan', 'Daftar tarif SPP per tahun ajaran')

@section('aksi')
    @if (auth()->user()->role === 'admin')
        <button type="button" data-modal-buka="tambah" data-modal-target="modal-tarif-spp"
                class="rounded-lg bg-teal-700 px-4 py-2 text-sm font-semibold text-white hover:bg-teal-800">
            Tambah tarif
        </button>
    @endif
@endsection

@section('isi')

    @include('admin.tuition-rates._table')

    @if (auth()->user()->role === 'admin')
        @include('admin.tuition-rates._modal')
        @include('partials._modal-script')
    @endif

@endsection
