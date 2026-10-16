@extends('layouts.app')

@section('judul', 'Dashboard Teller')
@section('keterangan', 'Layanan pembayaran SPP harian')

@section('isi')

    {{-- Angka pokok hari ini. --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">

        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Tagihan jatuh tempo hari ini</p>
            <p class="mt-1 text-3xl font-bold text-slate-900">{{ number_format($tagihan_hari_ini, 0, ',', '.') }}</p>
            <p class="mt-1 text-xs text-slate-400">Jatuh tempo per hari ini</p>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Siswa belum bayar</p>
            <p class="mt-1 text-3xl font-bold text-amber-600">{{ number_format($siswa_belum_bayar, 0, ',', '.') }}</p>
            <p class="mt-1 text-xs text-slate-400">Perlu ditindaklanjuti</p>
        </div>
    </div>

    {{-- Alat bantu kerja teller. --}}
    <div class="mt-6 grid grid-cols-1 gap-4 lg:grid-cols-2">

        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <h2 class="text-sm font-semibold text-slate-700">Cari siswa</h2>
            <p class="mt-1 text-sm text-slate-500">
                Cari berdasarkan NIS, NISN, nama, atau nomor kelas untuk melihat status tagihan.
            </p>
            <a href="{{ route('pencarian.index') }}"
               class="mt-3 inline-block rounded-lg bg-teal-700 px-4 py-2 text-sm font-semibold text-white hover:bg-teal-800">
                Buka pencarian
            </a>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <h2 class="text-sm font-semibold text-slate-700">Tagihan SPP</h2>
            <p class="mt-1 text-sm text-slate-500">
                Lihat daftar tagihan dan saring per bulan, tahun, siswa, atau status.
            </p>
            <a href="{{ route('tagihan-spp.lihat') }}"
               class="mt-3 inline-block rounded-lg bg-teal-700 px-4 py-2 text-sm font-semibold text-white hover:bg-teal-800">
                Lihat tagihan
            </a>
        </div>
    </div>

    {{-- Bagian yang belum bisa ditampilkan. --}}
    <div class="mt-6 rounded-xl border border-dashed border-slate-300 bg-white p-5">
        <h2 class="text-sm font-semibold text-slate-700">Belum tersedia</h2>
        <p class="mt-1 text-sm text-slate-500">
            Nominal transaksi hari ini, transaksi terbaru, dan pencarian virtual account
            belum dapat ditampilkan karena tabel pembayaran dan virtual account belum dibuat.
        </p>
        <ul class="mt-3 grid grid-cols-1 gap-2 text-sm text-slate-500 sm:grid-cols-2 lg:grid-cols-4">
            <li class="rounded-lg bg-slate-50 px-3 py-2">Nominal transaksi hari ini</li>
            <li class="rounded-lg bg-slate-50 px-3 py-2">Transaksi terbaru</li>
            <li class="rounded-lg bg-slate-50 px-3 py-2">Pencarian virtual account</li>
            <li class="rounded-lg bg-slate-50 px-3 py-2">Status pembayaran</li>
        </ul>
    </div>

@endsection
