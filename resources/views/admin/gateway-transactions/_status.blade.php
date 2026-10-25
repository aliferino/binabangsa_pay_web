{{--
    Lencana status transaksi payment gateway.

    Terpisah dari _status-badge.blade.php karena daftar statusnya berbeda:
    yang ini milik gateway (pending, berhasil, gagal, kedaluwarsa), yang itu
    milik tagihan SPP (lunas, belum bayar, tunggakan). Menggabungkan keduanya
    akan membuat satu partial menebak-nebak konteks.
--}}
@php
    $peta = [
        'pending' => ['label' => 'Pending', 'kelas' => 'bg-amber-100 text-amber-800'],
        'berhasil' => ['label' => 'Berhasil', 'kelas' => 'bg-neon text-tinta'],
        'gagal' => ['label' => 'Gagal', 'kelas' => 'bg-red-100 text-red-800'],
        'kedaluwarsa' => ['label' => 'Kedaluwarsa', 'kelas' => 'bg-latar-2 text-tinta dark:bg-gelap dark:text-teks-gelap'],
    ];

    $data = $peta[$status] ?? ['label' => $status, 'kelas' => 'bg-latar-2 text-tinta dark:bg-gelap dark:text-teks-gelap'];
@endphp

<span class="inline-flex whitespace-nowrap rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $data['kelas'] }}">
    {{ $data['label'] }}
</span>
