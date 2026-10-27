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
    @endphp

    {{-- Form filter. --}}
    <form method="GET" action="{{ route('kepsek.reports.payments') }}"
          class="mb-4 rounded-xl border border-garis dark:border-garis-gelap bg-white p-4 shadow-sm">
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
                <label for="dari" class="mb-1 block text-xs font-medium text-redup dark:text-redup-gelap">Tanggal dari</label>
                <input type="date" name="dari" id="dari" value="{{ $filter['dari'] ?? '' }}"
                       class="w-full rounded-lg border border-garis bg-white px-3 py-2 text-sm text-tinta shadow-sm focus:border-neon-teks focus:outline-none focus:ring-2 focus:ring-neon/40 dark:border-redup dark:bg-gelap dark:text-teks-gelap">
            </div>

            <div>
                <label for="sampai" class="mb-1 block text-xs font-medium text-redup dark:text-redup-gelap">Tanggal sampai</label>
                <input type="date" name="sampai" id="sampai" value="{{ $filter['sampai'] ?? '' }}"
                       class="w-full rounded-lg border border-garis bg-white px-3 py-2 text-sm text-tinta shadow-sm focus:border-neon-teks focus:outline-none focus:ring-2 focus:ring-neon/40 dark:border-redup dark:bg-gelap dark:text-teks-gelap">
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

            <div class="flex items-end gap-2">
                <button type="submit"
                        class="rounded-lg bg-neon px-4 py-2 text-sm font-semibold text-tinta hover:bg-neon-tekan tombol-tekan">
                    Terapkan
                </button>
                <a href="{{ route('kepsek.reports.payments') }}"
                   class="rounded-lg border border-garis dark:border-garis-gelap bg-white px-4 py-2 text-sm font-medium text-tinta dark:text-teks-gelap hover:bg-latar-2 dark:hover:bg-gelap">
                    Reset
                </a>
            </div>
        </div>
    </form>

    {{-- Ringkasan angka hasil filter. --}}
    <div class="mb-4 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">

        <div class="rounded-xl border border-garis dark:border-garis-gelap bg-white p-5 shadow-sm">
            <p class="text-sm text-redup dark:text-redup-gelap">Total nominal</p>
            <p class="mt-1 text-2xl font-bold text-tinta dark:text-teks-gelap">
                Rp{{ number_format((float) $total_nominal, 0, ',', '.') }}
            </p>
            <p class="mt-1 text-xs text-redup dark:text-redup-gelap">Semua tagihan pada filter ini</p>
        </div>

        <div class="rounded-xl border border-garis dark:border-garis-gelap bg-white p-5 shadow-sm">
            <p class="text-sm text-redup dark:text-redup-gelap">Lunas</p>
            <p class="mt-1 text-2xl font-bold text-emerald-600">
                Rp{{ number_format((float) $total_lunas, 0, ',', '.') }}
            </p>
        </div>

        <div class="rounded-xl border border-garis dark:border-garis-gelap bg-white p-5 shadow-sm">
            <p class="text-sm text-redup dark:text-redup-gelap">Belum bayar</p>
            <p class="mt-1 text-2xl font-bold text-amber-600">
                Rp{{ number_format((float) $total_belum_bayar, 0, ',', '.') }}
            </p>
        </div>

        <div class="rounded-xl border border-garis dark:border-garis-gelap bg-white p-5 shadow-sm">
            <p class="text-sm text-redup dark:text-redup-gelap">Tunggakan</p>
            <p class="mt-1 text-2xl font-bold text-red-600">
                Rp{{ number_format((float) $total_tunggakan, 0, ',', '.') }}
            </p>
        </div>
    </div>

    {{-- Tabel hasil. --}}
    <div class="overflow-hidden rounded-xl border border-garis dark:border-garis-gelap bg-white shadow-sm">
        <div class="border-b border-garis dark:border-garis-gelap px-5 py-4">
            <h2 class="text-sm font-semibold text-tinta dark:text-teks-gelap">Rincian tagihan</h2>
            <p class="mt-1 text-xs text-redup dark:text-redup-gelap">
                {{ $daftar_tagihan->count() }} baris.
                Lunas dan uang masuk dihitung dari tabel pembayaran, bukan dari status tagihan.
            </p>
        </div>

        @if ($daftar_tagihan->isEmpty())
            <p class="px-5 py-6 text-sm text-redup dark:text-redup-gelap">Tidak ada tagihan yang cocok dengan filter ini.</p>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-garis dark:divide-garis-gelap text-sm">
                    <thead class="bg-latar-2 dark:bg-gelap text-left text-xs uppercase tracking-wide text-redup dark:text-redup-gelap">
                        <tr>
                            <th class="px-5 py-3 font-semibold">Siswa</th>
                            <th class="px-5 py-3 font-semibold">Kelas</th>
                            <th class="px-5 py-3 font-semibold">Jurusan</th>
                            <th class="px-5 py-3 font-semibold">Periode</th>
                            <th class="px-5 py-3 font-semibold">Nominal</th>
                            <th class="px-5 py-3 font-semibold">Dibuat</th>
                            <th class="px-5 py-3 font-semibold">Jatuh tempo</th>
                            <th class="px-5 py-3 font-semibold">Tanggal bayar</th>
                            <th class="px-5 py-3 font-semibold">Terlambat</th>
                            <th class="px-5 py-3 font-semibold">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-garis dark:divide-garis-gelap">
                        @foreach ($daftar_tagihan as $tagihan)
                            <tr class="hover:bg-latar-2 dark:hover:bg-gelap">
                                <td class="px-5 py-3">
                                    <span class="font-medium text-tinta dark:text-teks-gelap">{{ $tagihan->siswa?->nama ?? '-' }}</span>
                                    <span class="block text-xs text-redup dark:text-redup-gelap">{{ $tagihan->siswa?->nis }}</span>
                                </td>
                                <td class="px-5 py-3 text-redup dark:text-redup-gelap">{{ $tagihan->siswa?->kelas?->nama_kelas ?? '-' }}</td>
                                <td class="px-5 py-3 text-redup dark:text-redup-gelap">{{ $tagihan->siswa?->kelas?->jurusan?->nama_jurusan ?? '-' }}</td>
                                <td class="px-5 py-3 text-tinta dark:text-teks-gelap">{{ $tagihan->namaBulan() }} {{ $tagihan->tahun }}</td>
                                <td class="px-5 py-3 text-tinta dark:text-teks-gelap">
                                    Rp{{ number_format((float) $tagihan->nominal, 0, ',', '.') }}
                                </td>
                                <td class="px-5 py-3 text-redup dark:text-redup-gelap">
                                    {{ $tagihan->created_at?->format('d/m/Y') ?? '-' }}
                                </td>
                                <td class="px-5 py-3 text-redup dark:text-redup-gelap">
                                    {{ $tagihan->tanggal_jatuh_tempo?->format('d/m/Y') ?? '-' }}
                                </td>
                                <td class="px-5 py-3 text-tinta dark:text-teks-gelap">
                                    {{ $tagihan->successfulPayment()?->paidAt()?->format('d/m/Y') ?? '-' }}
                                </td>
                                <td class="px-5 py-3 text-redup dark:text-redup-gelap">
                                    @php($late = $tagihan->daysLate())
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
                            <td colspan="6" class="px-5 py-3 text-right text-sm font-semibold text-tinta dark:text-teks-gelap">Total</td>
                            <td colspan="4" class="px-5 py-3 text-sm font-bold text-tinta dark:text-teks-gelap">
                                Rp{{ number_format((float) $total_nominal, 0, ',', '.') }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @endif
    </div>

@endsection
