{{-- Form filter dan tabel daftar pembayaran. --}}

{{--
    Form filter.
    Halaman ini dipakai admin dan teller lewat route berbeda, jadi aksi form
    memakai URL yang sedang dibuka supaya filter tetap bekerja untuk keduanya.
--}}
<form method="GET" action="{{ url()->current() }}"
      class="mb-4 rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-5">

        <div>
            <label for="filter_dari" class="mb-1 block text-xs font-medium text-slate-600">Dari tanggal</label>
            <input type="date" name="dari" id="filter_dari" value="{{ $filter['dari'] ?? '' }}"
                   class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-teal-600 focus:outline-none focus:ring-2 focus:ring-teal-100">
        </div>

        <div>
            <label for="filter_sampai" class="mb-1 block text-xs font-medium text-slate-600">Sampai tanggal</label>
            <input type="date" name="sampai" id="filter_sampai" value="{{ $filter['sampai'] ?? '' }}"
                   class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-teal-600 focus:outline-none focus:ring-2 focus:ring-teal-100">
        </div>

        <div>
            <label for="filter_metode_pembayaran" class="mb-1 block text-xs font-medium text-slate-600">Metode</label>
            <select name="metode_pembayaran" id="filter_metode_pembayaran"
                    class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-teal-600 focus:outline-none focus:ring-2 focus:ring-teal-100">
                <option value="">Semua metode</option>
                <option value="tunai" @selected(($filter['metode_pembayaran'] ?? '') === 'tunai')>Tunai</option>
                <option value="qris" @selected(($filter['metode_pembayaran'] ?? '') === 'qris')>QRIS</option>
                <option value="virtual_account" @selected(($filter['metode_pembayaran'] ?? '') === 'virtual_account')>Virtual Account</option>
            </select>
        </div>

        <div>
            <label for="filter_siswa_id" class="mb-1 block text-xs font-medium text-slate-600">Siswa</label>
            <select name="siswa_id" id="filter_siswa_id"
                    class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-teal-600 focus:outline-none focus:ring-2 focus:ring-teal-100">
                <option value="">Semua siswa</option>
                @foreach ($daftar_siswa as $siswa)
                    <option value="{{ $siswa->id_siswa }}" @selected(($filter['siswa_id'] ?? '') == $siswa->id_siswa)>
                        {{ $siswa->nis }} - {{ $siswa->nama }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="filter_status" class="mb-1 block text-xs font-medium text-slate-600">Status</label>
            <select name="status" id="filter_status"
                    class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-teal-600 focus:outline-none focus:ring-2 focus:ring-teal-100">
                <option value="">Semua status</option>
                <option value="berhasil" @selected(($filter['status'] ?? '') === 'berhasil')>Berhasil</option>
                <option value="gagal" @selected(($filter['status'] ?? '') === 'gagal')>Gagal</option>
                <option value="dibatalkan" @selected(($filter['status'] ?? '') === 'dibatalkan')>Dibatalkan</option>
            </select>
        </div>

        <div class="flex items-end gap-2">
            <button type="submit"
                    class="rounded-lg bg-teal-700 px-4 py-2 text-sm font-semibold text-white hover:bg-teal-800">
                Terapkan
            </button>
            <a href="{{ url()->current() }}"
               class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                Reset
            </a>
        </div>
    </div>
</form>

{{-- Tabel pembayaran. --}}
<div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                <tr>
                    <th class="px-5 py-3 font-semibold">Tanggal</th>
                    <th class="px-5 py-3 font-semibold">Siswa</th>
                    <th class="px-5 py-3 font-semibold">Nominal</th>
                    <th class="px-5 py-3 font-semibold">Metode</th>
                    <th class="px-5 py-3 font-semibold">Status</th>
                    <th class="px-5 py-3 text-right font-semibold">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($daftar_pembayaran as $pembayaran)
                    <tr class="hover:bg-slate-50">
                        <td class="px-5 py-3 text-slate-600">
                            {{ $pembayaran->created_at?->format('d/m/Y H:i') ?? '-' }}
                        </td>
                        <td class="px-5 py-3">
                            <span class="font-medium text-slate-800">{{ $pembayaran->siswa?->nama ?? '-' }}</span>
                            <span class="block text-xs text-slate-500">{{ $pembayaran->siswa?->nis }}</span>
                        </td>
                        <td class="px-5 py-3 text-slate-800">
                            Rp{{ number_format((float) $pembayaran->nominal_pembayaran, 0, ',', '.') }}
                        </td>
                        <td class="px-5 py-3 text-slate-600">
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
                                    'berhasil' => ['label' => 'Berhasil', 'kelas' => 'bg-emerald-100 text-emerald-800'],
                                    'gagal' => ['label' => 'Gagal', 'kelas' => 'bg-red-100 text-red-800'],
                                    'dibatalkan' => ['label' => 'Dibatalkan', 'kelas' => 'bg-slate-100 text-slate-700'],
                                ];
                                $dataStatus = $petaStatus[$pembayaran->status] ?? ['label' => $pembayaran->status, 'kelas' => 'bg-slate-100 text-slate-700'];
                            @endphp
                            <span class="inline-flex whitespace-nowrap rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $dataStatus['kelas'] }}">
                                {{ $dataStatus['label'] }}
                            </span>
                        </td>
                        <td class="px-5 py-3">
                            <div class="flex flex-wrap items-center justify-end gap-2">
                                <a href="{{ route('teller.payments.show', $pembayaran) }}"
                                   class="rounded-md border border-slate-300 px-2.5 py-1 text-xs font-medium text-slate-700 hover:bg-slate-100">
                                    Lihat
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-5 py-8 text-center text-slate-500">
                            Tidak ada pembayaran yang cocok dengan filter ini.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
