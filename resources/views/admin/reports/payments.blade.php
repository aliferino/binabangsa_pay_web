@extends('layouts.app')

@section('judul', 'Laporan Pembayaran')
@section('keterangan', 'Rekap tagihan SPP berdasarkan filter yang dipilih')

@section('isi')

    @php
        $namaBulan = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];
    @endphp

    {{-- Form filter. --}}
    <form method="GET" action="{{ route('laporan.pembayaran') }}"
          class="mb-4 rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">

            <div>
                <label for="bulan" class="mb-1 block text-xs font-medium text-slate-600">Bulan</label>
                <select name="bulan" id="bulan"
                        class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-teal-600 focus:outline-none focus:ring-2 focus:ring-teal-100">
                    <option value="">Semua bulan</option>
                    @foreach ($namaBulan as $angka => $nama)
                        <option value="{{ $angka }}" @selected(($filter['bulan'] ?? '') == $angka)>{{ $nama }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="tahun" class="mb-1 block text-xs font-medium text-slate-600">Tahun</label>
                <select name="tahun" id="tahun"
                        class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-teal-600 focus:outline-none focus:ring-2 focus:ring-teal-100">
                    <option value="">Semua tahun</option>
                    @foreach ($daftar_tahun as $tahun)
                        <option value="{{ $tahun }}" @selected(($filter['tahun'] ?? '') == $tahun)>{{ $tahun }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="id_tahun_ajaran" class="mb-1 block text-xs font-medium text-slate-600">Tahun ajaran</label>
                <select name="id_tahun_ajaran" id="id_tahun_ajaran"
                        class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-teal-600 focus:outline-none focus:ring-2 focus:ring-teal-100">
                    <option value="">Semua tahun ajaran</option>
                    @foreach ($daftar_tahun_ajaran as $tahunAjaran)
                        <option value="{{ $tahunAjaran->id_tahun_ajaran }}" @selected(($filter['id_tahun_ajaran'] ?? '') == $tahunAjaran->id_tahun_ajaran)>
                            {{ $tahunAjaran->tahun_ajaran }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="id_jurusan" class="mb-1 block text-xs font-medium text-slate-600">Jurusan</label>
                <select name="id_jurusan" id="id_jurusan"
                        class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-teal-600 focus:outline-none focus:ring-2 focus:ring-teal-100">
                    <option value="">Semua jurusan</option>
                    @foreach ($daftar_jurusan as $jurusan)
                        <option value="{{ $jurusan->id_jurusan }}" @selected(($filter['id_jurusan'] ?? '') == $jurusan->id_jurusan)>
                            {{ $jurusan->nama_jurusan }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="id_kelas" class="mb-1 block text-xs font-medium text-slate-600">Kelas</label>
                <select name="id_kelas" id="id_kelas"
                        class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-teal-600 focus:outline-none focus:ring-2 focus:ring-teal-100">
                    <option value="">Semua kelas</option>
                    @foreach ($daftar_kelas as $kelas)
                        <option value="{{ $kelas->id_kelas }}" @selected(($filter['id_kelas'] ?? '') == $kelas->id_kelas)>
                            {{ $kelas->nama_kelas }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="id_siswa" class="mb-1 block text-xs font-medium text-slate-600">Siswa</label>
                <select name="id_siswa" id="id_siswa"
                        class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-teal-600 focus:outline-none focus:ring-2 focus:ring-teal-100">
                    <option value="">Semua siswa</option>
                    @foreach ($daftar_siswa as $siswa)
                        <option value="{{ $siswa->id_siswa }}" @selected(($filter['id_siswa'] ?? '') == $siswa->id_siswa)>
                            {{ $siswa->nis }} - {{ $siswa->nama }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="status" class="mb-1 block text-xs font-medium text-slate-600">Status</label>
                <select name="status" id="status"
                        class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-teal-600 focus:outline-none focus:ring-2 focus:ring-teal-100">
                    <option value="">Semua status</option>
                    <option value="belum_bayar" @selected(($filter['status'] ?? '') === 'belum_bayar')>Belum bayar</option>
                    <option value="lunas" @selected(($filter['status'] ?? '') === 'lunas')>Lunas</option>
                    <option value="tunggakan" @selected(($filter['status'] ?? '') === 'tunggakan')>Tunggakan</option>
                </select>
            </div>

            <div class="flex items-end gap-2">
                <button type="submit"
                        class="rounded-lg bg-teal-700 px-4 py-2 text-sm font-semibold text-white hover:bg-teal-800">
                    Terapkan
                </button>
                <a href="{{ route('laporan.pembayaran') }}"
                   class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                    Reset
                </a>
            </div>
        </div>
    </form>

    {{-- Ringkasan angka hasil filter. --}}
    <div class="mb-4 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">

        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Total nominal</p>
            <p class="mt-1 text-2xl font-bold text-slate-900">
                Rp{{ number_format((float) $total_nominal, 0, ',', '.') }}
            </p>
            <p class="mt-1 text-xs text-slate-400">Semua tagihan pada filter ini</p>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Lunas</p>
            <p class="mt-1 text-2xl font-bold text-emerald-600">
                Rp{{ number_format((float) $total_lunas, 0, ',', '.') }}
            </p>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Belum bayar</p>
            <p class="mt-1 text-2xl font-bold text-amber-600">
                Rp{{ number_format((float) $total_belum_bayar, 0, ',', '.') }}
            </p>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Tunggakan</p>
            <p class="mt-1 text-2xl font-bold text-red-600">
                Rp{{ number_format((float) $total_tunggakan, 0, ',', '.') }}
            </p>
        </div>
    </div>

    {{-- Tabel hasil. --}}
    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-200 px-5 py-4">
            <h2 class="text-sm font-semibold text-slate-700">Rincian tagihan</h2>
            <p class="mt-1 text-xs text-slate-500">
                {{ $daftar_tagihan->count() }} baris.
                Angka lunas dan belum bayar dihitung dari status tagihan, karena tabel
                pembayaran belum dibuat.
            </p>
        </div>

        @if ($daftar_tagihan->isEmpty())
            <p class="px-5 py-6 text-sm text-slate-500">Tidak ada tagihan yang cocok dengan filter ini.</p>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="px-5 py-3 font-semibold">Siswa</th>
                            <th class="px-5 py-3 font-semibold">Kelas</th>
                            <th class="px-5 py-3 font-semibold">Jurusan</th>
                            <th class="px-5 py-3 font-semibold">Periode</th>
                            <th class="px-5 py-3 font-semibold">Nominal</th>
                            <th class="px-5 py-3 font-semibold">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($daftar_tagihan as $tagihan)
                            <tr class="hover:bg-slate-50">
                                <td class="px-5 py-3">
                                    <span class="font-medium text-slate-800">{{ $tagihan->siswa?->nama ?? '-' }}</span>
                                    <span class="block text-xs text-slate-500">{{ $tagihan->siswa?->nis }}</span>
                                </td>
                                <td class="px-5 py-3 text-slate-600">{{ $tagihan->siswa?->kelas?->nama_kelas ?? '-' }}</td>
                                <td class="px-5 py-3 text-slate-600">{{ $tagihan->siswa?->kelas?->jurusan?->nama_jurusan ?? '-' }}</td>
                                <td class="px-5 py-3 text-slate-700">{{ $tagihan->namaBulan() }} {{ $tagihan->tahun }}</td>
                                <td class="px-5 py-3 text-slate-800">
                                    Rp{{ number_format((float) $tagihan->nominal, 0, ',', '.') }}
                                </td>
                                <td class="px-5 py-3">
                                    @include('partials.lencana-status', ['status' => $tagihan->status])
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-slate-50">
                        <tr>
                            <td colspan="4" class="px-5 py-3 text-right text-sm font-semibold text-slate-700">Total</td>
                            <td colspan="2" class="px-5 py-3 text-sm font-bold text-slate-900">
                                Rp{{ number_format((float) $total_nominal, 0, ',', '.') }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @endif
    </div>

@endsection
