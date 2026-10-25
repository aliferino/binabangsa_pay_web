{{-- Form filter dan tabel daftar pembayaran. --}}

{{--
    Form filter.
    Halaman ini dipakai admin dan teller lewat route berbeda, jadi aksi form
    memakai URL yang sedang dibuka supaya filter tetap bekerja untuk keduanya.
--}}
<form method="GET" action="{{ url()->current() }}"
      class="mb-4 rounded-xl border border-garis dark:border-garis-gelap bg-white dark:bg-gelap-2 p-4 shadow-sm">
    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-5">

        <div>
            <label for="filter_dari" class="mb-1 block text-xs font-medium text-redup dark:text-redup-gelap">Dari tanggal</label>
            <input type="date" name="dari" id="filter_dari" value="{{ $filter['dari'] ?? '' }}"
                   class="w-full rounded-lg border border-garis px-3 py-2 text-sm text-tinta shadow-sm focus:border-neon-teks focus:outline-none focus:ring-2 focus:ring-neon/40 dark:border-redup dark:bg-gelap dark:text-teks-gelap dark:placeholder:text-redup-gelap">
        </div>

        <div>
            <label for="filter_sampai" class="mb-1 block text-xs font-medium text-redup dark:text-redup-gelap">Sampai tanggal</label>
            <input type="date" name="sampai" id="filter_sampai" value="{{ $filter['sampai'] ?? '' }}"
                   class="w-full rounded-lg border border-garis px-3 py-2 text-sm text-tinta shadow-sm focus:border-neon-teks focus:outline-none focus:ring-2 focus:ring-neon/40 dark:border-redup dark:bg-gelap dark:text-teks-gelap dark:placeholder:text-redup-gelap">
        </div>

        <div>
            <label for="filter_metode_pembayaran" class="mb-1 block text-xs font-medium text-redup dark:text-redup-gelap">Metode</label>
            <select name="metode_pembayaran" id="filter_metode_pembayaran"
                    class="w-full rounded-lg border border-garis bg-white pl-3 pr-9 py-2 text-sm text-tinta shadow-sm focus:border-neon-teks focus:outline-none focus:ring-2 focus:ring-neon/40 dark:border-redup dark:bg-gelap dark:text-teks-gelap">
                <option value="">Semua metode</option>
                <option value="tunai" @selected(($filter['metode_pembayaran'] ?? '') === 'tunai')>Tunai</option>
                <option value="qris" @selected(($filter['metode_pembayaran'] ?? '') === 'qris')>QRIS</option>
                <option value="virtual_account" @selected(($filter['metode_pembayaran'] ?? '') === 'virtual_account')>Virtual Account</option>
            </select>
        </div>

        <div>
            <label for="filter_siswa_id" class="mb-1 block text-xs font-medium text-redup dark:text-redup-gelap">Siswa</label>
            <select name="siswa_id" id="filter_siswa_id"
                    class="w-full rounded-lg border border-garis bg-white pl-3 pr-9 py-2 text-sm text-tinta shadow-sm focus:border-neon-teks focus:outline-none focus:ring-2 focus:ring-neon/40 dark:border-redup dark:bg-gelap dark:text-teks-gelap">
                <option value="">Semua siswa</option>
                @foreach ($daftar_siswa as $siswa)
                    <option value="{{ $siswa->id_siswa }}" @selected(($filter['siswa_id'] ?? '') == $siswa->id_siswa)>
                        {{ $siswa->nis }} - {{ $siswa->nama }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="filter_status" class="mb-1 block text-xs font-medium text-redup dark:text-redup-gelap">Status</label>
            <select name="status" id="filter_status"
                    class="w-full rounded-lg border border-garis bg-white pl-3 pr-9 py-2 text-sm text-tinta shadow-sm focus:border-neon-teks focus:outline-none focus:ring-2 focus:ring-neon/40 dark:border-redup dark:bg-gelap dark:text-teks-gelap">
                <option value="">Semua status</option>
                <option value="berhasil" @selected(($filter['status'] ?? '') === 'berhasil')>Berhasil</option>
                <option value="gagal" @selected(($filter['status'] ?? '') === 'gagal')>Gagal</option>
                <option value="dibatalkan" @selected(($filter['status'] ?? '') === 'dibatalkan')>Dibatalkan</option>
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

{{-- Tabel pembayaran. --}}
<div class="overflow-hidden rounded-xl border border-garis dark:border-garis-gelap bg-white dark:bg-gelap-2 shadow-sm">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-garis dark:divide-garis-gelap text-sm">
            <thead class="bg-latar-2 dark:bg-gelap text-left text-xs uppercase tracking-wide text-redup dark:text-redup-gelap">
                <tr>
                    <th class="px-5 py-3 font-semibold">Tanggal</th>
                    <th class="px-5 py-3 font-semibold">Siswa</th>
                    <th class="px-5 py-3 font-semibold">Nominal</th>
                    <th class="px-5 py-3 font-semibold">Metode</th>
                    <th class="px-5 py-3 font-semibold">Status</th>
                    <th class="px-5 py-3 text-right font-semibold">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-garis dark:divide-garis-gelap">
                @forelse ($daftar_pembayaran as $pembayaran)
                    <tr class="hover:bg-latar-2 dark:hover:bg-gelap">
                        <td class="px-5 py-3 text-redup dark:text-redup-gelap">
                            {{ $pembayaran->created_at?->format('d/m/Y H:i') ?? '-' }}
                        </td>
                        <td class="px-5 py-3">
                            <span class="font-medium text-tinta dark:text-teks-gelap">{{ $pembayaran->siswa?->nama ?? '-' }}</span>
                            <span class="block text-xs text-redup dark:text-redup-gelap">{{ $pembayaran->siswa?->nis }}</span>
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
                                    'dibatalkan' => ['label' => 'Dibatalkan', 'kelas' => 'bg-latar-2 dark:bg-gelap text-tinta dark:text-teks-gelap'],
                                ];
                                $dataStatus = $petaStatus[$pembayaran->status] ?? ['label' => $pembayaran->status, 'kelas' => 'bg-latar-2 dark:bg-gelap text-tinta dark:text-teks-gelap'];
                            @endphp
                            <span class="inline-flex whitespace-nowrap rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $dataStatus['kelas'] }}">
                                {{ $dataStatus['label'] }}
                            </span>
                        </td>
                        <td class="px-5 py-3">
                            <div class="flex flex-wrap items-center justify-end gap-2">
                                <a href="{{ route('teller.payments.show', $pembayaran) }}"
                                   class="inline-flex h-7 w-7 items-center justify-center rounded-md border border-garis text-redup transition-colors hover:border-neon-teks hover:bg-latar-2 hover:text-tinta dark:border-garis-gelap dark:text-redup-gelap dark:hover:bg-gelap dark:hover:text-teks-gelap"
                                   title="Lihat" aria-label="Lihat">
                                    <svg class="h-[18px] w-[18px]" aria-hidden="true"><use href="#i-lihat"/></svg>
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-5 py-8 text-center text-redup dark:text-redup-gelap">
                            Tidak ada pembayaran yang cocok dengan filter ini.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
