{{--
    Filter form and transaction table for the payment gateway listing.
    Identifiers are English, visible text stays Indonesian.
--}}

<form method="GET" action="{{ url()->current() }}"
      class="mb-4 rounded-xl border border-garis bg-white p-5 shadow-sm dark:border-garis-gelap dark:bg-gelap-2">
    <div class="grid grid-cols-1 gap-x-4 gap-y-5 sm:grid-cols-2 lg:grid-cols-[repeat(4,minmax(0,1fr))_auto]">

        <div>
            <label for="filter_from" class="mb-1.5 block text-xs font-medium text-redup dark:text-redup-gelap">Dari tanggal</label>
            <input type="date" name="dari" id="filter_from" value="{{ $filter['dari'] ?? '' }}"
                   class="w-full rounded-lg border border-garis px-3 py-2 text-sm text-tinta shadow-sm focus:border-neon-teks focus:outline-none focus:ring-2 focus:ring-neon/40 dark:border-redup dark:bg-gelap dark:text-teks-gelap">
        </div>

        <div>
            <label for="filter_to" class="mb-1.5 block text-xs font-medium text-redup dark:text-redup-gelap">Sampai tanggal</label>
            <input type="date" name="sampai" id="filter_to" value="{{ $filter['sampai'] ?? '' }}"
                   class="w-full rounded-lg border border-garis px-3 py-2 text-sm text-tinta shadow-sm focus:border-neon-teks focus:outline-none focus:ring-2 focus:ring-neon/40 dark:border-redup dark:bg-gelap dark:text-teks-gelap">
        </div>

        <div>
            <label for="filter_status" class="mb-1.5 block text-xs font-medium text-redup dark:text-redup-gelap">Status</label>
            <select name="status_transaksi" id="filter_status"
                    class="w-full rounded-lg border border-garis bg-white pl-3 pr-9 py-2 text-sm text-tinta shadow-sm focus:border-neon-teks focus:outline-none focus:ring-2 focus:ring-neon/40 dark:border-redup dark:bg-gelap dark:text-teks-gelap">
                <option value="">Semua status</option>
                <option value="pending" @selected(($filter['status_transaksi'] ?? '') === 'pending')>Pending</option>
                <option value="berhasil" @selected(($filter['status_transaksi'] ?? '') === 'berhasil')>Berhasil</option>
                <option value="gagal" @selected(($filter['status_transaksi'] ?? '') === 'gagal')>Gagal</option>
                <option value="kedaluwarsa" @selected(($filter['status_transaksi'] ?? '') === 'kedaluwarsa')>Kedaluwarsa</option>
            </select>
        </div>

        <div>
            <label for="filter_student" class="mb-1.5 block text-xs font-medium text-redup dark:text-redup-gelap">Siswa</label>
            <select name="siswa_id" id="filter_student"
                    class="w-full rounded-lg border border-garis bg-white pl-3 pr-9 py-2 text-sm text-tinta shadow-sm focus:border-neon-teks focus:outline-none focus:ring-2 focus:ring-neon/40 dark:border-redup dark:bg-gelap dark:text-teks-gelap">
                <option value="">Semua siswa</option>
                @foreach ($students as $student)
                    <option value="{{ $student->id_siswa }}" @selected(($filter['siswa_id'] ?? '') == $student->id_siswa)>
                        {{ $student->nis }} - {{ $student->nama }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="flex items-end gap-3 lg:pl-1">
            <button type="submit"
                    class="border border-transparent tombol-tekan rounded-lg bg-neon px-4 py-2 text-sm font-semibold text-tinta hover:bg-neon-tekan">
                Terapkan
            </button>
            <a href="{{ url()->current() }}"
               class="rounded-lg border border-garis bg-white px-4 py-2 text-sm font-medium text-tinta hover:bg-latar-2 dark:border-garis-gelap dark:bg-gelap-2 dark:text-teks-gelap dark:hover:bg-gelap">
                Reset
            </a>
        </div>
    </div>
</form>

<div class="overflow-hidden rounded-xl border border-garis bg-white shadow-sm dark:border-garis-gelap dark:bg-gelap-2">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-garis text-sm dark:divide-garis-gelap">
            <thead class="bg-latar-2 text-left text-xs uppercase tracking-wide text-redup dark:bg-gelap dark:text-redup-gelap">
                <tr>
                    <th class="px-5 py-3 font-semibold">Waktu</th>
                    <th class="px-5 py-3 font-semibold">ID transaksi</th>
                    <th class="px-5 py-3 font-semibold">Siswa</th>
                    <th class="px-5 py-3 font-semibold">Virtual account</th>
                    <th class="px-5 py-3 text-right font-semibold">Nominal</th>
                    <th class="px-5 py-3 font-semibold">Status</th>
                    <th class="px-5 py-3 text-right font-semibold">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-garis dark:divide-garis-gelap">
                @forelse ($transactions as $transaction)
                    <tr class="hover:bg-latar-2 dark:hover:bg-gelap">
                        <td class="whitespace-nowrap px-5 py-3 text-redup dark:text-redup-gelap">
                            {{ $transaction->created_at?->format('d/m/Y H:i') ?? '-' }}
                        </td>
                        <td class="px-5 py-3">
                            <span class="font-mono text-xs font-medium text-tinta dark:text-teks-gelap">{{ $transaction->transaction_id }}</span>
                        </td>
                        <td class="px-5 py-3">
                            <span class="font-medium text-tinta dark:text-teks-gelap">{{ $transaction->siswa?->nama ?? '-' }}</span>
                            <span class="mt-0.5 block text-xs text-redup dark:text-redup-gelap">{{ $transaction->siswa?->nis }}</span>
                        </td>
                        <td class="whitespace-nowrap px-5 py-3 font-mono text-xs text-redup dark:text-redup-gelap">
                            @include('layouts.partials._nomor-va', ['nomor' => $transaction->virtualAccount?->account_number ?: ($transaction->virtualAccount?->nomor_va ?? '-')])
                        </td>
                        <td class="whitespace-nowrap px-5 py-3 text-right text-tinta dark:text-teks-gelap">
                            Rp{{ number_format((float) $transaction->nominal, 0, ',', '.') }}
                        </td>
                        <td class="px-5 py-3">
                            @include('admin.gateway-transactions._status', ['status' => $transaction->status_transaksi])
                        </td>
                        <td class="px-5 py-3">
                            <div class="flex flex-wrap items-center justify-end gap-2">
                                <a href="{{ route('gateway-transactions.show', $transaction) }}"
                                   class="inline-flex h-7 w-7 items-center justify-center rounded-md border border-garis text-redup transition-colors hover:border-neon-teks hover:bg-latar-2 hover:text-tinta dark:border-garis-gelap dark:text-redup-gelap dark:hover:bg-gelap dark:hover:text-teks-gelap"
                                   title="Lihat" aria-label="Lihat">
                                    <svg class="h-[18px] w-[18px]" aria-hidden="true"><use href="#i-lihat"/></svg>
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-5 py-8 text-center text-redup dark:text-redup-gelap">
                            Belum ada transaksi payment gateway yang cocok dengan filter ini.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
