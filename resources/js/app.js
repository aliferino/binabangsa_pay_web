/*
    Perilaku bersama semua halaman: toast, modal, konfirmasi, dan pindah menu
    tanpa muat ulang.

    Semua di sini, bukan di <script> dalam view, karena isi halaman diganti
    lewat fetch. Script inline tidak jalan lagi setelah diganti, dan kalau
    dijalankan ulang pendengarnya menumpuk.
*/
(function () {
    'use strict';

    var JENIS_TOAST = {
        gagal: 'border-[#f3c7c7] bg-[#fdf3f2] text-[#8a1f1f] dark:border-[#7a2a2a] dark:bg-[#241717] dark:text-[#f6c9c9]',
        sukses: 'border-[#c9e6a3] bg-[#f4fbe8] text-[#3f6608] dark:border-[#4c6b1f] dark:bg-[#1a2113] dark:text-[#d3f08a]',
        peringatan: 'border-[#f0dca8] bg-[#fdf8ec] text-[#7a5410] dark:border-[#6b5320] dark:bg-[#221d12] dark:text-[#f2dda0]',
    };

    /* ============================ Toast ============================ */

    // Penjaga spam: lima toast dalam empat detik dianggap klik beruntun.
    var MAKS = 5;
    var RENTANG = 4000;
    var JEDA = 6000;
    var hitung = 0;
    var awalRentang = 0;
    var terkunci = false;

    function sisipToast(pesan, jenis) {
        var wadah = document.getElementById('wadah-toast');

        if (!wadah) {
            return null;
        }

        var el = document.createElement('div');
        el.className = 'toast-masuk pointer-events-auto w-full max-w-sm rounded-lg border px-4 py-3 text-sm font-medium shadow-sm '
            + (JENIS_TOAST[jenis] || JENIS_TOAST.gagal);
        el.setAttribute('role', jenis === 'sukses' ? 'status' : 'alert');
        el.textContent = pesan;
        wadah.appendChild(el);

        setTimeout(function () {
            el.classList.add('toast-keluar');
            setTimeout(function () { el.remove(); }, 200);
        }, 4000);

        return el;
    }

    function toast(pesan, jenis) {
        var kini = Date.now();

        if (kini - awalRentang > RENTANG) {
            awalRentang = kini;
            hitung = 0;
        }

        if (terkunci) {
            return null;
        }

        hitung += 1;

        if (hitung >= MAKS) {
            terkunci = true;
            setTimeout(function () {
                terkunci = false;
                hitung = 0;
            }, JEDA);

            return sisipToast('Terlalu banyak percobaan. Tunggu sebentar sebelum mencoba lagi.', 'peringatan');
        }

        return sisipToast(pesan, jenis);
    }

    window.toast = toast;

    // Pesan dari server (session sukses/gagal, sebagian galat validasi).
    var wadahAwal = document.getElementById('wadah-toast');

    if (wadahAwal) {
        try {
            JSON.parse(wadahAwal.dataset.toast || '[]').forEach(function (t) {
                toast(t.pesan, t.jenis);
            });
        } catch (e) { /* data rusak: tidak ada toast, halaman tetap jalan. */ }
    }

    /* ============================ Modal ============================ */

    function modalDari(id) {
        return id ? document.getElementById(id) : null;
    }

    /*
        Muncul dan hilangnya modal memakai keyframes CSS karena modal
        disembunyikan dengan display:none dan transition tidak berjalan dari
        display:none. Saat menutup, penyembunyian ditahan 150ms supaya
        animasi keluarnya sempat terlihat.
    */
    function bukaModal(modal) {
        clearTimeout(modal._pegangTutup);
        modal.classList.remove('hidden', 'modal-keluar');
        modal.classList.add('modal-masuk');
    }

    function tutupModal(modal) {
        if (!modal || modal.classList.contains('hidden') || modal.classList.contains('modal-keluar')) {
            return;
        }

        modal.classList.remove('modal-masuk');
        modal.classList.add('modal-keluar');
        modal._pegangTutup = setTimeout(function () {
            modal.classList.add('hidden');
            modal.classList.remove('modal-keluar');
        }, 150);
    }

    function bagian(modal) {
        var form = modal.querySelector('[data-form-modal]');

        return {
            form: form,
            judul: modal.querySelector('[data-modal-judul]'),
            metode: modal.querySelector('[data-metode]'),
            mode: form ? form.querySelector('[name="_mode"]') : null,
            id: form ? form.querySelector('[name="_id"]') : null,
        };
    }

    // Sebagian modal punya teks petunjuk yang berbeda antara tambah dan ubah,
    // misalnya keterangan password. Kalau ada, teksnya ditukar.
    function petunjuk(modal, mode) {
        var el = modal.querySelector('[data-modal-petunjuk-' + mode + ']');

        if (el) {
            el.textContent = el.getAttribute('data-modal-petunjuk-' + mode);
        }
    }

    function bukaTambah(modal) {
        var b = bagian(modal);

        /*
            Modal CRUD penuh membawa form lengkap: action store, _method,
            _mode, dan _id. Modal sederhana, misalnya buat Virtual Account,
            cukup membawa form yang action-nya sudah ditulis di Blade. Field
            yang tidak ada dilewati, bukan menggagalkan pembukaan modal.
        */
        if (b.form) {
            b.form.reset();

            var urlStore = b.form.getAttribute('data-url-store');

            if (urlStore) {
                b.form.action = urlStore;
            }

            if (b.metode) {
                b.metode.value = 'POST';
            }

            if (b.mode) {
                b.mode.value = 'tambah';
            }

            if (b.id) {
                b.id.value = '';
            }
        }

        if (b.judul) {
            b.judul.textContent = modal.getAttribute('data-judul-tambah') || b.judul.textContent;
        }

        petunjuk(modal, 'tambah');
        bukaModal(modal);
    }

    function bukaUbah(modal, tombol) {
        if (!modal) {
            return;
        }

        var b = bagian(modal);

        if (!b.form) {
            return;
        }

        var nilaiId = tombol.getAttribute('data-id');

        b.form.reset();
        b.form.action = b.form.getAttribute('data-url-update').replace('__ID__', nilaiId);
        b.metode.value = 'PUT';
        b.mode.value = 'ubah';
        b.id.value = nilaiId;
        b.judul.textContent = modal.getAttribute('data-judul-ubah') || b.judul.textContent;
        petunjuk(modal, 'ubah');

        Array.prototype.forEach.call(b.form.elements, function (el) {
            if (!el.name) {
                return;
            }

            var nilai = tombol.getAttribute('data-' + el.name);

            if (nilai !== null) {
                el.value = nilai;
            }

            // Sebagian pilihan sengaja tidak dikirim controller karena sudah
            // terpakai baris lain, misalnya akun siswa yang sedang tertaut.
            // Kalau nilainya belum ada di daftar, tambahkan supaya tidak hilang.
            if (el.tagName === 'SELECT' && nilai) {
                var ada = Array.prototype.some.call(el.options, function (opt) {
                    return opt.value === nilai;
                });

                if (!ada) {
                    var label = tombol.getAttribute('data-' + el.name + '_label') || nilai;
                    el.add(new Option(label, nilai));
                    el.value = nilai;
                }
            }
        });

        bukaModal(modal);
    }

    /* ======================= Modal konfirmasi ======================= */

    // Semua tindakan yang mengubah atau menghapus data lewat sini, bukan
    // lewat confirm() bawaan peramban.
    var aksiKonfirmasi = null;

    function tanya(pesan, lanjut, jenis) {
        var modal = document.getElementById('modal-konfirmasi');

        if (!modal) {
            if (window.confirm(pesan)) {
                lanjut();
            }

            return;
        }

        modal.querySelector('[data-konfirmasi-pesan]').textContent = pesan;

        var ya = modal.querySelector('[data-konfirmasi-ya]');
        var label = { hapus: 'Ya, hapus', keluar: 'Ya, keluar' };

        ya.textContent = label[jenis] || 'Ya, simpan';
        ya.className = 'tombol-tekan rounded-lg px-4 py-2 text-sm font-semibold '
            + (jenis === 'hapus' || jenis === 'keluar'
                ? 'bg-[#c10007] text-white hover:bg-[#a10006]'
                : 'bg-neon text-tinta hover:bg-neon-tekan');

        aksiKonfirmasi = lanjut;
        bukaModal(modal);
    }

    // Form wajib: cek kolom kosong sendiri (data-toast), lalu minta
    // konfirmasi untuk hapus dan simpan. Ditangkap di fase capture supaya
    // preventDefault berlaku sebelum pengiriman dimulai.
    document.addEventListener('submit', function (peristiwa) {
        var form = peristiwa.target;

        if (!(form instanceof HTMLFormElement)) {
            return;
        }

        if (form.hasAttribute('data-toast')) {
            var kurang = [];

            form.querySelectorAll('[required]').forEach(function (kolom) {
                if (!kolom.value.trim()) {
                    kurang.push(kolom.dataset.label || kolom.name);
                }
            });

            if (kurang.length) {
                peristiwa.preventDefault();
                toast(kurang.length === 1
                    ? 'Kolom ' + kurang[0] + ' wajib diisi.'
                    : 'Kolom ' + kurang.join(' dan ') + ' wajib diisi.', 'gagal');

                return;
            }
        }

        // Sudah disetujui: lanjutkan tanpa ditanya lagi.
        if (form.dataset.disetujui === '1') {
            delete form.dataset.disetujui;

            return;
        }

        var pesanHapus = form.getAttribute('data-konfirmasi');

        if (pesanHapus) {
            peristiwa.preventDefault();
            tanya(pesanHapus, function () {
                form.dataset.disetujui = '1';
                form.submit();
            }, form.getAttribute('data-konfirmasi-jenis') || 'hapus');

            return;
        }

        if (form.hasAttribute('data-form-modal')) {
            var mode = form.querySelector('[name="_mode"]');
            peristiwa.preventDefault();
            tanya(mode && mode.value === 'ubah'
                ? 'Simpan perubahan pada data ini?'
                : 'Simpan data baru ini?', function () {
                form.dataset.disetujui = '1';
                form.submit();
            }, 'simpan');
        }
    }, true);

    var isiHalaman = document.getElementById('isi-halaman');

    function siapkanHalaman() {
        isiHalaman = document.getElementById('isi-halaman');

        if (!isiHalaman) {
            return;
        }

        document.querySelectorAll('form[data-toast]').forEach(function (form) {
            form.setAttribute('novalidate', '');
        });

        // Form gagal divalidasi server: modal dibuka lagi dengan isian lama.
        var modalGagal = modalDari(isiHalaman.getAttribute('data-modal-gagal'));

        if (modalGagal) {
            var bGagal = bagian(modalGagal);
            var modeGagal = isiHalaman.getAttribute('data-mode-gagal');

            if (bGagal.form && modeGagal === 'ubah') {
                bGagal.form.action = bGagal.form.getAttribute('data-url-update')
                    .replace('__ID__', isiHalaman.getAttribute('data-id-gagal'));
                bGagal.metode.value = 'PUT';
                bGagal.judul.textContent = modalGagal.getAttribute('data-judul-ubah')
                    || bGagal.judul.textContent;
            }

            bukaModal(modalGagal);
        }

        // Tautan Ubah di halaman detail mengalihkan ke daftar dengan ?ubah=id.
        var diminta = new URLSearchParams(window.location.search).get('ubah');

        if (diminta) {
            var tombolnya = document.querySelector('[data-modal-buka="ubah"][data-id="' + diminta + '"]');

            if (tombolnya) {
                bukaUbah(modalDari(tombolnya.getAttribute('data-modal-target')), tombolnya);
            }
        }
    }

    document.addEventListener('click', function (peristiwa) {
        var tombolBuka = peristiwa.target.closest('[data-modal-buka]');

        if (tombolBuka) {
            var modal = modalDari(tombolBuka.getAttribute('data-modal-target'));

            if (modal) {
                if (tombolBuka.getAttribute('data-modal-buka') === 'ubah') {
                    bukaUbah(modal, tombolBuka);
                } else {
                    bukaTambah(modal);
                }
            }

            return;
        }

        var tombolYa = peristiwa.target.closest('[data-konfirmasi-ya]');

        if (tombolYa) {
            var lanjut = aksiKonfirmasi;
            aksiKonfirmasi = null;
            tutupModal(document.getElementById('modal-konfirmasi'));

            if (lanjut) {
                lanjut();
            }

            return;
        }

        var tombolTutup = peristiwa.target.closest('[data-modal-tutup]');

        if (tombolTutup) {
            var induk = tombolTutup.closest('[data-modal]');

            if (induk) {
                tutupModal(induk);
            }
        }
    });

    document.addEventListener('keydown', function (peristiwa) {
        if (peristiwa.key !== 'Escape') {
            return;
        }

        document.querySelectorAll('[data-modal]').forEach(tutupModal);
    });

    /* ===================== Pindah menu tanpa muat ulang ===================== */

    /*
        Menu samping diambil lewat fetch, lalu isi halaman dan menu diganti
        di tempat. Halaman yang sudah pernah dibuka disimpan di singgahan,
        dan saat kursor berhenti di tautan menu, halaman tujuan diambil lebih
        dulu supaya klik berikutnya tidak menunggu.
    */
    var singgahan = new Map();

    var berjalan = new Map();

    /*
        Satu URL hanya diambil sekali. Kalau kursor sudah menyentuh menu dan
        permintaannya masih jalan, klik berikutnya menumpang permintaan itu
        alih-alih menembak ulang. Tanpa ini prefetch justru menggandakan
        permintaan dan membuat klik lebih lambat.

        php artisan serve hanya satu proses, jadi permintaan yang menumpuk
        antre satu per satu. Karena itu prefetch hanya dilakukan untuk menu
        yang benar-benar disentuh kursor, bukan seluruh menu sekaligus.
    */
    function ambil(url) {
        if (singgahan.has(url)) {
            return Promise.resolve(singgahan.get(url));
        }

        if (berjalan.has(url)) {
            return berjalan.get(url);
        }

        var janji = fetch(url, {
            headers: { 'X-Navigasi': '1' },
            credentials: 'same-origin',
        }).then(function (respons) {
            if (!respons.ok) {
                throw new Error('HTTP ' + respons.status);
            }

            return respons.text();
        }).then(function (html) {
            singgahan.set(url, html);
            berjalan.delete(url);

            return html;
        }).catch(function (galat) {
            berjalan.delete(url);

            throw galat;
        });

        berjalan.set(url, janji);

        return janji;
    }

    /*
        Menu samping tidak digambar ulang. Dulu seluruh isi #menu-samping
        ditimpa HTML dari server tiap pindah halaman; node tombolnya hancur,
        animasi tekan ikut mati di tengah jalan, dan mata membacanya sebagai
        kedip. Sekarang hanya penanda aktif yang ditukar: kelas warna dan
        aria-current pada tautan yang sudah ada.
    */
    function tandaiMenu(url) {
        var menu = document.getElementById('menu-samping');

        if (!menu) {
            return;
        }

        var tujuan = new URL(url, window.location.href);

        menu.querySelectorAll('a[href]').forEach(function (a) {
            var sama = new URL(a.href, window.location.href).pathname === tujuan.pathname;

            // Kelas aktif diambil dari $kelasAktif di _sidebar.blade.php:
            // bg-neon font-semibold text-tinta shadow-sm, plus varian gelapnya.
            a.classList.toggle('bg-neon', sama);
            a.classList.toggle('font-semibold', sama);
            a.classList.toggle('text-tinta', sama);
            a.classList.toggle('shadow-sm', sama);
            a.classList.toggle('dark:bg-neon', sama);
            a.classList.toggle('dark:text-tinta', sama);
            a.classList.toggle('text-redup', !sama);
            a.classList.toggle('dark:text-redup-gelap', !sama);

            if (sama) {
                a.setAttribute('aria-current', 'page');
            } else {
                a.removeAttribute('aria-current');
            }
        });
    }

    function gantiIsi(html, url, tambahRiwayat) {
        var dokumen = new DOMParser().parseFromString(html, 'text/html');
        var isiBaru = dokumen.getElementById('isi-halaman');

        if (!isiBaru || !isiHalaman) {
            return false;
        }

        isiHalaman.innerHTML = isiBaru.innerHTML;

        var kelasGagal = isiBaru.getAttribute('data-modal-gagal');

        if (kelasGagal) {
            isiHalaman.setAttribute('data-modal-gagal', kelasGagal);
            isiHalaman.setAttribute('data-mode-gagal', isiBaru.getAttribute('data-mode-gagal') || '');
            isiHalaman.setAttribute('data-id-gagal', isiBaru.getAttribute('data-id-gagal') || '');
        } else {
            isiHalaman.removeAttribute('data-modal-gagal');
            isiHalaman.removeAttribute('data-mode-gagal');
            isiHalaman.removeAttribute('data-id-gagal');
        }

        tandaiMenu(url);

        document.title = dokumen.title;

        if (tambahRiwayat) {
            window.history.pushState({ url: url }, '', url);
        }

        siapkanHalaman();
        window.scrollTo({ top: 0, behavior: 'instant' });

        return true;
    }

    /*
        Arah geser isi mengikuti arah tombol menu: pindah ke menu yang lebih
        atas membuat isi bergeser ke atas, pindah ke menu yang lebih bawah
        membuatnya bergeser ke bawah. Diukur dari posisi layar tautan yang
        ditekan dibanding tautan yang sedang aktif, jadi tidak perlu daftar
        urutan menu yang harus dijaga.
    */
    function arahGeser(tautan) {
        var aktif = document.querySelector('#menu-samping a[aria-current="page"]');

        if (!aktif || aktif === tautan) {
            return 8;
        }

        return tautan.getBoundingClientRect().top < aktif.getBoundingClientRect().top ? 8 : -8;
    }

    function pindah(url, tambahRiwayat, geser) {
        if (!isiHalaman) {
            window.location.href = url;

            return;
        }

        ambil(url).then(function (html) {
            if (!gantiIsi(html, url, tambahRiwayat)) {
                window.location.href = url;

                return;
            }

            // Isi baru langsung tampil penuh, hanya digeser sedikit dari arah
            // gerak menu lalu menetap. Tanpa memudar: memudar masuk terbaca
            // seperti halaman memuat dua kali.
            isiHalaman.style.setProperty('--geser', (geser || 8) + 'px');
            isiHalaman.classList.remove('isi-geser');
            void isiHalaman.offsetWidth;
            isiHalaman.classList.add('isi-geser');
        }).catch(function () {
            window.location.href = url;
        });
    }

    document.addEventListener('click', function (peristiwa) {
        var tautan = peristiwa.target.closest('#menu-samping a[href]');

        if (!tautan || peristiwa.metaKey || peristiwa.ctrlKey || peristiwa.shiftKey || peristiwa.altKey) {
            return;
        }

        var url = new URL(tautan.href, window.location.href);

        if (url.origin !== window.location.origin) {
            return;
        }

        peristiwa.preventDefault();

        if (url.pathname + url.search === window.location.pathname + window.location.search) {
            return;
        }

        tautan.classList.remove('tautan-ditekan');
        void tautan.offsetWidth;
        tautan.classList.add('tautan-ditekan');

        pindah(url.pathname + url.search, true, arahGeser(tautan));
    });

    document.addEventListener('pointerenter', function (peristiwa) {
        var tautan = peristiwa.target.closest && peristiwa.target.closest('#menu-samping a[href]');

        if (!tautan) {
            return;
        }

        var url = new URL(tautan.href, window.location.href);

        if (url.origin === window.location.origin) {
            ambil(url.pathname + url.search).catch(function () { /* abaikan */ });
        }
    }, true);

    window.addEventListener('popstate', function () {
        var url = window.location.pathname + window.location.search;
        singgahan.delete(url);
        pindah(url, false, 8);
    });

    siapkanHalaman();
})();