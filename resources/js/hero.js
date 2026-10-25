/*
    Adegan hero halaman depan: kartu siswa tiga dimensi yang mengikuti kursor,
    dan koin neon yang memercik keluar dari tengah kartu begitu kartu diklik,
    lalu jatuh ke bawah karena berat.

    three.js dipakai karena diminta. Yang diimpor hanya inti pustaka, tanpa
    addon, supaya bundelnya sekecil mungkin. Adegannya digambar dari geometri
    dasar: satu bidang bertekstur untuk kartu dan silinder untuk koin.

    Kalau peramban tidak sanggup WebGL, fungsi ini berhenti di awal dan kartu
    cadangan (SVG di beranda) tetap tampil. Kalau pengguna meminta gerak
    dikurangi, adegan digambar sekali lalu diam, dan klik tidak memercikkan apa
    pun.

    ponytail: koin memakai kolam tetap dan dipakai ulang, bukan dibuat lalu
    dibuang tiap klik; batasnya 24 koin sekaligus, naikkan JUM_KOIN kalau perlu.
*/

import * as THREE from 'three';

const canvas = document.getElementById('hero-3d');
const cadangan = document.getElementById('hero-cadangan');

if (canvas) mulai();

function mulai() {
    let renderer;
    try {
        renderer = new THREE.WebGLRenderer({ canvas, antialias: true, alpha: true });
    } catch (e) {
        return; // tanpa WebGL, kartu cadangan tetap tampil.
    }

    renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));

    const adegan = new THREE.Scene();
    const kamera = new THREE.PerspectiveCamera(38, 1, 0.1, 100);
    kamera.position.set(0, 0, 4.6);

    // Satu ambient supaya sisi gelap tidak hitam, satu arah untuk bentuk,
    // satu titik neon supaya sisi kartu ikut menyala.
    adegan.add(new THREE.AmbientLight(0xffffff, 1.7));
    const arah = new THREE.DirectionalLight(0xffffff, 2.3);
    arah.position.set(2.4, 3, 4);
    adegan.add(arah);
    const neon = new THREE.PointLight(0xb8f048, 14, 14);
    neon.position.set(-2.2, -1.4, 2.4);
    adegan.add(neon);

    // ---------------- Kartu siswa ----------------
    const tekstur = new THREE.CanvasTexture(gambarKartu());
    tekstur.colorSpace = THREE.SRGBColorSpace;
    tekstur.anisotropy = 4;

    const kartu = new THREE.Mesh(
        new THREE.PlaneGeometry(3, 1.875),
        new THREE.MeshBasicMaterial({ map: tekstur, transparent: true })
    );
    adegan.add(kartu);

    // ---------------- Koin neon ----------------
    // Muka koin memakai tekstur, sisi tebal warna neon rata; semuanya hijau
    // neon supaya seragam dengan warna aplikasi.
    const geometriKoin = new THREE.CylinderGeometry(0.12, 0.12, 0.04, 24);
    const teksturKoin = new THREE.CanvasTexture(gambarKoin());
    teksturKoin.colorSpace = THREE.SRGBColorSpace;

    const JUM_KOIN = 24;
    const koin = [];
    for (let i = 0; i < JUM_KOIN; i++) {
        const bahan = [
            new THREE.MeshStandardMaterial({
                color: 0xa6dc3a, metalness: 0.35, roughness: 0.4, transparent: true,
            }),
            new THREE.MeshStandardMaterial({
                map: teksturKoin, metalness: 0.25, roughness: 0.45, transparent: true,
            }),
            new THREE.MeshStandardMaterial({
                map: teksturKoin, metalness: 0.25, roughness: 0.45, transparent: true,
            }),
        ];
        const m = new THREE.Mesh(geometriKoin, bahan);
        m.rotation.x = Math.PI / 2;          // muka koin menghadap kamera
        m.visible = false;
        // vx/vy/vz kecepatan, hidup sisa umur, putar kecepatan guling.
        m.userData = { vx: 0, vy: 0, vz: 0, hidup: 0, umur: 1, putar: 0, guling: 0 };
        adegan.add(m);
        koin.push(m);
    }

    // ---------------- Ukuran ----------------
    const bingkai = canvas.parentElement;
    // Kartu harus selalu masuk utuh, jadi jarak kamera dihitung dari rasio
    // bingkai: ambil yang paling jauh antara syarat lebar dan syarat tinggi.
    const LEBAR_KARTU = 3, TINGGI_KARTU = 1.875, MARGIN = 1.22;
    function ukur() {
        const w = bingkai.clientWidth || 1;
        const h = bingkai.clientHeight || 1;
        renderer.setSize(w, h, false);
        kamera.aspect = w / h;

        const setengahFov = THREE.MathUtils.degToRad(kamera.fov / 2);
        const tan = Math.tan(setengahFov);
        const zTinggi = (TINGGI_KARTU * MARGIN) / (2 * tan);
        const zLebar = (LEBAR_KARTU * MARGIN) / (2 * tan * kamera.aspect);
        kamera.position.z = Math.max(zTinggi, zLebar);
        kamera.updateProjectionMatrix();
    }
    ukur();
    new ResizeObserver(ukur).observe(bingkai);

    // ---------------- Kursor: kartu mengikuti, klik memercikkan koin ----
    const tunjuk = { x: 0, y: 0, tx: 0, ty: 0 };
    const sinar = new THREE.Raycaster();
    const ndc = new THREE.Vector2();
    document.body.style.cursor = 'default';

    window.addEventListener('pointermove', (e) => {
        tunjuk.tx = (e.clientX / window.innerWidth - 0.5) * 2;
        tunjuk.ty = (e.clientY / window.innerHeight - 0.5) * 2;

        // Kursor jadi tangan saat berada di atas kartu.
        const r = canvas.getBoundingClientRect();
        ndc.set(
            ((e.clientX - r.left) / r.width) * 2 - 1,
            -((e.clientY - r.top) / r.height) * 2 + 1
        );
        sinar.setFromCamera(ndc, kamera);
        document.body.style.cursor = sinar.intersectObject(kartu).length ? 'pointer' : 'default';
    }, { passive: true });

    const tenang = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    canvas.addEventListener('click', (e) => {
        if (tenang) return;
        const r = canvas.getBoundingClientRect();
        ndc.set(
            ((e.clientX - r.left) / r.width) * 2 - 1,
            -((e.clientY - r.top) / r.height) * 2 + 1
        );
        sinar.setFromCamera(ndc, kamera);
        // Percikan hanya kalau kartunya benar yang diklik.
        if (sinar.intersectObject(kartu).length) percikan();
    });

    // Koin dipakai ulang: cari yang menganggur, hidupkan dengan arah acak
    // menyebar ke luar dari pusat kartu, sedikit condong ke atas dulu.
    function percikan() {
        const sisa = koin.filter((m) => !m.visible).length;
        const jml = Math.min(sisa, 10);
        let dipakai = 0;
        for (const m of koin) {
            if (dipakai >= jml) break;
            if (m.visible) continue;
            dipakai++;

            // Titik lahir disebar di badan kartu (setengah lebar/tinggi kartu
            // dikurangi sedikit) supaya koin tidak menumpuk di satu titik.
            const sudut = Math.random() * Math.PI * 2;
            const kuat = 1.7 + Math.random() * 1.1;
            m.position.set(
                (Math.random() - 0.5) * 2.2,
                (Math.random() - 0.5) * 1.2,
                0.12
            );
            m.userData.vx = Math.cos(sudut) * kuat;
            m.userData.vy = Math.abs(Math.sin(sudut)) * kuat * 0.8 + 1.1; // condong ke atas
            m.userData.vz = (Math.random() - 0.5) * 0.3;
            m.userData.umur = 1.2 + Math.random() * 0.6;
            m.userData.hidup = m.userData.umur;
            m.userData.putar = Math.random() * Math.PI * 2;
            m.userData.guling = 6 + Math.random() * 6;
            m.rotation.set(Math.PI / 2, 0, 0);
            m.visible = true;
        }
    }

    // ---------------- Gambar ----------------
    const GRAVITASI = 6.5;   // koin berat, jadi jatuhnya cepat.
    const jam = new THREE.Clock();

    function gambar() {
        const dt = Math.min(jam.getDelta(), 0.05);
        const t = jam.getElapsedTime();
        const putar = tenang ? 0 : t;

        // Kartu mengikuti kursor, dengan sedikit apungan supaya tidak kaku.
        tunjuk.x += (tunjuk.tx - tunjuk.x) * 0.08;
        tunjuk.y += (tunjuk.ty - tunjuk.y) * 0.08;
        kartu.position.x = tunjuk.x * 0.22;
        kartu.position.y = -tunjuk.y * 0.14 + Math.sin(putar * 0.8) * 0.04;
        kartu.rotation.y = tunjuk.x * 0.5;
        kartu.rotation.x = tunjuk.y * 0.34 + Math.sin(putar * 0.5) * 0.03;

        // Koin: melengkung ke luar, lalu jatuh ke bawah karena gravitasi.
        for (const m of koin) {
            if (!m.visible) continue;
            m.userData.hidup -= dt;
            if (m.userData.hidup <= 0) { m.visible = false; continue; }

            m.userData.vy -= GRAVITASI * dt;
            m.position.x += m.userData.vx * dt;
            m.position.y += m.userData.vy * dt;
            m.position.z += m.userData.vz * dt;
            m.rotation.z += m.userData.guling * dt;
            m.userData.putar += dt;

            // Memudar di seperempat umur terakhir.
            const sisa = m.userData.hidup / m.userData.umur;
            const alfa = Math.min(1, sisa * 4);
            for (const b of m.material) b.opacity = alfa;
        }

        renderer.render(adegan, kamera);
    }

    if (tenang) {
        gambar(); // satu gambar, tanpa gerak.
    } else {
        renderer.setAnimationLoop(() => {
            if (document.visibilityState === 'visible') gambar();
        });
    }

    // Adegan jalan: sembunyikan kartu cadangan.
    if (cadangan) cadangan.style.display = 'none';
}

