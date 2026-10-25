{{--
    Modal pilih bank untuk menghubungkan Virtual Account internal ke Xendit.

    Bedanya dengan modal CRUD: form ini tidak punya data-url-store dan tidak
    punya field _method, _mode, _id. Alamat tujuannya sudah ditentukan tombol
    pemicu lewat data-url, karena tiap baris menunjuk siswa yang berbeda.
    app.js hanya mengisi action dari data-url kalau atributnya ada.
--}}
<div id="modal-bank" class="fixed inset-0 z-40 hidden" data-modal
     data-judul-tambah="Hubungkan ke Xendit">
    <div class="absolute inset-0 bg-black/50 dark:bg-black/70" data-modal-tutup></div>

    {{-- Tengah vertikal lewat flex setinggi layar, sama seperti modal konfirmasi.
         pointer-events-none wajib supaya lapisan gelap di belakang tetap bisa
         diklik untuk menutup modal. --}}
    <div class="pointer-events-none relative flex h-full w-full flex-col justify-center px-4 py-6">
        <div class="pointer-events-auto mx-auto w-full max-w-lg overflow-hidden rounded-xl border border-garis bg-white shadow-lg dark:border-garis-gelap dark:bg-gelap-2">

            <div class="flex items-center justify-between border-b border-garis px-5 py-4 dark:border-garis-gelap">
                <h2 class="text-sm font-semibold text-tinta dark:text-teks-gelap" data-modal-judul>Hubungkan ke Xendit</h2>
                <button type="button" data-modal-tutup
                        class="inline-flex h-7 w-7 items-center justify-center rounded-md border border-garis text-redup transition-colors hover:border-neon-teks hover:bg-latar-2 hover:text-tinta dark:border-garis-gelap dark:text-redup-gelap dark:hover:bg-gelap dark:hover:text-teks-gelap"
                        title="Tutup" aria-label="Tutup">
                    <svg class="h-[18px] w-[18px]" aria-hidden="true"><use href="#i-tutup"/></svg>
                </button>
            </div>

            <form method="POST" action="" data-form-modal
                  data-toast novalidate>
                @csrf

                <div class="space-y-4 px-5 py-4">
                    <div>
                        <label for="bank_code" class="mb-1 block text-sm font-medium text-redup dark:text-redup-gelap">
                            Bank <span class="text-red-600 dark:text-red-400">*</span>
                        </label>
                        <select name="bank_code" id="bank_code" required data-label="Bank"
                                class="w-full rounded-lg border border-garis bg-white pl-3 pr-9 py-2 text-sm text-tinta shadow-sm focus:border-neon-teks focus:outline-none focus:ring-2 focus:ring-neon/40 dark:border-redup dark:bg-gelap dark:text-teks-gelap">
                            <option value="">Pilih bank</option>
                            <option value="BCA_VIRTUAL_ACCOUNT">BCA</option>
                            <option value="BNI_VIRTUAL_ACCOUNT">BNI</option>
                            <option value="BRI_VIRTUAL_ACCOUNT">BRI</option>
                            <option value="MANDIRI_VIRTUAL_ACCOUNT">MANDIRI</option>
                            <option value="PERMATA_VIRTUAL_ACCOUNT">PERMATA</option>
                        </select>
                        @include('layouts.partials._error', ['nama' => 'bank_code'])
                        <p class="mt-1 text-xs text-redup dark:text-redup-gelap">
                            Nomor bank dari Xendit akan menggantikan nomor internal saat siswa membayar. Nomor internal tetap disimpan.
                        </p>
                    </div>

                    <div>
                        <label for="berlaku_hari" class="mb-1 block text-sm font-medium text-redup dark:text-redup-gelap">Masa berlaku (hari)</label>
                        <input type="number" name="berlaku_hari" id="berlaku_hari" min="1" max="365" value="30"
                               class="w-full rounded-lg border border-garis bg-white px-3 py-2 text-sm text-tinta shadow-sm focus:border-neon-teks focus:outline-none focus:ring-2 focus:ring-neon/40 dark:border-redup dark:bg-gelap dark:text-teks-gelap">
                        @include('layouts.partials._error', ['nama' => 'berlaku_hari'])
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-2 border-t border-garis px-5 py-4 dark:border-garis-gelap">
                    <button type="submit"
                            class="border border-transparent tombol-tekan inline-flex items-center gap-2 rounded-lg bg-neon px-4 py-2 text-sm font-semibold text-tinta hover:bg-neon-tekan"
                            title="Simpan" aria-label="Simpan">
                        <svg class="h-[18px] w-[18px]" aria-hidden="true"><use href="#i-simpan"/></svg>Simpan
                    </button>
                    <button type="button" data-modal-tutup
                            class="rounded-lg border border-garis bg-white px-4 py-2 text-sm font-medium text-tinta hover:bg-latar-2 dark:border-garis-gelap dark:bg-gelap-2 dark:text-teks-gelap dark:hover:bg-gelap">
                        Batal
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>