{{-- Tabel tagihan milik siswa yang login. Tanpa tombol aksi. --}}

<div class="overflow-hidden rounded-xl border border-garis dark:border-garis-gelap bg-white dark:bg-gelap-2 shadow-sm">
    <div class="border-b border-garis dark:border-garis-gelap px-5 py-4">
        <h2 class="text-sm font-semibold text-redup dark:text-redup-gelap">Tagihan SPP</h2>
        <p class="mt-1 text-xs text-redup dark:text-redup-gelap">
            Total {{ $daftar_tagihan->count() }} tagihan.
        </p>
    </div>

    @if ($daftar_tagihan->isEmpty())
        <p class="px-5 py-8 text-center text-sm text-redup dark:text-redup-gelap">
            Belum ada tagihan untuk akun Anda.
        </p>
    @else
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-garis dark:divide-garis-gelap text-sm">
                <thead class="bg-latar-2 dark:bg-gelap text-left text-xs uppercase tracking-wide text-redup dark:text-redup-gelap">
                    <tr>
                        <th class="px-5 py-3 font-semibold">Bulan</th>
                        <th class="px-5 py-3 font-semibold">Tahun</th>
                        <th class="px-5 py-3 font-semibold">Nominal</th>
                        <th class="px-5 py-3 font-semibold">Jatuh tempo</th>
                        <th class="px-5 py-3 font-semibold">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-garis dark:divide-garis-gelap">
                    @foreach ($daftar_tagihan->sortBy([['tahun', 'desc'], ['bulan', 'desc']]) as $tagihan)
                        <tr class="hover:bg-latar-2 dark:hover:bg-garis-gelap/40">
                            <td class="px-5 py-3 font-medium text-tinta dark:text-teks-gelap">{{ $tagihan->namaBulan() }}</td>
                            <td class="px-5 py-3 text-redup dark:text-redup-gelap">{{ $tagihan->tahun }}</td>
                            <td class="px-5 py-3 text-tinta dark:text-teks-gelap">
                                Rp{{ number_format((float) $tagihan->nominal, 0, ',', '.') }}
                            </td>
                            <td class="px-5 py-3 text-redup dark:text-redup-gelap">
                                {{ $tagihan->tanggal_jatuh_tempo?->format('d/m/Y') ?? '-' }}
                            </td>
                            <td class="px-5 py-3">
                                @include('layouts.partials._status-badge', ['tagihan' => $tagihan])
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
