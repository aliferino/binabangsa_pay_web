{{--
    Modal tambah dan ubah siswa. Satu modal dipakai untuk dua keperluan.
    Isian form memakai partial admin.students._form supaya susunan kolomnya
    sama persis dengan sebelumnya. Tombol Ubah mengisi form dari atribut data
    pada tombolnya, termasuk akun siswa yang sedang tertaut.
--}}
<div id="modal-siswa" class="fixed inset-0 z-40 hidden" data-modal
     data-judul-tambah="Tambah Siswa"
     data-judul-ubah="Ubah Siswa">
    <div class="absolute inset-0 bg-slate-900/50" data-modal-tutup></div>

    <div class="relative mx-auto mt-10 w-full max-w-3xl px-4 pb-10">
        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-lg">

            <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
                <h2 class="text-sm font-semibold text-slate-800" data-modal-judul>Tambah Siswa</h2>
                <button type="button" data-modal-tutup
                        class="rounded-md border border-slate-300 px-2.5 py-1 text-xs font-medium text-slate-700 hover:bg-slate-100">
                    Tutup
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
