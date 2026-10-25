@extends('layouts.app')

@section('judul', 'Dashboard Siswa')
@section('keterangan', 'Tagihan SPP dan tunggakan Anda')

@section('isi')

    @php
        $rupiah = fn ($n) => 'Rp'.number_format((float) $n, 0, ',', '.');
        $rupiahRingkas = function ($n) use ($rupiah) {
            $n = (float) $n;
            if ($n >= 1000000) {
                return 'Rp'.rtrim(rtrim(number_format($n / 1000000, 1, ',', '.'), '0'), ',').' jt';
            }
            if ($n >= 1000) {
                return 'Rp'.number_format($n / 1000, 0, ',', '.').' rb';
            }
            return $rupiah($n);
        };
    @endphp

    {{--
        Kasus pertama: akun belum ditautkan ke data siswa.
        Controller mengirim $siswa bernilai null dan pesan penjelasnya.
        Halaman harus tetap tampil tanpa error.
    --}}
    @if ($siswa === null)
        <div class="rounded-xl border border-garis bg-white px-5 py-6 dark:border-garis-gelap dark:bg-gelap-2">
            <h2 class="text-sm font-semibold text-tinta dark:text-teks-gelap">Data belum tersedia</h2>
            <p class="mt-1 text-sm leading-relaxed text-redup dark:text-redup-gelap">
                {{ $pesan ?? 'Akun ini belum ditautkan ke data siswa.' }}
                                </p>
        </div>
    @else

        <div class="space-y-4">

            {{-- Pita identitas: nama di kiri, sisa tagihan di kanan. Angka uang dapat panggung sendiri. --}}
            <section class="overflow-hidden rounded-2xl bg-white ring-1 ring-garis dark:bg-gelap-2 dark:ring-garis-gelap">
                <div class="grid lg:grid-cols-[1fr_auto]">

                    <div class="flex flex-col justify-center px-6 py-7 lg:px-8">
                                <p class="text-[10px] font-semibold uppercase tracking-[0.18em] text-redup dark:text-redup-gelap">
                            Tahun ajaran {{ $siswa->tahunAjaran?->tahun_ajaran ?? '-' }}
                                </p>
                        <h2 class="mt-2 text-2xl font-bold leading-tight tracking-tight text-tinta dark:text-teks-gelap">
                            {{ $siswa->nama }}
                        </h2>
                        <p class="mt-1 text-sm text-redup dark:text-redup-gelap">
                            {{ $siswa->kelas?->nama_kelas ?? '-' }}
                            <span class="mx-1.5 text-garis dark:text-garis-gelap">/</span>
                            {{ $siswa->kelas?->jurusan?->nama_jurusan ?? '-' }}
                                </p>

                        <dl class="mt-5 flex flex-wrap gap-x-7 gap-y-2 text-xs">
                            @foreach ([['NIS', $siswa->nis], ['NISN', $siswa->nisn]] as [$label, $nilai])
                                <div>
                                    <dt class="text-redup dark:text-redup-gelap">{{ $label }}</dt>
                                    <dd class="mt-0.5 font-mono text-[13px] font-medium tabular-nums text-tinta dark:text-teks-gelap">{{ $nilai ?? '-' }}</dd>
                                </div>
                            @endforeach
                        </dl>
                    </div>

                    <div class="border-t border-garis bg-latar-2 px-6 py-6 lg:min-w-[19rem] lg:border-l lg:border-t-0 lg:px-8 dark:border-garis-gelap dark:bg-gelap">
                                <p class="text-[10px] font-semibold uppercase tracking-[0.18em] text-redup dark:text-redup-gelap">
                            Sisa tagihan
                                </p>
                        <p class="mt-1.5 text-[2.1rem] font-bold leading-none tabular-nums tracking-tight text-tinta dark:text-teks-gelap">
                            {{ $rupiah($totalOutstanding) }}
                                </p>

                        {{-- Satu petak per tagihan. Petak neon berarti lunas, jadi 0 lunas terlihat
                             sebagai deretan petak kosong, bukan batang penuh yang ambigu. --}}
                        <div class="mt-4 flex gap-1" role="img"
                             aria-label="{{ $paidCount }} dari {{ $totalBills }} tagihan lunas">
                            @foreach ($bills as $petak)
                                <span @class([
                                    'h-2 flex-1 rounded-sm',
                                    'bg-neon' => $petak->status === 'lunas',
                                    'bg-redup/25 dark:bg-redup-gelap/30' => $petak->status !== 'lunas',
                                ])></span>
                            @endforeach
                        </div>
                        <p class="mt-2 text-[11px] leading-relaxed text-redup dark:text-redup-gelap">
                            <span class="font-semibold tabular-nums text-tinta dark:text-teks-gelap">{{ $paidCount }} dari {{ $totalBills }}</span>
                            tagihan lunas, {{ $rupiah($totalBilled) }} totalnya.
                                </p>
                    </div>

                </div>
            </section>

            <div class="grid items-start gap-4 lg:grid-cols-[1.15fr_1fr]">

                {{-- Tagihan terdekat: yang paling mendesak, jadi paling atas dan paling tegas. --}}
                <section class="rounded-2xl bg-white px-6 py-6 ring-1 ring-garis dark:bg-gelap-2 dark:ring-garis-gelap">
                    <div class="flex items-baseline justify-between gap-3">
                        <h2 class="text-sm font-semibold text-tinta dark:text-teks-gelap">Perlu dibayar berikutnya</h2>
                        @if ($nextBill !== null && $daysLate > 0)
                            <span class="rounded bg-red-50 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-red-700 dark:bg-red-950/40 dark:text-red-400">
                                Lewat {{ $daysLate }} hari
                            </span>
                        @endif
                    </div>

                    @if ($nextBill === null)
                        <p class="mt-3 text-sm leading-relaxed text-redup dark:text-redup-gelap">
                            Semua tagihan sudah lunas. Tidak ada yang perlu dibayar.
                                </p>
                    @else
                        <div class="mt-4 flex items-end justify-between gap-4">
                            <div>
                                <p class="text-3xl font-bold leading-none tabular-nums tracking-tight text-tinta dark:text-teks-gelap">
                                    {{ $rupiah($nextBill->nominal) }}
                                </p>
                                <p class="mt-1.5 text-xs text-redup dark:text-redup-gelap">
                                    {{ $nextBill->namaBulan() }} {{ $nextBill->tahun }}
                                    @if ($nextBill->tanggal_jatuh_tempo)
                                        <span class="mx-1.5 text-garis dark:text-garis-gelap">/</span>
                                        jatuh tempo {{ $nextBill->tanggal_jatuh_tempo->format('d/m/Y') }}
                                    @endif
                                </p>
                            </div>
                            @include('layouts.partials._status-badge', ['tagihan' => $nextBill])
                        </div>

                        {{-- Langkah berikutnya harus jelas. Tanpa nomor VA, satu-satunya jalan lewat sekolah. --}}
                        <div class="mt-5 rounded-xl bg-latar-2 px-4 py-3 dark:bg-gelap">
                            @if ($virtualAccount?->nomor_va)
                                <p class="text-[10px] font-semibold uppercase tracking-[0.16em] text-redup dark:text-redup-gelap">Virtual account</p>
                                <p class="mt-1 font-mono text-base font-bold tabular-nums tracking-tight text-tinta dark:text-teks-gelap">
                                    @include('layouts.partials._nomor-va', ['nomor' => $virtualAccount->account_number ?: $virtualAccount->nomor_va])
                                </p>
                                <p class="mt-1 text-[11px] leading-relaxed text-redup dark:text-redup-gelap">
                                    Transfer ke nomor ini lewat
                                    {{ $virtualAccount->bank_code ? strtoupper($virtualAccount->bank_code) : 'bank terdaftar' }}.
                                    Status berubah otomatis setelah pembayaran masuk.
                                </p>
                            @else
                                <p class="text-[10px] font-semibold uppercase tracking-[0.16em] text-redup dark:text-redup-gelap">Cara membayar</p>
                                <p class="mt-1 text-[11px] leading-relaxed text-redup dark:text-redup-gelap">
                                    Nomor virtual account belum dibuat untuk akun ini. Bayar langsung di
                                    <span class="font-medium text-tinta dark:text-teks-gelap">teller sekolah</span>,
                                    atau minta nomor VA-nya ke teller kalau ingin transfer.
                                </p>
                            @endif
                        </div>
                    @endif
                </section>

                {{-- Riwayat sebagai garis waktu, bukan tabel. Lebih cepat dipindai mata. --}}
                <section class="rounded-2xl bg-white px-6 py-6 ring-1 ring-garis dark:bg-gelap-2 dark:ring-garis-gelap">
                    <h2 class="text-sm font-semibold text-tinta dark:text-teks-gelap">Riwayat tagihan</h2>
                    <p class="mt-0.5 text-[11px] text-redup dark:text-redup-gelap">Terbaru di atas.</p>

                    @if ($bills->isEmpty())
                        <div class="mt-5 rounded-xl bg-latar-2 px-4 py-6 text-center dark:bg-gelap">
                            <p class="text-sm font-medium text-tinta dark:text-teks-gelap">Belum ada tagihan</p>
                            <p class="mt-1 text-[11px] leading-relaxed text-redup dark:text-redup-gelap">
                                Tagihan muncul di sini setelah bagian keuangan menerbitkannya.
                                </p>
                        </div>
                    @else
                        <ol class="mt-4 space-y-0">
                            @foreach ($bills as $tagihan)
                                @php
                                    $lunas = $tagihan->status === 'lunas';
                                    $telatBaris = ! $lunas && $tagihan->tanggal_jatuh_tempo?->isPast();
                                @endphp
                                <li class="relative flex items-start gap-3 pb-4 pl-5 last:pb-0">
                                    {{-- Garis waktu: garis tipis di kiri, berhenti di baris terakhir. --}}
                                    @unless ($loop->last)
                                        <span class="absolute left-[3px] top-4 h-full w-px bg-garis dark:bg-garis-gelap" aria-hidden="true"></span>
                                    @endunless
                                    <span @class([
                                        'absolute left-0 top-[7px] h-[7px] w-[7px] rounded-full ring-2 ring-white dark:ring-gelap-2',
                                        'bg-neon' => $lunas,
                                        'bg-red-500' => $telatBaris,
                                        'bg-garis dark:bg-garis-gelap' => ! $lunas && ! $telatBaris,
                                    ]) aria-hidden="true"></span>

                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-baseline justify-between gap-3">
                                            <p class="truncate text-[13px] font-semibold text-tinta dark:text-teks-gelap">
                                                {{ $tagihan->namaBulan() }} {{ $tagihan->tahun }}
                                </p>
                                            <p class="shrink-0 text-[13px] font-semibold tabular-nums text-tinta dark:text-teks-gelap">
                                                {{ $rupiahRingkas($tagihan->nominal) }}
                                </p>
                                        </div>
                                        <p class="mt-0.5 text-[11px] text-redup dark:text-redup-gelap">
                                            Jatuh tempo {{ $tagihan->tanggal_jatuh_tempo?->format('d/m/Y') ?? '-' }}
                                            <span class="mx-1.5 text-garis dark:text-garis-gelap">/</span>
                                            {{ $lunas ? 'Lunas' : ($telatBaris ? 'Tunggakan' : 'Belum bayar') }}
                                </p>
                                    </div>
                                </li>
                            @endforeach
                        </ol>
                    @endif
                </section>

            </div>

        </div>
    @endif

@endsection