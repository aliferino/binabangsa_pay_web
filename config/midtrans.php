<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Midtrans
    |--------------------------------------------------------------------------
    |
    | Server key diisi lewat .env dan tidak pernah ditulis di kode. Base URL
    | ditentukan dari saklar produksi, bukan dari nilai terpisah, supaya tidak
    | mungkin salah pasang kunci sandbox ke alamat produksi.
    |
    */

    'server_key' => env('MIDTRANS_SERVER_KEY'),

    'client_key' => env('MIDTRANS_CLIENT_KEY'),

    'is_production' => (bool) env('MIDTRANS_IS_PRODUCTION', false),

    'base_url' => env('MIDTRANS_IS_PRODUCTION', false)
        ? 'https://api.midtrans.com'
        : 'https://api.sandbox.midtrans.com',

];