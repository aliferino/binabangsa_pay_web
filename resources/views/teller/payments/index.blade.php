@extends('layouts.app')

@section('judul', 'Pembayaran')
@section('keterangan', 'Daftar pembayaran SPP yang sudah dicatat')

@section('isi')

    @include('teller.payments._table')

@endsection
