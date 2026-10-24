{{-- Transisi antar halaman beranda <-> login.

     Kenapa skrip ini inline di <head>, bukan di app.js: skrip @vite dimuat
     sebagai modul yang ditunda, jadi app.js jalan setelah halaman dilukis dan
     animasi masuk sudah kadung digambar. Skrip inline di head jalan lebih awal,
     tepat sebelum lukis pertama.

     Dipakai fitur bawaan browser, cross-document view transition, bukan
     pustaka animasi. Kalau peramban tidak mendukungnya, semua dilewati dan
     tautan berpindah seperti biasa.

     Alurnya:
     1. Klik tautan menuju beranda/login: simpan pusat tombol ke sessionStorage.
     2. pageswap di halaman lama: tandai arah transisi (ke-login/ke-beranda).
     3. Script di halaman baru: baca koordinat tadi, pasang sebagai
        --asal-x/--asal-y yang dipakai animasi clip-path di app.css.
--}}
<script>
    (function () {
        if (!('startViewTransition' in document) && !('ViewTransition' in window)) return;

        var KUNCI = 'transisi-asal';

        // 3. Halaman baru: pasang titik asal segera, sebelum lukis pertama.
        //    Dibaca langsung, bukan lewat event pagereveal, karena event itu
        //    tidak selalu menyala di semua peramban.
        try {
            var simpan = JSON.parse(sessionStorage.getItem(KUNCI) || 'null');
            sessionStorage.removeItem(KUNCI);
            if (simpan && typeof simpan.x === 'number') {
                document.documentElement.style.setProperty('--asal-x', simpan.x + 'px');
                document.documentElement.style.setProperty('--asal-y', simpan.y + 'px');
            }
        } catch (err) { /* sessionStorage bisa terblokir; sebaran mulai dari tengah. */ }

        function halaman(p) {
            return p === '/' || p === '/login';
        }

        // 2. Halaman lama pergi: tandai jenis transisinya.
        window.addEventListener('pageswap', function (e) {
            if (!e.viewTransition) return;
            var tuju = e.activation && e.activation.entry && e.activation.entry.url;
            if (!tuju) return;
            var p = new URL(tuju).pathname;
            if (p === '/login') e.viewTransition.types.add('ke-login');
            else if (p === '/') e.viewTransition.types.add('ke-beranda');
        });

        // 1. Klik tautan: simpan pusat tombol sebagai titik asal sebaran.
        document.addEventListener('click', function (e) {
            var tautan = e.target.closest && e.target.closest('a[href]');
            if (!tautan) return;

            var tujuan = new URL(tautan.href, location.href);
            if (tujuan.origin !== location.origin) return;
            if (!halaman(tujuan.pathname)) return;
            if (tujuan.pathname === location.pathname) return;

            var r = tautan.getBoundingClientRect();
            try {
                sessionStorage.setItem(KUNCI, JSON.stringify({
                    x: r.left + r.width / 2,
                    y: r.top + r.height / 2,
                }));
            } catch (err) { /* sessionStorage bisa terblokir; animasi tetap jalan. */ }
        });
    })();
</script>
