{{--
    Wadah toast. Isinya dibuat oleh resources/js/app.js.

    Kenapa toast, bukan kotak di dalam kartu: kotak galat di atas form membuat
    kartu login memanjang dan menggeser kolom isian. Toast melayang di atas,
    tidak mengubah tata letak.

    Pesan dari server (session sukses/gagal, galat validasi) dikirim lewat
    data-toast pada wadah ini. Pesan dari sisi klien (isian kosong, konfirmasi)
    dibuat oleh window.toast() yang sama. Tanpa pustaka.

    Pemakaian: @include('layouts.partials._toast')
--}}

@php
    $toastAwal = [];
    foreach ($errors->all() as $pesan) {
        $toastAwal[] = ['pesan' => $pesan, 'jenis' => 'gagal'];
    }
    if (session('gagal')) {
        $toastAwal[] = ['pesan' => session('gagal'), 'jenis' => 'gagal'];
    }
    if (session('sukses')) {
        $toastAwal[] = ['pesan' => session('sukses'), 'jenis' => 'sukses'];
    }
    if (session('peringatan')) {
        $toastAwal[] = ['pesan' => session('peringatan'), 'jenis' => 'peringatan'];
    }
@endphp

<div id="wadah-toast" aria-live="polite" aria-atomic="false"
     data-toast="{{ json_encode($toastAwal) }}"
     class="pointer-events-none fixed inset-x-0 top-4 z-50 flex flex-col items-center gap-2 px-4 sm:inset-x-auto sm:right-4 sm:items-end"></div>