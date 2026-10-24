{{--
    Toast ringan untuk pesan singkat: gagal login, isian kosong, atau sukses.

    Kenapa toast, bukan kotak di dalam kartu: kotak galat di atas form membuat
    kartu login memanjang dan menggeser kolom isian. Toast melayang di atas,
    tidak mengubah tata letak.

    Pesan dari server (@error / session) dikirim lewat data-toast pada wadah,
    dibaca skrip di bawah. Pesan dari sisi klien (isian kosong) dibuat oleh
    window.toast() yang sama. Tanpa pustaka.

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
@endphp

<div id="wadah-toast" aria-live="polite" aria-atomic="false"
     data-toast="{{ json_encode($toastAwal) }}"
     class="pointer-events-none fixed inset-x-0 top-4 z-50 flex flex-col items-center gap-2 px-4 sm:inset-x-auto sm:right-4 sm:items-end"></div>

<script>
    (function () {
        var wadah = document.getElementById('wadah-toast');
        if (!wadah) return;

        var JENIS = {
            gagal: 'border-[#f3c7c7] bg-[#fdf3f2] text-[#8a1f1f] dark:border-[#7a2a2a] dark:bg-[#241717] dark:text-[#f6c9c9]',
            sukses: 'border-[#c9e6a3] bg-[#f4fbe8] text-[#3f6608] dark:border-[#4c6b1f] dark:bg-[#1a2113] dark:text-[#d3f08a]',
            peringatan: 'border-[#f0dca8] bg-[#fdf8ec] text-[#7a5410] dark:border-[#6b5320] dark:bg-[#221d12] dark:text-[#f2dda0]',
        };

        // Penjaga spam: lima toast dalam satu rentang singkat dianggap
        // percobaan beruntun (klik kirim berkali-kali). Setelah itu toast
        // biasa ditahan dan hanya muncul satu peringatan, sampai jeda lewat.
        var MAKS = 5;
        var RENTANG = 4000;
        var JEDA = 6000;
        var hitung = 0;
        var awalRentang = 0;
        var terkunci = false;

        function sisip(pesan, jenis) {
            var el = document.createElement('div');
            el.className = 'toast-masuk pointer-events-auto w-full max-w-sm rounded-lg border px-4 py-3 text-sm font-medium shadow-sm '
                + (JENIS[jenis] || JENIS.gagal);
            el.setAttribute('role', jenis === 'sukses' ? 'status' : 'alert');
            el.textContent = pesan;
            wadah.appendChild(el);
            setTimeout(function () {
                el.classList.add('toast-keluar');
                setTimeout(function () { el.remove(); }, 200);
            }, 4000);
            return el;
        }

        function buat(pesan, jenis) {
            var kini = Date.now();
            if (kini - awalRentang > RENTANG) {
                awalRentang = kini;
                hitung = 0;
            }
            if (terkunci) return null;
            hitung += 1;
            if (hitung >= MAKS) {
                terkunci = true;
                setTimeout(function () {
                    terkunci = false;
                    hitung = 0;
                }, JEDA);
                return sisip('Terlalu banyak percobaan. Tunggu sebentar sebelum mencoba lagi.', 'peringatan');
            }
            return sisip(pesan, jenis);
        }

        window.toast = buat;

        try {
            JSON.parse(wadah.dataset.toast || '[]').forEach(function (t) {
                buat(t.pesan, t.jenis);
            });
        } catch (e) { /* data rusak: tidak ada toast, form tetap bisa dipakai. */ }

        // Form bertanda data-toast: cek kolom wajib sendiri, lalu tampilkan
        // toast alih-alih gelembung bawaan peramban.
        // Dipasang setelah DOM siap: partial ini disisipkan di <header>,
        // sedangkan form-nya ada di <main>, jadi belum ada saat skrip jalan.
        function pasangForm() {
            document.querySelectorAll('form[data-toast]').forEach(function (form) {
                form.setAttribute('novalidate', '');
                form.addEventListener('submit', function (e) {
                    var kurang = [];
                    form.querySelectorAll('[required]').forEach(function (kolom) {
                        if (!kolom.value.trim()) {
                            kurang.push(kolom.dataset.label || kolom.name);
                        }
                    });
                    if (kurang.length) {
                        e.preventDefault();
                        buat(kurang.length === 1
                            ? 'Kolom ' + kurang[0] + ' wajib diisi.'
                            : 'Kolom ' + kurang.join(' dan ') + ' wajib diisi.', 'gagal');
                    }
                });
            });
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', pasangForm);
        } else {
            pasangForm();
        }
    })();
</script>
