{{-- Tabel daftar jurusan. --}}
<div class="overflow-hidden rounded-xl border border-garis bg-white shadow-sm dark:border-garis-gelap dark:bg-gelap-2">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-garis dark:divide-garis-gelap text-sm">
            <thead class="bg-latar-2 text-left text-xs uppercase tracking-wide text-redup dark:bg-gelap dark:text-redup-gelap">
                <tr>
                    <th class="px-5 py-3 font-semibold">Kode</th>
                    <th class="px-5 py-3 font-semibold">Nama jurusan</th>
                    <th class="px-5 py-3 font-semibold">Keterangan</th>
                    <th class="px-5 py-3 font-semibold">Jumlah kelas</th>
                    <th class="px-5 py-3 text-right font-semibold">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-garis dark:divide-garis-gelap">
                @forelse ($daftar_jurusan as $jurusan)
                    <tr class="hover:bg-latar-2 dark:hover:bg-gelap">
                        <td class="px-5 py-3 font-medium text-tinta dark:text-teks-gelap">{{ $jurusan->kode_jurusan }}</td>
                        <td class="px-5 py-3 text-tinta dark:text-teks-gelap">{{ $jurusan->nama_jurusan }}</td>
                        <td class="px-5 py-3 text-redup dark:text-redup-gelap">{{ $jurusan->keterangan ?: '-' }}</td>
                        <td class="px-5 py-3 text-redup dark:text-redup-gelap">{{ $jurusan->kelas_count }}</td>
                        <td class="px-5 py-3">
                            <div class="flex flex-wrap items-center justify-end gap-2">
                                <a href="{{ route('jurusan.show', $jurusan) }}"
                                   class="rounded-md border border-garis px-2.5 py-1 text-xs font-medium text-tinta hover:bg-latar-2 dark:border-garis-gelap dark:text-teks-gelap dark:hover:bg-gelap">
                                    Detail
                                </a>

                                @if (auth()->user()->role === 'admin')
                                    <button type="button" data-modal-buka="ubah" data-modal-target="modal-jurusan"
                                            data-id="{{ $jurusan->id_jurusan }}"
                                            data-kode_jurusan="{{ $jurusan->kode_jurusan }}"
                                            data-nama_jurusan="{{ $jurusan->nama_jurusan }}"
                                            data-keterangan="{{ $jurusan->keterangan }}"
                                            class="rounded-md border border-garis px-2.5 py-1 text-xs font-medium text-tinta hover:bg-latar-2 dark:border-garis-gelap dark:text-teks-gelap dark:hover:bg-gelap">
                                        Ubah
                                    </button>

                                    <form method="POST" action="{{ route('jurusan.destroy', $jurusan) }}"
                                          onsubmit="return confirm('Hapus data jurusan ini? Tindakan ini tidak bisa dibatalkan.')">
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
                        <td colspan="5" class="px-5 py-8 text-center text-redup dark:text-redup-gelap">
                            Belum ada data jurusan.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
