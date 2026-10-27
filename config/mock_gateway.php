<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Mock Gateway
    |--------------------------------------------------------------------------
    |
    | Kunci ini BUKAN kredensial asli, hanya penanda tanda tangan gateway
    | tiruan lokal. Tidak ada permintaan ke jaringan luar, jadi nilainya boleh
    | terlihat. Ganti lewat .env kalau perlu, tetapi jangan pakai kunci
    | produksi di sini.
    |
    */

    'secret' => env('MOCK_GATEWAY_SECRET', 'mock-gateway-lokal-untuk-ukk'),

    'base_url' => env('MOCK_GATEWAY_BASE_URL', 'http://127.0.0.1:8000'),

    'timeout' => 5,

    /*
    | Sakelar halaman simulasi publik. Dulu penjaganya membaca
    | MIDTRANS_IS_PRODUCTION, jadi halaman simulasi masih ikut bergantung pada
    | pengaturan Midtrans. Sekarang penjaganya milik mock gateway sendiri,
    | supaya jalur simulasi benar benar lepas dari Midtrans dan Xendit.
    | Setel ke false kalau halaman simulasi harus dimatikan di produksi.
    */
    'simulasi_aktif' => env('MOCK_GATEWAY_SIMULASI_AKTIF', true),

];
