@extends('public.simulation._layout')

@php $judul = 'Simulasi transfer'; @endphp

@section('isi')

    @if ($daftarVa->isEmpty())

        <div class="rounded-xl border border-garis bg-white px-5 py-8 text-center dark:border-garis-gelap dark:bg-gelap-2">
            <p class="text-sm leading-relaxed text-redup dark:text-redup-gelap">
                Belum ada Virtual Account aktif. Minta teller membuatkannya dulu di menu Virtual Account.
            </p>
        </div>

    @else

        {{-- Pencarian langsung ke nomor Virtual Account. --}}
        <form method="POST" action="{{ route('simulasi.cari') }}"
              class="rounded-xl border border-garis bg-white px-5 py-4 dark:border-garis-gelap dark:bg-gelap-2">
            @csrf

            <label for="nomor_va" class="text-[11px] font-medium text-redup dark:text-redup-gelap">Nomor Virtual Account</label>

            <div class="mt-1.5 flex flex-col gap-2 sm:flex-row sm:items-center">
                <input type="text" id="nomor_va" name="nomor_va" inputmode="numeric" autocomplete="off"
                       value="{{ old('nomor_va') }}" placeholder="7770 1001 3333"
                       class="min-w-0 flex-1 rounded-lg border border-garis bg-white px-3 py-2 font-mono text-sm text-tinta placeholder:text-redup focus:outline-none focus-visible:outline-2 focus-visible:outline-offset-1 focus-visible:outline-neon-teks dark:border-garis-gelap dark:bg-gelap dark:text-teks-gelap dark:placeholder:text-redup-gelap dark:focus-visible:outline-neon">
                <button type="submit"
                        class="tombol-tekan shrink-0 rounded-lg bg-neon px-4 py-2 text-sm font-semibold text-tinta hover:bg-neon-tekan">
                    Cari
                </button>
            </div>

            @error('nomor_va')
                <p class="mt-2 text-xs font-medium text-red-700 dark:text-red-400">{{ $message }}</p>
            @enderror
        </form>

        {{-- Daftar nomor yang sedang aktif, supaya tidak perlu menghafal. --}}
        <p class="mt-5 text-[11px] font-medium text-redup dark:text-redup-gelap">Atau pilih nomor yang aktif</p>

        <div class="mt-2 grid gap-2">
            @foreach ($daftarVa as $va)
                @php
                    // Sabuk pengaman: controller mengirim model VirtualAccount,
                    // jadi nama dan kelas diambil lewat relasi. Nomor tampil
                    // dipakai kalau account_number terisi.
                    $nomorVa = trim((string) $va->account_number) !== ''
                        ? $va->account_number
                        : $va->nomor_va;
                @endphp
                <a href="{{ route('simulasi.detail', $nomorVa) }}"
                   class="block rounded-xl border border-garis bg-white px-5 py-3.5 transition-colors hover:border-neon-teks hover:bg-latar-2 dark:border-garis-gelap dark:bg-gelap-2 dark:hover:border-neon dark:hover:bg-gelap">
                    <p class="font-mono text-sm font-semibold tracking-wide text-tinta dark:text-teks-gelap">@include('layouts.partials._nomor-va', ['nomor' => $nomorVa])</p>
                    <p class="mt-1 text-sm text-tinta dark:text-teks-gelap">{{ $va->student?->nama ?? 'Tanpa nama' }}</p>
                    <p class="mt-0.5 text-[11px] text-redup dark:text-redup-gelap">{{ $va->student?->nis ?? '-' }} &middot; {{ $va->student?->kelas?->nama_kelas ?? '-' }}</p>
                </a>
            @endforeach
        </div>

    @endif

@endsection