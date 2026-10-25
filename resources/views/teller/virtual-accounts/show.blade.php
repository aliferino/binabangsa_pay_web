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

    @php
        // Tiga keadaan yang membingungkan teller kalau tidak dibedakan:
        // masih internal, sudah terhubung ke Xendit, dan sudah dinonaktifkan.
        $terhubung = filled($virtualAccount->xendit_id);
        $aktif = $virtualAccount->aktif();
    @endphp

    <div class="space-y-5">

        {{-- Keadaan sinkronisasi: satu blok, satu kesimpulan, satu aksi utama. --}}
        <div class="rounded-xl border border-garis bg-white p-5 shadow-sm dark:border-garis-gelap dark:bg-gelap-2">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div class="min-w-0">
                    <h2 class="text-sm font-semibold text-redup dark:text-redup-gelap">Status sinkronisasi</h2>

                    @if (! $aktif)
                        <p class="mt-2 flex items-center gap-2 text-sm font-medium text-tinta dark:text-teks-gelap">
                            <span class="inline-flex whitespace-nowrap rounded-full bg-latar-2 px-2.5 py-0.5 text-xs font-semibold text-tinta dark:bg-gelap dark:text-teks-gelap">Nonaktif</span>
                            Nomor ini dimatikan, siswa tidak bisa membayar ke nomor ini.
                        </p>
                    @elseif ($terhubung)
                        <p class="mt-2 flex items-center gap-2 text-sm font-medium text-tinta dark:text-teks-gelap">
                            <span class="inline-flex whitespace-nowrap rounded-full bg-neon text-tinta px-2.5 py-0.5 text-xs font-semibold">Terhubung</span>
                            Nomor bank dari Xendit sudah aktif dan bisa dipakai membayar.
                        </p>
                        <dl class="mt-4 grid grid-cols-1 gap-x-8 gap-y-3 text-sm sm:grid-cols-2">
                            <div class="flex items-baseline justify-between gap-4">
                                <dt class="text-redup dark:text-redup-gelap">Nomor bank</dt>
                                <dd class="font-mono text-lg font-bold text-tinta dark:text-teks-gelap">{{ $virtualAccount->account_number }}</dd>
                            </div>
                            <div class="flex items-baseline justify-between gap-4">
                                <dt class="text-redup dark:text-redup-gelap">Bank</dt>
                                <dd class="text-right font-medium text-tinta dark:text-teks-gelap">{{ $virtualAccount->bank_code }}</dd>
                            </div>
                            <div class="flex items-baseline justify-between gap-4">
                                <dt class="text-redup dark:text-redup-gelap">Berlaku sampai</dt>
                                <dd class="text-right text-tinta dark:text-teks-gelap">{{ $virtualAccount->expired_at?->format('d/m/Y H:i') ?? '-' }}</dd>
                            </div>
                            <div class="flex items-baseline justify-between gap-4">
                                <dt class="text-redup dark:text-redup-gelap">Nomor internal</dt>
                                <dd class="font-mono text-xs text-redup dark:text-redup-gelap">{{ $virtualAccount->nomor_va }}</dd>
                            </div>
                        </dl>
                    @else
                        <p class="mt-2 flex items-center gap-2 text-sm font-medium text-tinta dark:text-teks-gelap">
                            <span class="inline-flex whitespace-nowrap rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-semibold text-amber-800">Internal saja</span>
                            Belum terhubung ke Xendit, jadi belum ada nomor bank untuk siswa.
                        </p>
                        <p class="mt-3 text-sm text-redup dark:text-redup-gelap">
                            Nomor internal <span class="font-mono">{{ $virtualAccount->nomor_va }}</span> tetap disimpan dan tidak berubah.
                        </p>
                    @endif
                </div>

                {{-- Satu aksi utama per keadaan, supaya teller tidak menebak. --}}
                <div class="flex shrink-0 flex-wrap items-center gap-2">
                    @if (! $aktif)
                        @if (auth()->user()->role === 'admin')
                            <form method="POST" action="{{ route('virtual-account.aktifkan', $virtualAccount) }}"
                                  data-konfirmasi="Aktifkan kembali Virtual Account {{ $virtualAccount->nomor_va }}?" data-konfirmasi-jenis="simpan">
                                @csrf
                                <button type="submit"
                                        class="tombol-tekan rounded-lg bg-neon px-4 py-2 text-sm font-semibold text-tinta hover:bg-neon-tekan">
                                    Aktifkan
                                </button>
                            </form>
                        @endif
                    @elseif ($terhubung)
                        <form method="POST" action="{{ route('virtual-account.simulasi', $virtualAccount) }}"
                              data-konfirmasi="Kirim pembayaran tiruan ke Xendit untuk nomor {{ $virtualAccount->account_number }}? Ini hanya untuk mode uji, tagihan berubah lunas setelah callback masuk." data-konfirmasi-jenis="simpan">
                            @csrf
                            <button type="submit"
                                    class="tombol-tekan rounded-lg bg-neon px-4 py-2 text-sm font-semibold text-tinta hover:bg-neon-tekan">
                                Simulasi bayar
                            </button>
                        </form>
                    @else
                        <button type="button" data-modal-buka="tambah" data-modal-target="modal-bank"
                                data-url="{{ route('virtual-account.buat-xendit', $virtualAccount->siswa_id) }}"
                                class="tombol-tekan rounded-lg bg-neon px-4 py-2 text-sm font-semibold text-tinta hover:bg-neon-tekan">
                            Hubungkan ke Xendit
                        </button>
                    @endif
                </div>
            </div>
        </div>

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
                            <span class="inline-flex whitespace-nowrap rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $aktif ? 'bg-neon text-tinta' : 'bg-latar-2 text-tinta dark:bg-gelap dark:text-teks-gelap' }}">
                                {{ $aktif ? 'Aktif' : 'Nonaktif' }}
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

    @if (auth()->user()->role === 'admin' && ! $terhubung)
        @include('teller.virtual-accounts._bank_modal')
    @endif

@endsection