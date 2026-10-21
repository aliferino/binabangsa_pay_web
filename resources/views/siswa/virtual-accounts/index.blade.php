@extends('layouts.app')

@section('judul', 'Virtual Account Saya')
@section('keterangan', 'Nomor Virtual Account Anda untuk pembayaran SPP')

@section('isi')

    @include('siswa.virtual-accounts._table')

@endsection
