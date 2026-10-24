@extends('layouts.app')

@section('judul', 'Detail Transaksi Gateway')
@section('keterangan', 'Rincian satu transaksi beserta balasan mentah dari payment gateway.')

@section('aksi')
    <a href="{{ route($indexRoute) }}"
       class="inline-flex items-center gap-2 rounded-lg border border-garis bg-white px-4 py-2 text-sm font-medium text-tinta hover:bg-latar-2 dark:border-garis-gelap dark:bg-gelap-2 dark:text-teks-gelap dark:hover:bg-gelap">
        <svg class="h-[18px] w-[18px]" aria-hidden="true"><use href="#i-kembali"/></svg>Kembali
    </a>
@endsection

@section('isi')

    <div class="space-y-5">
        <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">

            {{-- Student who owns the transaction. --}}
            <div class="rounded-xl border border-garis bg-white p-5 shadow-sm dark:border-garis-gelap dark:bg-gelap-2">
                <h2 class="text-sm font-semibold text-redup dark:text-redup-gelap">Siswa</h2>
                <dl class="mt-4 space-y-4 text-sm">
                    <div class="flex items-baseline justify-between gap-4">
                        <dt class="text-redup dark:text-redup-gelap">Nama</dt>
                        <dd class="text-right font-medium text-tinta dark:text-teks-gelap">{{ $transaction->siswa?->nama ?? '-' }}</dd>
                    </div>
                    <div class="flex items-baseline justify-between gap-4">
                        <dt class="text-redup dark:text-redup-gelap">NIS</dt>
                        <dd class="text-right text-tinta dark:text-teks-gelap">{{ $transaction->siswa?->nis ?? '-' }}</dd>
                    </div>
                    <div class="flex items-baseline justify-between gap-4">
                        <dt class="text-redup dark:text-redup-gelap">Kelas</dt>
                        <dd class="text-right text-tinta dark:text-teks-gelap">{{ $transaction->siswa?->kelas?->nama_kelas ?? '-' }}</dd>
                    </div>
                </dl>
            </div>

            {{-- Transaction detail. --}}
            <div class="rounded-xl border border-garis bg-white p-5 shadow-sm dark:border-garis-gelap dark:bg-gelap-2">
                <h2 class="text-sm font-semibold text-redup dark:text-redup-gelap">Rincian transaksi</h2>
                <dl class="mt-4 space-y-4 text-sm">
                    <div class="flex items-baseline justify-between gap-4">
                        <dt class="shrink-0 text-redup dark:text-redup-gelap">ID transaksi</dt>
                        <dd class="truncate font-mono text-xs font-medium text-tinta dark:text-teks-gelap">{{ $transaction->transaction_id }}</dd>
                    </div>

                    <div class="flex items-baseline justify-between gap-4">
                        <dt class="text-redup dark:text-redup-gelap">Nominal</dt>
                        <dd class="text-right text-lg font-bold text-tinta dark:text-teks-gelap">
                            Rp{{ number_format((float) $transaction->nominal, 0, ',', '.') }}
                        </dd>
                    </div>

                    <div class="flex items-center justify-between gap-4">
                        <dt class="text-redup dark:text-redup-gelap">Status</dt>
                        <dd>@include('admin.gateway-transactions._status', ['status' => $transaction->status_transaksi])</dd>
                    </div>

                    @if ($transaction->payment_channel)
                        <div class="flex items-baseline justify-between gap-4">
                            <dt class="text-redup dark:text-redup-gelap">Kanal pembayaran</dt>
                            <dd class="text-right text-tinta dark:text-teks-gelap">{{ $transaction->payment_channel }}</dd>
                        </div>
                    @endif

                    @if ($transaction->reference_number)
                        <div class="flex items-baseline justify-between gap-4">
                            <dt class="shrink-0 text-redup dark:text-redup-gelap">Nomor referensi</dt>
                            <dd class="truncate font-mono text-xs text-tinta dark:text-teks-gelap">{{ $transaction->reference_number }}</dd>
                        </div>
                    @endif

                    @if ($transaction->virtualAccount)
                        <div class="flex items-baseline justify-between gap-4">
                            <dt class="text-redup dark:text-redup-gelap">Virtual account</dt>
                            <dd class="font-mono text-tinta dark:text-teks-gelap">
                                {{ $transaction->virtualAccount->account_number ?: $transaction->virtualAccount->nomor_va }}
                            </dd>
                        </div>
                    @endif

                    <div class="flex items-baseline justify-between gap-4">
                        <dt class="text-redup dark:text-redup-gelap">Dibuat</dt>
                        <dd class="text-right text-tinta dark:text-teks-gelap">{{ $transaction->created_at?->format('d/m/Y H:i') ?? '-' }}</dd>
                    </div>

                    @if ($transaction->paid_at)
                        <div class="flex items-baseline justify-between gap-4">
                            <dt class="text-redup dark:text-redup-gelap">Dibayar</dt>
                            <dd class="text-right text-tinta dark:text-teks-gelap">{{ $transaction->paid_at->format('d/m/Y H:i') }}</dd>
                        </div>
                    @endif
                </dl>
            </div>
        </div>

        {{--
            Raw gateway response, as-is. When a transaction failed, the reason
            is readable here. Shown as text rather than summarised, so nothing
            gets misread.
        --}}
        @if ($transaction->raw_response)
            <div class="rounded-xl border border-garis bg-white p-5 shadow-sm dark:border-garis-gelap dark:bg-gelap-2">
                <h2 class="text-sm font-semibold text-redup dark:text-redup-gelap">Balasan mentah gateway</h2>
                <pre class="mt-4 overflow-x-auto rounded-lg bg-latar-2 p-4 text-xs leading-relaxed text-tinta dark:bg-gelap dark:text-teks-gelap">{{ json_encode($transaction->raw_response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) }}</pre>
            </div>
        @endif
    </div>

@endsection
