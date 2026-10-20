@extends('layouts.app')

@section('judul', 'Dashboard Kepala Sekolah')
@section('keterangan', 'Rekap kondisi pembayaran SPP')

@section('isi')

    {{-- Empat angka pokok kondisi siswa. --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">

        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Siswa aktif</p>
            <p class="mt-1 text-3xl font-bold text-slate-900">{{ number_format($siswa_aktif, 0, ',', '.') }}</p>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Siswa lunas</p>
            <p class="mt-1 text-3xl font-bold text-emerald-600">{{ number_format($siswa_lunas, 0, ',', '.') }}</p>
            <p class="mt-1 text-xs text-slate-400">Punya minimal satu tagihan lunas</p>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Siswa belum bayar</p>
            <p class="mt-1 text-3xl font-bold text-amber-600">{{ number_format($siswa_belum_bayar, 0, ',', '.') }}</p>
            <p class="mt-1 text-xs text-slate-400">Masih dalam masa jatuh tempo</p>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Siswa menunggak</p>
            <p class="mt-1 text-3xl font-bold text-red-600">{{ number_format($siswa_menunggak, 0, ',', '.') }}</p>
            <p class="mt-1 text-xs text-slate-400">Sudah lewat jatuh tempo</p>
        </div>
    </div>

    {{-- Nominal tunggakan. --}}
    <div class="mt-4 rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
        <p class="text-sm font-medium text-slate-600">Total tunggakan</p>
        <p class="mt-1 text-3xl font-bold text-red-700">
            Rp{{ number_format((float) $total_tunggakan, 0, ',', '.') }}
        </p>
    </div>

    {{-- Catatan keterbatasan data. --}}
    <div class="mt-6 rounded-xl border border-dashed border-slate-300 bg-white p-5">
        <h2 class="text-sm font-semibold text-slate-700">Belum tersedia</h2>
        <p class="mt-1 text-sm text-slate-500">
            Total penerimaan SPP belum dapat ditampilkan karena tabel pembayaran belum dibuat.
            Angka penerimaan akan muncul setelah bagian pembayaran dikerjakan.
        </p>
    </div>

    <div class="mt-6">
        <h2 class="text-sm font-semibold text-slate-700">Laporan</h2>
        <div class="mt-3 flex flex-wrap gap-2">
            <a href="{{ route('laporan.pembayaran') }}"
               class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                Laporan pembayaran
            </a>
            <a href="{{ route('laporan.tunggakan') }}"
               class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                Laporan tunggakan
            </a>
        </div>
    </div>

@endsection