/* Kartu siswa bergaya kartu bank: contactless di kiri atas, logo Bank
   Indonesia di kanan atas, nomor virtual account besar di tengah, nama dan
   kelas di bawah, chip silver di kanan bawah. Digambar di kanvas 2D lalu
   dipakai sebagai tekstur bidang. */
function gambarKartu() {
    const c = document.createElement('canvas');
    c.width = 1200;
    c.height = 750;
    const x = c.getContext('2d');

    function bulat(x0, y0, w, h, r) {
        x.beginPath();
        x.moveTo(x0 + r, y0);
        x.arcTo(x0 + w, y0, x0 + w, y0 + h, r);
        x.arcTo(x0 + w, y0 + h, x0, y0 + h, r);
        x.arcTo(x0, y0 + h, x0, y0, r);
        x.arcTo(x0, y0, x0 + w, y0, r);
        x.closePath();
    }

    // Badan kartu: gradasi neon menyilang, seperti kartu bank.
    const latar = x.createLinearGradient(20, 20, 1180, 730);
    latar.addColorStop(0, '#d8f878');
    latar.addColorStop(0.5, '#b8f048');
    latar.addColorStop(1, '#9ad42c');
    x.fillStyle = latar;
    bulat(20, 20, 1160, 710, 56);
    x.fill();

    // Kilau lembut di kanan atas, mengalir keluar tepi kartu.
    const kilau = x.createRadialGradient(980, 90, 10, 980, 90, 380);
    kilau.addColorStop(0, 'rgba(255, 255, 255, 0.30)');
    kilau.addColorStop(1, 'rgba(255, 255, 255, 0)');
    x.fillStyle = kilau;
    bulat(20, 20, 1160, 710, 56);
    x.fill();

    // --- Lapisan artwork: dua pita gelombang melintang, seperti kartu bank ---
    // Digambar setelah badan kartu, sebelum tulisan, jadi tidak menutupi teks.
    x.save();
    bulat(20, 20, 1160, 710, 56);
    x.clip();

    // Satu pita = daerah antara dua kurva bezier sejajar. Ujungnya memudar
    // lewat gradasi mendatar supaya tepinya lembut, bukan potongan tajam.
    function pita(geser, tebal, alfa) {
        const g = x.createLinearGradient(0, 0, 1180, 0);
        g.addColorStop(0, 'rgba(255, 255, 255, 0)');
        g.addColorStop(0.45, `rgba(255, 255, 255, ${alfa})`);
        g.addColorStop(1, `rgba(255, 255, 255, ${alfa * 0.5})`);
        x.fillStyle = g;
        x.beginPath();
        x.moveTo(-40, 520 + geser);
        x.bezierCurveTo(280, 340 + geser, 420, 700 + geser, 760, 560 + geser);
        x.bezierCurveTo(980, 470 + geser, 1060, 250 + geser, 1240, 180 + geser);
        x.lineTo(1240, 180 + geser + tebal);
        x.bezierCurveTo(1060, 250 + geser + tebal, 980, 470 + geser + tebal, 760, 560 + geser + tebal);
        x.bezierCurveTo(420, 700 + geser + tebal, 280, 340 + geser + tebal, -40, 520 + geser + tebal);
        x.closePath();
        x.fill();
    }

    pita(0, 150, 0.34);
    pita(210, 96, 0.20);

    x.restore();

    // --- Contactless, kiri atas ---
    // Puncak tanda sinyal (177 - 44 = 133) sengaja disamakan dengan tepi atas
    // monogram BI (124 + 9 = 133), jadi kedua ikon di baris atas sejajar.
    // Angka 133 dipilih supaya jarak ke tepi atas kartu (133 - 20 = 113) hampir
    // sama dengan jarak baris nama ke tepi bawah (730 - 629 = 101). Sebelumnya
    // ikon atas hanya 40 dari tepi, jadi sisi atas terasa terjepit.
    x.strokeStyle = '#111111';
    x.lineWidth = 5;
    x.lineCap = 'round';
    for (let i = 0; i < 4; i++) {
        x.beginPath();
        x.arc(104, 177, 15 + i * 12, -Math.PI / 3, Math.PI / 3);
        x.stroke();
    }

    // --- Logo Bank Indonesia, kanan atas ---
    // Monogram hasil trace dari logo resmi (lihat scripts/trace_logo_bi.py di
    // D:/hermes). Digambar lewat Path2D dengan aturan evenodd supaya lubang
    // huruf B tetap berlubang.
    //
    // Titik x dipilih supaya pusat logo jatuh tepat di pusat chip (1000 + 104/2
    // = 1052): pusat tinta logo ada di 50.3 satuan lokal, dikali skala 1.167
    // jadi 58.7, sehingga 1052 - 58.7 = 993. Sumbu y 124 supaya tepi atas tinta
    // logo (124 + 9 = 133) sebaris dengan puncak tanda sinyal di kiri atas
    // (177 - 44 = 133), jadi kedua ikon baris atas rata.
    x.save();
    x.translate(993, 124);
    x.scale(1.167, 1.167);
    x.fillStyle = '#111111';
    x.fill(new Path2D('M37.60 8.02 C45.51 8.02 53.42 8.02 61.56 8.02 C61.56 8.91 61.56 9.81 61.56 10.73 C61.45 10.74 61.45 10.74 60.86 10.78 C57.20 11.11 57.20 11.11 55.94 12.19 C55.00 13.45 54.76 14.84 54.48 16.35 C54.60 16.35 54.71 16.35 54.84 16.34 C60.67 16.16 67.25 16.83 71.85 20.81 C74.05 22.92 75.46 25.26 75.58 28.36 C75.59 28.68 75.59 29.01 75.59 29.34 C75.58 29.51 75.58 29.68 75.58 29.86 C75.56 31.26 75.36 32.52 74.90 33.85 C74.83 34.06 74.76 34.26 74.70 34.47 C74.04 36.26 72.99 37.62 71.77 39.06 C71.66 39.20 71.54 39.34 71.42 39.48 C69.81 41.26 67.49 42.50 65.36 43.57 C65.22 43.64 65.08 43.71 64.94 43.78 C64.33 44.08 63.91 44.27 63.23 44.27 C63.23 44.41 63.23 44.55 63.23 44.69 C63.40 44.71 63.57 44.73 63.74 44.75 C64.64 44.93 65.42 45.26 66.25 45.64 C66.41 45.71 66.58 45.79 66.75 45.86 C72.36 48.44 77.44 52.33 79.69 58.23 C80.98 62.17 80.76 66.52 79.06 70.31 C76.87 74.54 73.35 77.39 69.06 79.27 C68.95 79.32 68.95 79.32 68.38 79.58 C67.12 80.10 65.85 80.46 64.52 80.77 C64.38 80.80 64.24 80.83 64.09 80.87 C61.15 81.49 58.25 81.62 55.26 81.61 C54.87 81.61 54.48 81.61 54.09 81.61 C53.04 81.61 51.99 81.61 50.94 81.60 C49.84 81.60 48.74 81.60 47.64 81.60 C45.56 81.60 43.48 81.60 41.40 81.59 C39.03 81.59 36.66 81.59 34.29 81.58 C29.43 81.58 24.56 81.57 19.69 81.56 C19.69 80.60 19.69 79.64 19.69 78.65 C19.96 78.62 20.24 78.60 20.53 78.58 C25.60 78.13 25.60 78.13 27.10 76.70 C28.26 75.32 28.26 73.58 28.26 71.87 C28.26 71.67 28.26 71.47 28.26 71.26 C28.26 70.59 28.26 69.91 28.26 69.24 C28.26 68.76 28.26 68.27 28.27 67.79 C28.27 66.48 28.27 65.17 28.27 63.85 C28.27 63.03 28.27 62.21 28.27 61.39 C28.28 59.12 28.28 56.85 28.28 54.57 C28.28 54.43 28.28 54.28 28.28 54.13 C28.28 53.99 28.28 53.84 28.28 53.69 C28.28 53.40 28.28 53.10 28.28 52.81 C28.28 52.73 28.28 52.73 28.28 52.36 C28.28 49.99 28.28 47.62 28.29 45.25 C28.29 42.81 28.30 40.37 28.30 37.94 C28.30 36.57 28.30 35.20 28.30 33.83 C28.31 32.55 28.31 31.26 28.30 29.97 C28.30 29.50 28.30 29.03 28.31 28.56 C28.31 27.91 28.31 27.27 28.31 26.62 C28.31 26.44 28.31 26.25 28.31 26.06 C28.30 24.13 27.90 22.00 26.56 20.52 C24.69 19.09 21.92 19.19 19.69 19.06 C19.69 18.17 19.69 17.27 19.69 16.35 C27.94 16.35 36.19 16.35 44.69 16.35 C44.12 12.95 44.12 12.95 43.01 11.71 C41.35 10.75 39.48 10.63 37.60 10.52 C37.60 9.70 37.60 8.87 37.60 8.02 Z  M39.86 19.45 C39.93 19.45 39.93 19.45 40.30 19.45 C40.80 19.44 41.29 19.44 41.79 19.44 C42.14 19.44 42.48 19.44 42.83 19.44 C43.56 19.44 44.28 19.44 45.01 19.44 C45.93 19.44 46.86 19.44 47.78 19.43 C48.50 19.43 49.21 19.43 49.93 19.43 C50.27 19.43 50.61 19.43 50.95 19.43 C52.74 19.42 54.51 19.47 56.29 19.66 C56.36 19.67 56.36 19.67 56.75 19.71 C59.63 20.05 62.20 21.26 64.05 23.54 C65.95 26.21 66.50 29.23 66.05 32.47 C65.36 35.91 63.48 38.44 60.69 40.48 C58.77 41.74 56.76 42.69 54.48 43.02 C54.48 36.08 54.48 29.13 54.48 21.98 C51.25 21.98 48.02 21.98 44.69 21.98 C44.69 29.47 44.69 36.97 44.69 44.69 C43.73 44.76 42.76 44.82 41.77 44.90 C41.29 46.00 40.81 47.10 40.31 48.23 C44.48 47.93 48.63 47.58 52.77 46.97 C52.88 46.96 52.88 46.96 53.42 46.88 C53.86 46.80 54.29 46.72 54.72 46.62 C56.26 46.29 57.10 46.39 58.42 47.24 C58.58 47.34 58.74 47.43 58.91 47.53 C60.36 48.42 61.72 49.42 63.02 50.52 C63.12 50.60 63.12 50.60 63.62 51.02 C67.15 54.06 69.49 58.17 70.10 62.81 C70.35 66.56 69.85 69.61 67.34 72.53 C64.65 75.36 60.86 77.15 56.98 77.60 C56.15 77.60 55.33 77.60 54.48 77.60 C54.41 68.46 54.34 59.32 54.27 49.90 C53.38 50.03 53.38 50.03 48.85 50.73 C48.17 50.76 48.17 50.76 44.69 50.94 C44.69 59.74 44.69 68.54 44.69 77.60 C42.69 77.60 41.29 77.32 39.83 75.91 C37.61 73.45 38.17 69.60 38.18 66.52 C38.18 66.01 38.18 65.50 38.18 64.99 C38.18 63.75 38.18 62.51 38.18 61.27 C38.18 59.83 38.18 58.38 38.18 56.94 C38.18 54.37 38.18 51.80 38.19 49.22 C38.19 46.72 38.19 44.23 38.19 41.73 C38.19 41.58 38.19 41.42 38.19 41.26 C38.19 41.11 38.19 40.95 38.19 40.80 C38.19 37.95 38.19 35.11 38.19 32.26 C38.19 31.25 38.19 30.25 38.19 29.24 C38.19 27.89 38.20 26.54 38.20 25.18 C38.20 24.69 38.20 24.19 38.20 23.69 C38.20 23.02 38.20 22.34 38.20 21.67 C38.20 21.47 38.20 21.27 38.20 21.06 C38.21 19.44 38.26 19.45 39.86 19.45 Z  M45.28 83.83 C45.37 83.83 45.37 83.83 45.80 83.83 C45.90 83.83 45.90 83.83 46.38 83.83 C46.59 83.83 46.80 83.83 47.01 83.83 C47.22 83.83 47.44 83.83 47.66 83.83 C48.34 83.84 49.02 83.84 49.70 83.84 C50.16 83.84 50.62 83.84 51.08 83.84 C52.21 83.85 53.35 83.85 54.48 83.85 C54.50 83.95 54.50 83.95 54.60 84.41 C54.65 84.66 54.71 84.90 54.76 85.15 C54.79 85.27 54.79 85.27 54.92 85.88 C55.23 87.02 55.71 87.88 56.63 88.62 C58.24 89.30 59.83 89.37 61.56 89.48 C61.56 90.37 61.56 91.27 61.56 92.19 C53.66 92.19 45.75 92.19 37.60 92.19 C37.60 91.36 37.60 90.54 37.60 89.69 C37.84 89.67 38.07 89.65 38.31 89.63 C42.16 89.26 42.16 89.26 43.52 87.97 C44.27 86.70 44.53 85.52 44.69 84.06 C44.90 83.85 44.90 83.85 45.28 83.83 Z'), 'evenodd');
    x.restore();

    // --- Nomor virtual account, besar di tengah ---
    x.fillStyle = '#111111';
    x.textAlign = 'left';
    x.textBaseline = 'alphabetic';
    x.font = '700 74px ui-monospace, monospace';
    if ('letterSpacing' in x) x.letterSpacing = '5px';
    x.fillText('7770 1001 3333', 96, 400);
    if ('letterSpacing' in x) x.letterSpacing = '0px';

    // --- Baris bawah: nama dan kelas ---
    function kolom(x0, label, nilai) {
        x.fillStyle = 'rgba(17, 17, 17, 0.62)';
        x.font = '600 24px system-ui, sans-serif';
        x.fillText(label, x0, 566);
        x.fillStyle = '#111111';
        x.font = '700 42px system-ui, sans-serif';
        x.fillText(nilai, x0, 618);
    }

    kolom(96, 'NAMA PEMILIK', 'Jin Kazama');
    kolom(620, 'KELAS', 'XII RPL 1');

    // --- Chip silver, kanan tengah, sejajar baris nomor virtual account ---
    // Chip digeser ke kanan supaya jarak ke tepi kanan kartu (1180 - 1104 = 76)
    // sama dengan jarak teks ke tepi kiri (96 - 20 = 76). Sebelumnya chip
    // berhenti di 1004, jadi sisi kanan terasa dua kali lebih lega dari sisi
    // kiri.
    x.fillStyle = '#f2f6ea';
    x.strokeStyle = '#111111';
    x.lineWidth = 5;
    bulat(1000, 336, 104, 76, 12);
    x.fill();
    x.stroke();

    x.lineWidth = 3;
    x.beginPath();
    x.moveTo(1000, 374);
    x.lineTo(1104, 374);
    x.moveTo(1035, 336);
    x.lineTo(1035, 412);
    x.moveTo(1070, 336);
    x.lineTo(1070, 412);
    x.stroke();

    return c;
}

/* Muka koin: bulatan neon dengan tanda Rp di tengah. */
function gambarKoin() {
    const c = document.createElement('canvas');
    c.width = 256;
    c.height = 256;
    const x = c.getContext('2d');

    x.fillStyle = '#b8f048';
    x.beginPath();
    x.arc(128, 128, 124, 0, Math.PI * 2);
    x.fill();

    x.strokeStyle = '#8fc22f';
    x.lineWidth = 14;
    x.beginPath();
    x.arc(128, 128, 110, 0, Math.PI * 2);
    x.stroke();

    x.fillStyle = '#111111';
    x.font = '700 120px system-ui, sans-serif';
    x.textAlign = 'center';
    x.textBaseline = 'middle';
    x.fillText('Rp', 128, 136);

    return c;
}
