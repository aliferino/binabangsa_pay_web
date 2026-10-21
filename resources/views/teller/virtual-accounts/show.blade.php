@extends('layouts.app')

@section('judul', 'Detail Virtual Account')
@section('keterangan', 'Rincian satu Virtual Account siswa')

@section('aksi')
    <a href="{{ route('teller.virtual-accounts.index') }}"
       class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
        Kembali
    </a>
@endsection

@section('isi')

    <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">

        {{-- Data siswa. --}}
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <h2 class="text-sm font-semibold text-slate-700">Siswa</h2>
            <dl class="mt-3 space-y-3 text-sm">
                <div>
                    <dt class="text-slate-500">Nama</dt>
                    <dd class="font-medium text-slate-900">{{ $va->siswa?->nama ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500">NIS</dt>
                    <dd class="text-slate-700">{{ $va->siswa?->nis ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500">Kelas</dt>
                    <dd class="text-slate-700">{{ $va->siswa?->kelas?->nama_kelas ?? '-' }}</dd>
                </div>
            </dl>
        </div>

        {{-- Rincian Virtual Account. --}}
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <h2 class="text-sm font-semibold text-slate-700">Rincian Virtual Account</h2>
            <dl class="mt-3 space-y-3 text-sm">

                @if ($va->account_number)
                    <div>
                        <dt class="text-slate-500">Nomor VA (bank)</dt>
                        <dd class="font-mono text-lg font-bold text-slate-900">{{ $va->account_number }}</dd>
                    </div>
                @endif

                <div>
                    <dt class="text-slate-500">Nomor VA internal</dt>
                    <dd class="font-mono font-medium text-slate-900">{{ $va->nomor_va }}</dd>
                </div>

                <div>
                    <dt class="text-slate-500">Prefix</dt>
                    <dd class="text-slate-700">{{ $va->prefix }}</dd>
                </div>

                @if ($va->bank_code)
                    <div>
                        <dt class="text-slate-500">Kode bank</dt>
                        <dd class="text-slate-700">{{ $va->bank_code }}</dd>
                    </div>
                @endif

                @if ($va->xendit_id)
                    <div>
                        <dt class="text-slate-500">Xendit ID</dt>
                        <dd class="font-mono text-xs text-slate-600">{{ $va->xendit_id }}</dd>
                    </div>
                @endif

                @if ($va->external_id)
                    <div>
                        <dt class="text-slate-500">External ID</dt>
                        <dd class="font-mono text-xs text-slate-600">{{ $va->external_id }}</dd>
                    </div>
                @endif

                <div>
                    <dt class="text-slate-500">Status</dt>
                    <dd>
                        @php
                            $petaStatus = [
                                'aktif' => ['label' => 'Aktif', 'kelas' => 'bg-emerald-100 text-emerald-800'],
                                'nonaktif' => ['label' => 'Nonaktif', 'kelas' => 'bg-slate-100 text-slate-700'],
                            ];
                            $dataStatus = $petaStatus[$va->status] ?? ['label' => $va->status, 'kelas' => 'bg-slate-100 text-slate-700'];
                        @endphp
                        <span class="inline-flex whitespace-nowrap rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $dataStatus['kelas'] }}">
                            {{ $dataStatus['label'] }}
                        </span>
                    </dd>
                </div>

                @if ($va->expired_at)
                    <div>
                        <dt class="text-slate-500">Kedaluwarsa</dt>
                        <dd class="text-slate-700">{{ $va->expired_at->format('d/m/Y H:i') }}</dd>
                    </div>
                @endif
            </dl>

            @if ($va->transaksi->isNotEmpty())
                <div class="mt-5">
                    <h3 class="text-sm font-semibold text-slate-700">Riwayat transaksi gateway</h3>
                    <div class="mt-3 overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200 text-xs">
                            <thead class="bg-slate-50 text-left uppercase tracking-wide text-slate-500">
                                <tr>
                                    <th class="px-3 py-2 font-semibold">Tanggal</th>
                                    <th class="px-3 py-2 font-semibold">Status</th>
                                    <th class="px-3 py-2 font-semibold">Nominal</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach ($va->transaksi as $transaksi)
                                    <tr>
                                        <td class="px-3 py-2 text-slate-600">
                                            {{ $transaksi->created_at?->format('d/m/Y H:i') ?? '-' }}
                                        </td>
                                        <td class="px-3 py-2 text-slate-600">{{ $transaksi->status ?? '-' }}</td>
                                        <td class="px-3 py-2 text-slate-800">
                                            @if ($transaksi->amount)
                                                Rp{{ number_format((float) $transaksi->amount, 0, ',', '.') }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>
    </div>

@endsection
