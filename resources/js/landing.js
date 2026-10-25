/*
    Animasi halaman depan: gerak saat menggulir. Jejak kursor TIDAK di sini,
    dipasang di semua halaman lewat app.js karena dimuat di seluruh halaman,
    sedangkan berkas ini hanya dimuat di beranda.

    Berhenti sendiri kalau pengguna meminta gerak dikurangi
    (prefers-reduced-motion).

    Catatan GSAP: semua animasi memakai fromTo(), bukan from(). from() membaca
    keadaan akhir dari DOM saat animasi dibuat, sehingga kalau ScrollTrigger
    memutar balik arahnya, elemen bisa kembali ke nilai yang salah. fromTo()
    menyebut kedua ujungnya dengan jelas, jadi aman diputar dua arah.

    toggleActions 'play none play reverse' artinya: masuk layar memutar maju,
    keluar layar atas tidak melakukan apa pun, masuk lagi memutar maju, dan
    keluar layar bawah memutar balik.
*/

import gsap from 'gsap';
import ScrollTrigger from 'gsap/ScrollTrigger';

gsap.registerPlugin(ScrollTrigger);

const kurangiGerak = window.matchMedia('(prefers-reduced-motion: reduce)');

function animasiGulir() {
    if (kurangiGerak.matches) {
        return;
    }

    // Setiap bagian halaman naik sedikit sambil muncul. Hero dilewati karena
    // sudah terlihat saat halaman dibuka, dan menganimasikannya membuatnya
    // sempat tak terlihat. fromTo dipakai supaya arah balik punya titik awal
    // dan titik akhir yang pasti.
    gsap.utils.toArray('section').slice(1).forEach(function (bagian) {
        gsap.fromTo(bagian,
            { autoAlpha: 0, y: 40 },
            {
                autoAlpha: 1,
                y: 0,
                duration: 0.7,
                ease: 'power2.out',
                scrollTrigger: {
                    trigger: bagian,
                    start: 'top 85%',
                    toggleActions: 'play none play reverse',
                },
            }
        );
    });

    // Tiga langkah alur masuk bergiliran, bukan sekaligus.
    const langkah = gsap.utils.toArray('#alur li');

    if (langkah.length) {
        gsap.fromTo(langkah,
            { autoAlpha: 0, y: 24 },
            {
                autoAlpha: 1,
                y: 0,
                duration: 0.55,
                ease: 'power2.out',
                stagger: 0.12,
                scrollTrigger: {
                    trigger: '#alur',
                    start: 'top 75%',
                    toggleActions: 'play none play reverse',
                },
            }
        );
    }
}

animasiGulir();