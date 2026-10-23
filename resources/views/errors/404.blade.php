{{-- Halaman galat 404. Isi teksnya saja, kerangkanya milik _layout. --}}
@include('errors._layout', [
    'kode' => '404',
    'judul' => 'Halaman tidak ditemukan',
    'pesan' => 'Alamat yang Anda buka tidak ada. Bisa jadi tautannya salah ketik, atau halamannya sudah dipindahkan.',
    'saran' => 'Periksa kembali alamatnya, atau kembali ke beranda lalu cari lewat menu.',
])
