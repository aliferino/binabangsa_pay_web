{{-- Tabel daftar kelas. --}}
<div class="overflow-hidden rounded-xl border border-garis bg-white shadow-sm dark:border-garis-gelap dark:bg-gelap-2">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-garis dark:divide-garis-gelap text-sm">
            <thead class="bg-latar-2 text-left text-xs uppercase tracking-wide text-redup dark:bg-gelap dark:text-redup-gelap">
                <tr>
                    <th class="px-5 py-3 font-semibold">Nama kelas</th>
                    <th class="px-5 py-3 font-semibold">Tingkat</th>
                    <th class="px-5 py-3 font-semibold">Jurusan</th>
                    <th class="px-5 py-3 font-semibold">Tahun ajaran</th>
                    <th class="px-5 py-3 font-semibold">Jumlah siswa</th>
                    <th class="px-5 py-3 text-right font-semibold">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-garis dark:divide-garis-gelap">
                @forelse ($daftar_kelas as $kelas)
                    <tr class="hover:bg-latar-2 dark:hover:bg-gelap">
                        <td class="px-5 py-3 font-medium text-tinta dark:text-teks-gelap">{{ $kelas->nama_kelas }}</td>
                        <td class="px-5 py-3 text-redup dark:text-redup-gelap">{{ $kelas->tingkat }}</td>
                        <td class="px-5 py-3 text-redup dark:text-redup-gelap">{{ $kelas->jurusan?->nama_jurusan ?? '-' }}</td>
                        <td class="px-5 py-3 text-redup dark:text-redup-gelap">{{ $kelas->tahunAjaran?->tahun_ajaran ?? '-' }}</td>
                        <td class="px-5 py-3 text-redup dark:text-redup-gelap">{{ $kelas->siswa_count }}</td>
                        <td class="px-5 py-3">
                            <div class="flex flex-wrap items-center justify-end gap-2">
<a href="{{ route('kelas.show', $kelas) }}" class="inline-flex h-8 w-8 items-center justify-center rounded-md border border-garis text-redup transition-colors hover:border-neon-teks hover:bg-latar-2 hover:text-tinta dark:border-garis-gelap dark:text-redup-gelap dark:hover:bg-gelap dark:hover:text-teks-gelap" title="Lihat detail" aria-label="Lihat detail"><svg class="h-[18px] w-[18px]" aria-hidden="true"><use href="#i-lihat"/></svg>
                                </a>

                                @if (auth()->user()->role === 'admin')
<button type="button" data-modal-buka="ubah" data-modal-target="modal-kelas" data-id="{{ $kelas->id_kelas }}" data-nama_kelas="{{ $kelas->nama_kelas }}" data-tingkat="{{ $kelas->tingkat }}" data-id_jurusan="{{ $kelas->id_jurusan }}" data-id_tahun_ajaran="{{ $kelas->id_tahun_ajaran }}" class="inline-flex h-8 w-8 items-center justify-center rounded-md border border-garis text-redup transition-colors hover:border-neon-teks hover:bg-latar-2 hover:text-tinta dark:border-garis-gelap dark:text-redup-gelap dark:hover:bg-gelap dark:hover:text-teks-gelap" title="Ubah" aria-label="Ubah"><svg class="h-[18px] w-[18px]" aria-hidden="true"><use href="#i-ubah"/></svg>
                                    </button>

                                    <form method="POST" action="{{ route('kelas.destroy', $kelas) }}"
                                          data-konfirmasi="Hapus data kelas ini? Tindakan ini tidak bisa dibatalkan.">
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
                        <td colspan="6" class="px-5 py-8 text-center text-redup dark:text-redup-gelap">
                            Belum ada data kelas.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
