{{-- Halaman galat 419. Isi teksnya saja, kerangkanya milik _layout. --}}
@include('errors._layout', [
    'kode' => '419',
    'judul' => 'Sesi kedaluwarsa',
    'pesan' => 'Halaman ini terlalu lama dibiarkan terbuka, jadi token keamanannya sudah tidak berlaku.',
    'saran' => 'Muat ulang halaman, lalu isi formulirnya lagi dari awal.',
])
