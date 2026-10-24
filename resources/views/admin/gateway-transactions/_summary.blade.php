<div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">

    {{-- Total transaksi pada rentang yang sedang disaring. --}}
    <div class="rounded-xl border border-garis bg-white p-4 shadow-sm dark:border-garis-gelap dark:bg-gelap-2">
        <p class="text-xs font-medium uppercase tracking-wide text-redup dark:text-redup-gelap">Jumlah transaksi</p>
        <p class="mt-2 text-2xl font-bold text-tinta dark:text-teks-gelap">{{ $summary['total'] }}</p>
        <p class="mt-1 text-xs text-redup dark:text-redup-gelap">baris pada rentang ini</p>
    </div>

    {{-- Nominal transaksi berhasil. --}}
    <div class="rounded-xl border border-garis bg-white p-4 shadow-sm dark:border-garis-gelap dark:bg-gelap-2">
        <p class="text-xs font-medium uppercase tracking-wide text-redup dark:text-redup-gelap">Nominal berhasil</p>
        <p class="mt-2 text-2xl font-bold text-tinta dark:text-teks-gelap">
            Rp{{ number_format((float) $summary['succeeded_amount'], 0, ',', '.') }}
        </p>
        <p class="mt-1 text-xs text-redup dark:text-redup-gelap">hanya status berhasil</p>
    </div>

    {{-- Yang belum selesai, supaya kelihatan kalau ada yang menggantung. --}}
    <div class="rounded-xl border border-garis bg-white p-4 shadow-sm dark:border-garis-gelap dark:bg-gelap-2">
        <p class="text-xs font-medium uppercase tracking-wide text-redup dark:text-redup-gelap">Belum selesai</p>
        <p class="mt-2 text-2xl font-bold text-tinta dark:text-teks-gelap">{{ $summary['pending'] }}</p>
        <p class="mt-1 text-xs text-redup dark:text-redup-gelap">status pending</p>
    </div>

    {{-- Yang gagal atau kedaluwarsa, biasanya karena siswa tidak menyelesaikan. --}}
    <div class="rounded-xl border border-garis bg-white p-4 shadow-sm dark:border-garis-gelap dark:bg-gelap-2">
        <p class="text-xs font-medium uppercase tracking-wide text-redup dark:text-redup-gelap">Gagal atau kedaluwarsa</p>
        <p class="mt-2 text-2xl font-bold text-tinta dark:text-teks-gelap">{{ $summary['failed'] }}</p>
        <p class="mt-1 text-xs text-redup dark:text-redup-gelap">perlu ditindaklanjuti</p>
    </div>
</div>
