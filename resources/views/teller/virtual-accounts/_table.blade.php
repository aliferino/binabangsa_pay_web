{{-- Form filter dan tabel daftar Virtual Account. --}}

{{--
    Form filter.
    Halaman ini dipakai admin dan teller lewat route berbeda, jadi aksi form
    memakai URL yang sedang dibuka supaya filter tetap bekerja untuk keduanya.
--}}
<form method="GET" action="{{ url()->current() }}"
      class="mb-4 rounded-xl border border-garis dark:border-garis-gelap bg-white p-4 shadow-sm">
    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">

        <div>
            <label for="filter_siswa_id" class="mb-1 block text-xs font-medium text-redup dark:text-redup-gelap">Siswa</label>
            <select name="siswa_id" id="filter_siswa_id"
                    class="w-full rounded-lg border border-garis bg-white px-3 py-2 text-sm text-tinta shadow-sm focus:border-neon-teks focus:outline-none focus:ring-2 focus:ring-neon/40 dark:border-redup dark:bg-gelap dark:text-teks-gelap">
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
                    class="w-full rounded-lg border border-garis bg-white px-3 py-2 text-sm text-tinta shadow-sm focus:border-neon-teks focus:outline-none focus:ring-2 focus:ring-neon/40 dark:border-redup dark:bg-gelap dark:text-teks-gelap">
                <option value="">Semua status</option>
                <option value="aktif" @selected(($filter['status'] ?? '') === 'aktif')>Aktif</option>
                <option value="nonaktif" @selected(($filter['status'] ?? '') === 'nonaktif')>Nonaktif</option>
            </select>
        </div>

        <div class="flex items-end gap-2 sm:col-span-2">
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
                    <th class="px-5 py-3 font-semibold">Siswa</th>
                    <th class="px-5 py-3 font-semibold">Nomor VA</th>
                    <th class="px-5 py-3 font-semibold">Bank</th>
                    <th class="px-5 py-3 font-semibold">Status</th>
                    <th class="px-5 py-3 font-semibold">Kedaluwarsa</th>
                    <th class="px-5 py-3 text-right font-semibold">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-garis dark:divide-garis-gelap">
                @forelse ($daftar_va as $va)
                    <tr class="hover:bg-latar-2 dark:hover:bg-gelap">
                        <td class="px-5 py-3">
                            <span class="font-medium text-tinta dark:text-teks-gelap">{{ $va->siswa?->nama ?? '-' }}</span>
                            <span class="block text-xs text-redup dark:text-redup-gelap">{{ $va->siswa?->nis }}</span>
                        </td>
                        <td class="px-5 py-3">
                            @if ($va->account_number)
                                <span class="font-mono font-medium text-tinta dark:text-teks-gelap">{{ $va->account_number }}</span>
                                <span class="block text-xs text-redup dark:text-redup-gelap">{{ $va->nomor_va }}</span>
                            @else
                                <span class="font-mono font-medium text-tinta dark:text-teks-gelap">{{ $va->nomor_va }}</span>
                            @endif
                        </td>
                        <td class="px-5 py-3 text-redup dark:text-redup-gelap">{{ $va->bank_code ?: '-' }}</td>
                        <td class="px-5 py-3">
                            @php
                                $petaStatus = [
                                    'aktif' => ['label' => 'Aktif', 'kelas' => 'bg-emerald-100 text-emerald-800'],
                                    'nonaktif' => ['label' => 'Nonaktif', 'kelas' => 'bg-latar-2 dark:bg-gelap text-tinta dark:text-teks-gelap'],
                                ];
                                $dataStatus = $petaStatus[$va->status] ?? ['label' => $va->status, 'kelas' => 'bg-latar-2 dark:bg-gelap text-tinta dark:text-teks-gelap'];
                            @endphp
                            <span class="inline-flex whitespace-nowrap rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $dataStatus['kelas'] }}">
                                {{ $dataStatus['label'] }}
                            </span>
                        </td>
                        <td class="px-5 py-3 text-redup dark:text-redup-gelap">
                            {{ $va->expired_at?->format('d/m/Y') ?? '-' }}
                        </td>
                        <td class="px-5 py-3">
                            <div class="flex flex-wrap items-center justify-end gap-2">
                                <a href="{{ route('teller.virtual-accounts.show', $va) }}"
                                   class="rounded-md border border-garis dark:border-garis-gelap px-2.5 py-1 text-xs font-medium text-tinta dark:text-teks-gelap hover:bg-latar-2 dark:hover:bg-gelap">
                                    Lihat
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-5 py-8 text-center text-redup dark:text-redup-gelap">
                            Tidak ada Virtual Account yang cocok dengan filter ini.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
