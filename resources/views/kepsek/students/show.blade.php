@extends('layouts.app')

@section('judul', 'Detail Siswa')
@section('keterangan', 'Profil siswa dan daftar tagihannya')

@section('aksi')
    <a href="{{ route('kepsek.students.index') }}"
       class="inline-flex items-center gap-2 rounded-lg border border-garis bg-white px-4 py-2 text-sm font-medium text-tinta hover:bg-latar-2 dark:border-garis-gelap dark:bg-gelap-2 dark:text-teks-gelap dark:hover:bg-gelap">
        <svg class="h-[18px] w-[18px]" aria-hidden="true"><use href="#i-kembali"/></svg>
        Kembali
    </a>
@endsection

@section('isi')

    @php
        $bills = $siswa->tagihan;
        $billCount = $bills->count();
        $billTotal = (float) $bills->sum('nominal');
        $billOverdue = $bills->filter(fn ($item) => $item->status !== 'lunas' && $item->tanggal_jatuh_tempo?->isPast())->count();
        $isActive = $siswa->status === 'aktif';
    @endphp

    <div class="grid grid-cols-1 gap-5 lg:grid-cols-3">

        {{-- Kartu identitas: blok utama lebih menonjol, detail di bawahnya meredup. --}}
        <div class="rounded-xl border border-garis bg-white p-5 shadow-sm dark:border-garis-gelap dark:bg-gelap-2 lg:col-span-1">
            <div class="flex items-start gap-4">
                <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-neon text-base font-bold text-tinta">
                    {{ mb_substr($siswa->nama, 0, 1) }}
                </span>
                <div class="min-w-0">
                    <p class="truncate text-base font-semibold text-tinta dark:text-teks-gelap">{{ $siswa->nama }}</p>
                    <p class="mt-0.5 font-mono text-xs text-redup dark:text-redup-gelap">NIS {{ $siswa->nis }}</p>
                    <span class="mt-2 inline-flex whitespace-nowrap rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $isActive ? 'bg-neon text-tinta' : 'bg-latar-2 text-redup dark:bg-gelap dark:text-redup-gelap' }}">
                        {{ ucfirst($siswa->status) }}
                    </span>
                </div>
            </div>

            <dl class="mt-5 divide-y divide-garis border-t border-garis text-sm dark:divide-garis-gelap dark:border-garis-gelap">
                <div class="flex items-start justify-between gap-4 py-2.5">
                    <dt class="text-redup dark:text-redup-gelap">NISN</dt>
                    <dd class="text-right font-medium text-tinta dark:text-teks-gelap">{{ $siswa->nisn }}</dd>
                </div>
                <div class="flex items-start justify-between gap-4 py-2.5">
                    <dt class="text-redup dark:text-redup-gelap">Jenis kelamin</dt>
                    <dd class="text-right font-medium text-tinta dark:text-teks-gelap">{{ $siswa->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }}</dd>
                </div>
                <div class="flex items-start justify-between gap-4 py-2.5">
                    <dt class="text-redup dark:text-redup-gelap">Tempat, tanggal lahir</dt>
                    <dd class="text-right font-medium text-tinta dark:text-teks-gelap">
                        {{ $siswa->tempat_lahir }},
                        {{ $siswa->tanggal_lahir?->format('d/m/Y') ?? '-' }}
                    </dd>
                </div>
                <div class="flex items-start justify-between gap-4 py-2.5">
                    <dt class="text-redup dark:text-redup-gelap">Alamat</dt>
                    <dd class="text-right font-medium text-tinta dark:text-teks-gelap">{{ $siswa->alamat }}</dd>
                </div>
                <div class="flex items-start justify-between gap-4 py-2.5">
                    <dt class="text-redup dark:text-redup-gelap">Nomor HP</dt>
                    <dd class="text-right font-medium text-tinta dark:text-teks-gelap">{{ $siswa->no_hp ?: '-' }}</dd>
                </div>
                <div class="flex items-start justify-between gap-4 py-2.5">
                    <dt class="text-redup dark:text-redup-gelap">Nama orang tua</dt>
                    <dd class="text-right font-medium text-tinta dark:text-teks-gelap">{{ $siswa->nama_orang_tua ?: '-' }}</dd>
                </div>
                <div class="flex items-start justify-between gap-4 py-2.5">
                    <dt class="text-redup dark:text-redup-gelap">Kelas</dt>
                    <dd class="text-right font-medium text-tinta dark:text-teks-gelap">
                        {{ $siswa->kelas?->nama_kelas ?? '-' }}
                        @if ($siswa->kelas?->jurusan)
                            <span class="block text-xs font-normal text-redup dark:text-redup-gelap">{{ $siswa->kelas->jurusan->nama_jurusan }}</span>
                        @endif
                    </dd>
                </div>
                <div class="flex items-start justify-between gap-4 py-2.5">
                    <dt class="text-redup dark:text-redup-gelap">Tahun ajaran</dt>
                    <dd class="text-right font-medium text-tinta dark:text-teks-gelap">{{ $siswa->tahunAjaran?->tahun_ajaran ?? '-' }}</dd>
                </div>
                <div class="flex items-start justify-between gap-4 py-2.5">
                    <dt class="text-redup dark:text-redup-gelap">Akun login</dt>
                    <dd class="text-right font-medium text-tinta dark:text-teks-gelap">
                        @if ($siswa->user)
                            {{ $siswa->user->nama }}
                            <span class="block font-mono text-xs font-normal text-redup dark:text-redup-gelap">{{ $siswa->user->username }}</span>
                        @else
                            Belum ditautkan
                        @endif
                    </dd>
                </div>
            </dl>
        </div>

        <div class="space-y-5 lg:col-span-2">

            {{-- Ringkasan tagihan: tiga angka, tetap tampil walau kosong. --}}
            <dl class="grid grid-cols-1 gap-px overflow-hidden rounded-xl border border-garis bg-garis sm:grid-cols-3 dark:border-garis-gelap dark:bg-garis-gelap">
                <div class="bg-white px-5 py-4 dark:bg-gelap-2">
                    <dd class="font-mono text-2xl font-bold tabular-nums text-tinta dark:text-teks-gelap">{{ $billCount }}</dd>
                    <dt class="mt-1 text-xs text-redup dark:text-redup-gelap">Jumlah tagihan</dt>
                </div>
                <div class="bg-white px-5 py-4 dark:bg-gelap-2">
                    <dd class="font-mono text-2xl font-bold tabular-nums text-tinta dark:text-teks-gelap">Rp{{ number_format($billTotal, 0, ',', '.') }}</dd>
                    <dt class="mt-1 text-xs text-redup dark:text-redup-gelap">Total nominal</dt>
                </div>
                <div class="bg-white px-5 py-4 dark:bg-gelap-2">
                    <dd class="font-mono text-2xl font-bold tabular-nums {{ $billOverdue > 0 ? 'text-red-700 dark:text-red-400' : 'text-tinta dark:text-teks-gelap' }}">{{ $billOverdue }}</dd>
                    <dt class="mt-1 text-xs text-redup dark:text-redup-gelap">Tunggakan</dt>
                </div>
            </dl>

            <div class="overflow-hidden rounded-xl border border-garis bg-white shadow-sm dark:border-garis-gelap dark:bg-gelap-2">
                <div class="border-b border-garis px-5 py-4 dark:border-garis-gelap">
                    <h2 class="text-sm font-semibold text-tinta dark:text-teks-gelap">Tagihan SPP</h2>
                    <p class="mt-1 text-xs text-redup dark:text-redup-gelap">Riwayat tagihan siswa ini.</p>
                </div>

                @if ($bills->isEmpty())
                    <p class="px-5 py-6 text-sm text-redup dark:text-redup-gelap">Siswa ini belum punya tagihan.</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-garis text-sm dark:divide-garis-gelap">
                            <thead class="bg-latar-2 text-left text-xs uppercase tracking-wide text-redup dark:bg-gelap dark:text-redup-gelap">
                                <tr>
                                    <th class="px-5 py-3 font-semibold">Bulan</th>
                                    <th class="px-5 py-3 font-semibold">Tahun</th>
                                    <th class="px-5 py-3 text-right font-semibold">Nominal</th>
                                    <th class="px-5 py-3 font-semibold">Jatuh tempo</th>
                                    <th class="px-5 py-3 font-semibold">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-garis dark:divide-garis-gelap">
                                @foreach ($bills->sortBy([['tahun', 'desc'], ['bulan', 'desc']]) as $tagihan)
                                    <tr class="transition-colors hover:bg-latar-2 dark:hover:bg-gelap">
                                        <td class="px-5 py-3.5 font-medium text-tinta dark:text-teks-gelap">{{ $tagihan->namaBulan() }}</td>
                                        <td class="px-5 py-3.5 text-redup dark:text-redup-gelap">{{ $tagihan->tahun }}</td>
                                        <td class="px-5 py-3.5 text-right font-mono tabular-nums text-tinta dark:text-teks-gelap">
                                            Rp{{ number_format((float) $tagihan->nominal, 0, ',', '.') }}
                                        </td>
                                        <td class="px-5 py-3.5 text-redup dark:text-redup-gelap">
                                            {{ $tagihan->tanggal_jatuh_tempo?->format('d/m/Y') ?? '-' }}
                                        </td>
                                        <td class="px-5 py-3.5">
                                            @include('layouts.partials._status-badge', ['tagihan' => $tagihan])
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>

@endsection