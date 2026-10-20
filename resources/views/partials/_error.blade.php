{{--
    Pesan error untuk satu kolom isian.
    Dipakai di bawah setiap input supaya pengguna tahu kolom mana yang salah.

    Cara pakai:
      @include('partials.error', ['nama' => 'nama_jurusan'])
--}}
@error($nama)
    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
@enderror
