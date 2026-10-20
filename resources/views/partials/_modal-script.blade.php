{{--
    Script modal bersama untuk semua halaman daftar.

    Satu modal dipakai untuk tambah dan ubah. Tombol Tambah membuka modal
    dengan form kosong, tombol Ubah mengisi form dari atribut data pada
    tombolnya. Form diarahkan ke route store atau update sesuai mode.

    Atribut yang dipakai:
      modal  : data-modal, data-judul-tambah, data-judul-ubah
      tombol : data-modal-buka ("tambah" atau "ubah"), data-modal-target (id modal),
               dan data-<nama-kolom> untuk mengisi form saat mengubah
      form   : data-form-modal, data-url-store, data-url-update (memakai __ID__)
--}}
<script>
    (function () {
        var semuaModal = document.querySelectorAll('[data-modal]');

        if (!semuaModal.length) {
            return;
        }

        function modalDari(id) {
            return id ? document.getElementById(id) : null;
        }

        function bagian(modal) {
            var form = modal.querySelector('[data-form-modal]');

            return {
                form: form,
                judul: modal.querySelector('[data-modal-judul]'),
                metode: modal.querySelector('[data-metode]'),
                mode: form ? form.querySelector('[name="_mode"]') : null,
                id: form ? form.querySelector('[name="_id"]') : null
            };
        }

        // Sebagian modal punya teks petunjuk yang berbeda antara tambah dan
        // ubah, misalnya keterangan password. Kalau ada, teksnya ditukar.
        function petunjuk(modal, mode) {
            var el = modal.querySelector('[data-modal-petunjuk-' + mode + ']');

            if (el) {
                el.textContent = el.getAttribute('data-modal-petunjuk-' + mode);
            }
        }

        function bukaTambah(modal) {
            var b = bagian(modal);

            if (!b.form) {
                return;
            }

            b.form.reset();
            b.form.action = b.form.getAttribute('data-url-store');
            b.metode.value = 'POST';
            b.mode.value = 'tambah';
            b.id.value = '';
            b.judul.textContent = modal.getAttribute('data-judul-tambah') || b.judul.textContent;
            petunjuk(modal, 'tambah');
            modal.classList.remove('hidden');
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

            modal.classList.remove('hidden');
        }

        document.addEventListener('click', function (peristiwa) {
            var tombolBuka = peristiwa.target.closest('[data-modal-buka]');

            if (tombolBuka) {
                var modal = modalDari(tombolBuka.getAttribute('data-modal-target'));

                if (!modal) {
                    return;
                }

                if (tombolBuka.getAttribute('data-modal-buka') === 'ubah') {
                    bukaUbah(modal, tombolBuka);
                } else {
                    bukaTambah(modal);
                }

                return;
            }

            var tombolTutup = peristiwa.target.closest('[data-modal-tutup]');

            if (tombolTutup) {
                var induk = tombolTutup.closest('[data-modal]');

                if (induk) {
                    induk.classList.add('hidden');
                }
            }
        });

        document.addEventListener('keydown', function (peristiwa) {
            if (peristiwa.key !== 'Escape') {
                return;
            }

            Array.prototype.forEach.call(semuaModal, function (modal) {
                modal.classList.add('hidden');
            });
        });

        // Form gagal divalidasi, modal dibuka lagi dengan isian sebelumnya.
        var modalGagal = modalDari(@json(old('_modal')));
        var modeGagal = @json(old('_mode'));

        if (modalGagal) {
            var bGagal = bagian(modalGagal);

            if (bGagal.form && modeGagal === 'ubah') {
                bGagal.form.action = bGagal.form.getAttribute('data-url-update')
                    .replace('__ID__', @json(old('_id')));
                bGagal.metode.value = 'PUT';
                bGagal.judul.textContent = modalGagal.getAttribute('data-judul-ubah')
                    || bGagal.judul.textContent;
            }

            modalGagal.classList.remove('hidden');
        }

        // Tautan Ubah di halaman detail mengalihkan ke daftar dengan ?ubah=id,
        // jadi modal langsung dibuka dengan data baris yang dimaksud.
        var diminta = new URLSearchParams(window.location.search).get('ubah');

        if (diminta) {
            var tombolnya = document.querySelector('[data-modal-buka="ubah"][data-id="' + diminta + '"]');

            if (tombolnya) {
                bukaUbah(modalDari(tombolnya.getAttribute('data-modal-target')), tombolnya);
            }
        }
    })();
</script>
