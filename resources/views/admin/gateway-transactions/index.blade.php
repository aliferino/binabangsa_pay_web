@extends('layouts.app')

@section('judul', $title)
@section('keterangan', $description)

@section('isi')

    <div class="space-y-5">
        @include('admin.gateway-transactions._summary')
        @include('admin.gateway-transactions._table')
    </div>

@endsection
