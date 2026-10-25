{{--
    Tema terang dan gelap untuk semua halaman.

    Dua bagian, sengaja digabung satu berkas supaya tidak ada dua skrip yang
    berebut kunci localStorage:

    1. Bagian pertama berjalan di <head>, SEBELUM body dilukis. Tanpa ini
       halaman sempat tampil terang lalu berkedip jadi gelap.
    2. Bagian kedua memasang satu pendengar klik untuk semua [data-tombol-tema].
       Memakai atribut, bukan id, karena tombolnya ada di navbar beranda, di
       navbar login, dan di bubble profil panel.

    Kunci localStorage: "tema", nilainya "dark" atau "light".
--}}
<script>
    (function () {
        var html = document.documentElement;

        try {
            if (localStorage.getItem('tema') === 'dark') {
                html.classList.add('dark');
            }
        } catch (e) { /* localStorage bisa terblokir; tetap terang. */ }

        function segarkan() {
            var gelap = html.classList.contains('dark');

            document.querySelectorAll('[data-tombol-tema]').forEach(function (tombol) {
                var teks = tombol.querySelector('[data-label-tema]');

                if (teks) {
                    teks.textContent = gelap ? 'Mode terang' : 'Mode gelap';
                }

                tombol.setAttribute('aria-pressed', gelap ? 'true' : 'false');
            });
        }

        document.addEventListener('click', function (e) {
            var tombol = e.target.closest('[data-tombol-tema]');

            if (!tombol) {
                return;
            }

            var gelap = !html.classList.contains('dark');

            html.classList.toggle('dark', gelap);

            try {
                localStorage.setItem('tema', gelap ? 'dark' : 'light');
            } catch (e) { /* pilihan tidak tersimpan, tema tetap berubah. */ }

            segarkan();
        });

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', segarkan);
        } else {
            segarkan();
        }
    })();
</script>