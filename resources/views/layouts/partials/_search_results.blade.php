{{--
    Hasil pencarian siswa: satu kartu per siswa, isinya status tagihan per bulan.

    Dipakai admin dan teller supaya tampilannya tidak berbeda. Variabel yang
    dibutuhkan: $results (kumpulan array) dan $keyword (kata kunci yang dicari).
--}}

@php
    // Lencana status per bulan. Disimpan sekali, dipakai di dalam perulangan.
    $kelasLencana = fn (string $status) => match ($status) {
        'lunas' => 'bg-emerald-100 text-emerald-800',
        'tunggakan' => 'bg-red-100 text-red-800',
        'belum_bayar' => 'bg-amber-100 text-amber-800',
        default => 'bg-latar-2 text-tinta dark:bg-gelap dark:text-teks-gelap',
    };

    $labelStatus = fn (string $status) => match ($status) {
        'lunas' => 'LUNAS',
        'tunggakan' => 'TUNGGAKAN',
        'belum_bayar' => 'BELUM BAYAR',
        default => strtoupper(str_replace('_', ' ', $status)),
    };
@endphp

@if ($results->isEmpty())
    <div class="rounded-xl border border-garis bg-white px-5 py-8 text-center text-sm text-redup shadow-sm dark:border-garis-gelap dark:bg-gelap-2 dark:text-redup-gelap">
        Tidak ada siswa yang cocok dengan kata kunci itu.
    </div>
@else
    <div class="space-y-5">
        @foreach ($results as $baris)
            @php
                $statusRingkas = (string) data_get($baris, 'status_tagihan', '');
                $perBulan = (array) data_get($baris, 'status_per_bulan', []);

                $kelasRingkas = match ($statusRingkas) {
                    'semua lunas' => 'bg-emerald-100 text-emerald-800',
                    'ada tunggakan' => 'bg-red-100 text-red-800',
                    'sebagian belum bayar' => 'bg-amber-100 text-amber-800',
                    default => 'bg-latar-2 text-tinta dark:bg-gelap dark:text-teks-gelap',
                };
            @endphp

            <div class="overflow-hidden rounded-xl border border-garis bg-white shadow-sm dark:border-garis-gelap dark:bg-gelap-2">

                {{-- Identitas siswa dan kesimpulan sekilas. --}}
                <div class="flex flex-wrap items-start justify-between gap-4 border-b border-garis px-5 py-4 dark:border-garis-gelap">
                    <div class="min-w-0">
                        <p class="font-semibold text-tinta dark:text-teks-gelap">{{ data_get($baris, 'nama') }}</p>
                        <p class="mt-1 text-xs text-redup dark:text-redup-gelap">
                            NIS {{ data_get($baris, 'nis') ?? '-' }}
                            <span class="mx-1.5">|</span>
                            NISN {{ data_get($baris, 'nisn') ?? '-' }}
                            <span class="mx-1.5">|</span>
                            {{ data_get($baris, 'kelas') ?? '-' }}, {{ data_get($baris, 'jurusan') ?? '-' }}
                            @if (data_get($baris, 'no_va'))
                                <span class="mx-1.5">|</span>
                                VA <span class="font-mono">{{ data_get($baris, 'no_va') }}</span>
                            @endif
                        </p>
                    </div>
                    <span class="inline-flex whitespace-nowrap rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $kelasRingkas }}">
                        {{ ucfirst($statusRingkas) }}
                    </span>
                </div>

                {{-- Status per bulan: jawaban langsung untuk pertanyaan ortu. --}}
                @if (empty($perBulan))
                    <p class="px-5 py-6 text-center text-sm text-redup dark:text-redup-gelap">
                        Belum ada tagihan untuk siswa ini.
                    </p>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-garis text-sm dark:divide-garis-gelap">
                            <thead class="bg-latar-2 text-left text-xs uppercase tracking-wide text-redup dark:bg-gelap dark:text-redup-gelap">
                                <tr>
                                    <th class="px-5 py-3.5 font-semibold">Bulan</th>
                                    <th class="px-5 py-3.5 font-semibold">Jatuh tempo</th>
                                    <th class="px-5 py-3.5 font-semibold">Status</th>
                                    <th class="px-5 py-3.5 text-right font-semibold">Nominal</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-garis dark:divide-garis-gelap">
                                @foreach ($perBulan as $bulan)
                                    @php $statusBulan = (string) data_get($bulan, 'status', ''); @endphp
                                    <tr class="hover:bg-latar-2 dark:hover:bg-gelap">
                                        <td class="whitespace-nowrap px-5 py-3.5 font-medium text-tinta dark:text-teks-gelap">
                                            {{ data_get($bulan, 'nama_bulan') ?? data_get($bulan, 'bulan') }}
                                            {{ data_get($bulan, 'tahun') }}
                                        </td>
                                        <td class="whitespace-nowrap px-5 py-3.5 text-redup dark:text-redup-gelap">
                                            {{ data_get($bulan, 'jatuh_tempo') ? \Illuminate\Support\Carbon::parse(data_get($bulan, 'jatuh_tempo'))->format('d/m/Y') : '-' }}
                                        </td>
                                        <td class="px-5 py-3.5">
                                            <span class="inline-flex whitespace-nowrap rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $kelasLencana($statusBulan) }}">
                                                {{ $labelStatus($statusBulan) }}
                                            </span>
                                        </td>
                                        <td class="whitespace-nowrap px-5 py-3.5 text-right text-tinta dark:text-teks-gelap">
                                            Rp{{ number_format((float) data_get($bulan, 'nominal', 0), 0, ',', '.') }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        @endforeach
    </div>
@endif