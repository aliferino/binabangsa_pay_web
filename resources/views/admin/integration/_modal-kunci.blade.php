{{--
    Modal ubah kunci API. Isinya satu form yang dikirim ke integrasi.kunci.simpan.

    Tombol Simpan sengaja diletakkan di footer, di luar <form>, lalu diikat lewat
    atribut form="form-kunci". Kalau tombolnya di dalam form, tombol "Kembalikan
    ke bawaan" harus ikut di dalamnya juga, dan form bersarang tidak sah.

    Nilai kunci penuh tidak pernah dikirim ke halaman ini. Server key hanya
    memakai nilai tersamar sebagai placeholder, dan inputnya dikosongkan.
--}}
<div id="modal-kunci" class="fixed inset-0 z-40 hidden" data-modal
     data-judul-tambah="Ubah kunci API">
    <div class="absolute inset-0 bg-black/50 dark:bg-black/70" data-modal-tutup></div>

    <div class="pointer-events-none relative mx-auto mt-6 mb-6 w-full max-w-lg px-4 pb-10">
        <div class="pointer-events-auto max-h-[calc(100vh-3rem)] overflow-y-auto rounded-xl border border-garis bg-white shadow-lg dark:border-garis-gelap dark:bg-gelap-2">

            <div class="flex items-center justify-between border-b border-garis px-5 py-4 dark:border-garis-gelap sticky top-0 z-10 bg-white dark:bg-gelap-2">
                <h2 class="text-sm font-semibold text-tinta dark:text-teks-gelap" data-modal-judul>Ubah kunci API</h2>
                <button type="button" data-modal-tutup
                        class="inline-flex h-7 w-7 items-center justify-center rounded-md border border-garis text-redup transition-colors hover:border-neon-teks hover:bg-latar-2 hover:text-tinta dark:border-garis-gelap dark:text-redup-gelap dark:hover:bg-gelap dark:hover:text-teks-gelap"
                        title="Tutup" aria-label="Tutup">
                    <svg class="h-[18px] w-[18px]" aria-hidden="true"><use href="#i-tutup"/></svg>
                </button>
            </div>

            <form id="form-kunci" method="POST" action="{{ route('integrasi.kunci.simpan') }}">
                @csrf

                <div class="max-h-[70vh] overflow-y-auto px-5 py-4">
                    <div class="space-y-4">

                        <div>
                            <label for="kunci_server" class="{{ $kelasLabel ?? 'text-[11px] font-medium text-redup dark:text-redup-gelap' }}">
                                Midtrans Server Key
                            </label>
                            <input type="password" name="midtrans_server_key" id="kunci_server"
                                   value="" autocomplete="new-password"
                                   placeholder="{{ $sandiTersamar }}"
                                   class="mt-1.5 w-full rounded-lg border border-garis px-3 py-2 font-mono text-sm text-tinta shadow-sm focus:border-neon-teks focus:outline-none focus:ring-2 focus:ring-neon/40 dark:border-redup dark:bg-gelap dark:text-teks-gelap dark:placeholder:text-redup-gelap">
                            <p class="mt-1 text-[11px] leading-relaxed text-redup dark:text-redup-gelap">
                                Dipakai untuk menghitung signature notifikasi. Wajib sama dengan dashboard Midtrans.
                                Biarkan kosong kalau tidak ingin mengubah.
                            </p>
                        </div>

                        <div>
                            <label for="kunci_client" class="{{ $kelasLabel ?? 'text-[11px] font-medium text-redup dark:text-redup-gelap' }}">
                                Midtrans Client Key
                            </label>
                            <input type="text" name="midtrans_client_key" id="kunci_client"
                                   value="" autocomplete="off"
                                   placeholder="SB-Mid-client-..."
                                   class="mt-1.5 w-full rounded-lg border border-garis px-3 py-2 font-mono text-sm text-tinta shadow-sm focus:border-neon-teks focus:outline-none focus:ring-2 focus:ring-neon/40 dark:border-redup dark:bg-gelap dark:text-teks-gelap dark:placeholder:text-redup-gelap">
                            <p class="mt-1 text-[11px] leading-relaxed text-redup dark:text-redup-gelap">
                                Dipakai Snap di sisi peramban. Boleh dikosongkan kalau belum dipakai.
                                Biarkan kosong kalau tidak ingin mengubah.
                            </p>
                        </div>

                        <div>
                            <label for="kunci_mode" class="{{ $kelasLabel ?? 'text-[11px] font-medium text-redup dark:text-redup-gelap' }}">
                                Mode produksi
                            </label>
                            <select name="midtrans_is_production" id="kunci_mode"
                                    class="mt-1.5 w-full rounded-lg border border-garis bg-white px-3 py-2 text-sm text-tinta shadow-sm focus:border-neon-teks focus:outline-none focus:ring-2 focus:ring-neon/40 dark:border-redup dark:bg-gelap dark:text-teks-gelap">
                                <option value="0" @selected(! $midtransProduksi)>Sandbox</option>
                                <option value="1" @selected($midtransProduksi)>Produksi</option>
                            </select>
                            <p class="mt-1 text-[11px] leading-relaxed text-redup dark:text-redup-gelap">
                                Produksi mematikan halaman simulasi dan tombol uji notifikasi.
                            </p>
                        </div>

                    </div>
                </div>
            </form>

            <div class="flex flex-wrap items-center gap-2 border-t border-garis px-5 py-4 dark:border-garis-gelap">

                <form method="POST" action="{{ route('integrasi.kunci.reset') }}"
                      data-konfirmasi="Kembalikan kunci ke bawaan .env? Kunci yang tersimpan di berkas akan dihapus."
                      data-konfirmasi-jenis="simpan">
                    @csrf
                    <button type="submit"
                            class="rounded-lg border border-garis bg-white px-3 py-2 text-xs font-medium text-red-700 hover:bg-latar-2 dark:border-garis-gelap dark:bg-gelap-2 dark:text-red-400 dark:hover:bg-gelap">
                        Kembalikan ke bawaan
                    </button>
                </form>

                <div class="ml-auto flex flex-wrap items-center gap-2">
                    <button type="button" data-modal-tutup
                            class="rounded-lg border border-garis bg-white px-4 py-2 text-sm font-medium text-tinta hover:bg-latar-2 dark:border-garis-gelap dark:bg-gelap-2 dark:text-teks-gelap dark:hover:bg-gelap">
                        Batal
                    </button>
                    <button type="submit" form="form-kunci"
                            class="tombol-tekan inline-flex items-center gap-2 rounded-lg bg-neon px-4 py-2 text-sm font-semibold text-tinta hover:bg-neon-tekan"
                            title="Simpan" aria-label="Simpan">
                        <svg class="h-[18px] w-[18px]" aria-hidden="true"><use href="#i-simpan"/></svg>Simpan
                    </button>
                </div>
            </div>

        </div>
    </div>
</div>