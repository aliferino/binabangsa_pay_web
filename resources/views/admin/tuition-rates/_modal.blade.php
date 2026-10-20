{{--
    Modal tambah dan ubah tarif SPP. Satu modal dipakai untuk dua keperluan.
    Tombol Tambah membuka modal dengan form kosong, tombol Ubah mengisi form
    dari atribut data pada tombolnya. Scriptnya ada di partials._modal-script.
--}}
<div id="modal-tarif-spp" class="fixed inset-0 z-40 hidden" data-modal
     data-judul-tambah="Tambah Tarif SPP"
     data-judul-ubah="Ubah Tarif SPP">
    <div class="absolute inset-0 bg-slate-900/50" data-modal-tutup></div>

    <div class="relative mx-auto mt-10 w-full max-w-2xl px-4 pb-10">
        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-lg">

            <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
                <h2 class="text-sm font-semibold text-slate-800" data-modal-judul>Tambah Tarif SPP</h2>
                <button type="button" data-modal-tutup
                        class="rounded-md border border-slate-300 px-2.5 py-1 text-xs font-medium text-slate-700 hover:bg-slate-100">
                    Tutup
                </button>
            </div>

            <form method="POST" action="{{ route('tarif-spp.store') }}" data-form-modal
                  data-url-store="{{ route('tarif-spp.store') }}"
                  data-url-update="{{ route('tarif-spp.update', ['tarifSpp' => '__ID__']) }}">
                @csrf
                <input type="hidden" name="_method" value="POST" data-metode>
                <input type="hidden" name="_mode" value="{{ old('_mode', 'tambah') }}">
                <input type="hidden" name="_id" value="{{ old('_id') }}">
                <input type="hidden" name="_modal" value="modal-tarif-spp">

                <div class="max-h-[70vh] overflow-y-auto px-5 py-4">
                    <div class="space-y-4">

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

                        <div>
                            <label for="nominal" class="mb-1 block text-sm font-medium text-slate-700">
                                Nominal <span class="text-red-600">*</span>
                            </label>
                            <input type="number" name="nominal" id="nominal" min="1" step="1000"
                                   value="{{ old('nominal') }}" required placeholder="Contoh: 350000"
                                   class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-teal-600 focus:outline-none focus:ring-2 focus:ring-teal-100">
                            @include('partials._error', ['nama' => 'nominal'])
                            <p class="mt-1 text-xs text-slate-500">Isi angka saja, tanpa titik atau tanda rupiah.</p>
                        </div>

                        <div>
                            <label for="keterangan" class="mb-1 block text-sm font-medium text-slate-700">Keterangan</label>
                            <textarea name="keterangan" id="keterangan" rows="3"
                                      class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-teal-600 focus:outline-none focus:ring-2 focus:ring-teal-100">{{ old('keterangan') }}</textarea>
                            @include('partials._error', ['nama' => 'keterangan'])
                        </div>

                        <div>
                            <label for="status" class="mb-1 block text-sm font-medium text-slate-700">
                                Status <span class="text-red-600">*</span>
                            </label>
                            <select name="status" id="status" required
                                    class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-teal-600 focus:outline-none focus:ring-2 focus:ring-teal-100">
                                <option value="aktif" @selected(old('status') === 'aktif')>Aktif</option>
                                <option value="nonaktif" @selected(old('status') === 'nonaktif')>Nonaktif</option>
                            </select>
                            @include('partials._error', ['nama' => 'status'])
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
