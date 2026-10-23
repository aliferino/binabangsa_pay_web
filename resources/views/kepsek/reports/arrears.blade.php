@extends('layouts.app')

@section('judul', 'Laporan Tunggakan')
@section('keterangan', 'Rekap tunggakan SPP per siswa')

@section('isi')

    {{--
        Catatan bentuk data.
        Controller mengirim $daftar_tunggakan sebagai kumpulan array dengan kunci
        nis, nama, kelas, bulan_tunggakan, jumlah_tunggakan, dan total_nominal.
        Supaya tetap aman kalau nanti bentuknya diubah menjadi objek, nilai dibaca
        lewat data_get yang bisa menangani array maupun objek.
    --}}

    {{-- Form filter. --}}
    <form method="GET" action="{{ route('kepsek.reports.arrears') }}"
          class="mb-4 rounded-xl border border-garis dark:border-garis-gelap bg-white p-4 shadow-sm">
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">

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

            <div class="flex items-end gap-2 sm:col-span-2">
                <button type="submit"
                        class="rounded-lg bg-neon px-4 py-2 text-sm font-semibold text-tinta hover:bg-neon-tekan tombol-tekan">
                    Terapkan
                </button>
                <a href="{{ route('kepsek.reports.arrears') }}"
                   class="rounded-lg border border-garis dark:border-garis-gelap bg-white px-4 py-2 text-sm font-medium text-tinta dark:text-teks-gelap hover:bg-latar-2 dark:hover:bg-gelap">
                    Reset
                </a>
            </div>
        </div>
    </form>

    {{-- Total keseluruhan. --}}
    <div class="mb-4 rounded-xl border border-red-200 bg-red-50 p-5">
        <p class="text-sm font-medium text-red-700">Total seluruh tunggakan</p>
        <p class="mt-1 text-3xl font-bold text-red-800">
            Rp{{ number_format((float) $total_semua, 0, ',', '.') }}
        </p>
        <p class="mt-1 text-xs text-red-600">
            {{ $daftar_tunggakan->count() }} siswa menunggak pada filter ini.
        </p>
    </div>

    {{-- Tabel tunggakan. --}}
    <div class="overflow-hidden rounded-xl border border-garis dark:border-garis-gelap bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-garis dark:divide-garis-gelap text-sm">
                <thead class="bg-latar-2 dark:bg-gelap text-left text-xs uppercase tracking-wide text-redup dark:text-redup-gelap">
                    <tr>
                        <th class="px-5 py-3 font-semibold">NIS</th>
                        <th class="px-5 py-3 font-semibold">Nama</th>
                        <th class="px-5 py-3 font-semibold">Kelas</th>
                        <th class="px-5 py-3 font-semibold">Bulan tunggakan</th>
                        <th class="px-5 py-3 font-semibold">Jumlah</th>
                        <th class="px-5 py-3 font-semibold">Total nominal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-garis dark:divide-garis-gelap">
                    @forelse ($daftar_tunggakan as $baris)
                        <tr class="hover:bg-latar-2 dark:hover:bg-gelap">
                            <td class="px-5 py-3 font-medium text-tinta dark:text-teks-gelap">{{ data_get($baris, 'nis') }}</td>
                            <td class="px-5 py-3 text-tinta dark:text-teks-gelap">{{ data_get($baris, 'nama') }}</td>
                            <td class="px-5 py-3 text-redup dark:text-redup-gelap">{{ data_get($baris, 'kelas') ?? '-' }}</td>
                            <td class="px-5 py-3 text-redup dark:text-redup-gelap">{{ data_get($baris, 'bulan_tunggakan') ?: '-' }}</td>
                            <td class="px-5 py-3 text-redup dark:text-redup-gelap">{{ data_get($baris, 'jumlah_tunggakan') }}</td>
                            <td class="px-5 py-3 font-medium text-red-700">
                                Rp{{ number_format((float) data_get($baris, 'total_nominal', 0), 0, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-8 text-center text-redup dark:text-redup-gelap">
                                Tidak ada tunggakan pada filter ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @if ($daftar_tunggakan->isNotEmpty())
                    <tfoot class="bg-latar-2 dark:bg-gelap">
                        <tr>
                            <td colspan="5" class="px-5 py-3 text-right text-sm font-semibold text-tinta dark:text-teks-gelap">Total</td>
                            <td class="px-5 py-3 text-sm font-bold text-red-800">
                                Rp{{ number_format((float) $total_semua, 0, ',', '.') }}
                            </td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>

@endsection
