{{--
    Modal tambah dan ubah kelas. Satu modal dipakai untuk dua keperluan.
    Tombol Tambah membuka modal dengan form kosong, tombol Ubah mengisi form
    dari atribut data pada tombolnya. Scriptnya ada di partials._modal-script.
--}}
<div id="modal-kelas" class="fixed inset-0 z-40 hidden" data-modal
     data-judul-tambah="Tambah Kelas"
     data-judul-ubah="Ubah Kelas">
    <div class="absolute inset-0 bg-slate-900/50" data-modal-tutup></div>

    <div class="relative mx-auto mt-10 w-full max-w-2xl px-4 pb-10">
        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-lg">

            <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
                <h2 class="text-sm font-semibold text-slate-800" data-modal-judul>Tambah Kelas</h2>
                <button type="button" data-modal-tutup
                        class="rounded-md border border-slate-300 px-2.5 py-1 text-xs font-medium text-slate-700 hover:bg-slate-100">
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
                            <label for="nama_kelas" class="mb-1 block text-sm font-medium text-slate-700">
                                Nama kelas <span class="text-red-600">*</span>
                            </label>
                            <input type="text" name="nama_kelas" id="nama_kelas"
                                   value="{{ old('nama_kelas') }}" required placeholder="Contoh: XI RPL 1"
                                   class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-teal-600 focus:outline-none focus:ring-2 focus:ring-teal-100">
                            @include('partials._error', ['nama' => 'nama_kelas'])
                        </div>

                        <div>
                            <label for="tingkat" class="mb-1 block text-sm font-medium text-slate-700">
                                Tingkat <span class="text-red-600">*</span>
                            </label>
                            <input type="text" name="tingkat" id="tingkat"
                                   value="{{ old('tingkat') }}" required placeholder="Contoh: XI"
                                   class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-teal-600 focus:outline-none focus:ring-2 focus:ring-teal-100">
                            @include('partials._error', ['nama' => 'tingkat'])
                        </div>

                        <div>
                            <label for="id_jurusan" class="mb-1 block text-sm font-medium text-slate-700">
                                Jurusan <span class="text-red-600">*</span>
                            </label>
                            <select name="id_jurusan" id="id_jurusan" required
                                    class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-teal-600 focus:outline-none focus:ring-2 focus:ring-teal-100">
                                <option value="">Pilih jurusan</option>
                                @foreach ($daftar_jurusan as $jurusan)
                                    <option value="{{ $jurusan->id_jurusan }}" @selected(old('id_jurusan') == $jurusan->id_jurusan)>
                                        {{ $jurusan->nama_jurusan }}
                                    </option>
                                @endforeach
                            </select>
                            @include('partials._error', ['nama' => 'id_jurusan'])
                        </div>

                        <div>
                            <label for="id_tahun_ajaran" class="mb-1 block text-sm font-medium text-slate-700">
                                Tahun ajaran <span class="text-red-600">*</span>
                            </label>
                            <select name="id_tahun_ajaran" id="id_tahun_ajaran" required
                                    class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-teal-600 focus:outline-none focus:ring-2 focus:ring-teal-100">
                                <option value="">Pilih tahun ajaran</option>
                                @foreach ($daftar_tahun_ajaran as $tahun)
                                    <option value="{{ $tahun->id_tahun_ajaran }}" @selected(old('id_tahun_ajaran') == $tahun->id_tahun_ajaran)>
                                        {{ $tahun->tahun_ajaran }}
                                    </option>
                                @endforeach
                            </select>
                            @include('partials._error', ['nama' => 'id_tahun_ajaran'])
                        </div>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-2 border-t border-slate-200 px-5 py-4">
                    <button type="submit"
                            class="rounded-lg bg-teal-700 px-4 py-2 text-sm font-semibold text-white hover:bg-teal-800">
                        Simpan
                    </button>
                    <button type="button" data-modal-tutup
                            class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                        Batal
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
