@extends('layouts.app')

@section('judul', 'Virtual Account')
@section('keterangan', 'Nomor pembayaran tetap milik tiap siswa. Satu siswa satu Virtual Account.')

@section('aksi')
    @if (auth()->user()->role === 'admin')
        <button type="button" data-modal-buka="tambah" data-modal-target="modal-virtual-account"
                class="tombol-tekan rounded-lg bg-neon px-4 py-2 text-sm font-semibold text-tinta hover:bg-neon-tekan">
            Buat Virtual Account
        </button>
    @endif
@endsection

@section('isi')

    @include('teller.virtual-accounts._table')

    @if (auth()->user()->role === 'admin')
        @include('teller.virtual-accounts._modal')
        @include('teller.virtual-accounts._bank_modal')
    @endif

@endsection