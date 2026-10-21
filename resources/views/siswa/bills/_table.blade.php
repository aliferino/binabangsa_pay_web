{{-- Tabel tagihan milik siswa yang login. Tanpa tombol aksi. --}}

<div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
    <div class="border-b border-slate-200 px-5 py-4">
        <h2 class="text-sm font-semibold text-slate-700">Tagihan SPP</h2>
        <p class="mt-1 text-xs text-slate-500">
            Total {{ $daftar_tagihan->count() }} tagihan.
        </p>
    </div>

    @if ($daftar_tagihan->isEmpty())
        <p class="px-5 py-8 text-center text-sm text-slate-500">
            Belum ada tagihan untuk akun Anda.
        </p>
    @else
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-5 py-3 font-semibold">Bulan</th>
                        <th class="px-5 py-3 font-semibold">Tahun</th>
                        <th class="px-5 py-3 font-semibold">Nominal</th>
                        <th class="px-5 py-3 font-semibold">Jatuh tempo</th>
                        <th class="px-5 py-3 font-semibold">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($daftar_tagihan->sortBy([['tahun', 'desc'], ['bulan', 'desc']]) as $tagihan)
                        <tr class="hover:bg-slate-50">
                            <td class="px-5 py-3 font-medium text-slate-800">{{ $tagihan->namaBulan() }}</td>
                            <td class="px-5 py-3 text-slate-600">{{ $tagihan->tahun }}</td>
                            <td class="px-5 py-3 text-slate-800">
                                Rp{{ number_format((float) $tagihan->nominal, 0, ',', '.') }}
                            </td>
                            <td class="px-5 py-3 text-slate-600">
                                {{ $tagihan->tanggal_jatuh_tempo?->format('d/m/Y') ?? '-' }}
                            </td>
                            <td class="px-5 py-3">
                                @include('partials._status-badge', ['status' => $tagihan->status])
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
