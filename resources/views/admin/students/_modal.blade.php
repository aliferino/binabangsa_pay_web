{{--
    Modal tambah dan ubah siswa. Satu modal dipakai untuk dua keperluan.
    Isian form memakai partial admin.students._form supaya susunan kolomnya
    sama persis dengan sebelumnya. Tombol Ubah mengisi form dari atribut data
    pada tombolnya, termasuk akun siswa yang sedang tertaut.
--}}
<div id="modal-siswa" class="fixed inset-0 z-40 hidden" data-modal
     data-judul-tambah="Tambah Siswa"
     data-judul-ubah="Ubah Siswa">
    <div class="absolute inset-0 bg-black/50 dark:bg-black/70" data-modal-tutup></div>

    <div class="pointer-events-none relative mx-auto mt-10 w-full max-w-3xl px-4 pb-10">
        <div class="pointer-events-auto overflow-hidden rounded-xl border border-garis dark:border-garis-gelap bg-white dark:bg-gelap-2 shadow-lg">

            <div class="flex items-center justify-between border-b border-garis dark:border-garis-gelap px-5 py-4">
                <h2 class="text-sm font-semibold text-tinta dark:text-teks-gelap" data-modal-judul>Tambah Siswa</h2>
<button type="button" data-modal-tutup class="inline-flex h-7 w-7 items-center justify-center rounded-md border border-garis text-redup transition-colors hover:border-neon-teks hover:bg-latar-2 hover:text-tinta dark:border-garis-gelap dark:text-redup-gelap dark:hover:bg-gelap dark:hover:text-teks-gelap" title="Tutup" aria-label="Tutup"><svg class="h-[18px] w-[18px]" aria-hidden="true"><use href="#i-tutup"/></svg>
                </button>
            </div>

            <form method="POST" action="{{ route('siswa.store') }}" data-form-modal
                  data-url-store="{{ route('siswa.store') }}"
                  data-url-update="{{ route('siswa.update', ['siswa' => '__ID__']) }}">
                @csrf
                <input type="hidden" name="_method" value="POST" data-metode>
                <input type="hidden" name="_mode" value="{{ old('_mode', 'tambah') }}">
                <input type="hidden" name="_id" value="{{ old('_id') }}">
                <input type="hidden" name="_modal" value="modal-siswa">

                <div class="max-h-[70vh] overflow-y-auto px-5 py-4">
                    @include('admin.students._form', ['siswa' => null])
                </div>

                <div class="flex flex-wrap items-center gap-2 border-t border-garis dark:border-garis-gelap px-5 py-4">
<button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-neon px-4 py-2 text-sm font-semibold text-tinta hover:bg-neon-tekan tombol-tekan" title="Simpan" aria-label="Simpan"><svg class="h-[18px] w-[18px]" aria-hidden="true"><use href="#i-simpan"/></svg>Simpan
                    </button>
<button type="button" data-modal-tutup class="rounded-lg border border-garis bg-white px-4 py-2 text-sm font-medium text-tinta hover:bg-latar-2 dark:border-garis-gelap dark:bg-gelap-2 dark:text-teks-gelap dark:hover:bg-gelap">Batal
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
