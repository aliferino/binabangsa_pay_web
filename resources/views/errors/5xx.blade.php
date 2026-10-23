{{-- Halaman cadangan untuk kode 5xx yang tidak punya view sendiri. --}}
@include('errors._layout', [
    'kode' => (string) ($exception->getStatusCode() ?? 500),
    'judul' => 'Terjadi gangguan di server',
    'pesan' => 'Ada kesalahan di sisi kami saat memproses permintaan Anda.',
    'saran' => 'Coba muat ulang beberapa saat lagi. Kalau tetap gagal, laporkan ke admin sekolah.',
])
