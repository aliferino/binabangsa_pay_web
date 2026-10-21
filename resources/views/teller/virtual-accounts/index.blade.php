@extends('layouts.app')

@section('judul', 'Virtual Account')
@section('keterangan', 'Daftar Virtual Account siswa')

@section('isi')

    @include('teller.virtual-accounts._table')

@endsection
