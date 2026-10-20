{{-- Tabel daftar tarif SPP. --}}
<div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                <tr>
                    <th class="px-5 py-3 font-semibold">Tahun ajaran</th>
                    <th class="px-5 py-3 font-semibold">Nominal</th>
                    <th class="px-5 py-3 font-semibold">Keterangan</th>
                    <th class="px-5 py-3 font-semibold">Status</th>
                    <th class="px-5 py-3 text-right font-semibold">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($daftar_tarif as $tarif)
                    <tr class="hover:bg-slate-50">
                        <td class="px-5 py-3 font-medium text-slate-800">
                            {{ $tarif->tahunAjaran?->tahun_ajaran ?? '-' }}
                        </td>
                        <td class="px-5 py-3 text-slate-800">
                            Rp{{ number_format((float) $tarif->nominal, 0, ',', '.') }}
                        </td>
                        <td class="px-5 py-3 text-slate-600">{{ $tarif->keterangan ?: '-' }}</td>
                        <td class="px-5 py-3">
                            @if ($tarif->status === 'aktif')
                                <span class="inline-flex rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-semibold text-emerald-800">Aktif</span>
                            @else
                                <span class="inline-flex rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-semibold text-slate-700">Nonaktif</span>
                            @endif
                        </td>
                        <td class="px-5 py-3">
                            <div class="flex flex-wrap items-center justify-end gap-2">
                                <a href="{{ route('tarif-spp.show', $tarif) }}"
                                   class="rounded-md border border-slate-300 px-2.5 py-1 text-xs font-medium text-slate-700 hover:bg-slate-100">
                                    Detail
                                </a>

                                @if (auth()->user()->role === 'admin')
                                    <button type="button" data-modal-buka="ubah" data-modal-target="modal-tarif-spp"
                                            data-id="{{ $tarif->id_tarif }}"
                                            data-id_tahun_ajaran="{{ $tarif->id_tahun_ajaran }}"
                                            data-nominal="{{ (int) $tarif->nominal }}"
                                            data-keterangan="{{ $tarif->keterangan }}"
                                            data-status="{{ $tarif->status }}"
                                            class="rounded-md border border-slate-300 px-2.5 py-1 text-xs font-medium text-slate-700 hover:bg-slate-100">
                                        Ubah
                                    </button>

                                    <form method="POST" action="{{ route('tarif-spp.destroy', $tarif) }}"
                                          onsubmit="return confirm('Hapus data tarif ini? Tindakan ini tidak bisa dibatalkan.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="rounded-md border border-red-300 px-2.5 py-1 text-xs font-medium text-red-700 hover:bg-red-50">
                                            Hapus
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-5 py-8 text-center text-slate-500">
                            Belum ada data tarif SPP.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
