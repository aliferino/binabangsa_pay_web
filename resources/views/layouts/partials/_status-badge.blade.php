{{--
    Lencana status tagihan. Dipakai berulang di banyak tabel.

    Warna sengaja dibedakan supaya keadaan tagihan langsung terbaca:
      lunas        hijau
      belum_bayar  kuning atau oranye
      tunggakan    merah

    Kolom status di tabel hanya berisi belum_bayar atau lunas. Tunggakan
    dihitung dari tanggal jatuh tempo, jadi kirim $tagihan kalau mau lencana
    ikut menandai yang sudah lewat tempo. Cukup $status kalau tidak perlu.
--}}
@php
    $peta = [
        'lunas' => ['label' => 'Lunas', 'kelas' => 'bg-emerald-100 text-emerald-800'],
        'belum_bayar' => ['label' => 'Belum bayar', 'kelas' => 'bg-amber-100 text-amber-800'],
        'tunggakan' => ['label' => 'Tunggakan', 'kelas' => 'bg-red-100 text-red-800'],
    ];

    $status = $status ?? $tagihan?->status;

    // Tagihan terbuka yang sudah lewat jatuh tempo ditampilkan sebagai tunggakan.
    if ($status === 'belum_bayar' && isset($tagihan) && $tagihan->tanggal_jatuh_tempo?->isPast()) {
        $status = 'tunggakan';
    }

    $data = $peta[$status] ?? ['label' => $status, 'kelas' => 'bg-slate-100 text-slate-700'];
@endphp

<span class="inline-flex whitespace-nowrap rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $data['kelas'] }}">
    {{ $data['label'] }}
</span>