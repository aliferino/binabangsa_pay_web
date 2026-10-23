{{--
    Modal tambah dan ubah jurusan. Satu modal dipakai untuk dua keperluan.
    Tombol Tambah membuka modal dengan form kosong, tombol Ubah mengisi form
    dari atribut data pada tombolnya. Scriptnya ada di layouts.partials._modal-script.
--}}
<div id="modal-jurusan" class="fixed inset-0 z-40 hidden" data-modal
     data-judul-tambah="Tambah Jurusan"
     data-judul-ubah="Ubah Jurusan">
    <div class="absolute inset-0 bg-slate-900/50 dark:bg-black/70" data-modal-tutup></div>

    <div class="relative mx-auto mt-10 w-full max-w-2xl px-4 pb-10">
        <div class="overflow-hidden rounded-xl border border-garis bg-white shadow-lg dark:border-garis-gelap dark:bg-gelap-2 dark:border-garis-gelap dark:bg-gelap-2">

            <div class="flex items-center justify-between border-b border-garis px-5 py-4 dark:border-garis-gelap">
                <h2 class="text-sm font-semibold text-tinta dark:text-teks-gelap" data-modal-judul>Tambah Jurusan</h2>
                <button type="button" data-modal-tutup
                        class="rounded-md border border-garis px-2.5 py-1 text-xs font-medium text-tinta hover:bg-latar-2 dark:border-garis-gelap dark:text-teks-gelap dark:hover:bg-gelap">
                    Tutup
                </button>
            </div>

            <form method="POST" action="{{ route('jurusan.store') }}" data-form-modal
                  data-url-store="{{ route('jurusan.store') }}"
                  data-url-update="{{ route('jurusan.update', ['jurusan' => '__ID__']) }}">
                @csrf
                <input type="hidden" name="_method" value="POST" data-metode>
                <input type="hidden" name="_mode" value="{{ old('_mode', 'tambah') }}">
                <input type="hidden" name="_id" value="{{ old('_id') }}">
                <input type="hidden" name="_modal" value="modal-jurusan">

                <div class="max-h-[70vh] overflow-y-auto px-5 py-4">
                    <div class="space-y-4">

                        <div>
                            <label for="kode_jurusan" class="mb-1 block text-sm font-medium text-redup dark:text-redup-gelap">
                                Kode jurusan <span class="text-red-600 dark:text-red-400">*</span>
                            </label>
                            <input type="text" name="kode_jurusan" id="kode_jurusan"
                                   value="{{ old('kode_jurusan') }}" required
                                   class="w-full rounded-lg border border-garis px-3 py-2 text-sm text-tinta shadow-sm focus:border-neon-teks focus:outline-none focus:ring-2 focus:ring-neon/40 dark:border-redup dark:bg-gelap dark:text-teks-gelap dark:placeholder:text-redup-gelap">
                            @include('layouts.partials._error', ['nama' => 'kode_jurusan'])
                        </div>

                        <div>
                            <label for="nama_jurusan" class="mb-1 block text-sm font-medium text-redup dark:text-redup-gelap">
                                Nama jurusan <span class="text-red-600 dark:text-red-400">*</span>
                            </label>
                            <input type="text" name="nama_jurusan" id="nama_jurusan"
                                   value="{{ old('nama_jurusan') }}" required
                                   class="w-full rounded-lg border border-garis px-3 py-2 text-sm text-tinta shadow-sm focus:border-neon-teks focus:outline-none focus:ring-2 focus:ring-neon/40 dark:border-redup dark:bg-gelap dark:text-teks-gelap dark:placeholder:text-redup-gelap">
                            @include('layouts.partials._error', ['nama' => 'nama_jurusan'])
                        </div>

                        <div>
                            <label for="keterangan" class="mb-1 block text-sm font-medium text-redup dark:text-redup-gelap">Keterangan</label>
                            <textarea name="keterangan" id="keterangan" rows="3"
                                      class="w-full rounded-lg border border-garis px-3 py-2 text-sm text-tinta shadow-sm focus:border-neon-teks focus:outline-none focus:ring-2 focus:ring-neon/40 dark:border-redup dark:bg-gelap dark:text-teks-gelap dark:placeholder:text-redup-gelap">{{ old('keterangan') }}</textarea>
                            @include('layouts.partials._error', ['nama' => 'keterangan'])
                        </div>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-2 border-t border-garis px-5 py-4 dark:border-garis-gelap">
                    <button type="submit"
                            class="rounded-lg bg-neon px-4 py-2 text-sm font-semibold text-tinta hover:bg-neon-tekan tombol-tekan">
                        Simpan
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
