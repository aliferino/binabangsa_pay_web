<script>
    /*
        Pindah menu samping tanpa memuat ulang dokumen.

        Kenapa: tiap klik menu dulu memuat ulang seluruh halaman. Sidebar, footer,
        dan posisi gulir ikut dipasang ulang, jadi yang terlihat cuma kedip.
        Sekarang hanya #isi-halaman yang diganti, sisanya diam.

        Cara kerjanya sengaja sederhana, tanpa pustaka:
        1. Klik tautan menu yang masih satu situs: ambil halamannya lewat fetch.
        2. Ambil #isi-halaman dan #judul-halaman dari HTML hasil fetch.
        3. Ganti isi, perbarui judul tab, perbarui penanda menu aktif, dorong URL.
        4. Kalau fetch gagal, muat ulang biasa. Tidak ada jalan buntu.

        Skrip dan gaya dari halaman baru tidak dijalankan ulang. app.js sudah
        memakai penanganan event di document, jadi perilakunya tetap jalan pada
        isi yang baru disisipkan.
    */
    (function () {
        var wadah = document.getElementById('isi-halaman');
        var menu = document.getElementById('menu-samping');

        if (!wadah || !menu) {
            return;
        }

        var sedangJalan = false;

        function tandaiAktif(url) {
            var tujuan = new URL(url, location.href);

            menu.querySelectorAll('a[href]').forEach(function (a) {
                var ini = new URL(a.getAttribute('href'), location.href);
                var sama = ini.pathname === tujuan.pathname;

                a.classList.toggle('bg-neon', sama);
                a.classList.toggle('font-semibold', sama);
                a.classList.toggle('text-tinta', sama);
                a.classList.toggle('shadow-sm', sama);
                a.classList.toggle('text-redup', !sama);
                a.classList.toggle('dark:text-redup-gelap', !sama);

                if (sama) {
                    a.setAttribute('aria-current', 'page');
                } else {
                    a.removeAttribute('aria-current');
                }
            });
        }

        function mainkan(el, kelas) {
            el.classList.remove(kelas);
            void el.offsetWidth;
            el.classList.add(kelas);
        }

        menu.addEventListener('click', function (peristiwa) {
            var a = peristiwa.target.closest('a[href]');

            if (!a) {
                return;
            }

            if (peristiwa.metaKey || peristiwa.ctrlKey || peristiwa.shiftKey || peristiwa.button !== 0) {
                return;
            }

            var tujuan = new URL(a.getAttribute('href'), location.href);

            if (tujuan.origin !== location.origin) {
                return;
            }

            if (tujuan.pathname === location.pathname) {
                peristiwa.preventDefault();
                return;
            }

            // Tombol keluar adalah form POST, bukan tautan, jadi tidak lewat sini.
            peristiwa.preventDefault();

            if (sedangJalan) {
                return;
            }

            sedangJalan = true;
            mainkan(a, 'tautan-ditekan');
            menu.setAttribute('aria-busy', 'true');

            fetch(tujuan.href, {
                headers: { 'X-Permintaan-Menu': '1' },
                credentials: 'same-origin'
            })
                .then(function (jawab) {
                    if (!jawab.ok) {
                        throw new Error('HTTP ' + jawab.status);
                    }

                    return jawab.text();
                })
                .then(function (html) {
                    var dok = new DOMParser().parseFromString(html, 'text/html');
                    var baru = dok.getElementById('isi-halaman');

                    if (!baru) {
                        throw new Error('isi halaman tidak ditemukan');
                    }

                    wadah.innerHTML = baru.innerHTML;
                    mainkan(wadah, 'isi-masuk');

                    var judulBaru = dok.querySelector('title');
                    if (judulBaru) {
                        document.title = judulBaru.textContent;
                    }

                    tandaiAktif(tujuan.href);
                    history.pushState({ menu: tujuan.href }, '', tujuan.href);

                    document.dispatchEvent(new CustomEvent('halaman-diganti'));
                    window.scrollTo(0, 0);
                })
                .catch(function () {
                    location.href = tujuan.href;
                })
                .finally(function () {
                    sedangJalan = false;
                    menu.removeAttribute('aria-busy');
                });
        });

        // Tombol kembali dan maju: ambil ulang halamannya, jangan biarkan isi
        // lama tampil dengan URL baru.
        window.addEventListener('popstate', function () {
            location.reload();
        });
    })();
</script>