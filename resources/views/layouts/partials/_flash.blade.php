{{--
    Pesan sukses dan gagal.
    Controller memakai ->with('sukses', ...) dan ->with('gagal', ...).
    Pesan validasi ditampilkan di bawah tiap input, bukan di sini, supaya
    pengguna langsung tahu kolom mana yang salah.
--}}

@if (session('sukses'))
    <div class="mb-4 flex items-start gap-3 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800"
         role="status">
        <span class="mt-0.5 font-bold">Berhasil</span>
        <span>{{ session('sukses') }}</span>
    </div>
@endif

@if (session('gagal'))
    <div class="mb-4 flex items-start gap-3 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800"
         role="alert">
        <span class="mt-0.5 font-bold">Gagal</span>
        <span>{{ session('gagal') }}</span>
    </div>
@endif

@if ($errors->any())
    <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800" role="alert">
        <p class="font-bold">Periksa kembali isian berikut:</p>
        <ul class="mt-1 list-inside list-disc">
            @foreach ($errors->all() as $pesan)
                <li>{{ $pesan }}</li>
            @endforeach
        </ul>
    </div>
@endif
