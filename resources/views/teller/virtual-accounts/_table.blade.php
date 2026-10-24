{{-- Form filter dan tabel daftar Virtual Account. --}}

{{--
    Form filter.
    Halaman ini dipakai admin dan teller lewat route berbeda, jadi aksi form
    memakai URL yang sedang dibuka supaya filter tetap bekerja untuk keduanya.
--}}
<form method="GET" action="{{ url()->current() }}"
      class="rounded-xl border border-garis dark:border-garis-gelap bg-white p-4 shadow-sm">
    <div class="grid grid-cols-1 gap-x-4 gap-y-5 sm:grid-cols-2 lg:grid-cols-[repeat(2,minmax(0,1fr))_auto]">

        <div>
            <label for="filter_siswa_id" class="mb-1.5 block text-xs font-medium text-redup dark:text-redup-gelap">Siswa</label>
            <select name="siswa_id" id="filter_siswa_id"
                    class="w-full rounded-lg border border-garis bg-white px-3 py-2 text-sm text-tinta shadow-sm focus:border-neon-teks focus:outline-none focus:ring-2 focus:ring-neon/40 dark:border-redup dark:bg-gelap dark:text-teks-gelap">
                <option value="">Semua siswa</option>
                @foreach ($students as $student)
                    <option value="{{ $student->id_siswa }}" @selected(($filter['siswa_id'] ?? '') == $student->id_siswa)>
                        {{ $student->nis }} - {{ $student->nama }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="filter_status" class="mb-1.5 block text-xs font-medium text-redup dark:text-redup-gelap">Status</label>
            <select name="status" id="filter_status"
                    class="w-full rounded-lg border border-garis bg-white px-3 py-2 text-sm text-tinta shadow-sm focus:border-neon-teks focus:outline-none focus:ring-2 focus:ring-neon/40 dark:border-redup dark:bg-gelap dark:text-teks-gelap">
                <option value="">Semua status</option>
                <option value="aktif" @selected(($filter['status'] ?? '') === 'aktif')>Aktif</option>
                <option value="nonaktif" @selected(($filter['status'] ?? '') === 'nonaktif')>Nonaktif</option>
            </select>
        </div>

        <div class="flex items-end gap-3 sm:col-span-2 lg:col-span-1 lg:pl-1">
            <button type="submit"
                    class="rounded-lg bg-neon px-4 py-2 text-sm font-semibold text-tinta hover:bg-neon-tekan tombol-tekan">
                Terapkan
            </button>
            <a href="{{ url()->current() }}"
               class="rounded-lg border border-garis dark:border-garis-gelap bg-white px-4 py-2 text-sm font-medium text-tinta dark:text-teks-gelap hover:bg-latar-2 dark:hover:bg-gelap">
                Reset
            </a>
        </div>
    </div>
</form>

{{-- Tabel Virtual Account. --}}
<div class="overflow-hidden rounded-xl border border-garis dark:border-garis-gelap bg-white shadow-sm">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-garis dark:divide-garis-gelap text-sm">
            <thead class="bg-latar-2 dark:bg-gelap text-left text-xs uppercase tracking-wide text-redup dark:text-redup-gelap">
                <tr>
                    <th class="px-5 py-3.5 font-semibold">Siswa</th>
                    <th class="px-5 py-3.5 font-semibold">Nomor VA</th>
                    <th class="px-5 py-3.5 font-semibold">Bank</th>
                    <th class="px-5 py-3.5 font-semibold">Status</th>
                    <th class="px-5 py-3.5 font-semibold">Kedaluwarsa</th>
                    <th class="px-5 py-3.5 text-right font-semibold">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-garis dark:divide-garis-gelap">
                @forelse ($virtualAccounts as $virtualAccount)
                    <tr class="hover:bg-latar-2 dark:hover:bg-gelap">
                        <td class="px-5 py-3.5">
                            <span class="font-medium text-tinta dark:text-teks-gelap">{{ $virtualAccount->siswa?->nama ?? '-' }}</span>
                            <span class="block text-xs text-redup dark:text-redup-gelap">{{ $virtualAccount->siswa?->nis }}</span>
                        </td>
                        <td class="px-5 py-3.5">
                            @if ($virtualAccount->account_number)
                                <span class="font-mono font-medium text-tinta dark:text-teks-gelap">{{ $virtualAccount->account_number }}</span>
                                <span class="block text-xs text-redup dark:text-redup-gelap">{{ $virtualAccount->nomor_va }}</span>
                            @else
                                <span class="font-mono font-medium text-tinta dark:text-teks-gelap">{{ $virtualAccount->nomor_va }}</span>
                            @endif
                        </td>
                        <td class="px-5 py-3 text-redup dark:text-redup-gelap">{{ $virtualAccount->bank_code ?: '-' }}</td>
                        <td class="px-5 py-3.5">
                            @php
                                $petaStatus = [
                                    'aktif' => ['label' => 'Aktif', 'kelas' => 'bg-emerald-100 text-emerald-800'],
                                    'nonaktif' => ['label' => 'Nonaktif', 'kelas' => 'bg-latar-2 dark:bg-gelap text-tinta dark:text-teks-gelap'],
                                ];
                                $dataStatus = $petaStatus[$virtualAccount->status] ?? ['label' => $virtualAccount->status, 'kelas' => 'bg-latar-2 dark:bg-gelap text-tinta dark:text-teks-gelap'];
                            @endphp
                            <span class="inline-flex whitespace-nowrap rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $dataStatus['kelas'] }}">
                                {{ $dataStatus['label'] }}
                            </span>
                        </td>
                        <td class="px-5 py-3 text-redup dark:text-redup-gelap">
                            {{ $virtualAccount->expired_at?->format('d/m/Y') ?? '-' }}
                        </td>
                        <td class="px-5 py-3.5">
                            {{--
                                Tombol ikon, seragam dengan tabel lain. Aksi aktif
                                dan nonaktif hanya muncul untuk admin, karena
                                mengubah status nomor pembayaran bukan hak teller.
                            --}}
                            <div class="flex flex-wrap items-center justify-end gap-2">
                                <a href="{{ route('virtual-account.show', $virtualAccount) }}"
                                   class="inline-flex h-7 w-7 items-center justify-center rounded-md border border-garis text-redup transition-colors hover:border-neon-teks hover:bg-latar-2 hover:text-tinta dark:border-garis-gelap dark:text-redup-gelap dark:hover:bg-gelap dark:hover:text-teks-gelap"
                                   title="Lihat" aria-label="Lihat">
                                    <svg class="h-[18px] w-[18px]" aria-hidden="true"><use href="#i-lihat"/></svg>
                                </a>

                                @if (auth()->user()->role === 'admin')
                                    @if ($virtualAccount->aktif())
                                        <form method="POST" action="{{ route('virtual-account.nonaktifkan', $virtualAccount) }}"
                                              data-konfirmasi="Nonaktifkan Virtual Account {{ $virtualAccount->nomor_va }}? Tindakan ini tidak menghapus data, hanya mematikan nomornya." data-konfirmasi-jenis="hapus">
                                            @csrf
                                            <button type="submit"
                                                    class="inline-flex h-7 w-7 items-center justify-center rounded-md border border-garis text-redup transition-colors hover:border-red-300 hover:bg-red-50 hover:text-red-700 dark:border-garis-gelap dark:text-redup-gelap dark:hover:bg-red-950/40"
                                                    title="Nonaktifkan" aria-label="Nonaktifkan">
                                                <svg class="h-[18px] w-[18px]" aria-hidden="true"><use href="#i-padam"/></svg>
                                            </button>
                                        </form>
                                    @else
                                        <form method="POST" action="{{ route('virtual-account.aktifkan', $virtualAccount) }}"
                                              data-konfirmasi="Aktifkan kembali Virtual Account {{ $virtualAccount->nomor_va }}?" data-konfirmasi-jenis="simpan">
                                            @csrf
                                            <button type="submit"
                                                    class="inline-flex h-7 w-7 items-center justify-center rounded-md border border-garis text-redup transition-colors hover:border-neon-teks hover:bg-latar-2 hover:text-tinta dark:border-garis-gelap dark:text-redup-gelap dark:hover:bg-gelap dark:hover:text-teks-gelap"
                                                    title="Aktifkan" aria-label="Aktifkan">
                                                <svg class="h-[18px] w-[18px]" aria-hidden="true"><use href="#i-padam"/></svg>
                                            </button>
                                        </form>
                                    @endif
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-5 py-10 text-center text-redup dark:text-redup-gelap">
                            Tidak ada Virtual Account yang cocok dengan filter ini.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
