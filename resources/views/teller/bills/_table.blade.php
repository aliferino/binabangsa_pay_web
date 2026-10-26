{{-- Form filter dan tabel daftar tagihan SPP. --}}

{{--
    Form filter.
    Halaman ini dipakai admin dan teller lewat route berbeda, jadi aksi form
    memakai URL yang sedang dibuka supaya filter tetap bekerja untuk keduanya.
--}}
<form method="GET" action="{{ url()->current() }}"
      class="mb-4 rounded-xl border border-garis dark:border-garis-gelap bg-white dark:bg-gelap-2 p-4 shadow-sm">
    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-5">

        <div>
            <label for="filter_id_siswa" class="mb-1 block text-xs font-medium text-redup dark:text-redup-gelap">Siswa</label>
            <select name="id_siswa" id="filter_id_siswa"
                    class="w-full rounded-lg border border-garis bg-white pl-3 pr-9 py-2 text-sm text-tinta shadow-sm focus:border-neon-teks focus:outline-none focus:ring-2 focus:ring-neon/40 dark:border-redup dark:bg-gelap dark:text-teks-gelap">
                <option value="">Semua siswa</option>
                @foreach ($daftar_siswa as $siswa)
                    <option value="{{ $siswa->id_siswa }}" @selected(($filter['id_siswa'] ?? '') == $siswa->id_siswa)>
                        {{ $siswa->nis }} - {{ $siswa->nama }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="filter_bulan" class="mb-1 block text-xs font-medium text-redup dark:text-redup-gelap">Bulan</label>
            <select name="bulan" id="filter_bulan"
                    class="w-full rounded-lg border border-garis bg-white pl-3 pr-9 py-2 text-sm text-tinta shadow-sm focus:border-neon-teks focus:outline-none focus:ring-2 focus:ring-neon/40 dark:border-redup dark:bg-gelap dark:text-teks-gelap">
                <option value="">Semua bulan</option>
                @foreach (['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'] as $angka => $nama)
                    <option value="{{ $angka + 1 }}" @selected(($filter['bulan'] ?? '') == $angka + 1)>
                        {{ $nama }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="filter_tahun" class="mb-1 block text-xs font-medium text-redup dark:text-redup-gelap">Tahun</label>
            <select name="tahun" id="filter_tahun"
                    class="w-full rounded-lg border border-garis bg-white pl-3 pr-9 py-2 text-sm text-tinta shadow-sm focus:border-neon-teks focus:outline-none focus:ring-2 focus:ring-neon/40 dark:border-redup dark:bg-gelap dark:text-teks-gelap">
                <option value="">Semua tahun</option>
                @foreach ($daftar_tahun as $tahun)
                    <option value="{{ $tahun }}" @selected(($filter['tahun'] ?? '') == $tahun)>{{ $tahun }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="filter_status" class="mb-1 block text-xs font-medium text-redup dark:text-redup-gelap">Status</label>
            <select name="status" id="filter_status"
                    class="w-full rounded-lg border border-garis bg-white pl-3 pr-9 py-2 text-sm text-tinta shadow-sm focus:border-neon-teks focus:outline-none focus:ring-2 focus:ring-neon/40 dark:border-redup dark:bg-gelap dark:text-teks-gelap">
                <option value="">Semua status</option>
                <option value="belum_bayar" @selected(($filter['status'] ?? '') === 'belum_bayar')>Belum bayar</option>
                <option value="lunas" @selected(($filter['status'] ?? '') === 'lunas')>Lunas</option>
                <option value="tunggakan" @selected(($filter['status'] ?? '') === 'tunggakan')>Tunggakan</option>
            </select>
        </div>

        <div class="flex items-end gap-2">
            <button type="submit"
                    class="border border-transparent rounded-lg bg-neon px-4 py-2 text-sm font-semibold text-tinta hover:bg-neon-tekan tombol-tekan">
                Terapkan
            </button>
            <a href="{{ url()->current() }}"
               class="rounded-lg border border-garis dark:border-garis-gelap bg-white dark:bg-gelap-2 px-4 py-2 text-sm font-medium text-tinta dark:text-teks-gelap hover:bg-latar-2 dark:hover:bg-gelap">
                Reset
            </a>
        </div>
    </div>
</form>

{{-- Tabel tagihan. --}}
<div class="overflow-hidden rounded-xl border border-garis dark:border-garis-gelap bg-white dark:bg-gelap-2 shadow-sm">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-garis dark:divide-garis-gelap text-sm">
            <thead class="bg-latar-2 dark:bg-gelap text-left text-xs uppercase tracking-wide text-redup dark:text-redup-gelap">
                <tr>
                    <th class="px-5 py-3 font-semibold">Siswa</th>
                    <th class="px-5 py-3 font-semibold">Kelas</th>
                    <th class="px-5 py-3 font-semibold">Periode</th>
                    <th class="px-5 py-3 font-semibold">Nominal</th>
                    <th class="px-5 py-3 font-semibold">Jatuh tempo</th>
                    <th class="px-5 py-3 font-semibold">Status</th>
                    <th class="px-5 py-3 text-right font-semibold">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-garis dark:divide-garis-gelap">
                @forelse ($daftar_tagihan as $tagihan)
                    <tr class="hover:bg-latar-2 dark:hover:bg-gelap">
                        <td class="px-5 py-3">
                            <span class="font-medium text-tinta dark:text-teks-gelap">{{ $tagihan->siswa?->nama ?? '-' }}</span>
                            <span class="block text-xs text-redup dark:text-redup-gelap">{{ $tagihan->siswa?->nis }}</span>
                        </td>
                        <td class="px-5 py-3 text-redup dark:text-redup-gelap">{{ $tagihan->siswa?->kelas?->nama_kelas ?? '-' }}</td>
                        <td class="px-5 py-3 text-tinta dark:text-teks-gelap">{{ $tagihan->namaBulan() }} {{ $tagihan->tahun }}</td>
                        <td class="px-5 py-3 text-tinta dark:text-teks-gelap">
                            Rp{{ number_format((float) $tagihan->nominal, 0, ',', '.') }}
                        </td>
                        <td class="px-5 py-3 text-redup dark:text-redup-gelap">
                            {{ $tagihan->tanggal_jatuh_tempo?->format('d/m/Y') ?? '-' }}
                        </td>
                        <td class="px-5 py-3">
                            @include('layouts.partials._status-badge', ['tagihan' => $tagihan])
                        </td>
                        <td class="px-5 py-3">
                            <div class="flex flex-wrap items-center justify-end gap-2">
<a href="{{ route(auth()->user()->role === 'teller' ? 'teller.bills.show' : 'tagihan-spp.show', $tagihan) }}" class="inline-flex h-8 w-8 items-center justify-center rounded-md border border-garis text-redup transition-colors hover:border-neon-teks hover:bg-latar-2 hover:text-tinta dark:border-garis-gelap dark:text-redup-gelap dark:hover:bg-gelap dark:hover:text-teks-gelap" title="Lihat detail" aria-label="Lihat detail"><svg class="h-[18px] w-[18px]" aria-hidden="true"><use href="#i-lihat"/></svg>
                                </a>

                                @if (auth()->user()->role === 'admin')
<button type="button" data-modal-buka="ubah" data-modal-target="modal-tagihan" data-id="{{ $tagihan->id_tagihan }}" data-id_siswa="{{ $tagihan->id_siswa }}" data-id_siswa_label="{{ $tagihan->siswa ? $tagihan->siswa->nis.' - '.$tagihan->siswa->nama : '' }}" data-bulan="{{ $tagihan->bulan }}" data-tahun="{{ $tagihan->tahun }}" data-id_tahun_ajaran="{{ $tagihan->id_tahun_ajaran }}" data-id_tarif="{{ $tagihan->id_tarif }}" data-nominal="{{ (int) $tagihan->nominal }}" data-tanggal_jatuh_tempo="{{ $tagihan->tanggal_jatuh_tempo?->format('Y-m-d') }}" data-status="{{ $tagihan->status }}" class="inline-flex h-8 w-8 items-center justify-center rounded-md border border-garis text-redup transition-colors hover:border-neon-teks hover:bg-latar-2 hover:text-tinta dark:border-garis-gelap dark:text-redup-gelap dark:hover:bg-gelap dark:hover:text-teks-gelap" title="Ubah" aria-label="Ubah"><svg class="h-[18px] w-[18px]" aria-hidden="true"><use href="#i-ubah"/></svg>
                                    </button>

                                    <form method="POST" action="{{ route('tagihan-spp.destroy', $tagihan) }}"
                                          data-konfirmasi="Hapus data tagihan ini? Tindakan ini tidak bisa dibatalkan.">
                                        @csrf
                                        @method('DELETE')
<button type="submit" class="inline-flex h-8 w-8 items-center justify-center rounded-md border border-red-300 text-red-700 transition-colors hover:bg-red-50 dark:border-red-900 dark:text-red-400 dark:hover:bg-red-950/40" title="Hapus" aria-label="Hapus"><svg class="h-[18px] w-[18px]" aria-hidden="true"><use href="#i-hapus"/></svg>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-5 py-8 text-center text-redup dark:text-redup-gelap">
                            Tidak ada tagihan yang cocok dengan filter ini.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
