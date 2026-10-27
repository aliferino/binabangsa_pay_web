@extends('public.simulation._layout')

@php $judul = 'Simulasi transfer'; @endphp

@section('isi')

    {{-- Hanya ketik manual. Tidak ada daftar nomor yang bisa diklik. --}}
    <form method="POST" action="{{ route('simulasi.cari') }}"
          class="rounded-xl border border-garis bg-white px-5 py-4 dark:border-garis-gelap dark:bg-gelap-2">
        @csrf

        <label for="nomor_va" class="text-[11px] font-medium text-redup dark:text-redup-gelap">Nomor Virtual Account</label>

        <div class="mt-1.5 flex flex-col gap-2 sm:flex-row sm:items-center">
            <input type="text" id="nomor_va" name="nomor_va" inputmode="numeric" autocomplete="off"
                   value="{{ old('nomor_va') }}" placeholder="7770 1001 3333"
                   @error('nomor_va') aria-invalid="true" aria-describedby="galat-nomor_va" @enderror
                   class="min-w-0 flex-1 rounded-lg border border-garis bg-white px-3 py-2 font-mono text-sm text-tinta placeholder:text-redup focus:outline-none focus-visible:outline-2 focus-visible:outline-offset-1 focus-visible:outline-neon-teks dark:border-garis-gelap dark:bg-gelap dark:text-teks-gelap dark:placeholder:text-redup-gelap dark:focus-visible:outline-neon">
            <button type="submit"
                    class="border border-transparent tombol-tekan shrink-0 rounded-lg bg-neon px-4 py-2 text-sm font-semibold text-tinta hover:bg-neon-tekan">
                Cari
            </button>
        </div>

        @error('nomor_va')
            <p id="galat-nomor_va" class="mt-2 text-xs font-medium text-red-700 dark:text-red-400">{{ $message }}</p>
        @enderror
    </form>

@endsection
