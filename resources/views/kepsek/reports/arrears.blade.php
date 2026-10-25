@extends('layouts.app')

@section('judul', 'Laporan Tunggakan')
@section('keterangan', 'Rekap tunggakan SPP per siswa')

@section('isi')

    {{-- Catatan bentuk data: $daftar_tunggakan berisi array dengan kunci nis, nama,
         kelas, bulan_tunggakan, jumlah_tunggakan, total_nominal, hari_terlambat.
         Dibaca lewat data_get supaya tetap jalan kalau nanti jadi objek. --}}

    @php
        $jumlahSiswa = $daftar_tunggakan->count();
        $jumlahTagihan = $daftar_tunggakan->sum(fn ($b) => (int) data_get($b, 'jumlah_tunggakan', 0));
        $hariTerlama = (int) $daftar_tunggakan->max(fn ($b) => (int) data_get($b, 'hari_terlambat', 0));
        $kelasTerdampak = $daftar_tunggakan->pluck('kelas')->filter()->unique()->count();
        $rataSiswa = $jumlahSiswa > 0 ? (float) $total_semua / $jumlahSiswa : 0.0;
        $rupiah = fn ($n) => 'Rp'.number_format((float) $n, 0, ',', '.');

        // Sebaran bulan tertunggak: dihitung dari daftar bulan tiap siswa, bukan
        // query baru. Urutan bulan mengikuti kalender, bukan urutan abjad.
        $urutanBulan = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
            'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        $sebaran = [];

        foreach ($daftar_tunggakan as $baris) {
            foreach (array_filter(array_map('trim', explode(',', (string) data_get($baris, 'bulan_tunggakan')))) as $namaBulanTunggakan) {
                $sebaran[$namaBulanTunggakan] = ($sebaran[$namaBulanTunggakan] ?? 0) + 1;
            }
        }

        $sebaran = collect($sebaran)
            ->sortBy(fn ($jumlah, $nama) => array_search($nama, $urutanBulan, true) ?: 99);
        $puncakSebaran = $sebaran->max() ?: 1;
    @endphp

    @if ($jumlahSiswa > 0)
        {{-- Neraca tunggakan: satu panel bergaris tipis, sejalan dengan dashboard. --}}
        <section class="mb-5 overflow-hidden rounded-xl border border-garis bg-white dark:border-garis-gelap dark:bg-gelap-2">

            <div class="grid gap-px bg-garis sm:grid-cols-[1.35fr_1fr] dark:bg-garis-gelap">

                <div class="bg-white px-5 py-5 dark:bg-gelap-2">
                    <p class="text-xs font-medium text-redup dark:text-redup-gelap">Total tunggakan</p>
                    <p class="mt-1.5 text-3xl font-bold tabular-nums tracking-tight text-red-700 sm:text-4xl dark:text-red-400">
                        {{ $rupiah($total_semua) }}
                    </p>
                    <p class="mt-1 text-xs leading-relaxed text-redup dark:text-redup-gelap">
                        Dari {{ $jumlahTagihan }} tagihan lewat jatuh tempo.
                        Tunggakan terlama <span class="font-semibold text-tinta dark:text-teks-gelap">{{ $hariTerlama }} hari</span>.
                    </p>
                </div>

                <dl class="grid grid-cols-2 gap-px bg-garis dark:bg-garis-gelap">
                    <div class="bg-white px-4 py-4 dark:bg-gelap-2">
                        <dt class="text-[11px] font-medium text-redup dark:text-redup-gelap">Siswa menunggak</dt>
                        <dd class="mt-1 text-xl font-bold tabular-nums tracking-tight text-tinta dark:text-teks-gelap">{{ $jumlahSiswa }}</dd>
                        <dd class="mt-0.5 text-[11px] leading-snug text-redup dark:text-redup-gelap">pada filter ini</dd>
                    </div>
                    <div class="bg-white px-4 py-4 dark:bg-gelap-2">
                        <dt class="text-[11px] font-medium text-redup dark:text-redup-gelap">Tagihan tertunggak</dt>
                        <dd class="mt-1 text-xl font-bold tabular-nums tracking-tight text-tinta dark:text-teks-gelap">{{ $jumlahTagihan }}</dd>
                        <dd class="mt-0.5 text-[11px] leading-snug text-redup dark:text-redup-gelap">bulan belum dibayar</dd>
                    </div>
                    <div class="bg-white px-4 py-4 dark:bg-gelap-2">
                        <dt class="text-[11px] font-medium text-redup dark:text-redup-gelap">Kelas terdampak</dt>
                        <dd class="mt-1 text-xl font-bold tabular-nums tracking-tight text-tinta dark:text-teks-gelap">{{ $kelasTerdampak }}</dd>
                        <dd class="mt-0.5 text-[11px] leading-snug text-redup dark:text-redup-gelap">kelas menunggak</dd>
                    </div>
                    <div class="bg-white px-4 py-4 dark:bg-gelap-2">
                        <dt class="text-[11px] font-medium text-redup dark:text-redup-gelap">Rata-rata per siswa</dt>
                        <dd class="mt-1 text-base font-bold tabular-nums tracking-tight text-tinta dark:text-teks-gelap">{{ $rupiah($rataSiswa) }}</dd>
                        <dd class="mt-0.5 text-[11px] leading-snug text-redup dark:text-redup-gelap">beban tiap siswa</dd>
                    </div>
                </dl>

            </div>
        </section>
    @endif

    {{-- Form filter. --}}
    <form method="GET" action="{{ route('kepsek.reports.arrears') }}"
          class="mb-5 rounded-xl border border-garis bg-white px-4 py-3 dark:border-garis-gelap dark:bg-gelap-2">
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-[1fr_1fr_auto]">

            <div>
                <label for="id_jurusan" class="mb-1 block text-xs font-medium text-redup dark:text-redup-gelap">Jurusan</label>
                <select name="id_jurusan" id="id_jurusan"
                        class="w-full rounded-lg border border-garis bg-white pl-3 pr-9 py-2 text-sm text-tinta shadow-sm focus:border-neon-teks focus:outline-none focus:ring-2 focus:ring-neon/40 dark:border-redup dark:bg-gelap dark:text-teks-gelap">
                    <option value="">Semua jurusan</option>
                    @foreach ($daftar_jurusan as $jurusan)
                        <option value="{{ $jurusan->id_jurusan }}" @selected(($filter['id_jurusan'] ?? '') == $jurusan->id_jurusan)>
                            {{ $jurusan->nama_jurusan }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="id_kelas" class="mb-1 block text-xs font-medium text-redup dark:text-redup-gelap">Kelas</label>
                <select name="id_kelas" id="id_kelas"
                        class="w-full rounded-lg border border-garis bg-white pl-3 pr-9 py-2 text-sm text-tinta shadow-sm focus:border-neon-teks focus:outline-none focus:ring-2 focus:ring-neon/40 dark:border-redup dark:bg-gelap dark:text-teks-gelap">
                    <option value="">Semua kelas</option>
                    @foreach ($daftar_kelas as $kelas)
                        <option value="{{ $kelas->id_kelas }}" @selected(($filter['id_kelas'] ?? '') == $kelas->id_kelas)>
                            {{ $kelas->nama_kelas }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-end gap-2">
                <button type="submit"
                        class="border border-transparent rounded-lg bg-neon px-4 py-2 text-sm font-semibold text-tinta hover:bg-neon-tekan tombol-tekan">
                    Terapkan
                </button>
                <a href="{{ route('kepsek.reports.arrears') }}"
                   class="rounded-lg border border-garis bg-white px-4 py-2 text-sm font-medium text-tinta hover:bg-latar-2 dark:border-garis-gelap dark:bg-gelap-2 dark:text-teks-gelap dark:hover:bg-gelap">
                    Reset
                </a>
            </div>
        </div>
    </form>

    {{-- Daftar siswa menunggak. --}}
    <section class="overflow-hidden rounded-xl border border-garis bg-white dark:border-garis-gelap dark:bg-gelap-2">
        <div class="flex flex-wrap items-baseline justify-between gap-2 border-b border-garis px-5 py-3.5 dark:border-garis-gelap">
            <h2 class="text-sm font-semibold text-tinta dark:text-teks-gelap">Siswa menunggak</h2>
            <span class="text-[11px] text-redup dark:text-redup-gelap">
                {{ $jumlahSiswa }} siswa &middot; {{ $jumlahTagihan }} tagihan
            </span>
        </div>

        @if ($daftar_tunggakan->isEmpty())
            <p class="px-5 py-6 text-sm leading-relaxed text-redup dark:text-redup-gelap">
                Tidak ada tunggakan pada filter ini. Semua tagihan yang jatuh tempo sudah lunas.
            </p>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-garis text-sm dark:divide-garis-gelap">
                    <thead class="bg-latar-2 text-left text-xs uppercase tracking-wide text-redup dark:bg-gelap dark:text-redup-gelap">
                        <tr>
                            <th class="px-5 py-3 font-semibold">Siswa</th>
                            <th class="px-5 py-3 font-semibold">Kelas</th>
                            <th class="px-5 py-3 font-semibold">Bulan tertunggak</th>
                            <th class="px-5 py-3 text-right font-semibold">Terlambat</th>
                            <th class="px-5 py-3 text-right font-semibold">Nominal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-garis dark:divide-garis-gelap">
                        @foreach ($daftar_tunggakan as $baris)
                            @php
                                $hari = (int) data_get($baris, 'hari_terlambat', 0);
                                $bulan = array_filter(array_map('trim', explode(',', (string) data_get($baris, 'bulan_tunggakan'))));
                            @endphp
                            <tr class="align-top hover:bg-latar-2 dark:hover:bg-gelap">
                                <td class="px-5 py-3">
                                    <span class="font-medium text-tinta dark:text-teks-gelap">{{ data_get($baris, 'nama') }}</span>
                                    <span class="mt-0.5 block font-mono text-[11px] text-redup dark:text-redup-gelap">{{ data_get($baris, 'nis') }}</span>
                                </td>
                                <td class="px-5 py-3 text-redup dark:text-redup-gelap">{{ data_get($baris, 'kelas') ?? '-' }}</td>
                                <td class="px-5 py-3">
                                    <span class="flex flex-wrap gap-1">
                                        @foreach ($bulan as $namaBulanTunggakan)
                                            <span class="rounded-md bg-latar-2 px-2 py-0.5 text-[11px] font-medium text-tinta dark:bg-gelap dark:text-teks-gelap">{{ $namaBulanTunggakan }}</span>
                                        @endforeach
                                    </span>
                                </td>
                                <td class="px-5 py-3 text-right tabular-nums {{ $hari >= 60 ? 'font-semibold text-red-700 dark:text-red-400' : 'text-redup dark:text-redup-gelap' }}">
                                    {{ $hari }} hari
                                </td>
                                <td class="px-5 py-3 text-right font-semibold tabular-nums text-tinta dark:text-teks-gelap">
                                    {{ $rupiah(data_get($baris, 'total_nominal', 0)) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-latar-2 dark:bg-gelap">
                        <tr>
                            <td colspan="4" class="px-5 py-3 text-right text-sm font-semibold text-tinta dark:text-teks-gelap">Total</td>
                            <td class="px-5 py-3 text-right text-sm font-bold tabular-nums text-red-700 dark:text-red-400">
                                {{ $rupiah($total_semua) }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @endif
    </section>

{{-- Sebaran bulan tertunggak. Batang disusun dari jumlah siswa per bulan,
         tanpa pustaka grafik, sama seperti grafik di dashboard. --}}
    @if ($sebaran->isNotEmpty())
        <section class="mt-5 rounded-xl border border-garis bg-white px-5 py-5 dark:border-garis-gelap dark:bg-gelap-2">
            <div class="flex flex-wrap items-baseline justify-between gap-2">
                <h2 class="text-sm font-semibold text-tinta dark:text-teks-gelap">Sebaran bulan tertunggak</h2>
                <span class="text-[11px] text-redup dark:text-redup-gelap">
                    Jumlah siswa yang belum membayar tiap bulan
                </span>
            </div>

            <div class="mt-4 flex items-end gap-2 sm:gap-3">
                @foreach ($sebaran as $namaBulanTunggakan => $jumlahSiswaBulan)
                    @php($tinggi = max(6, (int) round($jumlahSiswaBulan / $puncakSebaran * 96)))
                    <div class="flex min-w-0 flex-1 flex-col items-center gap-1.5">
                        <span class="text-[11px] font-medium tabular-nums text-redup dark:text-redup-gelap">{{ $jumlahSiswaBulan }}</span>
                        <div class="w-9 rounded-t-md bg-red-600 dark:bg-red-500"
                             style="height: {{ $tinggi }}px"
                             title="{{ $namaBulanTunggakan }}: {{ $jumlahSiswaBulan }} siswa"></div>
                        <span class="w-full truncate border-t border-garis pt-1.5 text-center text-[11px] text-redup dark:border-garis-gelap dark:text-redup-gelap">
                            {{ mb_substr($namaBulanTunggakan, 0, 3) }}
                        </span>
                    </div>
                @endforeach
            </div>
        </section>
    @endif

@endsection