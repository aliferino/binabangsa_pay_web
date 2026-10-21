@extends('layouts.app')

@section('judul', 'Tagihan SPP Saya')
@section('keterangan', 'Daftar tagihan SPP Anda')

@section('isi')

    @include('siswa.bills._table')

@endsection
