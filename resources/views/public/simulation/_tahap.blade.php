{{--
    Penunjuk empat tahap pembayaran. Dipakai halaman detail simulasi dan
    halaman bukti pembayaran, supaya urutannya cuma ditulis di satu tempat.

    Variabel:
    - $tahap      : tahap yang sedang berjalan, salah satu nilai MockGatewayService
    - $tahapDaftar: urutan tahap dari controller

    Tahap simulasi dan berhasil tidak pernah dikirim terpisah oleh controller:
    begitu gateway mencatat permintaan, tahapnya sudah di virtual account, dan
    yang tersisa adalah tombol bayar lalu callback. Karena itu peta di bawah
    ini yang menerjemahkan tahap menjadi posisi di daftar.
--}}
@php
    $urut = $tahapDaftar ?? [
        'generate',
        'gateway',
        'virtual_account',
        'simulasi',
    ];

    $keterangan = [
        'generate' => ['Generate Payment', 'Permintaan pembayaran dibuat untuk tagihan siswa.'],
        'gateway' => ['Payment Gateway', 'Gateway lokal menerima permintaan dan memberi referensi.'],
        'virtual_account' => ['Virtual Account', 'Nomor VA dicocokkan dengan tagihan siswa.'],
        'simulasi' => ['Simulasi Pembayaran', 'Pembayaran dijalankan, menunggu callback masuk.'],
    ];

    // Posisi yang sudah dilewati. Tahap virtual_account berarti tiga tahap
    // pertama selesai dan tahap simulasi sedang menunggu dijalankan.
    $posisi = match ($tahap ?? 'generate') {
        'berhasil' => count($urut),
        'virtual_account' => 3,
        default => 1,
    };

    $selesai = $tahap === 'berhasil';
@endphp

<ol class="space-y-2.5" role="list" aria-label="Alur pembayaran">

    @foreach ($urut as $i => $nama)

        @php
            $nomor = $i + 1;
            $sudah = $selesai || $nomor <= $posisi;
            $sekarang = ! $selesai && $nomor === $posisi;
        @endphp

        <li class="flex items-start gap-3">

            <span class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full border text-[11px] font-bold
                         {{ $sudah
                             ? 'border-transparent bg-neon text-tinta'
                             : 'border-garis text-redup dark:border-garis-gelap dark:text-redup-gelap' }}"
                  aria-hidden="true">{{ $nomor }}</span>

            <div class="min-w-0">
                <p class="text-xs font-semibold {{ $sudah ? 'text-tinta dark:text-teks-gelap' : 'text-redup dark:text-redup-gelap' }}">
                    {{ $keterangan[$nama][0] ?? $nama }}
                    @if ($sekarang)
                        <span class="ml-1 rounded-full bg-latar-2 px-2 py-0.5 text-[10px] font-medium text-redup dark:bg-gelap dark:text-redup-gelap">sekarang</span>
                    @endif
                </p>
                <p class="mt-0.5 text-[11px] leading-relaxed text-redup dark:text-redup-gelap">{{ $keterangan[$nama][1] ?? '' }}</p>
            </div>

        </li>

    @endforeach

    {{-- Tahap terakhir hanya muncul setelah callback masuk, karena status lunas
         tidak sah kalau bukan dari callback. --}}
    <li class="flex items-start gap-3">

        <span class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full border text-[11px] font-bold
                     {{ $selesai
                         ? 'border-transparent bg-neon text-tinta'
                         : 'border-garis text-redup dark:border-garis-gelap dark:text-redup-gelap' }}"
              aria-hidden="true">5</span>

        <div class="min-w-0">
            <p class="text-xs font-semibold {{ $selesai ? 'text-tinta dark:text-teks-gelap' : 'text-redup dark:text-redup-gelap' }}">Payment Berhasil</p>
            <p class="mt-0.5 text-[11px] leading-relaxed text-redup dark:text-redup-gelap">Callback diterima, tagihan berubah lunas.</p>
        </div>

    </li>

</ol>
