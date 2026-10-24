{{--
    Modal buat Virtual Account.

    Hanya satu kolom: pilih siswa. Nomor VA disusun sistem dari lima digit
    terakhir NIS, jadi tidak ada yang perlu diketik operator. Tombol simpan
    membuka konfirmasi lebih dulu (lihat app.js, data-konfirmasi).
--}}
<div id="modal-virtual-account" class="fixed inset-0 z-40 hidden" data-modal
     data-judul-tambah="Buat Virtual Account">
    <div class="absolute inset-0 bg-slate-900/50 dark:bg-black/70" data-modal-tutup></div>

    {{-- Tengah vertikal lewat flex setinggi layar, sama seperti modal konfirmasi.
         pointer-events-none wajib supaya lapisan gelap di belakang tetap bisa
         diklik untuk menutup modal. --}}
    <div class="pointer-events-none relative flex h-full w-full flex-col justify-center px-4 py-6">
        <div class="pointer-events-auto mx-auto w-full max-w-lg overflow-hidden rounded-xl border border-garis bg-white shadow-lg dark:border-garis-gelap dark:bg-gelap-2">

            <div class="flex items-center justify-between border-b border-garis px-5 py-4 dark:border-garis-gelap">
                <h2 class="text-sm font-semibold text-tinta dark:text-teks-gelap" data-modal-judul>Buat Virtual Account</h2>
                <button type="button" data-modal-tutup
                        class="inline-flex h-7 w-7 items-center justify-center rounded-md border border-garis text-redup transition-colors hover:border-neon-teks hover:bg-latar-2 hover:text-tinta dark:border-garis-gelap dark:text-redup-gelap dark:hover:bg-gelap dark:hover:text-teks-gelap"
                        title="Tutup" aria-label="Tutup">
                    <svg class="h-[18px] w-[18px]" aria-hidden="true"><use href="#i-tutup"/></svg>
                </button>
            </div>

            <form method="POST" action="{{ route('virtual-account.generate') }}"
                  data-toast novalidate
                  data-konfirmasi="Buat Virtual Account untuk siswa ini?" data-konfirmasi-jenis="simpan">
                @csrf

                <div class="px-5 py-4">
                    <div>
                        <label for="va_siswa_id" class="mb-1 block text-sm font-medium text-redup dark:text-redup-gelap">
                            Siswa <span class="text-red-600 dark:text-red-400">*</span>
                        </label>
                        <select name="siswa_id" id="va_siswa_id" required data-label="Siswa"
                                class="w-full rounded-lg border border-garis bg-white px-3 py-2 text-sm text-tinta shadow-sm focus:border-neon-teks focus:outline-none focus:ring-2 focus:ring-neon/40 dark:border-redup dark:bg-gelap dark:text-teks-gelap">
                            <option value="">Pilih siswa</option>
                            @foreach ($students as $student)
                                <option value="{{ $student->id_siswa }}">{{ $student->nis }} - {{ $student->nama }}</option>
                            @endforeach
                        </select>
                        @include('layouts.partials._error', ['nama' => 'siswa_id'])
                        <p class="mt-1 text-xs text-redup dark:text-redup-gelap">
                            Nomor VA disusun dari lima digit terakhir NIS. Satu siswa hanya boleh punya satu.
                        </p>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-2 border-t border-garis px-5 py-4 dark:border-garis-gelap">
                    <button type="submit"
                            class="tombol-tekan inline-flex items-center gap-2 rounded-lg bg-neon px-4 py-2 text-sm font-semibold text-tinta hover:bg-neon-tekan"
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
