@extends('layouts.app')

@section('judul', 'Detail Pengguna')
@section('keterangan', 'Informasi akun')

@section('aksi')
    <a href="{{ route('user.edit', $user) }}"
       class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
        Ubah
    </a>
    <a href="{{ route('user.index') }}"
       class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
        Kembali
    </a>
@endsection

@section('isi')

    @php
        $labelRole = [
            'admin' => 'Administrator',
            'kepala_sekolah' => 'Kepala Sekolah',
            'teller' => 'Teller',
            'siswa' => 'Siswa',
        ];
    @endphp

    <div class="max-w-2xl rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
        <h2 class="text-sm font-semibold text-slate-700">Data akun</h2>
        <dl class="mt-3 space-y-3 text-sm">
            <div>
                <dt class="text-slate-500">Nama</dt>
                <dd class="font-medium text-slate-900">{{ $user->nama }}</dd>
            </div>
            <div>
                <dt class="text-slate-500">Username</dt>
                <dd class="text-slate-700">{{ $user->username }}</dd>
            </div>
            <div>
                <dt class="text-slate-500">Role</dt>
                <dd class="text-slate-700">{{ $labelRole[$user->role] ?? $user->role }}</dd>
            </div>
            <div>
                <dt class="text-slate-500">Status</dt>
                <dd>
                    @if ($user->status === 'aktif')
                        <span class="inline-flex rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-semibold text-emerald-800">Aktif</span>
                    @else
                        <span class="inline-flex rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-semibold text-slate-700">Nonaktif</span>
                    @endif
                </dd>
            </div>
        </dl>

        {{-- Data siswa yang tertaut, kalau akun ini berrole siswa. --}}
        @if ($user->role === 'siswa')
            <div class="mt-5 border-t border-slate-200 pt-4">
                <h3 class="text-sm font-semibold text-slate-700">Data siswa tertaut</h3>
                @if ($user->siswa)
                    <dl class="mt-3 space-y-2 text-sm">
                        <div>
                            <dt class="text-slate-500">Nama siswa</dt>
                            <dd class="text-slate-700">{{ $user->siswa->nama }}</dd>
                        </div>
                        <div>
                            <dt class="text-slate-500">NIS</dt>
                            <dd class="text-slate-700">{{ $user->siswa->nis }}</dd>
                        </div>
                        <div>
                            <dt class="text-slate-500">Kelas</dt>
                            <dd class="text-slate-700">{{ $user->siswa->kelas?->nama_kelas ?? '-' }}</dd>
                        </div>
                    </dl>
                @else
                    <p class="mt-2 text-sm text-slate-500">
                        Akun ini belum ditautkan ke data siswa.
                    </p>
                @endif
            </div>
        @endif
    </div>

@endsection
