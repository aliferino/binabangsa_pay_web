@extends('layouts.app')

@section('judul', 'Detail Siswa')
@section('keterangan', 'Profil siswa dan daftar tagihannya')

@section('aksi')
    @if (auth()->user()->role === 'admin')
        <a href="{{ route('siswa.edit', $siswa) }}"
           class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
            Ubah
        </a>
    @endif
    <a href="{{ route('siswa.index') }}"
       class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
        Kembali
    </a>
@endsection

@section('isi')

    <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">

        {{-- Profil siswa. --}}
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm lg:col-span-1">
            <h2 class="text-sm font-semibold text-slate-700">Profil siswa</h2>
            <dl class="mt-3 space-y-3 text-sm">
                <div>
                    <dt class="text-slate-500">Nama</dt>
                    <dd class="font-medium text-slate-900">{{ $siswa->nama }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500">NIS</dt>
                    <dd class="text-slate-700">{{ $siswa->nis }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500">NISN</dt>
                    <dd class="text-slate-700">{{ $siswa->nisn }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500">Jenis kelamin</dt>
                    <dd class="text-slate-700">{{ $siswa->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500">Tempat, tanggal lahir</dt>
                    <dd class="text-slate-700">
                        {{ $siswa->tempat_lahir }},
                        {{ $siswa->tanggal_lahir?->format('d/m/Y') ?? '-' }}
                    </dd>
                </div>
                <div>
                    <dt class="text-slate-500">Alamat</dt>
                    <dd class="text-slate-700">{{ $siswa->alamat }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500">Nomor HP</dt>
                    <dd class="text-slate-700">{{ $siswa->no_hp ?: '-' }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500">Nama orang tua</dt>
                    <dd class="text-slate-700">{{ $siswa->nama_orang_tua ?: '-' }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500">Kelas</dt>
                    <dd class="text-slate-700">
                        {{ $siswa->kelas?->nama_kelas ?? '-' }}
                        @if ($siswa->kelas?->jurusan)
                            <span class="text-slate-500">({{ $siswa->kelas->jurusan->nama_jurusan }})</span>
                        @endif
                    </dd>
                </div>
                <div>
                    <dt class="text-slate-500">Tahun ajaran</dt>
                    <dd class="text-slate-700">{{ $siswa->tahunAjaran?->tahun_ajaran ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500">Akun login</dt>
                    <dd class="text-slate-700">
                        @if ($siswa->user)
                            {{ $siswa->user->nama }} ({{ $siswa->user->username }})
                        @else
                            Belum ditautkan
                        @endif
                    </dd>
                </div>
                <div>
                    <dt class="text-slate-500">Status</dt>
                    <dd class="text-slate-700">{{ ucfirst($siswa->status) }}</dd>
                </div>
            </dl>
        </div>

        {{-- Daftar tagihan siswa. --}}
        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm lg:col-span-2">
            <div class="border-b border-slate-200 px-5 py-4">
                <h2 class="text-sm font-semibold text-slate-700">Tagihan SPP</h2>
                <p class="mt-1 text-xs text-slate-500">
                    Total {{ $siswa->tagihan->count() }} tagihan.
                </p>
            </div>

            @if ($siswa->tagihan->isEmpty())
                <p class="px-5 py-6 text-sm text-slate-500">Siswa ini belum punya tagihan.</p>
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
                            @foreach ($siswa->tagihan->sortBy([['tahun', 'desc'], ['bulan', 'desc']]) as $tagihan)
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
    </div>

@endsection
