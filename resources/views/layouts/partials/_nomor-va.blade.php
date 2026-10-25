{{--
    Format nomor Virtual Account jadi kelompok empat digit: 7770 1001 2345.

    Dipakai semua tempat yang menampilkan nomor VA supaya bentuknya seragam.
    Hanya untuk tampilan. Untuk URL atau atribut yang butuh nomor asli, pakai
    nilai mentahnya, jangan hasil partial ini.

    Pemakaian:
        @include('layouts.partials._nomor-va', ['nomor' => $va->account_number])
--}}
@php
    $angka = preg_replace('/[^0-9]/', '', (string) $nomor);
@endphp
{{ $angka === '' ? '-' : trim(chunk_split($angka, 4, ' ')) }}