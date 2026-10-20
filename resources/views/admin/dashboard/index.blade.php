@extends('layouts.app')

@section('judul', 'Dashboard Admin')
@section('keterangan', 'Ringkasan data sekolah')

@section('isi')

    {{-- Ringkasan angka utama. --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">

        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Total siswa</p>
            <p class="mt-1 text-3xl font-bold text-slate-900">{{ number_format($total_siswa, 0, ',', '.') }}</p>
            <p class="mt-1 text-xs text-slate-400">Seluruh siswa terdaftar</p>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Total tagihan</p>
            <p class="mt-1 text-3xl font-bold text-slate-900">{{ number_format($total_tagihan, 0, ',', '.') }}</p>
            <p class="mt-1 text-xs text-slate-400">Semua tagihan SPP</p>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Siswa belum bayar</p>
            <p class="mt-1 text-3xl font-bold text-amber-600">{{ number_format($total_siswa_belum_bayar, 0, ',', '.') }}</p>
            <p class="mt-1 text-xs text-slate-400">Punya tagihan berstatus belum bayar</p>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Tagihan tunggakan</p>
            <p class="mt-1 text-3xl font-bold text-red-600">{{ number_format($total_tunggakan, 0, ',', '.') }}</p>
            <p class="mt-1 text-xs text-slate-400">Sudah lewat jatuh tempo</p>
        </div>
    </div>

    {{-- Nominal tunggakan. --}}
    <div class="mt-4 rounded-xl border border-red-200 bg-red-50 p-5">
        <p class="text-sm font-medium text-red-700">Total nominal tunggakan</p>
        <p class="mt-1 text-3xl font-bold text-red-800">
            Rp{{ number_format((float) $nominal_tunggakan, 0, ',', '.') }}
        </p>
        <p class="mt-1 text-xs text-red-600">
            Jumlah rupiah dari seluruh tagihan yang berstatus tunggakan.
        </p>
    </div>

    {{-- Bagian yang belum bisa ditampilkan. --}}
    <div class="mt-6 rounded-xl border border-dashed border-slate-300 bg-white p-5">
        <h2 class="text-sm font-semibold text-slate-700">Belum tersedia</h2>
        <p class="mt-1 text-sm text-slate-500">
            Data pembayaran, transaksi, dan virtual account belum dapat ditampilkan karena
            tabelnya belum dibuat. Bagian ini akan diisi setelah skema pembayaran disetujui.
        </p>
        <ul class="mt-3 grid grid-cols-1 gap-2 text-sm text-slate-500 sm:grid-cols-2 lg:grid-cols-4">
            <li class="rounded-lg bg-slate-50 px-3 py-2">Total pembayaran</li>
            <li class="rounded-lg bg-slate-50 px-3 py-2">Transaksi hari ini</li>
            <li class="rounded-lg bg-slate-50 px-3 py-2">Grafik pembayaran</li>
            <li class="rounded-lg bg-slate-50 px-3 py-2">Transaksi terbaru</li>
        </ul>
    </div>

    {{-- Jalan pintas ke menu yang sering dipakai. --}}
    <div class="mt-6">
        <h2 class="text-sm font-semibold text-slate-700">Jalan pintas</h2>
        <div class="mt-3 flex flex-wrap gap-2">
            <a href="{{ route('siswa.index') }}"
               class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                Data siswa
            </a>
            <a href="{{ route('tagihan-spp.index') }}"
               class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                Tagihan SPP
            </a>
            <a href="{{ route('laporan.tunggakan') }}"
               class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                Laporan tunggakan
            </a>
            <a href="{{ route('user.index') }}"
               class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                Pengguna
            </a>
        </div>
    </div>

@endsection
