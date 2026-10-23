{{-- Tabel daftar tahun ajaran. --}}
<div class="overflow-hidden rounded-xl border border-garis bg-white shadow-sm dark:border-garis-gelap dark:bg-gelap-2">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-garis dark:divide-garis-gelap text-sm">
            <thead class="bg-latar-2 text-left text-xs uppercase tracking-wide text-redup dark:bg-gelap dark:text-redup-gelap">
                <tr>
                    <th class="px-5 py-3 font-semibold">Tahun ajaran</th>
                    <th class="px-5 py-3 font-semibold">Mulai</th>
                    <th class="px-5 py-3 font-semibold">Selesai</th>
                    <th class="px-5 py-3 font-semibold">Status</th>
                    <th class="px-5 py-3 font-semibold">Kelas</th>
                    <th class="px-5 py-3 font-semibold">Tarif</th>
                    <th class="px-5 py-3 text-right font-semibold">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-garis dark:divide-garis-gelap">
                @forelse ($daftar_tahun_ajaran as $tahun)
                    <tr class="hover:bg-latar-2 dark:hover:bg-gelap">
                        <td class="px-5 py-3 font-medium text-tinta dark:text-teks-gelap">{{ $tahun->tahun_ajaran }}</td>
                        <td class="px-5 py-3 text-redup dark:text-redup-gelap">{{ $tahun->tanggal_mulai?->format('d/m/Y') ?? '-' }}</td>
                        <td class="px-5 py-3 text-redup dark:text-redup-gelap">{{ $tahun->tanggal_selesai?->format('d/m/Y') ?? '-' }}</td>
                        <td class="px-5 py-3">
                            @if ($tahun->status === 'aktif')
                                <span class="inline-flex rounded-full bg-neon px-2.5 py-0.5 text-xs font-semibold text-tinta">Aktif</span>
                            @else
                                <span class="inline-flex rounded-full bg-latar-2 px-2.5 py-0.5 text-xs font-semibold text-redup dark:bg-gelap dark:text-redup-gelap">Nonaktif</span>
                            @endif
                        </td>
                        <td class="px-5 py-3 text-redup dark:text-redup-gelap">{{ $tahun->kelas_count }}</td>
                        <td class="px-5 py-3 text-redup dark:text-redup-gelap">{{ $tahun->tarif_spp_count }}</td>
                        <td class="px-5 py-3">
                            <div class="flex flex-wrap items-center justify-end gap-2">
                                <a href="{{ route('tahun-ajaran.show', $tahun) }}"
                                   class="rounded-md border border-garis px-2.5 py-1 text-xs font-medium text-tinta hover:bg-latar-2 dark:border-garis-gelap dark:text-teks-gelap dark:hover:bg-gelap">
                                    Detail
                                </a>

                                @if (auth()->user()->role === 'admin')
                                    <button type="button" data-modal-buka="ubah" data-modal-target="modal-tahun-ajaran"
                                            data-id="{{ $tahun->id_tahun_ajaran }}"
                                            data-tahun_ajaran="{{ $tahun->tahun_ajaran }}"
                                            data-tanggal_mulai="{{ $tahun->tanggal_mulai?->format('Y-m-d') }}"
                                            data-tanggal_selesai="{{ $tahun->tanggal_selesai?->format('Y-m-d') }}"
                                            data-status="{{ $tahun->status }}"
                                            class="rounded-md border border-garis px-2.5 py-1 text-xs font-medium text-tinta hover:bg-latar-2 dark:border-garis-gelap dark:text-teks-gelap dark:hover:bg-gelap">
                                        Ubah
                                    </button>

                                    <form method="POST" action="{{ route('tahun-ajaran.destroy', $tahun) }}"
                                          onsubmit="return confirm('Hapus data tahun ajaran ini? Tindakan ini tidak bisa dibatalkan.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="rounded-md border border-red-300 px-2.5 py-1 text-xs font-medium text-red-700 hover:bg-red-50 dark:border-red-900 dark:text-red-400 dark:hover:bg-red-950/40">
                                            Hapus
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-5 py-8 text-center text-redup dark:text-redup-gelap">
                            Belum ada data tahun ajaran.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
