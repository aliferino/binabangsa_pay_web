@extends('layouts.app')

@section('judul', 'Tagihan SPP')
@section('keterangan', 'Daftar tagihan SPP seluruh siswa')

@section('aksi')
    @if (auth()->user()->role === 'admin')
        <a href="{{ route('tagihan-spp.create') }}"
           class="rounded-lg bg-teal-700 px-4 py-2 text-sm font-semibold text-white hover:bg-teal-800">
            Tambah tagihan
        </a>

        {{-- Menandai tagihan yang sudah lewat jatuh tempo menjadi tunggakan. --}}
        <form method="POST" action="{{ route('tagihan-spp.tandai-tunggakan') }}"
              onsubmit="return confirm('Tandai semua tagihan yang sudah lewat jatuh tempo sebagai tunggakan?')">
            @csrf
            <button type="submit"
                    class="rounded-lg border border-amber-400 bg-amber-50 px-4 py-2 text-sm font-semibold text-amber-800 hover:bg-amber-100">
                Tandai tunggakan
            </button>
        </form>
    @endif
@endsection

@section('isi')

    {{--
        Form filter.
        Halaman ini dipakai admin dan teller lewat route berbeda, jadi aksi form
        memakai URL yang sedang dibuka supaya filter tetap bekerja untuk keduanya.
    --}}
    <form method="GET" action="{{ url()->current() }}"
          class="mb-4 rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-5">

            <div>
                <label for="id_siswa" class="mb-1 block text-xs font-medium text-slate-600">Siswa</label>
                <select name="id_siswa" id="id_siswa"
                        class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-teal-600 focus:outline-none focus:ring-2 focus:ring-teal-100">
                    <option value="">Semua siswa</option>
                    @foreach ($daftar_siswa as $siswa)
                        <option value="{{ $siswa->id_siswa }}" @selected(($filter['id_siswa'] ?? '') == $siswa->id_siswa)>
                            {{ $siswa->nis }} - {{ $siswa->nama }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="bulan" class="mb-1 block text-xs font-medium text-slate-600">Bulan</label>
                <select name="bulan" id="bulan"
                        class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-teal-600 focus:outline-none focus:ring-2 focus:ring-teal-100">
                    <option value="">Semua bulan</option>
                    @foreach (['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'] as $angka => $nama)
                        <option value="{{ $angka + 1 }}" @selected(($filter['bulan'] ?? '') == $angka + 1)>
                            {{ $nama }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="tahun" class="mb-1 block text-xs font-medium text-slate-600">Tahun</label>
                <select name="tahun" id="tahun"
                        class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-teal-600 focus:outline-none focus:ring-2 focus:ring-teal-100">
                    <option value="">Semua tahun</option>
                    @foreach ($daftar_tahun as $tahun)
                        <option value="{{ $tahun }}" @selected(($filter['tahun'] ?? '') == $tahun)>{{ $tahun }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="status" class="mb-1 block text-xs font-medium text-slate-600">Status</label>
                <select name="status" id="status"
                        class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-teal-600 focus:outline-none focus:ring-2 focus:ring-teal-100">
                    <option value="">Semua status</option>
                    <option value="belum_bayar" @selected(($filter['status'] ?? '') === 'belum_bayar')>Belum bayar</option>
                    <option value="lunas" @selected(($filter['status'] ?? '') === 'lunas')>Lunas</option>
                    <option value="tunggakan" @selected(($filter['status'] ?? '') === 'tunggakan')>Tunggakan</option>
                </select>
            </div>

            <div class="flex items-end gap-2">
                <button type="submit"
                        class="rounded-lg bg-teal-700 px-4 py-2 text-sm font-semibold text-white hover:bg-teal-800">
                    Terapkan
                </button>
                <a href="{{ url()->current() }}"
                   class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                    Reset
                </a>
            </div>
        </div>
    </form>

    {{-- Tabel tagihan. --}}
    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-5 py-3 font-semibold">Siswa</th>
                        <th class="px-5 py-3 font-semibold">Kelas</th>
                        <th class="px-5 py-3 font-semibold">Periode</th>
                        <th class="px-5 py-3 font-semibold">Nominal</th>
                        <th class="px-5 py-3 font-semibold">Jatuh tempo</th>
                        <th class="px-5 py-3 font-semibold">Status</th>
                        <th class="px-5 py-3 text-right font-semibold">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($daftar_tagihan as $tagihan)
                        <tr class="hover:bg-slate-50">
                            <td class="px-5 py-3">
                                <span class="font-medium text-slate-800">{{ $tagihan->siswa?->nama ?? '-' }}</span>
                                <span class="block text-xs text-slate-500">{{ $tagihan->siswa?->nis }}</span>
                            </td>
                            <td class="px-5 py-3 text-slate-600">{{ $tagihan->siswa?->kelas?->nama_kelas ?? '-' }}</td>
                            <td class="px-5 py-3 text-slate-700">{{ $tagihan->namaBulan() }} {{ $tagihan->tahun }}</td>
                            <td class="px-5 py-3 text-slate-800">
                                Rp{{ number_format((float) $tagihan->nominal, 0, ',', '.') }}
                            </td>
                            <td class="px-5 py-3 text-slate-600">
                                {{ $tagihan->tanggal_jatuh_tempo?->format('d/m/Y') ?? '-' }}
                            </td>
                            <td class="px-5 py-3">
                                @include('partials.lencana-status', ['status' => $tagihan->status])
                            </td>
                            <td class="px-5 py-3">
                                <div class="flex flex-wrap items-center justify-end gap-2">
                                    <a href="{{ route('tagihan-spp.show', $tagihan) }}"
                                       class="rounded-md border border-slate-300 px-2.5 py-1 text-xs font-medium text-slate-700 hover:bg-slate-100">
                                        Detail
                                    </a>

                                    @if (auth()->user()->role === 'admin')
                                        <a href="{{ route('tagihan-spp.edit', $tagihan) }}"
                                           class="rounded-md border border-slate-300 px-2.5 py-1 text-xs font-medium text-slate-700 hover:bg-slate-100">
                                            Ubah
                                        </a>

                                        <form method="POST" action="{{ route('tagihan-spp.destroy', $tagihan) }}"
                                              onsubmit="return confirm('Hapus data tagihan ini? Tindakan ini tidak bisa dibatalkan.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="rounded-md border border-red-300 px-2.5 py-1 text-xs font-medium text-red-700 hover:bg-red-50">
                                                Hapus
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-8 text-center text-slate-500">
                                Tidak ada tagihan yang cocok dengan filter ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

@endsection
