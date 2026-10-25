{{--
    Form siswa, dipakai bersama oleh halaman tambah dan ubah.

    Variabel yang dipakai:
      $daftar_kelas, $daftar_tahun_ajaran, $daftar_akun
      $siswa   diisi kalau mode ubah, bernilai null kalau mode tambah

    Catatan penting soal daftar akun: controller hanya mengirim akun siswa yang
    belum dipakai siswa lain. Pada mode ubah, akun yang sedang tertaut ke siswa
    ini otomatis ikut tersaring keluar. Karena itu akun tersebut ditambahkan
    kembali di sini supaya pilihannya tidak hilang saat menyimpan.
--}}

@php
    $ubah = isset($siswa) && $siswa !== null;
    $nilai = fn (string $kolom, $bawaan = null) => old($kolom, $ubah ? $siswa->{$kolom} : $bawaan);

    // Akun yang sedang tertaut, kalau ada dan belum ada di daftar pilihan.
    $akunTertaut = $ubah ? $siswa->user : null;
    $akunPerluDitambah = $akunTertaut !== null
        && ! $daftar_akun->contains('id_user', $akunTertaut->id_user);
@endphp

<div class="space-y-6">

    {{-- Bagian 1: identitas siswa. --}}
    <div class="rounded-xl border border-garis dark:border-garis-gelap bg-white dark:bg-gelap-2 p-5 shadow-sm">
        <h2 class="text-sm font-semibold text-tinta dark:text-teks-gelap">Identitas siswa</h2>

        <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">

            <div>
                <label for="nis" class="mb-1 block text-sm font-medium text-tinta dark:text-teks-gelap">
                    NIS <span class="text-red-600">*</span>
                </label>
                <input type="text" name="nis" id="nis" inputmode="numeric" maxlength="5" pattern="[0-9]{5}" title="NIS harus 5 angka" autocomplete="off" value="{{ $nilai('nis') }}" required
                       class="w-full rounded-lg border border-garis px-3 py-2 text-sm text-tinta shadow-sm focus:border-neon-teks focus:outline-none focus:ring-2 focus:ring-neon/40 dark:border-redup dark:bg-gelap dark:text-teks-gelap dark:placeholder:text-redup-gelap">
                @include('layouts.partials._error', ['nama' => 'nis'])
            </div>

            <div>
                <label for="nisn" class="mb-1 block text-sm font-medium text-tinta dark:text-teks-gelap">
                    NISN <span class="text-red-600">*</span>
                </label>
                <input type="text" name="nisn" id="nisn" inputmode="numeric" maxlength="10" pattern="[0-9]{10}" title="NISN harus 10 angka" autocomplete="off" value="{{ $nilai('nisn') }}" required
                       class="w-full rounded-lg border border-garis px-3 py-2 text-sm text-tinta shadow-sm focus:border-neon-teks focus:outline-none focus:ring-2 focus:ring-neon/40 dark:border-redup dark:bg-gelap dark:text-teks-gelap dark:placeholder:text-redup-gelap">
                @include('layouts.partials._error', ['nama' => 'nisn'])
            </div>

            <div class="sm:col-span-2">
                <label for="nama" class="mb-1 block text-sm font-medium text-tinta dark:text-teks-gelap">
                    Nama lengkap <span class="text-red-600">*</span>
                </label>
                <input type="text" name="nama" id="nama" maxlength="255" autocomplete="off" value="{{ $nilai('nama') }}" required
                       class="w-full rounded-lg border border-garis px-3 py-2 text-sm text-tinta shadow-sm focus:border-neon-teks focus:outline-none focus:ring-2 focus:ring-neon/40 dark:border-redup dark:bg-gelap dark:text-teks-gelap dark:placeholder:text-redup-gelap">
                @include('layouts.partials._error', ['nama' => 'nama'])
            </div>

            <div>
                <label for="jenis_kelamin" class="mb-1 block text-sm font-medium text-tinta dark:text-teks-gelap">
                    Jenis kelamin <span class="text-red-600">*</span>
                </label>
                <select name="jenis_kelamin" id="jenis_kelamin" required
                        class="w-full rounded-lg border border-garis bg-white px-3 py-2 text-sm text-tinta shadow-sm focus:border-neon-teks focus:outline-none focus:ring-2 focus:ring-neon/40 dark:border-redup dark:bg-gelap dark:text-teks-gelap">
                    <option value="">Pilih jenis kelamin</option>
                    <option value="L" @selected($nilai('jenis_kelamin') === 'L')>Laki-laki</option>
                    <option value="P" @selected($nilai('jenis_kelamin') === 'P')>Perempuan</option>
                </select>
                @include('layouts.partials._error', ['nama' => 'jenis_kelamin'])
            </div>

            <div>
                <label for="status" class="mb-1 block text-sm font-medium text-tinta dark:text-teks-gelap">
                    Status <span class="text-red-600">*</span>
                </label>
                <select name="status" id="status" required
                        class="w-full rounded-lg border border-garis bg-white px-3 py-2 text-sm text-tinta shadow-sm focus:border-neon-teks focus:outline-none focus:ring-2 focus:ring-neon/40 dark:border-redup dark:bg-gelap dark:text-teks-gelap">
                    <option value="aktif" @selected($nilai('status') === 'aktif')>Aktif</option>
                    <option value="lulus" @selected($nilai('status') === 'lulus')>Lulus</option>
                    <option value="keluar" @selected($nilai('status') === 'keluar')>Keluar</option>
                </select>
                @include('layouts.partials._error', ['nama' => 'status'])
            </div>

            <div>
                <label for="tempat_lahir" class="mb-1 block text-sm font-medium text-tinta dark:text-teks-gelap">
                    Tempat lahir <span class="text-red-600">*</span>
                </label>
                <input type="text" name="tempat_lahir" id="tempat_lahir"
                       value="{{ $nilai('tempat_lahir') }}" required
                       class="w-full rounded-lg border border-garis px-3 py-2 text-sm text-tinta shadow-sm focus:border-neon-teks focus:outline-none focus:ring-2 focus:ring-neon/40 dark:border-redup dark:bg-gelap dark:text-teks-gelap dark:placeholder:text-redup-gelap">
                @include('layouts.partials._error', ['nama' => 'tempat_lahir'])
            </div>

            <div>
                <label for="tanggal_lahir" class="mb-1 block text-sm font-medium text-tinta dark:text-teks-gelap">
                    Tanggal lahir <span class="text-red-600">*</span>
                </label>
                <input type="date" name="tanggal_lahir" id="tanggal_lahir" required
                       value="{{ old('tanggal_lahir', $ubah ? $siswa->tanggal_lahir?->format('Y-m-d') : '') }}"
                       class="w-full rounded-lg border border-garis px-3 py-2 text-sm text-tinta shadow-sm focus:border-neon-teks focus:outline-none focus:ring-2 focus:ring-neon/40 dark:border-redup dark:bg-gelap dark:text-teks-gelap dark:placeholder:text-redup-gelap">
                @include('layouts.partials._error', ['nama' => 'tanggal_lahir'])
            </div>

            <div class="sm:col-span-2">
                <label for="alamat" class="mb-1 block text-sm font-medium text-tinta dark:text-teks-gelap">
                    Alamat <span class="text-red-600">*</span>
                </label>
                <textarea name="alamat" id="alamat" rows="3" required
                          class="w-full rounded-lg border border-garis px-3 py-2 text-sm text-tinta shadow-sm focus:border-neon-teks focus:outline-none focus:ring-2 focus:ring-neon/40 dark:border-redup dark:bg-gelap dark:text-teks-gelap dark:placeholder:text-redup-gelap">{{ $nilai('alamat') }}</textarea>
                @include('layouts.partials._error', ['nama' => 'alamat'])
            </div>

            <div>
                <label for="no_hp" class="mb-1 block text-sm font-medium text-tinta dark:text-teks-gelap">Nomor HP</label>
                <input type="text" name="no_hp" id="no_hp" inputmode="numeric" maxlength="15" pattern="[0-9]{10,15}" title="Nomor HP 10 sampai 15 angka" autocomplete="off" value="{{ $nilai('no_hp') }}"
                       class="w-full rounded-lg border border-garis px-3 py-2 text-sm text-tinta shadow-sm focus:border-neon-teks focus:outline-none focus:ring-2 focus:ring-neon/40 dark:border-redup dark:bg-gelap dark:text-teks-gelap dark:placeholder:text-redup-gelap">
                @include('layouts.partials._error', ['nama' => 'no_hp'])
            </div>

            <div>
                <label for="nama_orang_tua" class="mb-1 block text-sm font-medium text-tinta dark:text-teks-gelap">Nama orang tua</label>
                <input type="text" name="nama_orang_tua" id="nama_orang_tua"
                       value="{{ $nilai('nama_orang_tua') }}"
                       class="w-full rounded-lg border border-garis px-3 py-2 text-sm text-tinta shadow-sm focus:border-neon-teks focus:outline-none focus:ring-2 focus:ring-neon/40 dark:border-redup dark:bg-gelap dark:text-teks-gelap dark:placeholder:text-redup-gelap">
                @include('layouts.partials._error', ['nama' => 'nama_orang_tua'])
            </div>
        </div>
    </div>

    {{-- Bagian 2: data akademik dan akun. --}}
    <div class="rounded-xl border border-garis dark:border-garis-gelap bg-white dark:bg-gelap-2 p-5 shadow-sm">
        <h2 class="text-sm font-semibold text-tinta dark:text-teks-gelap">Data akademik dan akun</h2>

        <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">

            <div>
                <label for="id_kelas" class="mb-1 block text-sm font-medium text-tinta dark:text-teks-gelap">
                    Kelas <span class="text-red-600">*</span>
                </label>
                <select name="id_kelas" id="id_kelas" required
                        class="w-full rounded-lg border border-garis bg-white px-3 py-2 text-sm text-tinta shadow-sm focus:border-neon-teks focus:outline-none focus:ring-2 focus:ring-neon/40 dark:border-redup dark:bg-gelap dark:text-teks-gelap">
                    <option value="">Pilih kelas</option>
                    @foreach ($daftar_kelas as $kelas)
                        <option value="{{ $kelas->id_kelas }}" @selected($nilai('id_kelas') == $kelas->id_kelas)>
                            {{ $kelas->nama_kelas }}{{ $kelas->jurusan ? ' - '.$kelas->jurusan->nama_jurusan : '' }}
                        </option>
                    @endforeach
                </select>
                @include('layouts.partials._error', ['nama' => 'id_kelas'])
            </div>

            <div>
                <label for="id_tahun_ajaran" class="mb-1 block text-sm font-medium text-tinta dark:text-teks-gelap">
                    Tahun ajaran <span class="text-red-600">*</span>
                </label>
                <select name="id_tahun_ajaran" id="id_tahun_ajaran" required
                        class="w-full rounded-lg border border-garis bg-white px-3 py-2 text-sm text-tinta shadow-sm focus:border-neon-teks focus:outline-none focus:ring-2 focus:ring-neon/40 dark:border-redup dark:bg-gelap dark:text-teks-gelap">
                    <option value="">Pilih tahun ajaran</option>
                    @foreach ($daftar_tahun_ajaran as $tahun)
                        <option value="{{ $tahun->id_tahun_ajaran }}" @selected($nilai('id_tahun_ajaran') == $tahun->id_tahun_ajaran)>
                            {{ $tahun->tahun_ajaran }}
                        </option>
                    @endforeach
                </select>
                @include('layouts.partials._error', ['nama' => 'id_tahun_ajaran'])
            </div>

            <div class="sm:col-span-2">
                <label for="id_user" class="mb-1 block text-sm font-medium text-tinta dark:text-teks-gelap">Akun login siswa</label>
                <select name="id_user" id="id_user"
                        class="w-full rounded-lg border border-garis bg-white px-3 py-2 text-sm text-tinta shadow-sm focus:border-neon-teks focus:outline-none focus:ring-2 focus:ring-neon/40 dark:border-redup dark:bg-gelap dark:text-teks-gelap">
                    <option value="">Tidak ditautkan</option>

                    {{-- Akun yang sedang tertaut, ditambahkan kembali kalau tersaring keluar. --}}
                    @if ($akunPerluDitambah)
                        <option value="{{ $akunTertaut->id_user }}" @selected($nilai('id_user') == $akunTertaut->id_user)>
                            {{ $akunTertaut->nama }} ({{ $akunTertaut->username }})
                        </option>
                    @endif

                    @foreach ($daftar_akun as $akun)
                        <option value="{{ $akun->id_user }}" @selected($nilai('id_user') == $akun->id_user)>
                            {{ $akun->nama }} ({{ $akun->username }})
                        </option>
                    @endforeach
                </select>
                @include('layouts.partials._error', ['nama' => 'id_user'])
                <p class="mt-1 text-xs text-redup dark:text-redup-gelap">
                    Hanya akun berrole siswa yang belum dipakai siswa lain. Boleh dikosongkan.
                </p>
            </div>
        </div>
    </div>
</div>
