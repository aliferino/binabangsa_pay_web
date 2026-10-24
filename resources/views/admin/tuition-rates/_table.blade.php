{{-- Tabel daftar tarif SPP. --}}
<div class="overflow-hidden rounded-xl border border-garis bg-white shadow-sm dark:border-garis-gelap dark:bg-gelap-2">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-garis text-sm dark:divide-garis-gelap">
            <thead class="bg-latar-2 text-left text-xs uppercase tracking-wide text-redup dark:bg-gelap dark:text-redup-gelap">
                <tr>
                    <th class="px-5 py-3 font-semibold">Tahun ajaran</th>
                    <th class="px-5 py-3 font-semibold">Nominal</th>
                    <th class="px-5 py-3 font-semibold">Keterangan</th>
                    <th class="px-5 py-3 font-semibold">Status</th>
                    <th class="px-5 py-3 text-right font-semibold">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-garis dark:divide-garis-gelap">
                @forelse ($daftar_tarif as $tarif)
                    <tr class="hover:bg-latar-2 dark:hover:bg-gelap">
                        <td class="px-5 py-3 font-medium text-tinta dark:text-teks-gelap">
                            {{ $tarif->tahunAjaran?->tahun_ajaran ?? '-' }}
                        </td>
                        <td class="px-5 py-3 text-tinta dark:text-teks-gelap">
                            Rp{{ number_format((float) $tarif->nominal, 0, ',', '.') }}
                        </td>
                        <td class="px-5 py-3 text-redup dark:text-redup-gelap">{{ $tarif->keterangan ?: '-' }}</td>
                        <td class="px-5 py-3">
                            @if ($tarif->status === 'aktif')
                                <span class="inline-flex rounded-full bg-neon px-2.5 py-0.5 text-xs font-semibold text-tinta">Aktif</span>
                            @else
                                <span class="inline-flex rounded-full bg-latar-2 px-2.5 py-0.5 text-xs font-semibold text-redup dark:bg-gelap dark:text-redup-gelap">Nonaktif</span>
                            @endif
                        </td>
                        <td class="px-5 py-3">
                            <div class="flex flex-wrap items-center justify-end gap-2">
<a href="{{ route('tarif-spp.show', $tarif) }}" class="inline-flex h-8 w-8 items-center justify-center rounded-md border border-garis text-redup transition-colors hover:border-neon-teks hover:bg-latar-2 hover:text-tinta dark:border-garis-gelap dark:text-redup-gelap dark:hover:bg-gelap dark:hover:text-teks-gelap" title="Lihat detail" aria-label="Lihat detail"><svg class="h-[18px] w-[18px]" aria-hidden="true"><use href="#i-lihat"/></svg>
                                </a>

                                @if (auth()->user()->role === 'admin')
<button type="button" data-modal-buka="ubah" data-modal-target="modal-tarif-spp" data-id="{{ $tarif->id_tarif }}" data-id_tahun_ajaran="{{ $tarif->id_tahun_ajaran }}" data-nominal="{{ (int) $tarif->nominal }}" data-keterangan="{{ $tarif->keterangan }}" data-status="{{ $tarif->status }}" class="inline-flex h-8 w-8 items-center justify-center rounded-md border border-garis text-redup transition-colors hover:border-neon-teks hover:bg-latar-2 hover:text-tinta dark:border-garis-gelap dark:text-redup-gelap dark:hover:bg-gelap dark:hover:text-teks-gelap" title="Ubah" aria-label="Ubah"><svg class="h-[18px] w-[18px]" aria-hidden="true"><use href="#i-ubah"/></svg>
                                    </button>

                                    <form method="POST" action="{{ route('tarif-spp.destroy', $tarif) }}"
                                          data-konfirmasi="Hapus data tarif ini? Tindakan ini tidak bisa dibatalkan.">
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
                        <td colspan="5" class="px-5 py-8 text-center text-redup dark:text-redup-gelap">
                            Belum ada data tarif SPP.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
