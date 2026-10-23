{{-- Halaman galat 401. Isi teksnya saja, kerangkanya milik _layout. --}}
@include('errors._layout', [
    'kode' => '401',
    'judul' => 'Tidak berwenang',
    'pesan' => 'Halaman ini perlu login dulu. Sesi Anda mungkin sudah berakhir karena dibiarkan terlalu lama.',
    'saran' => 'Masuk lagi lewat halaman login, lalu ulangi langkah tadi.',
])
