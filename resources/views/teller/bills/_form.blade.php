{{--
    Form tagihan, dipakai bersama oleh halaman tambah dan ubah.

    Variabel yang dipakai:
      $daftar_siswa, $daftar_tarif, $daftar_tahun_ajaran
      $tagihan   diisi kalau mode ubah, bernilai null kalau mode tambah
--}}

@php
    $ubah = isset($tagihan) && $tagihan !== null;
    $nilai = fn (string $kolom, $bawaan = null) => old($kolom, $ubah ? $tagihan->{$kolom} : $bawaan);

    $namaBulan = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
    ];
@endphp

<div class="space-y-6">

    {{-- Bagian 1: siswa dan periode. --}}
    <div class="rounded-xl border border-garis dark:border-garis-gelap bg-white p-5 shadow-sm">
        <h2 class="text-sm font-semibold text-tinta dark:text-teks-gelap">Siswa dan periode</h2>

        <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">

            <div class="sm:col-span-2">
                <label for="id_siswa" class="mb-1 block text-sm font-medium text-tinta dark:text-teks-gelap">
                    Siswa <span class="text-red-600">*</span>
                </label>
                <select name="id_siswa" id="id_siswa" required
                        class="w-full rounded-lg border border-garis bg-white px-3 py-2 text-sm text-tinta shadow-sm focus:border-neon-teks focus:outline-none focus:ring-2 focus:ring-neon/40 dark:border-redup dark:bg-gelap dark:text-teks-gelap">
                    <option value="">Pilih siswa</option>
                    @foreach ($daftar_siswa as $siswa)
                        <option value="{{ $siswa->id_siswa }}" @selected($nilai('id_siswa') == $siswa->id_siswa)>
                            {{ $siswa->nis }} - {{ $siswa->nama }}{{ $siswa->kelas ? ' ('.$siswa->kelas->nama_kelas.')' : '' }}
                        </option>
                    @endforeach
                </select>
                @include('layouts.partials._error', ['nama' => 'id_siswa'])
            </div>

            <div>
                <label for="bulan" class="mb-1 block text-sm font-medium text-tinta dark:text-teks-gelap">
                    Bulan <span class="text-red-600">*</span>
                </label>
                <select name="bulan" id="bulan" required
                        class="w-full rounded-lg border border-garis bg-white px-3 py-2 text-sm text-tinta shadow-sm focus:border-neon-teks focus:outline-none focus:ring-2 focus:ring-neon/40 dark:border-redup dark:bg-gelap dark:text-teks-gelap">
                    <option value="">Pilih bulan</option>
                    @foreach ($namaBulan as $angka => $nama)
                        <option value="{{ $angka }}" @selected($nilai('bulan') == $angka)>{{ $nama }}</option>
                    @endforeach
                </select>
                @include('layouts.partials._error', ['nama' => 'bulan'])
            </div>

            <div>
                <label for="tahun" class="mb-1 block text-sm font-medium text-tinta dark:text-teks-gelap">
                    Tahun <span class="text-red-600">*</span>
                </label>
                <input type="number" name="tahun" id="tahun" min="2000" max="2100"
                       value="{{ $nilai('tahun', date('Y')) }}" required
                       class="w-full rounded-lg border border-garis px-3 py-2 text-sm text-tinta shadow-sm focus:border-neon-teks focus:outline-none focus:ring-2 focus:ring-neon/40 dark:border-redup dark:bg-gelap dark:text-teks-gelap dark:placeholder:text-redup-gelap">
                @include('layouts.partials._error', ['nama' => 'tahun'])
            </div>

            <div>
                <label for="id_tahun_ajaran" class="mb-1 block text-sm font-medium text-tinta dark:text-teks-gelap">
                    Tahun ajaran <span class="text-red-600">*</span>
                </label>
                <select name="id_tahun_ajaran" id="id_tahun_ajaran" required
                        class="w-full rounded-lg border border-garis bg-white px-3 py-2 text-sm text-tinta shadow-sm focus:border-neon-teks focus:outline-none focus:ring-2 focus:ring-neon/40 dark:border-redup dark:bg-gelap dark:text-teks-gelap">
                    <option value="">Pilih tahun ajaran</option>
                    @foreach ($daftar_tahun_ajaran as $tahunAjaran)
                        <option value="{{ $tahunAjaran->id_tahun_ajaran }}" @selected($nilai('id_tahun_ajaran') == $tahunAjaran->id_tahun_ajaran)>
                            {{ $tahunAjaran->tahun_ajaran }}
                        </option>
                    @endforeach
                </select>
                @include('layouts.partials._error', ['nama' => 'id_tahun_ajaran'])
            </div>

            <div>
                <label for="id_tarif" class="mb-1 block text-sm font-medium text-tinta dark:text-teks-gelap">
                    Tarif acuan <span class="text-red-600">*</span>
                </label>
                <select name="id_tarif" id="id_tarif" required
                        class="w-full rounded-lg border border-garis bg-white px-3 py-2 text-sm text-tinta shadow-sm focus:border-neon-teks focus:outline-none focus:ring-2 focus:ring-neon/40 dark:border-redup dark:bg-gelap dark:text-teks-gelap">
                    <option value="">Pilih tarif</option>
                    @foreach ($daftar_tarif as $tarif)
                        <option value="{{ $tarif->id_tarif }}" @selected($nilai('id_tarif') == $tarif->id_tarif)>
                            Rp{{ number_format((float) $tarif->nominal, 0, ',', '.') }}
                            {{ $tarif->tahunAjaran ? '- '.$tarif->tahunAjaran->tahun_ajaran : '' }}
                        </option>
                    @endforeach
                </select>
                @include('layouts.partials._error', ['nama' => 'id_tarif'])
            </div>
        </div>
    </div>

    {{-- Bagian 2: nominal dan status. --}}
    <div class="rounded-xl border border-garis dark:border-garis-gelap bg-white p-5 shadow-sm">
        <h2 class="text-sm font-semibold text-tinta dark:text-teks-gelap">Nominal dan status</h2>

        <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">

            <div>
                <label for="nominal" class="mb-1 block text-sm font-medium text-tinta dark:text-teks-gelap">
                    Nominal <span class="text-red-600">*</span>
                </label>
                <input type="number" name="nominal" id="nominal" min="1" step="1000"
                       value="{{ $nilai('nominal') }}" required placeholder="Contoh: 350000"
                       class="w-full rounded-lg border border-garis px-3 py-2 text-sm text-tinta shadow-sm focus:border-neon-teks focus:outline-none focus:ring-2 focus:ring-neon/40 dark:border-redup dark:bg-gelap dark:text-teks-gelap dark:placeholder:text-redup-gelap">
                @include('layouts.partials._error', ['nama' => 'nominal'])
                <p class="mt-1 text-xs text-redup dark:text-redup-gelap">Isi angka saja, tanpa titik atau tanda rupiah.</p>
            </div>

            <div>
                <label for="tanggal_jatuh_tempo" class="mb-1 block text-sm font-medium text-tinta dark:text-teks-gelap">
                    Tanggal jatuh tempo <span class="text-red-600">*</span>
                </label>
                <input type="date" name="tanggal_jatuh_tempo" id="tanggal_jatuh_tempo" required
                       value="{{ old('tanggal_jatuh_tempo', $ubah ? $tagihan->tanggal_jatuh_tempo?->format('Y-m-d') : '') }}"
                       class="w-full rounded-lg border border-garis px-3 py-2 text-sm text-tinta shadow-sm focus:border-neon-teks focus:outline-none focus:ring-2 focus:ring-neon/40 dark:border-redup dark:bg-gelap dark:text-teks-gelap dark:placeholder:text-redup-gelap">
                @include('layouts.partials._error', ['nama' => 'tanggal_jatuh_tempo'])
            </div>

            <div>
                <label for="status" class="mb-1 block text-sm font-medium text-tinta dark:text-teks-gelap">
                    Status <span class="text-red-600">*</span>
                </label>
                <select name="status" id="status" required
                        class="w-full rounded-lg border border-garis bg-white px-3 py-2 text-sm text-tinta shadow-sm focus:border-neon-teks focus:outline-none focus:ring-2 focus:ring-neon/40 dark:border-redup dark:bg-gelap dark:text-teks-gelap">
                    <option value="belum_bayar" @selected($nilai('status') === 'belum_bayar')>Belum bayar</option>
                    <option value="lunas" @selected($nilai('status') === 'lunas')>Lunas</option>
                    <option value="tunggakan" @selected($nilai('status') === 'tunggakan')>Tunggakan</option>
                </select>
                @include('layouts.partials._error', ['nama' => 'status'])
                <p class="mt-1 text-xs text-redup dark:text-redup-gelap">
                    Satu siswa hanya boleh punya satu tagihan per bulan per tahun.
                </p>
            </div>
        </div>
    </div>
</div>
