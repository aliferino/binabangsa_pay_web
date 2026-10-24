@extends('layouts.app')

@section('judul', 'Detail Virtual Account')
@section('keterangan', 'Rincian satu Virtual Account siswa')

@section('aksi')
    <a href="{{ route('virtual-account.index') }}"
       class="inline-flex items-center gap-2 rounded-lg border border-garis bg-white px-4 py-2 text-sm font-medium text-tinta hover:bg-latar-2 dark:border-garis-gelap dark:bg-gelap-2 dark:text-teks-gelap dark:hover:bg-gelap">
        <svg class="h-[18px] w-[18px]" aria-hidden="true"><use href="#i-kembali"/></svg>Kembali
    </a>
@endsection

@section('isi')

    <div class="space-y-5">
        <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">

            {{-- Data siswa pemilik nomor ini. --}}
            <div class="rounded-xl border border-garis bg-white p-5 shadow-sm dark:border-garis-gelap dark:bg-gelap-2">
                <h2 class="text-sm font-semibold text-redup dark:text-redup-gelap">Siswa</h2>
                <dl class="mt-4 space-y-4 text-sm">
                    <div class="flex items-baseline justify-between gap-4">
                        <dt class="text-redup dark:text-redup-gelap">Nama</dt>
                        <dd class="text-right font-medium text-tinta dark:text-teks-gelap">{{ $virtualAccount->siswa?->nama ?? '-' }}</dd>
                    </div>
                    <div class="flex items-baseline justify-between gap-4">
                        <dt class="text-redup dark:text-redup-gelap">NIS</dt>
                        <dd class="text-right text-tinta dark:text-teks-gelap">{{ $virtualAccount->siswa?->nis ?? '-' }}</dd>
                    </div>
                    <div class="flex items-baseline justify-between gap-4">
                        <dt class="text-redup dark:text-redup-gelap">Kelas</dt>
                        <dd class="text-right text-tinta dark:text-teks-gelap">{{ $virtualAccount->siswa?->kelas?->nama_kelas ?? '-' }}</dd>
                    </div>
                </dl>
            </div>

            {{-- Rincian nomor pembayaran. --}}
            <div class="rounded-xl border border-garis bg-white p-5 shadow-sm dark:border-garis-gelap dark:bg-gelap-2">
                <h2 class="text-sm font-semibold text-redup dark:text-redup-gelap">Rincian Virtual Account</h2>
                <dl class="mt-4 space-y-4 text-sm">

                    @if ($virtualAccount->account_number)
                        <div class="flex items-baseline justify-between gap-4">
                            <dt class="text-redup dark:text-redup-gelap">Nomor VA (bank)</dt>
                            <dd class="font-mono text-lg font-bold text-tinta dark:text-teks-gelap">{{ $virtualAccount->account_number }}</dd>
                        </div>
                    @endif

                    <div class="flex items-baseline justify-between gap-4">
                        <dt class="text-redup dark:text-redup-gelap">Nomor VA internal</dt>
                        <dd class="font-mono font-medium text-tinta dark:text-teks-gelap">{{ $virtualAccount->nomor_va }}</dd>
                    </div>

                    <div class="flex items-baseline justify-between gap-4">
                        <dt class="text-redup dark:text-redup-gelap">Prefix</dt>
                        <dd class="text-right text-tinta dark:text-teks-gelap">{{ $virtualAccount->prefix }}</dd>
                    </div>

                    @if ($virtualAccount->bank_code)
                        <div class="flex items-baseline justify-between gap-4">
                            <dt class="text-redup dark:text-redup-gelap">Kode bank</dt>
                            <dd class="text-right text-tinta dark:text-teks-gelap">{{ $virtualAccount->bank_code }}</dd>
                        </div>
                    @endif

                    @if ($virtualAccount->xendit_id)
                        <div class="flex items-baseline justify-between gap-4">
                            <dt class="shrink-0 text-redup dark:text-redup-gelap">Xendit ID</dt>
                            <dd class="truncate font-mono text-xs text-redup dark:text-redup-gelap">{{ $virtualAccount->xendit_id }}</dd>
                        </div>
                    @endif

                    @if ($virtualAccount->external_id)
                        <div class="flex items-baseline justify-between gap-4">
                            <dt class="shrink-0 text-redup dark:text-redup-gelap">External ID</dt>
                            <dd class="truncate font-mono text-xs text-redup dark:text-redup-gelap">{{ $virtualAccount->external_id }}</dd>
                        </div>
                    @endif

                    <div class="flex items-center justify-between gap-4">
                        <dt class="text-redup dark:text-redup-gelap">Status</dt>
                        <dd>
                            <span class="inline-flex whitespace-nowrap rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $virtualAccount->aktif() ? 'bg-emerald-100 text-emerald-800' : 'bg-latar-2 text-tinta dark:bg-gelap dark:text-teks-gelap' }}">
                                {{ $virtualAccount->aktif() ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </dd>
                    </div>

                    @if ($virtualAccount->expired_at)
                        <div class="flex items-baseline justify-between gap-4">
                            <dt class="text-redup dark:text-redup-gelap">Kedaluwarsa</dt>
                            <dd class="text-right text-tinta dark:text-teks-gelap">{{ $virtualAccount->expired_at->format('d/m/Y H:i') }}</dd>
                        </div>
                    @endif
                </dl>
            </div>
        </div>

        {{--
            Riwayat transaksi yang masuk ke nomor ini. Status dan nominal dibaca
            dari kolom gateway yang sebenarnya (status_transaksi, nominal).
        --}}
        @if ($virtualAccount->transaksi->isNotEmpty())
            <div class="overflow-hidden rounded-xl border border-garis bg-white shadow-sm dark:border-garis-gelap dark:bg-gelap-2">
                <div class="border-b border-garis px-5 py-4 dark:border-garis-gelap">
                    <h2 class="text-sm font-semibold text-redup dark:text-redup-gelap">Riwayat transaksi gateway</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-garis text-sm dark:divide-garis-gelap">
                        <thead class="bg-latar-2 text-left text-xs uppercase tracking-wide text-redup dark:bg-gelap dark:text-redup-gelap">
                            <tr>
                                <th class="px-5 py-3.5 font-semibold">Tanggal</th>
                                <th class="px-5 py-3.5 font-semibold">ID transaksi</th>
                                <th class="px-5 py-3.5 font-semibold">Status</th>
                                <th class="px-5 py-3.5 text-right font-semibold">Nominal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-garis dark:divide-garis-gelap">
                            @foreach ($virtualAccount->transaksi as $transaksi)
                                <tr class="hover:bg-latar-2 dark:hover:bg-gelap">
                                    <td class="whitespace-nowrap px-5 py-3.5 text-redup dark:text-redup-gelap">
                                        {{ $transaksi->created_at?->format('d/m/Y H:i') ?? '-' }}
                                    </td>
                                    <td class="px-5 py-3.5">
                                        <span class="font-mono text-xs text-tinta dark:text-teks-gelap">{{ $transaksi->transaction_id }}</span>
                                    </td>
                                    <td class="px-5 py-3.5">
                                        @include('admin.gateway-transactions._status', ['status' => $transaksi->status_transaksi])
                                    </td>
                                    <td class="whitespace-nowrap px-5 py-3.5 text-right text-tinta dark:text-teks-gelap">
                                        Rp{{ number_format((float) $transaksi->nominal, 0, ',', '.') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>

@endsection
