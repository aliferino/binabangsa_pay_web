{{-- Halaman cadangan untuk kode 4xx yang tidak punya view sendiri. --}}
@include('errors._layout', [
    'kode' => (string) ($exception->getStatusCode() ?? 400),
    'judul' => 'Permintaan tidak bisa diproses',
    'pesan' => 'Permintaan Anda tidak bisa dilayani. Coba ulangi dari halaman sebelumnya.',
    'saran' => 'Kembali ke beranda, lalu ulangi langkah tadi lewat menu.',
])
