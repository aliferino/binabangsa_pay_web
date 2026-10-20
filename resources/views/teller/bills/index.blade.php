@extends('layouts.app')

@section('judul', 'Tagihan SPP')
@section('keterangan', 'Daftar tagihan SPP seluruh siswa')

@section('aksi')
    @if (auth()->user()->role === 'admin')
        <button type="button" data-modal-buka="tambah" data-modal-target="modal-tagihan"
                class="rounded-lg bg-teal-700 px-4 py-2 text-sm font-semibold text-white hover:bg-teal-800">
            Tambah tagihan
        </button>

        {{-- Menandai tagihan yang sudah lewat jatuh tempo menjadi tunggakan. --}}
        <form method="POST" action="{{ route('tagihan-spp.tandai-tunggakan') }}"
              onsubmit="return confirm('Tandai semua tagihan yang sudah lewat jatuh tempo sebagai tunggakan?')">
            @csrf
            <button type="submit"
                    class="rounded-lg border border-amber-400 bg-amber-50 px-4 py-2 text-sm font-semibold text-amber-800 hover:bg-amber-100">
                Tandai tunggakan
            </button>
        </form>
    @endif
@endsection

@section('isi')

    @include('teller.bills._table')

    @if (auth()->user()->role === 'admin')
        @include('teller.bills._modal')
        @include('partials._modal-script')
    @endif

@endsection
