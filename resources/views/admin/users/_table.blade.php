{{-- Tabel daftar pengguna. --}}
@php
    $labelRole = [
        'admin' => 'Administrator',
        'kepala_sekolah' => 'Kepala Sekolah',
        'teller' => 'Teller',
        'siswa' => 'Siswa',
    ];
@endphp

<div class="overflow-hidden rounded-xl border border-garis dark:border-garis-gelap bg-white dark:bg-gelap-2 shadow-sm">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-garis dark:divide-garis-gelap text-sm">
            <thead class="bg-latar-2 dark:bg-gelap text-left text-xs uppercase tracking-wide text-redup dark:text-redup-gelap">
                <tr>
                    <th class="px-5 py-3 font-semibold">Nama</th>
                    <th class="px-5 py-3 font-semibold">Username</th>
                    <th class="px-5 py-3 font-semibold">Role</th>
                    <th class="px-5 py-3 font-semibold">Status</th>
                    <th class="px-5 py-3 text-right font-semibold">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-garis dark:divide-garis-gelap">
                @forelse ($daftar_user as $user)
                    <tr class="hover:bg-latar-2 dark:hover:bg-garis-gelap/40">
                        <td class="px-5 py-3 font-medium text-tinta dark:text-teks-gelap">{{ $user->nama }}</td>
                        <td class="px-5 py-3 text-redup dark:text-redup-gelap">{{ $user->username }}</td>
                        <td class="px-5 py-3 text-redup dark:text-redup-gelap">{{ $labelRole[$user->role] ?? $user->role }}</td>
                        <td class="px-5 py-3">
                            @if ($user->status === 'aktif')
                                <span class="inline-flex rounded-full bg-neon text-tinta px-2.5 py-0.5 text-xs font-semibold">Aktif</span>
                            @else
                                <span class="inline-flex rounded-full bg-latar-2 dark:bg-gelap px-2.5 py-0.5 text-xs font-semibold text-redup dark:text-redup-gelap">Nonaktif</span>
                            @endif
                        </td>
                        <td class="px-5 py-3">
                            <div class="flex flex-wrap items-center justify-end gap-2">
<a href="{{ route('user.show', $user) }}" class="inline-flex h-8 w-8 items-center justify-center rounded-md border border-garis text-redup transition-colors hover:border-neon-teks hover:bg-latar-2 hover:text-tinta dark:border-garis-gelap dark:text-redup-gelap dark:hover:bg-gelap dark:hover:text-teks-gelap" title="Lihat detail" aria-label="Lihat detail"><svg class="h-[18px] w-[18px]" aria-hidden="true"><use href="#i-lihat"/></svg>
                                </a>

<button type="button" data-modal-buka="ubah" data-modal-target="modal-pengguna" data-id="{{ $user->id_user }}" data-nama="{{ $user->nama }}" data-username="{{ $user->username }}" data-role="{{ $user->role }}" data-status="{{ $user->status }}" class="inline-flex h-8 w-8 items-center justify-center rounded-md border border-garis text-redup transition-colors hover:border-neon-teks hover:bg-latar-2 hover:text-tinta dark:border-garis-gelap dark:text-redup-gelap dark:hover:bg-gelap dark:hover:text-teks-gelap" title="Ubah" aria-label="Ubah"><svg class="h-[18px] w-[18px]" aria-hidden="true"><use href="#i-ubah"/></svg>
                                </button>

                                <form method="POST" action="{{ route('user.destroy', $user) }}"
                                          data-konfirmasi="Hapus data pengguna ini? Tindakan ini tidak bisa dibatalkan.">
                                    @csrf
                                    @method('DELETE')
<button type="submit" class="inline-flex h-8 w-8 items-center justify-center rounded-md border border-red-300 text-red-700 transition-colors hover:bg-red-50 dark:border-red-900 dark:text-red-400 dark:hover:bg-red-950/40" title="Hapus" aria-label="Hapus"><svg class="h-[18px] w-[18px]" aria-hidden="true"><use href="#i-hapus"/></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-5 py-8 text-center text-redup dark:text-redup-gelap">
                            Belum ada data pengguna.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
