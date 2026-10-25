@extends('layouts.app')

@section('judul', 'Laporan Pembayaran')
@section('keterangan', 'Rekap tagihan SPP berdasarkan filter yang dipilih')

@section('isi')

    @php
        $namaBulan = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];

        $rupiah = fn ($n) => 'Rp'.number_format((float) $n, 0, ',', '.');
        $totalSemua = (float) $total_nominal;
        $persenLunas = $totalSemua > 0 ? round((float) $total_lunas / $totalSemua * 100) : 0;
        $barisLunas = $daftar_tagihan->where('status', 'lunas')->count();
        $barisTunggakan = $daftar_tagihan->filter(fn ($t) => $t->status !== 'lunas' && $t->tanggal_jatuh_tempo?->isPast())->count();
    @endphp

    {{-- Neraca tagihan: satu panel bergaris tipis, sejalan dengan dashboard dan
         laporan tunggakan. Batang tipis menunjukkan porsi yang sudah masuk. --}}
    <section class="mb-5 overflow-hidden rounded-xl border border-garis bg-white dark:border-garis-gelap dark:bg-gelap-2">

        <div class="grid gap-px bg-garis sm:grid-cols-[1.35fr_1fr] dark:bg-garis-gelap">

            <div class="bg-white px-5 py-5 dark:bg-gelap-2">
                <p class="text-xs font-medium text-redup dark:text-redup-gelap">Total nominal</p>
                <p class="mt-1.5 text-3xl font-bold tabular-nums tracking-tight text-tinta sm:text-4xl dark:text-teks-gelap">
                    {{ $rupiah($totalSemua) }}
                </p>
                <p class="mt-1 text-xs leading-relaxed text-redup dark:text-redup-gelap">
                    Dari {{ $daftar_tagihan->count() }} baris tagihan.
                    Sudah masuk <span class="font-semibold text-tinta dark:text-teks-gelap">{{ $rupiah($total_lunas) }}</span>.
                </p>

                <div class="mt-4">
                    <div class="flex h-1.5 overflow-hidden rounded-full bg-latar-2 dark:bg-gelap">
                        <div class="bg-neon" style="width: {{ $persenLunas }}%"></div>
                    </div>
                    <div class="mt-1.5 flex items-center justify-between text-[11px] text-redup dark:text-redup-gelap">
                        <span>Terbayar {{ $persenLunas }}%</span>
                        <span>{{ $rupiah($total_lunas) }} dari {{ $rupiah($totalSemua) }}</span>
                    </div>
                </div>
            </div>

            <dl class="grid grid-cols-2 gap-px bg-garis dark:bg-garis-gelap">
                <div class="bg-white px-4 py-4 dark:bg-gelap-2">
                    <dt class="text-[11px] font-medium text-redup dark:text-redup-gelap">Lunas</dt>
                    <dd class="mt-1 text-base font-bold tabular-nums tracking-tight text-neon-teks dark:text-neon">{{ $rupiah($total_lunas) }}</dd>
                    <dd class="mt-0.5 text-[11px] leading-snug text-redup dark:text-redup-gelap">{{ $barisLunas }} tagihan</dd>
                </div>
                <div class="bg-white px-4 py-4 dark:bg-gelap-2">
                    <dt class="text-[11px] font-medium text-redup dark:text-redup-gelap">Belum bayar</dt>
                    <dd class="mt-1 text-base font-bold tabular-nums tracking-tight text-tinta dark:text-teks-gelap">{{ $rupiah($total_belum_bayar) }}</dd>
                    <dd class="mt-0.5 text-[11px] leading-snug text-redup dark:text-redup-gelap">belum jatuh tempo dan lewat</dd>
                </div>
                <div class="bg-white px-4 py-4 dark:bg-gelap-2">
                    <dt class="text-[11px] font-medium text-redup dark:text-redup-gelap">Tunggakan</dt>
                    <dd class="mt-1 text-base font-bold tabular-nums tracking-tight text-red-700 dark:text-red-400">{{ $rupiah($total_tunggakan) }}</dd>
                    <dd class="mt-0.5 text-[11px] leading-snug text-redup dark:text-redup-gelap">{{ $barisTunggakan }} lewat jatuh tempo</dd>
                </div>
                <div class="bg-white px-4 py-4 dark:bg-gelap-2">
                    <dt class="text-[11px] font-medium text-redup dark:text-redup-gelap">Baris tagihan</dt>
                    <dd class="mt-1 text-xl font-bold tabular-nums tracking-tight text-tinta dark:text-teks-gelap">{{ $daftar_tagihan->count() }}</dd>
                    <dd class="mt-0.5 text-[11px] leading-snug text-redup dark:text-redup-gelap">pada filter ini</dd>
                </div>
            </dl>

        </div>
    </section>

    {{-- Form filter. Delapan pilihan dirapatkan jadi grid, tombol di ujung. --}}
    <form method="GET" action="{{ route('kepsek.reports.payments') }}"
          class="mb-5 rounded-xl border border-garis bg-white px-4 py-3 dark:border-garis-gelap dark:bg-gelap-2">
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">

            <div>
                <label for="bulan" class="mb-1 block text-xs font-medium text-redup dark:text-redup-gelap">Bulan</label>
                <select name="bulan" id="bulan"
                        class="w-full rounded-lg border border-garis bg-white px-3 py-2 text-sm text-tinta shadow-sm focus:border-neon-teks focus:outline-none focus:ring-2 focus:ring-neon/40 dark:border-redup dark:bg-gelap dark:text-teks-gelap">
                    <option value="">Semua bulan</option>
                    @foreach ($namaBulan as $angka => $nama)
                        <option value="{{ $angka }}" @selected(($filter['bulan'] ?? '') == $angka)>{{ $nama }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="tahun" class="mb-1 block text-xs font-medium text-redup dark:text-redup-gelap">Tahun</label>
                <select name="tahun" id="tahun"
                        class="w-full rounded-lg border border-garis bg-white px-3 py-2 text-sm text-tinta shadow-sm focus:border-neon-teks focus:outline-none focus:ring-2 focus:ring-neon/40 dark:border-redup dark:bg-gelap dark:text-teks-gelap">
                    <option value="">Semua tahun</option>
                    @foreach ($daftar_tahun as $tahun)
                        <option value="{{ $tahun }}" @selected(($filter['tahun'] ?? '') == $tahun)>{{ $tahun }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="id_tahun_ajaran" class="mb-1 block text-xs font-medium text-redup dark:text-redup-gelap">Tahun ajaran</label>
                <select name="id_tahun_ajaran" id="id_tahun_ajaran"
                        class="w-full rounded-lg border border-garis bg-white px-3 py-2 text-sm text-tinta shadow-sm focus:border-neon-teks focus:outline-none focus:ring-2 focus:ring-neon/40 dark:border-redup dark:bg-gelap dark:text-teks-gelap">
                    <option value="">Semua tahun ajaran</option>
                    @foreach ($daftar_tahun_ajaran as $tahunAjaran)
                        <option value="{{ $tahunAjaran->id_tahun_ajaran }}" @selected(($filter['id_tahun_ajaran'] ?? '') == $tahunAjaran->id_tahun_ajaran)>
                            {{ $tahunAjaran->tahun_ajaran }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="status" class="mb-1 block text-xs font-medium text-redup dark:text-redup-gelap">Status</label>
                <select name="status" id="status"
                        class="w-full rounded-lg border border-garis bg-white px-3 py-2 text-sm text-tinta shadow-sm focus:border-neon-teks focus:outline-none focus:ring-2 focus:ring-neon/40 dark:border-redup dark:bg-gelap dark:text-teks-gelap">
                    <option value="">Semua status</option>
                    <option value="belum_bayar" @selected(($filter['status'] ?? '') === 'belum_bayar')>Belum bayar</option>
                    <option value="lunas" @selected(($filter['status'] ?? '') === 'lunas')>Lunas</option>
                    <option value="tunggakan" @selected(($filter['status'] ?? '') === 'tunggakan')>Tunggakan</option>
                </select>
            </div>

            <div>
                <label for="id_jurusan" class="mb-1 block text-xs font-medium text-redup dark:text-redup-gelap">Jurusan</label>
                <select name="id_jurusan" id="id_jurusan"
                        class="w-full rounded-lg border border-garis bg-white px-3 py-2 text-sm text-tinta shadow-sm focus:border-neon-teks focus:outline-none focus:ring-2 focus:ring-neon/40 dark:border-redup dark:bg-gelap dark:text-teks-gelap">
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
                        class="w-full rounded-lg border border-garis bg-white px-3 py-2 text-sm text-tinta shadow-sm focus:border-neon-teks focus:outline-none focus:ring-2 focus:ring-neon/40 dark:border-redup dark:bg-gelap dark:text-teks-gelap">
                    <option value="">Semua kelas</option>
                    @foreach ($daftar_kelas as $kelas)
                        <option value="{{ $kelas->id_kelas }}" @selected(($filter['id_kelas'] ?? '') == $kelas->id_kelas)>
                            {{ $kelas->nama_kelas }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="id_siswa" class="mb-1 block text-xs font-medium text-redup dark:text-redup-gelap">Siswa</label>
                <select name="id_siswa" id="id_siswa"
                        class="w-full rounded-lg border border-garis bg-white px-3 py-2 text-sm text-tinta shadow-sm focus:border-neon-teks focus:outline-none focus:ring-2 focus:ring-neon/40 dark:border-redup dark:bg-gelap dark:text-teks-gelap">
                    <option value="">Semua siswa</option>
                    @foreach ($daftar_siswa as $siswa)
                        <option value="{{ $siswa->id_siswa }}" @selected(($filter['id_siswa'] ?? '') == $siswa->id_siswa)>
                            {{ $siswa->nis }} - {{ $siswa->nama }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="dari" class="mb-1 block text-xs font-medium text-redup dark:text-redup-gelap">Dari</label>
                <input type="date" name="dari" id="dari" value="{{ $filter['dari'] ?? '' }}"
                       class="w-full rounded-lg border border-garis bg-white px-3 py-2 text-sm text-tinta shadow-sm focus:border-neon-teks focus:outline-none focus:ring-2 focus:ring-neon/40 dark:border-redup dark:bg-gelap dark:text-teks-gelap">
            </div>

            <div>
                <label for="sampai" class="mb-1 block text-xs font-medium text-redup dark:text-redup-gelap">Sampai</label>
                <input type="date" name="sampai" id="sampai" value="{{ $filter['sampai'] ?? '' }}"
                       class="w-full rounded-lg border border-garis bg-white px-3 py-2 text-sm text-tinta shadow-sm focus:border-neon-teks focus:outline-none focus:ring-2 focus:ring-neon/40 dark:border-redup dark:bg-gelap dark:text-teks-gelap">
            </div>

            <div class="flex items-end justify-start gap-2 sm:col-span-2 lg:col-span-3 lg:justify-end">
                <button type="submit"
                        class="rounded-lg bg-neon px-4 py-2 text-sm font-semibold text-tinta hover:bg-neon-tekan tombol-tekan">
                    Terapkan
                </button>
                <a href="{{ route('kepsek.reports.payments') }}"
                   class="rounded-lg border border-garis bg-white px-4 py-2 text-sm font-medium text-tinta hover:bg-latar-2 dark:border-garis-gelap dark:bg-gelap-2 dark:text-teks-gelap dark:hover:bg-gelap">
                    Reset
                </a>
            </div>
        </div>
    </form>

    {{-- Rincian tagihan. --}}
    <section class="overflow-hidden rounded-xl border border-garis bg-white dark:border-garis-gelap dark:bg-gelap-2">
        <div class="flex flex-wrap items-baseline justify-between gap-2 border-b border-garis px-5 py-3.5 dark:border-garis-gelap">
            <h2 class="text-sm font-semibold text-tinta dark:text-teks-gelap">Rincian tagihan</h2>
            <span class="text-[11px] text-redup dark:text-redup-gelap">
                {{ $daftar_tagihan->count() }} baris. Lunas dan uang masuk dihitung dari tabel pembayaran.
            </span>
        </div>

        @if ($daftar_tagihan->isEmpty())
            <p class="px-5 py-6 text-sm leading-relaxed text-redup dark:text-redup-gelap">
                Tidak ada tagihan yang cocok dengan filter ini.
            </p>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-garis text-sm dark:divide-garis-gelap">
                    <thead class="bg-latar-2 text-left text-xs uppercase tracking-wide text-redup dark:bg-gelap dark:text-redup-gelap">
                        <tr>
                            <th class="px-5 py-3 font-semibold">Siswa</th>
                            <th class="px-5 py-3 font-semibold">Kelas</th>
                            <th class="px-5 py-3 font-semibold">Periode</th>
                            <th class="px-5 py-3 text-right font-semibold">Nominal</th>
                            <th class="px-5 py-3 font-semibold">Jatuh tempo</th>
                            <th class="px-5 py-3 font-semibold">Tanggal bayar</th>
                            <th class="px-5 py-3 text-right font-semibold">Terlambat</th>
                            <th class="px-5 py-3 font-semibold">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-garis dark:divide-garis-gelap">
                        @foreach ($daftar_tagihan as $tagihan)
                            @php
                                // daysLate() hanya menghitung tagihan yang sudah dibayar.
                                // Untuk yang belum dibayar, keterlambatan dihitung sampai hari ini.
                                $late = $tagihan->daysLate();

                                if ($late === null && $tagihan->status !== 'lunas' && $tagihan->tanggal_jatuh_tempo?->isPast()) {
                                    $late = (int) $tagihan->tanggal_jatuh_tempo->copy()->startOfDay()->diffInDays(today());
                                }
                            @endphp
                            <tr class="hover:bg-latar-2 dark:hover:bg-gelap">
                                <td class="px-5 py-3">
                                    <span class="font-medium text-tinta dark:text-teks-gelap">{{ $tagihan->siswa?->nama ?? '-' }}</span>
                                    <span class="mt-0.5 block font-mono text-[11px] text-redup dark:text-redup-gelap">{{ $tagihan->siswa?->nis }}</span>
                                </td>
                                <td class="px-5 py-3">
                                    <span class="text-tinta dark:text-teks-gelap">{{ $tagihan->siswa?->kelas?->nama_kelas ?? '-' }}</span>
                                    <span class="mt-0.5 block text-[11px] text-redup dark:text-redup-gelap">{{ $tagihan->siswa?->kelas?->jurusan?->nama_jurusan ?? '-' }}</span>
                                </td>
                                <td class="px-5 py-3 whitespace-nowrap text-tinta dark:text-teks-gelap">
                                    {{ $tagihan->namaBulan() }} {{ $tagihan->tahun }}
                                </td>
                                <td class="px-5 py-3 text-right font-semibold tabular-nums text-tinta dark:text-teks-gelap">
                                    {{ $rupiah($tagihan->nominal) }}
                                </td>
                                <td class="px-5 py-3 whitespace-nowrap text-redup dark:text-redup-gelap">
                                    {{ $tagihan->tanggal_jatuh_tempo?->format('d/m/Y') ?? '-' }}
                                    <span class="mt-0.5 block text-[11px]">dibuat {{ $tagihan->created_at?->format('d/m/Y') ?? '-' }}</span>
                                </td>
                                <td class="px-5 py-3 whitespace-nowrap text-tinta dark:text-teks-gelap">
                                    {{ $tagihan->successfulPayment()?->paidAt()?->format('d/m/Y') ?? '-' }}
                                </td>
                                <td class="px-5 py-3 whitespace-nowrap text-right tabular-nums {{ ($late ?? 0) >= 60 ? 'font-semibold text-red-700 dark:text-red-400' : 'text-redup dark:text-redup-gelap' }}">
                                    {{ $late === null ? '-' : $late.' hari' }}
                                </td>
                                <td class="px-5 py-3">
                                    @include('layouts.partials._status-badge', ['tagihan' => $tagihan])
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-latar-2 dark:bg-gelap">
                        <tr>
                            <td colspan="3" class="px-5 py-3 text-right text-sm font-semibold text-tinta dark:text-teks-gelap">Total</td>
                            <td class="px-5 py-3 text-right text-sm font-bold tabular-nums text-tinta dark:text-teks-gelap">
                                {{ $rupiah($total_nominal) }}
                            </td>
                            <td colspan="4"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @endif
    </section>

@endsection