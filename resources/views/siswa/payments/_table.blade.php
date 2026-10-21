{{-- Tabel riwayat pembayaran milik siswa yang login. Tombol Lihat saja. --}}

<div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
    <div class="border-b border-slate-200 px-5 py-4">
        <h2 class="text-sm font-semibold text-slate-700">Riwayat pembayaran</h2>
        <p class="mt-1 text-xs text-slate-500">
            Total {{ $daftar_pembayaran->count() }} pembayaran.
        </p>
    </div>

    @if ($daftar_pembayaran->isEmpty())
        <p class="px-5 py-8 text-center text-sm text-slate-500">
            Belum ada pembayaran untuk akun Anda.
        </p>
    @else
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-5 py-3 font-semibold">Tanggal</th>
                        <th class="px-5 py-3 font-semibold">Nominal</th>
                        <th class="px-5 py-3 font-semibold">Metode</th>
                        <th class="px-5 py-3 font-semibold">Status</th>
                        <th class="px-5 py-3 text-right font-semibold">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($daftar_pembayaran as $pembayaran)
                        <tr class="hover:bg-slate-50">
                            <td class="px-5 py-3 text-slate-600">
                                {{ $pembayaran->created_at?->format('d/m/Y H:i') ?? '-' }}
                            </td>
                            <td class="px-5 py-3 text-slate-800">
                                Rp{{ number_format((float) $pembayaran->nominal_pembayaran, 0, ',', '.') }}
                            </td>
                            <td class="px-5 py-3 text-slate-600">
                                @php
                                    $labelMetode = [
                                        'tunai' => 'Tunai',
                                        'qris' => 'QRIS',
                                        'virtual_account' => 'Virtual Account',
                                    ];
                                @endphp
                                {{ $labelMetode[$pembayaran->metode_pembayaran] ?? $pembayaran->metode_pembayaran }}
                            </td>
                            <td class="px-5 py-3">
                                @php
                                    $petaStatus = [
                                        'berhasil' => ['label' => 'Berhasil', 'kelas' => 'bg-emerald-100 text-emerald-800'],
                                        'gagal' => ['label' => 'Gagal', 'kelas' => 'bg-red-100 text-red-800'],
                                        'dibatalkan' => ['label' => 'Dibatalkan', 'kelas' => 'bg-slate-100 text-slate-700'],
                                    ];
                                    $dataStatus = $petaStatus[$pembayaran->status] ?? ['label' => $pembayaran->status, 'kelas' => 'bg-slate-100 text-slate-700'];
                                @endphp
                                <span class="inline-flex whitespace-nowrap rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $dataStatus['kelas'] }}">
                                    {{ $dataStatus['label'] }}
                                </span>
                            </td>
                            <td class="px-5 py-3">
                                <div class="flex flex-wrap items-center justify-end gap-2">
                                    <a href="{{ route('siswa.payments.show', $pembayaran) }}"
                                       class="rounded-md border border-slate-300 px-2.5 py-1 text-xs font-medium text-slate-700 hover:bg-slate-100">
                                        Lihat
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-8 text-center text-slate-500">
                                Belum ada pembayaran untuk akun Anda.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @endif
</div>
