{{-- Form filter dan tabel daftar Virtual Account. --}}

{{--
    Form filter.
    Halaman ini dipakai admin dan teller lewat route berbeda, jadi aksi form
    memakai URL yang sedang dibuka supaya filter tetap bekerja untuk keduanya.
--}}
<form method="GET" action="{{ url()->current() }}"
      class="mb-4 rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">

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
                <option value="aktif" @selected(($filter['status'] ?? '') === 'aktif')>Aktif</option>
                <option value="nonaktif" @selected(($filter['status'] ?? '') === 'nonaktif')>Nonaktif</option>
            </select>
        </div>

        <div class="flex items-end gap-2 sm:col-span-2">
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

{{-- Tabel Virtual Account. --}}
<div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                <tr>
                    <th class="px-5 py-3 font-semibold">Siswa</th>
                    <th class="px-5 py-3 font-semibold">Nomor VA</th>
                    <th class="px-5 py-3 font-semibold">Bank</th>
                    <th class="px-5 py-3 font-semibold">Status</th>
                    <th class="px-5 py-3 font-semibold">Kedaluwarsa</th>
                    <th class="px-5 py-3 text-right font-semibold">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($daftar_va as $va)
                    <tr class="hover:bg-slate-50">
                        <td class="px-5 py-3">
                            <span class="font-medium text-slate-800">{{ $va->siswa?->nama ?? '-' }}</span>
                            <span class="block text-xs text-slate-500">{{ $va->siswa?->nis }}</span>
                        </td>
                        <td class="px-5 py-3">
                            @if ($va->account_number)
                                <span class="font-mono font-medium text-slate-900">{{ $va->account_number }}</span>
                                <span class="block text-xs text-slate-500">{{ $va->nomor_va }}</span>
                            @else
                                <span class="font-mono font-medium text-slate-900">{{ $va->nomor_va }}</span>
                            @endif
                        </td>
                        <td class="px-5 py-3 text-slate-600">{{ $va->bank_code ?: '-' }}</td>
                        <td class="px-5 py-3">
                            @php
                                $petaStatus = [
                                    'aktif' => ['label' => 'Aktif', 'kelas' => 'bg-emerald-100 text-emerald-800'],
                                    'nonaktif' => ['label' => 'Nonaktif', 'kelas' => 'bg-slate-100 text-slate-700'],
                                ];
                                $dataStatus = $petaStatus[$va->status] ?? ['label' => $va->status, 'kelas' => 'bg-slate-100 text-slate-700'];
                            @endphp
                            <span class="inline-flex whitespace-nowrap rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $dataStatus['kelas'] }}">
                                {{ $dataStatus['label'] }}
                            </span>
                        </td>
                        <td class="px-5 py-3 text-slate-600">
                            {{ $va->expired_at?->format('d/m/Y') ?? '-' }}
                        </td>
                        <td class="px-5 py-3">
                            <div class="flex flex-wrap items-center justify-end gap-2">
                                <a href="{{ route('teller.virtual-accounts.show', $va) }}"
                                   class="rounded-md border border-slate-300 px-2.5 py-1 text-xs font-medium text-slate-700 hover:bg-slate-100">
                                    Lihat
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-5 py-8 text-center text-slate-500">
                            Tidak ada Virtual Account yang cocok dengan filter ini.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
