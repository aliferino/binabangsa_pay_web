{{--
    Modal konfirmasi untuk simpan dan hapus. Satu modal, teks dan warna tombol
    diisi app.js lewat data-konfirmasi-pesan dan data-konfirmasi-ya.

    Kenapa modal, bukan confirm() bawaan peramban: dialog bawaan memblokir
    seluruh halaman, tidak bisa digayai, dan tidak bisa diletakkan di atas
    modal formulir. Modal ini z-nya di atas modal formulir.

    z-[55] sengaja di atas modal formulir (z-40) tapi tetap di bawah toast
    (z-50) supaya pesan galat tetap terbaca.

    z-index dipasang di CSS (aturan #modal-konfirmasi), bukan lewat kelas
    Tailwind arbitrer z-[55]. Kelas itu tidak pernah masuk CSS terbangun,
    jadi modal ini tidak punya z-index dan tertimbun modal formulir z-40.
--}}
<div id="modal-konfirmasi" class="fixed inset-0 hidden" data-modal>
    <div class="absolute inset-0 bg-slate-900/50 dark:bg-black/70" data-modal-tutup></div>

    {{-- Tengah vertikal: pembungkus setinggi layar dengan flex, bukan mt-24.
         flex-col + justify-center membuat kotak benar-benar di tengah, dan
         tetap bisa digulir kalau pesannya panjang di layar pendek.
         pointer-events-none wajib: tanpa itu pembungkus ini menutupi lapisan
         gelap di belakangnya, sehingga klik di luar kotak tidak menutup modal. --}}
    <div class="pointer-events-none relative flex h-full w-full flex-col justify-center px-4 py-6">
        <div class="pointer-events-auto mx-auto w-full max-w-sm overflow-hidden rounded-xl border border-garis bg-white shadow-lg dark:border-garis-gelap dark:bg-gelap-2">

            <div class="px-5 pb-1 pt-5">
                <h2 class="text-sm font-semibold text-tinta dark:text-teks-gelap">Konfirmasi</h2>
                <p class="mt-2 text-sm leading-relaxed text-redup dark:text-redup-gelap"
                   data-konfirmasi-pesan></p>
            </div>

            <div class="flex flex-wrap items-center justify-end gap-2 px-5 py-4">
                <button type="button" data-modal-tutup
                        class="rounded-lg border border-garis bg-white px-4 py-2 text-sm font-medium text-tinta hover:bg-latar-2 dark:border-garis-gelap dark:bg-gelap-2 dark:text-teks-gelap dark:hover:bg-gelap">
                    Batal
                </button>
                {{-- Kelas warna tombol ini ditukar app.js: merah untuk hapus,
                     neon untuk simpan. --}}
                <button type="button" data-konfirmasi-ya
                        class="tombol-tekan rounded-lg bg-neon px-4 py-2 text-sm font-semibold text-tinta hover:bg-neon-tekan">
                    Ya
                </button>
            </div>
        </div>
    </div>
</div>