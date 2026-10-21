@extends('layouts.app')

@section('judul', 'Data Siswa')
@section('keterangan', 'Daftar siswa beserta kelas dan tahun ajarannya')

@section('isi')

    @include('kepsek.students._table')

@endsection
