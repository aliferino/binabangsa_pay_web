{{--
    Modal tambah dan ubah tahun ajaran. Satu modal dipakai untuk dua keperluan.
    Tombol Tambah membuka modal dengan form kosong, tombol Ubah mengisi form
    dari atribut data pada tombolnya. Scriptnya ada di layouts.partials._modal-script.
--}}
<div id="modal-tahun-ajaran" class="fixed inset-0 z-40 hidden" data-modal
     data-judul-tambah="Tambah Tahun Ajaran"
     data-judul-ubah="Ubah Tahun Ajaran">
    <div class="absolute inset-0 bg-black/50 dark:bg-black/70" data-modal-tutup></div>

    <div class="pointer-events-none relative mx-auto mt-6 mb-6 w-full max-w-2xl px-4 pb-10">
        <div class="pointer-events-auto max-h-[calc(100vh-3rem)] overflow-y-auto rounded-xl border border-garis bg-white shadow-lg dark:border-garis-gelap dark:bg-gelap-2">

            <div class="flex items-center justify-between border-b border-garis px-5 py-4 dark:border-garis-gelap sticky top-0 z-10 bg-white dark:bg-gelap-2">
                <h2 class="text-sm font-semibold text-tinta dark:text-teks-gelap" data-modal-judul>Tambah Tahun Ajaran</h2>
<button type="button" data-modal-tutup class="inline-flex h-7 w-7 items-center justify-center rounded-md border border-garis text-redup transition-colors hover:border-neon-teks hover:bg-latar-2 hover:text-tinta dark:border-garis-gelap dark:text-redup-gelap dark:hover:bg-gelap dark:hover:text-teks-gelap" title="Tutup" aria-label="Tutup"><svg class="h-[18px] w-[18px]" aria-hidden="true"><use href="#i-tutup"/></svg>
                </button>
            </div>

            <form method="POST" action="{{ route('tahun-ajaran.store') }}" data-form-modal
                  data-url-store="{{ route('tahun-ajaran.store') }}"
                  data-url-update="{{ route('tahun-ajaran.update', ['tahunAjaran' => '__ID__']) }}">
                @csrf
                <input type="hidden" name="_method" value="POST" data-metode>
                <input type="hidden" name="_mode" value="{{ old('_mode', 'tambah') }}">
                <input type="hidden" name="_id" value="{{ old('_id') }}">
                <input type="hidden" name="_modal" value="modal-tahun-ajaran">

                <div class="max-h-[70vh] overflow-y-auto px-5 py-4">
                    <div class="space-y-4">

                        <div>
                            <label for="tahun_ajaran" class="mb-1 block text-sm font-medium text-redup dark:text-redup-gelap">
                                Tahun ajaran <span class="text-red-600 dark:text-red-400">*</span>
                            </label>
                            <input type="text" name="tahun_ajaran" id="tahun_ajaran"
                                   value="{{ old('tahun_ajaran') }}" required placeholder="Contoh: 2026/2027"
                                   class="w-full rounded-lg border border-garis px-3 py-2 text-sm text-tinta shadow-sm focus:border-neon-teks focus:outline-none focus:ring-2 focus:ring-neon/40 dark:border-redup dark:bg-gelap dark:text-teks-gelap dark:placeholder:text-redup-gelap">
                            @include('layouts.partials._error', ['nama' => 'tahun_ajaran'])
                        </div>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label for="tanggal_mulai" class="mb-1 block text-sm font-medium text-redup dark:text-redup-gelap">
                                    Tanggal mulai <span class="text-red-600 dark:text-red-400">*</span>
                                </label>
                                <input type="date" name="tanggal_mulai" id="tanggal_mulai"
                                       value="{{ old('tanggal_mulai') }}" required
                                       class="w-full rounded-lg border border-garis px-3 py-2 text-sm text-tinta shadow-sm focus:border-neon-teks focus:outline-none focus:ring-2 focus:ring-neon/40 dark:border-redup dark:bg-gelap dark:text-teks-gelap dark:placeholder:text-redup-gelap" data-placeholder="dd/mm/yyyy">
                                @include('layouts.partials._error', ['nama' => 'tanggal_mulai'])
                            </div>

                            <div>
                                <label for="tanggal_selesai" class="mb-1 block text-sm font-medium text-redup dark:text-redup-gelap">
                                    Tanggal selesai <span class="text-red-600 dark:text-red-400">*</span>
                                </label>
                                <input type="date" name="tanggal_selesai" id="tanggal_selesai"
                                       value="{{ old('tanggal_selesai') }}" required
                                       class="w-full rounded-lg border border-garis px-3 py-2 text-sm text-tinta shadow-sm focus:border-neon-teks focus:outline-none focus:ring-2 focus:ring-neon/40 dark:border-redup dark:bg-gelap dark:text-teks-gelap dark:placeholder:text-redup-gelap" data-placeholder="dd/mm/yyyy">
                                @include('layouts.partials._error', ['nama' => 'tanggal_selesai'])
                            </div>
                        </div>

                        <div>
                            <label for="status" class="mb-1 block text-sm font-medium text-redup dark:text-redup-gelap">
                                Status <span class="text-red-600 dark:text-red-400">*</span>
                            </label>
                            <select name="status" id="status" required
                                    class="w-full rounded-lg border border-garis bg-white pl-3 pr-9 py-2 text-sm text-tinta shadow-sm focus:border-neon-teks focus:outline-none focus:ring-2 focus:ring-neon/40 dark:border-redup dark:bg-gelap dark:text-teks-gelap dark:placeholder:text-redup-gelap">
                                <option value="aktif" @selected(old('status') === 'aktif')>Aktif</option>
                                <option value="nonaktif" @selected(old('status') === 'nonaktif')>Nonaktif</option>
                            </select>
                            @include('layouts.partials._error', ['nama' => 'status'])
                            <p class="mt-1 text-xs text-redup dark:text-redup-gelap">
                                Kalau dipilih aktif, tahun ajaran lain otomatis dinonaktifkan.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-2 border-t border-garis px-5 py-4 dark:border-garis-gelap">
<button type="submit" class="border border-transparent inline-flex items-center gap-2 rounded-lg bg-neon px-4 py-2 text-sm font-semibold text-tinta hover:bg-neon-tekan tombol-tekan" title="Simpan" aria-label="Simpan"><svg class="h-[18px] w-[18px]" aria-hidden="true"><use href="#i-simpan"/></svg>Simpan
                    </button>
<button type="button" data-modal-tutup class="rounded-lg border border-garis bg-white px-4 py-2 text-sm font-medium text-tinta hover:bg-latar-2 dark:border-garis-gelap dark:bg-gelap-2 dark:text-teks-gelap dark:hover:bg-gelap">Batal
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
