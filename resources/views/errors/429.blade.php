{{-- Halaman galat 429. Isi teksnya saja, kerangkanya milik _layout. --}}
@include('errors._layout', [
    'kode' => '429',
    'judul' => 'Terlalu banyak permintaan',
    'pesan' => 'Anda membuka terlalu banyak halaman dalam waktu singkat. Sistem membatasi supaya tidak kelebihan beban.',
    'saran' => 'Tunggu sekitar satu menit, lalu coba lagi.',
])
