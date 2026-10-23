{{-- Halaman galat 503. Isi teksnya saja, kerangkanya milik _layout. --}}
@include('errors._layout', [
    'kode' => '503',
    'judul' => 'Layanan sedang diperbaiki',
    'pesan' => 'Sistem pembayaran sedang tidak tersedia, biasanya karena pemeliharaan terjadwal.',
    'saran' => 'Coba lagi nanti. Untuk pembayaran yang mendesak, bayar langsung di teller sekolah.',
])
