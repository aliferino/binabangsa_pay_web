{{-- Halaman galat 403. Isi teksnya saja, kerangkanya milik _layout. --}}
@include('errors._layout', [
    'kode' => '403',
    'judul' => 'Akses ditolak',
    'pesan' => 'Akun Anda tidak punya izin membuka halaman ini. Ini bukan salah Anda, memang hak aksesnya berbeda per peran.',
    'saran' => 'Kembali ke dashboard sesuai peran Anda. Kalau merasa harusnya boleh, hubungi admin sekolah.',
])
