{{-- Tabel daftar pengguna. --}}
@php
    $labelRole = [
        'admin' => 'Administrator',
        'kepala_sekolah' => 'Kepala Sekolah',
        'teller' => 'Teller',
        'siswa' => 'Siswa',
    ];
@endphp

<div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                <tr>
                    <th class="px-5 py-3 font-semibold">Nama</th>
                    <th class="px-5 py-3 font-semibold">Username</th>
                    <th class="px-5 py-3 font-semibold">Role</th>
                    <th class="px-5 py-3 font-semibold">Status</th>
                    <th class="px-5 py-3 text-right font-semibold">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($daftar_user as $user)
                    <tr class="hover:bg-slate-50">
                        <td class="px-5 py-3 font-medium text-slate-800">{{ $user->nama }}</td>
                        <td class="px-5 py-3 text-slate-600">{{ $user->username }}</td>
                        <td class="px-5 py-3 text-slate-600">{{ $labelRole[$user->role] ?? $user->role }}</td>
                        <td class="px-5 py-3">
                            @if ($user->status === 'aktif')
                                <span class="inline-flex rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-semibold text-emerald-800">Aktif</span>
                            @else
                                <span class="inline-flex rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-semibold text-slate-700">Nonaktif</span>
                            @endif
                        </td>
                        <td class="px-5 py-3">
                            <div class="flex flex-wrap items-center justify-end gap-2">
                                <a href="{{ route('user.show', $user) }}"
                                   class="rounded-md border border-slate-300 px-2.5 py-1 text-xs font-medium text-slate-700 hover:bg-slate-100">
                                    Detail
                                </a>

                                <button type="button" data-modal-buka="ubah" data-modal-target="modal-pengguna"
                                        data-id="{{ $user->id_user }}"
                                        data-nama="{{ $user->nama }}"
                                        data-username="{{ $user->username }}"
                                        data-role="{{ $user->role }}"
                                        data-status="{{ $user->status }}"
                                        class="rounded-md border border-slate-300 px-2.5 py-1 text-xs font-medium text-slate-700 hover:bg-slate-100">
                                    Ubah
                                </button>

                                <form method="POST" action="{{ route('user.destroy', $user) }}"
                                      onsubmit="return confirm('Hapus data pengguna ini? Tindakan ini tidak bisa dibatalkan.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="rounded-md border border-red-300 px-2.5 py-1 text-xs font-medium text-red-700 hover:bg-red-50">
                                        Hapus
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-5 py-8 text-center text-slate-500">
                            Belum ada data pengguna.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
