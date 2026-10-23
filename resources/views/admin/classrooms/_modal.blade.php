{{--
    Modal tambah dan ubah kelas. Satu modal dipakai untuk dua keperluan.
    Tombol Tambah membuka modal dengan form kosong, tombol Ubah mengisi form
    dari atribut data pada tombolnya. Scriptnya ada di layouts.partials._modal-script.
--}}
<div id="modal-kelas" class="fixed inset-0 z-40 hidden" data-modal
     data-judul-tambah="Tambah Kelas"
     data-judul-ubah="Ubah Kelas">
    <div class="absolute inset-0 bg-slate-900/50 dark:bg-black/70" data-modal-tutup></div>

    <div class="relative mx-auto mt-10 w-full max-w-2xl px-4 pb-10">
        <div class="overflow-hidden rounded-xl border border-garis bg-white shadow-lg dark:border-garis-gelap dark:bg-gelap-2 dark:border-garis-gelap dark:bg-gelap-2">

            <div class="flex items-center justify-between border-b border-garis px-5 py-4 dark:border-garis-gelap">
                <h2 class="text-sm font-semibold text-tinta dark:text-teks-gelap" data-modal-judul>Tambah Kelas</h2>
                <button type="button" data-modal-tutup
                        class="rounded-md border border-garis px-2.5 py-1 text-xs font-medium text-tinta hover:bg-latar-2 dark:border-garis-gelap dark:text-teks-gelap dark:hover:bg-gelap">
                    Tutup
                </button>
            </div>

            <form method="POST" action="{{ route('kelas.store') }}" data-form-modal
                  data-url-store="{{ route('kelas.store') }}"
                  data-url-update="{{ route('kelas.update', ['kelas' => '__ID__']) }}">
                @csrf
                <input type="hidden" name="_method" value="POST" data-metode>
                <input type="hidden" name="_mode" value="{{ old('_mode', 'tambah') }}">
                <input type="hidden" name="_id" value="{{ old('_id') }}">
                <input type="hidden" name="_modal" value="modal-kelas">

                <div class="max-h-[70vh] overflow-y-auto px-5 py-4">
                    <div class="space-y-4">

                        <div>
                            <label for="nama_kelas" class="mb-1 block text-sm font-medium text-redup dark:text-redup-gelap">
                                Nama kelas <span class="text-red-600 dark:text-red-400">*</span>
                            </label>
                            <input type="text" name="nama_kelas" id="nama_kelas"
                                   value="{{ old('nama_kelas') }}" required placeholder="Contoh: XI RPL 1"
                                   class="w-full rounded-lg border border-garis px-3 py-2 text-sm text-tinta shadow-sm focus:border-neon-teks focus:outline-none focus:ring-2 focus:ring-neon/40 dark:border-redup dark:bg-gelap dark:text-teks-gelap dark:placeholder:text-redup-gelap">
                            @include('layouts.partials._error', ['nama' => 'nama_kelas'])
                        </div>

                        <div>
                            <label for="tingkat" class="mb-1 block text-sm font-medium text-redup dark:text-redup-gelap">
                                Tingkat <span class="text-red-600 dark:text-red-400">*</span>
                            </label>
                            <input type="text" name="tingkat" id="tingkat"
                                   value="{{ old('tingkat') }}" required placeholder="Contoh: XI"
                                   class="w-full rounded-lg border border-garis px-3 py-2 text-sm text-tinta shadow-sm focus:border-neon-teks focus:outline-none focus:ring-2 focus:ring-neon/40 dark:border-redup dark:bg-gelap dark:text-teks-gelap dark:placeholder:text-redup-gelap">
                            @include('layouts.partials._error', ['nama' => 'tingkat'])
                        </div>

                        <div>
                            <label for="id_jurusan" class="mb-1 block text-sm font-medium text-redup dark:text-redup-gelap">
                                Jurusan <span class="text-red-600 dark:text-red-400">*</span>
                            </label>
                            <select name="id_jurusan" id="id_jurusan" required
                                    class="w-full rounded-lg border border-garis bg-white px-3 py-2 text-sm text-tinta shadow-sm focus:border-neon-teks focus:outline-none focus:ring-2 focus:ring-neon/40 dark:border-redup dark:bg-gelap dark:text-teks-gelap dark:placeholder:text-redup-gelap">
                                <option value="">Pilih jurusan</option>
                                @foreach ($daftar_jurusan as $jurusan)
                                    <option value="{{ $jurusan->id_jurusan }}" @selected(old('id_jurusan') == $jurusan->id_jurusan)>
                                        {{ $jurusan->nama_jurusan }}
                                    </option>
                                @endforeach
                            </select>
                            @include('layouts.partials._error', ['nama' => 'id_jurusan'])
                        </div>

                        <div>
                            <label for="id_tahun_ajaran" class="mb-1 block text-sm font-medium text-redup dark:text-redup-gelap">
                                Tahun ajaran <span class="text-red-600 dark:text-red-400">*</span>
                            </label>
                            <select name="id_tahun_ajaran" id="id_tahun_ajaran" required
                                    class="w-full rounded-lg border border-garis bg-white px-3 py-2 text-sm text-tinta shadow-sm focus:border-neon-teks focus:outline-none focus:ring-2 focus:ring-neon/40 dark:border-redup dark:bg-gelap dark:text-teks-gelap dark:placeholder:text-redup-gelap">
                                <option value="">Pilih tahun ajaran</option>
                                @foreach ($daftar_tahun_ajaran as $tahun)
                                    <option value="{{ $tahun->id_tahun_ajaran }}" @selected(old('id_tahun_ajaran') == $tahun->id_tahun_ajaran)>
                                        {{ $tahun->tahun_ajaran }}
                                    </option>
                                @endforeach
                            </select>
                            @include('layouts.partials._error', ['nama' => 'id_tahun_ajaran'])
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
