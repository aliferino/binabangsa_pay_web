{{--
    Modal tambah dan ubah pengguna. Satu modal dipakai untuk dua keperluan.
    Tombol Tambah membuka modal dengan form kosong, tombol Ubah mengisi form
    dari atribut data pada tombolnya. Scriptnya ada di layouts.partials._modal-script.

    Password tidak wajib di atribut required karena saat mengubah boleh kosong.
    Aturan wajib isi saat menambah tetap dijaga validasi di controller.
--}}
<div id="modal-pengguna" class="fixed inset-0 z-40 hidden" data-modal
     data-judul-tambah="Tambah Pengguna"
     data-judul-ubah="Ubah Pengguna">
    <div class="absolute inset-0 bg-slate-900/50 dark:bg-black/70" data-modal-tutup></div>

    <div class="relative mx-auto mt-10 w-full max-w-2xl px-4 pb-10">
        <div class="overflow-hidden rounded-xl border border-garis dark:border-garis-gelap bg-white shadow-lg">

            <div class="flex items-center justify-between border-b border-garis dark:border-garis-gelap px-5 py-4">
                <h2 class="text-sm font-semibold text-tinta dark:text-teks-gelap" data-modal-judul>Tambah Pengguna</h2>
                <button type="button" data-modal-tutup
                        class="rounded-md border border-garis dark:border-garis-gelap px-2.5 py-1 text-xs font-medium text-tinta dark:text-teks-gelap hover:bg-latar-2 dark:hover:bg-gelap">
                    Tutup
                </button>
            </div>

            <form method="POST" action="{{ route('user.store') }}" data-form-modal
                  data-url-store="{{ route('user.store') }}"
                  data-url-update="{{ route('user.update', ['user' => '__ID__']) }}">
                @csrf
                <input type="hidden" name="_method" value="POST" data-metode>
                <input type="hidden" name="_mode" value="{{ old('_mode', 'tambah') }}">
                <input type="hidden" name="_id" value="{{ old('_id') }}">
                <input type="hidden" name="_modal" value="modal-pengguna">

                <div class="max-h-[70vh] overflow-y-auto px-5 py-4">
                    <div class="space-y-4">

                        <div>
                            <label for="nama" class="mb-1 block text-sm font-medium text-tinta dark:text-teks-gelap">
                                Nama <span class="text-red-600">*</span>
                            </label>
                            <input type="text" name="nama" id="nama" value="{{ old('nama') }}" required
                                   class="w-full rounded-lg border border-garis px-3 py-2 text-sm text-tinta shadow-sm focus:border-neon-teks focus:outline-none focus:ring-2 focus:ring-neon/40 dark:border-redup dark:bg-gelap dark:text-teks-gelap dark:placeholder:text-redup-gelap">
                            @include('layouts.partials._error', ['nama' => 'nama'])
                        </div>

                        <div>
                            <label for="username" class="mb-1 block text-sm font-medium text-tinta dark:text-teks-gelap">
                                Username <span class="text-red-600">*</span>
                            </label>
                            <input type="text" name="username" id="username" value="{{ old('username') }}" required
                                   autocomplete="off"
                                   class="w-full rounded-lg border border-garis px-3 py-2 text-sm text-tinta shadow-sm focus:border-neon-teks focus:outline-none focus:ring-2 focus:ring-neon/40 dark:border-redup dark:bg-gelap dark:text-teks-gelap dark:placeholder:text-redup-gelap">
                            @include('layouts.partials._error', ['nama' => 'username'])
                        </div>

                        <div>
                            <label for="password" class="mb-1 block text-sm font-medium text-tinta dark:text-teks-gelap">Password</label>
                            <input type="password" name="password" id="password"
                                   autocomplete="new-password"
                                   class="w-full rounded-lg border border-garis px-3 py-2 text-sm text-tinta shadow-sm focus:border-neon-teks focus:outline-none focus:ring-2 focus:ring-neon/40 dark:border-redup dark:bg-gelap dark:text-teks-gelap dark:placeholder:text-redup-gelap">
                            @include('layouts.partials._error', ['nama' => 'password'])
                            <p class="mt-1 text-xs text-redup dark:text-redup-gelap"
                               data-modal-petunjuk-tambah="Minimal 6 karakter."
                               data-modal-petunjuk-ubah="Kosongkan kalau tidak ingin mengganti password. Kalau diisi, minimal 6 karakter.">
                                Minimal 6 karakter.
                            </p>
                        </div>

                        <div>
                            <label for="role" class="mb-1 block text-sm font-medium text-tinta dark:text-teks-gelap">
                                Role <span class="text-red-600">*</span>
                            </label>
                            <select name="role" id="role" required
                                    class="w-full rounded-lg border border-garis bg-white px-3 py-2 text-sm text-tinta shadow-sm focus:border-neon-teks focus:outline-none focus:ring-2 focus:ring-neon/40 dark:border-redup dark:bg-gelap dark:text-teks-gelap">
                                <option value="">Pilih role</option>
                                <option value="admin" @selected(old('role') === 'admin')>Administrator</option>
                                <option value="kepala_sekolah" @selected(old('role') === 'kepala_sekolah')>Kepala Sekolah</option>
                                <option value="teller" @selected(old('role') === 'teller')>Teller</option>
                                <option value="siswa" @selected(old('role') === 'siswa')>Siswa</option>
                            </select>
                            @include('layouts.partials._error', ['nama' => 'role'])
                        </div>

                        <div>
                            <label for="status" class="mb-1 block text-sm font-medium text-tinta dark:text-teks-gelap">
                                Status <span class="text-red-600">*</span>
                            </label>
                            <select name="status" id="status" required
                                    class="w-full rounded-lg border border-garis bg-white px-3 py-2 text-sm text-tinta shadow-sm focus:border-neon-teks focus:outline-none focus:ring-2 focus:ring-neon/40 dark:border-redup dark:bg-gelap dark:text-teks-gelap">
                                <option value="aktif" @selected(old('status') === 'aktif')>Aktif</option>
                                <option value="nonaktif" @selected(old('status') === 'nonaktif')>Nonaktif</option>
                            </select>
                            @include('layouts.partials._error', ['nama' => 'status'])
                        </div>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-2 border-t border-garis dark:border-garis-gelap px-5 py-4">
                    <button type="submit"
                            class="rounded-lg bg-neon px-4 py-2 text-sm font-semibold text-tinta hover:bg-neon-tekan tombol-tekan">
                        Simpan
                    </button>
                    <button type="button" data-modal-tutup
                            class="rounded-lg border border-garis dark:border-garis-gelap bg-white px-4 py-2 text-sm font-medium text-tinta dark:text-teks-gelap hover:bg-latar-2 dark:hover:bg-gelap">
                        Batal
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
