{{-- Tombol cetak laporan. Dipakai keenam halaman laporan; sembunyi saat dicetak
     lewat kelas cetak-sembunyi (aturan @media print di resources/css/app.css). --}}
<button type="button" onclick="window.print()"
        class="cetak-sembunyi rounded-lg border border-garis bg-white px-4 py-2 text-sm font-semibold text-tinta hover:bg-latar-2 dark:border-garis-gelap dark:bg-gelap-2 dark:text-teks-gelap dark:hover:bg-gelap">
    Cetak laporan
</button>