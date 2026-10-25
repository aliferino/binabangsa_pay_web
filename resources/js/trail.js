/*
    Jejak kursor. Dipasang di SEMUA halaman lewat app.js, bukan cuma beranda.

    Tiga lapis supaya tidak terasa cuma satu titik:

    1. Ekor   : rangkaian titik di belakang kursor, makin ke belakang makin
                besar dan makin pudar. Membentuk jalur halus.
    2. Percikan: partikel kecil yang menyebar ke arah berlawanan gerak, lalu
                melambat dan jatuh. Ini yang bikin terasa "hidup".
    3. Inti   : tiap bagian digambar dua kali, lingkaran lebar beropasitas
                rendah sebagai halo lalu inti pekat di atasnya.

    Ukuran dan opasitas kedua tema sama persis. Yang bertukar cuma warnanya:
    mode terang memakai tinta #111111 supaya terbaca di atas putih, mode gelap
    memakai neon #b8f048 supaya menyala di atas hitam.

    Semua digambar di satu kanvas. pointer-events none supaya tombol di bawahnya
    tetap bisa diklik, dan loop berhenti sendiri saat tidak ada isi supaya tidak
    membakar baterai saat kursor diam.

    Dipisah dari app.js supaya berkas perilaku bersama tetap fokus, dan supaya
    mudah dilepas kalau nanti tidak diinginkan.
*/

const kurangiGerak = window.matchMedia('(prefers-reduced-motion: reduce)');

export default function jejakKursor() {
    if (kurangiGerak.matches || window.matchMedia('(pointer: coarse)').matches) {
        return;
    }

    const kanvas = document.createElement('canvas');
    kanvas.setAttribute('aria-hidden', 'true');
    kanvas.style.cssText = 'position:fixed;inset:0;width:100%;height:100%;pointer-events:none;z-index:50';
    document.body.appendChild(kanvas);

    const konteks = kanvas.getContext('2d');
    const ekor = [];
    const percikan = [];
    const MAKS_EKOR = 26;
    const MAKS_PERC = 70;
    const NEON = '184, 240, 72';
    const TINTA = '17, 17, 17';

    let gerakTerakhir = 0;
    let arahX = 0;
    let arahY = 0;
    let siapGambar = null;

    function gelap() {
        return document.documentElement.classList.contains('dark');
    }

    function ukur() {
        const rasio = Math.min(window.devicePixelRatio || 1, 2);
        kanvas.width = window.innerWidth * rasio;
        kanvas.height = window.innerHeight * rasio;
        konteks.setTransform(rasio, 0, 0, rasio, 0, 0);
    }

    ukur();
    window.addEventListener('resize', ukur);

    window.addEventListener('pointermove', function (e) {
        arahX = e.clientX - (ekor.length ? ekor[ekor.length - 1].x : e.clientX);
        arahY = e.clientY - (ekor.length ? ekor[ekor.length - 1].y : e.clientY);

        ekor.push({ x: e.clientX, y: e.clientY });

        if (ekor.length > MAKS_EKOR) {
            ekor.shift();
        }

        // Percikan dilahirkan berkala, bukan tiap piksel gerak, supaya jumlahnya
        // tetap terkendali walau kursor digerakkan cepat.
        const sekarang = performance.now();

        if (sekarang - gerakTerakhir > 28) {
            gerakTerakhir = sekarang;

            const arah = Math.atan2(arahY, arahX) + Math.PI + (Math.random() - 0.5) * 1.6;
            const laju = 0.5 + Math.random() * 1.8;

            percikan.push({
                x: e.clientX,
                y: e.clientY,
                dariX: e.clientX,
                dariY: e.clientY,
                lajuX: Math.cos(arah) * laju,
                lajuY: Math.sin(arah) * laju,
                turun: 0.02 + Math.random() * 0.05,
                tebal: 0.6 + Math.random() * 1.5,
                hidup: 1,
            });

            if (percikan.length > MAKS_PERC) {
                percikan.shift();
            }
        }

        // Bangunkan loop kalau sedang tidur karena tidak ada isi.
        if (siapGambar === null) {
            siapGambar = requestAnimationFrame(gambar);
        }
    }, { passive: true });

    function garis(x1, y1, x2, y2, tebal, warna) {
        konteks.strokeStyle = warna;
        konteks.lineWidth = tebal;
        konteks.beginPath();
        konteks.moveTo(x1, y1);
        konteks.lineTo(x2, y2);
        konteks.stroke();
    }

    function gambar() {
        konteks.clearRect(0, 0, window.innerWidth, window.innerHeight);

        const malam = gelap();

        // Warna inti dibalik menurut tema: terang memakai tinta gelap supaya
        // terbaca di atas putih, gelap memakai neon supaya menyala. Ukuran dan
        // opasitas kedua tema sama persis, cuma warnanya yang bertukar.
        const inti = malam ? NEON : TINTA;

        // Lapis 1: ekor.
        for (let i = 0; i < ekor.length; i++) {
            const bagian = (i + 1) / ekor.length;
            const jari = bagian * 4.5;

            konteks.beginPath();
            konteks.fillStyle = 'rgba(' + inti + ', ' + (bagian * 0.3).toFixed(3) + ')';
            konteks.arc(ekor[i].x, ekor[i].y, jari * 1.9, 0, Math.PI * 2);
            konteks.fill();

            konteks.beginPath();
            konteks.fillStyle = 'rgba(' + inti + ', ' + (bagian * 0.85).toFixed(3) + ')';
            konteks.arc(ekor[i].x, ekor[i].y, jari, 0, Math.PI * 2);
            konteks.fill();
        }

        ekor.length = Math.max(0, ekor.length - 1);

        // Lapis 2: percikan. Dihitung mundur karena ada yang dihapus di tengah.
        for (let i = percikan.length - 1; i >= 0; i--) {
            const p = percikan[i];

            p.dariX = p.x;
            p.dariY = p.y;
            p.x += p.lajuX;
            p.y += p.lajuY;
            p.lajuX *= 0.96;
            p.lajuY = p.lajuY * 0.96 + p.turun;
            p.hidup -= 0.022;

            if (p.hidup <= 0) {
                percikan.splice(i, 1);
                continue;
            }

            konteks.beginPath();
            konteks.fillStyle = 'rgba(' + inti + ', ' + (p.hidup * 0.3).toFixed(3) + ')';
            konteks.arc(p.x, p.y, p.tebal * p.hidup * 2.6, 0, Math.PI * 2);
            konteks.fill();

            garis(p.dariX, p.dariY, p.x, p.y, p.tebal * p.hidup,
                'rgba(' + inti + ', ' + (p.hidup * 0.95).toFixed(3) + ')');
        }

        // Berhenti menggambar kalau tidak ada isi, supaya tidak membakar baterai
        // saat kursor diam. Bangun lagi lewat pointermove.
        if (ekor.length || percikan.length) {
            siapGambar = requestAnimationFrame(gambar);
        } else {
            konteks.clearRect(0, 0, window.innerWidth, window.innerHeight);
            siapGambar = null;
        }
    }

    function mulai() {
        if (siapGambar === null) {
            siapGambar = requestAnimationFrame(gambar);
        }
    }

    document.addEventListener('visibilitychange', function () {
        if (document.hidden) {
            if (siapGambar !== null) {
                cancelAnimationFrame(siapGambar);
                siapGambar = null;
            }
        } else {
            mulai();
        }
    });
}
