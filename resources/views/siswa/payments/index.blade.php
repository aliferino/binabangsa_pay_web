@extends('layouts.app')

@section('judul', 'Riwayat Pembayaran')
@section('keterangan', 'Daftar pembayaran SPP Anda')

@section('isi')

    @include('siswa.payments._table')

@endsection
