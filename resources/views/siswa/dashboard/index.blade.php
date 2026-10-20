@extends('layouts.app')

@section('judul', 'Dashboard Siswa')
@section('keterangan', 'Tagihan SPP dan tunggakan Anda')

@section('isi')

    {{--
        Kasus pertama: akun belum ditautkan ke data siswa.
        Controller mengirim $siswa bernilai null dan pesan penjelasnya.
        Halaman harus tetap tampil tanpa error.
    --}}
    @if ($siswa === null)
        <div class="rounded-xl border border-amber-200 bg-amber-50 p-5">
            <h2 class="text-sm font-semibold text-amber-800">Data belum tersedia</h2>
            <p class="mt-1 text-sm text-amber-700">
                {{ $pesan ?? 'Akun ini belum ditautkan ke data siswa.' }}
            </p>
        </div>
    @else

        {{-- Identitas siswa. --}}
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wide text-teal-700">Selamat datang</p>
            <h2 class="mt-1 text-2xl font-bold text-slate-900">{{ $siswa->nama }}</h2>
            <div class="mt-3 grid grid-cols-1 gap-2 text-sm text-slate-600 sm:grid-cols-3">
                <p>NIS: <span class="font-medium text-slate-800">{{ $siswa->nis }}</span></p>
                <p>NISN: <span class="font-medium text-slate-800">{{ $siswa->nisn }}</span></p>
                <p>Kelas: <span class="font-medium text-slate-800">{{ $siswa->kelas?->nama_kelas ?? '-' }}</span></p>
            </div>
            <p class="mt-1 text-sm text-slate-600">
                Jurusan: <span class="font-medium text-slate-800">{{ $siswa->kelas?->jurusan?->nama_jurusan ?? '-' }}</span>
                <span class="mx-2 text-slate-300">|</span>
                Tahun ajaran: <span class="font-medium text-slate-800">{{ $siswa->tahunAjaran?->tahun_ajaran ?? '-' }}</span>
            </p>
        </div>

        {{-- Total tunggakan. --}}
        <div class="mt-4 rounded-xl border border-red-200 bg-red-50 p-5">
            <p class="text-sm font-medium text-red-700">Total tunggakan</p>
            <p class="mt-1 text-3xl font-bold text-red-800">
                Rp{{ number_format((float) $total_tunggakan, 0, ',', '.') }}
            </p>
        </div>

        {{--
            Tempat virtual account.
            Nomornya belum bisa ditampilkan karena tabel virtual_account belum dibuat.
            Sementara ini hanya keterangan, supaya tidak memanggil relasi yang tidak ada.
        --}}
        <div class="mt-4 rounded-xl border border-dashed border-slate-300 bg-white p-5">
            <p class="text-sm font-semibold text-slate-700">Virtual account</p>
            <p class="mt-1 text-sm text-slate-500">
                Nomor virtual account belum tersedia. Nomor ini akan muncul setelah bagian
                pembayaran dan tabel virtual account selesai dikerjakan.
            </p>
        </div>

        {{-- Riwayat tagihan. --}}
        <div class="mt-6 rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 px-5 py-4">
                <h2 class="text-sm font-semibold text-slate-700">Riwayat tagihan</h2>
                <p class="mt-1 text-xs text-slate-500">
                    Status tagihan Anda per bulan. Warna menandakan keadaannya.
                </p>
            </div>

            @if ($daftar_tagihan->isEmpty())
                <p class="px-5 py-6 text-sm text-slate-500">Belum ada tagihan untuk akun ini.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                            <tr>
                                <th class="px-5 py-3 font-semibold">Bulan</th>
                                <th class="px-5 py-3 font-semibold">Tahun</th>
                                <th class="px-5 py-3 font-semibold">Nominal</th>
                                <th class="px-5 py-3 font-semibold">Jatuh tempo</th>
                                <th class="px-5 py-3 font-semibold">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($daftar_tagihan as $tagihan)
                                <tr class="hover:bg-slate-50">
                                    <td class="px-5 py-3 font-medium text-slate-800">{{ $tagihan->namaBulan() }}</td>
                                    <td class="px-5 py-3 text-slate-600">{{ $tagihan->tahun }}</td>
                                    <td class="px-5 py-3 text-slate-800">
                                        Rp{{ number_format((float) $tagihan->nominal, 0, ',', '.') }}
                                    </td>
                                    <td class="px-5 py-3 text-slate-600">
                                        {{ $tagihan->tanggal_jatuh_tempo?->format('d/m/Y') ?? '-' }}
                                    </td>
                                    <td class="px-5 py-3">
                                        @include('partials._status-badge', ['status' => $tagihan->status])
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    @endif

@endsection
