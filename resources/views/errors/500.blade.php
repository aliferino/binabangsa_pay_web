{{-- Halaman galat 500. Isi teksnya saja, kerangkanya milik _layout. --}}
@include('errors._layout', [
    'kode' => '500',
    'judul' => 'Terjadi gangguan di server',
    'pesan' => 'Ada kesalahan di sisi kami saat memproses permintaan Anda. Data yang sudah tersimpan tidak terpengaruh.',
    'saran' => 'Coba muat ulang beberapa saat lagi. Kalau tetap gagal, laporkan ke admin sekolah beserta waktu kejadiannya.',
])
