{{-- Tabel riwayat pembayaran milik siswa yang login. Tombol Lihat saja. --}}

<div class="overflow-hidden rounded-xl border border-garis dark:border-garis-gelap bg-white dark:bg-gelap-2 shadow-sm">
    <div class="border-b border-garis dark:border-garis-gelap px-5 py-4">
        <h2 class="text-sm font-semibold text-redup dark:text-redup-gelap">Riwayat pembayaran</h2>
        <p class="mt-1 text-xs text-redup dark:text-redup-gelap">
            Total {{ $daftar_pembayaran->count() }} pembayaran.
        </p>
    </div>

    @if ($daftar_pembayaran->isEmpty())
        <p class="px-5 py-8 text-center text-sm text-redup dark:text-redup-gelap">
            Belum ada pembayaran untuk akun Anda.
        </p>
    @else
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-garis dark:divide-garis-gelap text-sm">
                <thead class="bg-latar-2 dark:bg-gelap text-left text-xs uppercase tracking-wide text-redup dark:text-redup-gelap">
                    <tr>
                        <th class="px-5 py-3 font-semibold">Tanggal</th>
                        <th class="px-5 py-3 font-semibold">Nominal</th>
                        <th class="px-5 py-3 font-semibold">Metode</th>
                        <th class="px-5 py-3 font-semibold">Status</th>
                        <th class="px-5 py-3 text-right font-semibold">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-garis dark:divide-garis-gelap">
                    @forelse ($daftar_pembayaran as $pembayaran)
                        <tr class="hover:bg-latar-2 dark:hover:bg-garis-gelap/40">
                            <td class="px-5 py-3 text-redup dark:text-redup-gelap">
                                {{ $pembayaran->created_at?->format('d/m/Y H:i') ?? '-' }}
                            </td>
                            <td class="px-5 py-3 text-tinta dark:text-teks-gelap">
                                Rp{{ number_format((float) $pembayaran->nominal_pembayaran, 0, ',', '.') }}
                            </td>
                            <td class="px-5 py-3 text-redup dark:text-redup-gelap">
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
                                        'berhasil' => ['label' => 'Berhasil', 'kelas' => 'bg-neon text-tinta'],
                                        'gagal' => ['label' => 'Gagal', 'kelas' => 'bg-red-100 text-red-800'],
                                        'dibatalkan' => ['label' => 'Dibatalkan', 'kelas' => 'bg-latar-2 dark:bg-gelap text-redup dark:text-redup-gelap'],
                                    ];
                                    $dataStatus = $petaStatus[$pembayaran->status] ?? ['label' => $pembayaran->status, 'kelas' => 'bg-latar-2 dark:bg-gelap text-redup dark:text-redup-gelap'];
                                @endphp
                                <span class="inline-flex whitespace-nowrap rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $dataStatus['kelas'] }}">
                                    {{ $dataStatus['label'] }}
                                </span>
                            </td>
                            <td class="px-5 py-3">
                                <div class="flex flex-wrap items-center justify-end gap-2">
                                    <a href="{{ route('siswa.payments.show', $pembayaran) }}"
                                       class="rounded-md border border-garis dark:border-garis-gelap px-2.5 py-1 text-xs font-medium text-redup dark:text-redup-gelap hover:bg-latar-2 dark:hover:bg-garis-gelap/40">
                                        Lihat
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-8 text-center text-redup dark:text-redup-gelap">
                                Belum ada pembayaran untuk akun Anda.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @endif
</div>
