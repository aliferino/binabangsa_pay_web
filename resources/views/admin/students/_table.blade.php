{{-- Form filter dan tabel daftar siswa. --}}

{{-- Form filter. Isian dipertahankan supaya filter yang aktif tetap terlihat. --}}
<form method="GET" action="{{ route('siswa.index') }}"
      class="mb-4 rounded-xl border border-garis dark:border-garis-gelap bg-white p-4 shadow-sm">
    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-5">

        <div>
            <label for="cari" class="mb-1 block text-xs font-medium text-redup dark:text-redup-gelap">Cari</label>
            <input type="text" name="cari" id="cari" value="{{ $filter['cari'] ?? '' }}"
                   placeholder="NIS, NISN, atau nama"
                   class="w-full rounded-lg border border-garis px-3 py-2 text-sm text-tinta shadow-sm focus:border-neon-teks focus:outline-none focus:ring-2 focus:ring-neon/40 dark:border-redup dark:bg-gelap dark:text-teks-gelap dark:placeholder:text-redup-gelap">
        </div>

        <div>
            <label for="filter_id_kelas" class="mb-1 block text-xs font-medium text-redup dark:text-redup-gelap">Kelas</label>
            <select name="id_kelas" id="filter_id_kelas"
                    class="w-full rounded-lg border border-garis bg-white px-3 py-2 text-sm text-tinta shadow-sm focus:border-neon-teks focus:outline-none focus:ring-2 focus:ring-neon/40 dark:border-redup dark:bg-gelap dark:text-teks-gelap">
                <option value="">Semua kelas</option>
                @foreach ($daftar_kelas as $kelas)
                    <option value="{{ $kelas->id_kelas }}" @selected(($filter['id_kelas'] ?? '') == $kelas->id_kelas)>
                        {{ $kelas->nama_kelas }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="filter_id_tahun_ajaran" class="mb-1 block text-xs font-medium text-redup dark:text-redup-gelap">Tahun ajaran</label>
            <select name="id_tahun_ajaran" id="filter_id_tahun_ajaran"
                    class="w-full rounded-lg border border-garis bg-white px-3 py-2 text-sm text-tinta shadow-sm focus:border-neon-teks focus:outline-none focus:ring-2 focus:ring-neon/40 dark:border-redup dark:bg-gelap dark:text-teks-gelap">
                <option value="">Semua tahun</option>
                @foreach ($daftar_tahun_ajaran as $tahun)
                    <option value="{{ $tahun->id_tahun_ajaran }}" @selected(($filter['id_tahun_ajaran'] ?? '') == $tahun->id_tahun_ajaran)>
                        {{ $tahun->tahun_ajaran }}
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
                <option value="lulus" @selected(($filter['status'] ?? '') === 'lulus')>Lulus</option>
                <option value="keluar" @selected(($filter['status'] ?? '') === 'keluar')>Keluar</option>
            </select>
        </div>

        <div class="flex items-end gap-2">
            <button type="submit"
                    class="rounded-lg bg-neon px-4 py-2 text-sm font-semibold text-tinta hover:bg-neon-tekan tombol-tekan">
                Terapkan
            </button>
            <a href="{{ route('siswa.index') }}"
               class="rounded-lg border border-garis dark:border-garis-gelap bg-white px-4 py-2 text-sm font-medium text-tinta dark:text-teks-gelap hover:bg-latar-2 dark:hover:bg-gelap">
                Reset
            </a>
        </div>
    </div>
</form>

{{-- Tabel siswa. --}}
<div class="overflow-hidden rounded-xl border border-garis dark:border-garis-gelap bg-white shadow-sm">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-garis dark:divide-garis-gelap text-sm">
            <thead class="bg-latar-2 dark:bg-gelap text-left text-xs uppercase tracking-wide text-redup dark:text-redup-gelap">
                <tr>
                    <th class="px-5 py-3 font-semibold">NIS</th>
                    <th class="px-5 py-3 font-semibold">Nama</th>
                    <th class="px-5 py-3 font-semibold">Kelas</th>
                    <th class="px-5 py-3 font-semibold">Tahun ajaran</th>
                    <th class="px-5 py-3 font-semibold">Jenis kelamin</th>
                    <th class="px-5 py-3 font-semibold">Status</th>
                    <th class="px-5 py-3 text-right font-semibold">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-garis dark:divide-garis-gelap">
                @forelse ($daftar_siswa as $siswa)
                    <tr class="hover:bg-latar-2 dark:hover:bg-gelap">
                        <td class="px-5 py-3 font-medium text-tinta dark:text-teks-gelap">{{ $siswa->nis }}</td>
                        <td class="px-5 py-3 text-tinta dark:text-teks-gelap">{{ $siswa->nama }}</td>
                        <td class="px-5 py-3 text-redup dark:text-redup-gelap">{{ $siswa->kelas?->nama_kelas ?? '-' }}</td>
                        <td class="px-5 py-3 text-redup dark:text-redup-gelap">{{ $siswa->tahunAjaran?->tahun_ajaran ?? '-' }}</td>
                        <td class="px-5 py-3 text-redup dark:text-redup-gelap">{{ $siswa->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }}</td>
                        <td class="px-5 py-3 text-redup dark:text-redup-gelap">{{ ucfirst($siswa->status) }}</td>
                        <td class="px-5 py-3">
                            <div class="flex flex-wrap items-center justify-end gap-2">
                                <a href="{{ route('siswa.show', $siswa) }}"
                                   class="rounded-md border border-garis dark:border-garis-gelap px-2.5 py-1 text-xs font-medium text-tinta dark:text-teks-gelap hover:bg-latar-2 dark:hover:bg-gelap">
                                    Detail
                                </a>

                                @if (auth()->user()->role === 'admin')
                                    <button type="button" data-modal-buka="ubah" data-modal-target="modal-siswa"
                                            data-id="{{ $siswa->id_siswa }}"
                                            data-nis="{{ $siswa->nis }}"
                                            data-nisn="{{ $siswa->nisn }}"
                                            data-nama="{{ $siswa->nama }}"
                                            data-jenis_kelamin="{{ $siswa->jenis_kelamin }}"
                                            data-status="{{ $siswa->status }}"
                                            data-tempat_lahir="{{ $siswa->tempat_lahir }}"
                                            data-tanggal_lahir="{{ $siswa->tanggal_lahir?->format('Y-m-d') }}"
                                            data-alamat="{{ $siswa->alamat }}"
                                            data-no_hp="{{ $siswa->no_hp }}"
                                            data-nama_orang_tua="{{ $siswa->nama_orang_tua }}"
                                            data-id_kelas="{{ $siswa->id_kelas }}"
                                            data-id_tahun_ajaran="{{ $siswa->id_tahun_ajaran }}"
                                            data-id_user="{{ $siswa->id_user }}"
                                            data-id_user_label="{{ $siswa->user ? $siswa->user->nama.' ('.$siswa->user->username.')' : '' }}"
                                            class="rounded-md border border-garis dark:border-garis-gelap px-2.5 py-1 text-xs font-medium text-tinta dark:text-teks-gelap hover:bg-latar-2 dark:hover:bg-gelap">
                                        Ubah
                                    </button>

                                    <form method="POST" action="{{ route('siswa.destroy', $siswa) }}"
                                          onsubmit="return confirm('Hapus data siswa ini? Tindakan ini tidak bisa dibatalkan.')">
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
                        <td colspan="7" class="px-5 py-8 text-center text-redup dark:text-redup-gelap">
                            Tidak ada siswa yang cocok dengan filter ini.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
