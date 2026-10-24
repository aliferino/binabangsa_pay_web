@extends('layouts.app')

@section('judul', 'Detail Pengguna')
@section('keterangan', 'Informasi akun')

@section('aksi')
    <a href="{{ route('user.edit', $user) }}" class="inline-flex h-8 w-8 items-center justify-center rounded-md border border-garis text-redup transition-colors hover:border-neon-teks hover:bg-latar-2 hover:text-tinta dark:border-garis-gelap dark:text-redup-gelap dark:hover:bg-gelap dark:hover:text-teks-gelap" title="Ubah" aria-label="Ubah"><svg class="h-[18px] w-[18px]" aria-hidden="true"><use href="#i-ubah"/></svg></a>
    <a href="{{ route('user.index') }}"
       class="inline-flex items-center gap-2 rounded-lg border border-garis bg-white px-4 py-2 text-sm font-medium text-tinta hover:bg-latar-2 dark:border-garis-gelap dark:bg-gelap-2 dark:text-teks-gelap dark:hover:bg-gelap">
        <svg class="h-[18px] w-[18px]" aria-hidden="true"><use href="#i-kembali"/></svg>
        Kembali
    </a>
@endsection

@section('isi')

    @php
        $roleLabels = [
            'admin' => 'Administrator',
            'kepala_sekolah' => 'Kepala Sekolah',
            'teller' => 'Teller',
            'siswa' => 'Siswa',
        ];
        $isActive = $user->status === 'aktif';
    @endphp

    <div class="max-w-2xl space-y-5">

        <div class="rounded-xl border border-garis bg-white p-5 shadow-sm dark:border-garis-gelap dark:bg-gelap-2">
            <div class="flex items-start gap-4">
                <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-neon text-base font-bold text-tinta">
                    {{ mb_substr($user->nama, 0, 1) }}
                </span>
                <div class="min-w-0">
                    <p class="truncate text-base font-semibold text-tinta dark:text-teks-gelap">{{ $user->nama }}</p>
                    <p class="mt-0.5 font-mono text-xs text-redup dark:text-redup-gelap">{{ $user->username }}</p>
                    <span class="mt-2 inline-flex whitespace-nowrap rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $isActive ? 'bg-neon text-tinta' : 'bg-latar-2 text-redup dark:bg-gelap dark:text-redup-gelap' }}">
                        {{ $isActive ? 'Aktif' : 'Nonaktif' }}
                    </span>
                </div>
            </div>

            <dl class="mt-5 divide-y divide-garis border-t border-garis text-sm dark:divide-garis-gelap dark:border-garis-gelap">
                <div class="flex items-start justify-between gap-4 py-2.5">
                    <dt class="text-redup dark:text-redup-gelap">Nama</dt>
                    <dd class="text-right font-medium text-tinta dark:text-teks-gelap">{{ $user->nama }}</dd>
                </div>
                <div class="flex items-start justify-between gap-4 py-2.5">
                    <dt class="text-redup dark:text-redup-gelap">Username</dt>
                    <dd class="text-right font-mono font-medium text-tinta dark:text-teks-gelap">{{ $user->username }}</dd>
                </div>
                <div class="flex items-start justify-between gap-4 py-2.5">
                    <dt class="text-redup dark:text-redup-gelap">Role</dt>
                    <dd class="text-right font-medium text-tinta dark:text-teks-gelap">{{ $roleLabels[$user->role] ?? $user->role }}</dd>
                </div>
                <div class="flex items-start justify-between gap-4 py-2.5">
                    <dt class="text-redup dark:text-redup-gelap">Status</dt>
                    <dd class="text-right font-medium text-tinta dark:text-teks-gelap">{{ $isActive ? 'Aktif' : 'Nonaktif' }}</dd>
                </div>
            </dl>
        </div>

        {{-- Data siswa yang tertaut, kalau akun ini berrole siswa. --}}
        @if ($user->role === 'siswa')
            <div class="rounded-xl border border-garis bg-white p-5 shadow-sm dark:border-garis-gelap dark:bg-gelap-2">
                <h2 class="text-sm font-semibold text-tinta dark:text-teks-gelap">Data siswa tertaut</h2>

                @if ($user->siswa)
                    <dl class="mt-4 divide-y divide-garis border-t border-garis text-sm dark:divide-garis-gelap dark:border-garis-gelap">
                        <div class="flex items-start justify-between gap-4 py-2.5">
                            <dt class="text-redup dark:text-redup-gelap">Nama siswa</dt>
                            <dd class="text-right font-medium text-tinta dark:text-teks-gelap">{{ $user->siswa->nama }}</dd>
                        </div>
                        <div class="flex items-start justify-between gap-4 py-2.5">
                            <dt class="text-redup dark:text-redup-gelap">NIS</dt>
                            <dd class="text-right font-mono font-medium text-tinta dark:text-teks-gelap">{{ $user->siswa->nis }}</dd>
                        </div>
                        <div class="flex items-start justify-between gap-4 py-2.5">
                            <dt class="text-redup dark:text-redup-gelap">Kelas</dt>
                            <dd class="text-right font-medium text-tinta dark:text-teks-gelap">{{ $user->siswa->kelas?->nama_kelas ?? '-' }}</dd>
                        </div>
                    </dl>

                    <a href="{{ route('siswa.show', $user->siswa) }}"
                       class="mt-4 inline-flex items-center gap-2 rounded-lg border border-garis bg-white px-3 py-2 text-sm font-medium text-tinta transition-colors hover:border-neon-teks hover:bg-latar-2 dark:border-garis-gelap dark:bg-gelap-2 dark:text-teks-gelap dark:hover:bg-gelap">
                        <svg class="h-[18px] w-[18px]" aria-hidden="true"><use href="#i-lihat"/></svg>
                        Lihat detail siswa
                    </a>
                @else
                    <p class="mt-3 text-sm text-redup dark:text-redup-gelap">Akun ini belum ditautkan ke data siswa.</p>
                @endif
            </div>
        @endif
    </div>

@endsection