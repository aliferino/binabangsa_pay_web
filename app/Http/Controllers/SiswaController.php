<?php

namespace App\Http\Controllers;

use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SiswaController extends Controller
{
    /**
     * Daftar siswa.
     */
    public function index(Request $request)
    {
        $kueri = Siswa::with('kelas.jurusan', 'tahunAjaran', 'user');

        // Filter opsional supaya daftar panjang tetap enak dibaca.
        if ($request->filled('id_kelas')) {
            $kueri->where('id_kelas', $request->input('id_kelas'));
        }

        if ($request->filled('id_tahun_ajaran')) {
            $kueri->where('id_tahun_ajaran', $request->input('id_tahun_ajaran'));
        }

        if ($request->filled('status')) {
            $kueri->where('status', $request->input('status'));
        }

        if ($request->filled('cari')) {
            $kata = $request->input('cari');
            $kueri->where(function ($q) use ($kata) {
                $q->where('nama', 'like', '%'.$kata.'%')
                    ->orWhere('nis', 'like', '%'.$kata.'%')
                    ->orWhere('nisn', 'like', '%'.$kata.'%');
            });
        }

        return $this->tampilkan('siswa.index', [
            'daftar_siswa' => $kueri->orderBy('nama')->get(),
            'filter' => $request->only(['id_kelas', 'id_tahun_ajaran', 'status', 'cari']),
        ] + $this->pilihan());
    }

    /**
     * Tampilkan form tambah siswa.
     */
    public function create()
    {
        return $this->tampilkan('siswa.create', $this->pilihan());
    }

    /**
     * Simpan siswa baru.
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate($this->aturan(), $this->pesan());

        Siswa::create($data);

        return redirect()->route('siswa.index')->with('sukses', 'Siswa berhasil ditambahkan.');
    }

    /**
     * Tampilkan detail satu siswa.
     */
    public function show(Siswa $siswa)
    {
        return $this->tampilkan('siswa.show', [
            'siswa' => $siswa->load('kelas.jurusan', 'tahunAjaran', 'user', 'tagihan'),
        ]);
    }

    /**
     * Tampilkan form ubah siswa.
     */
    public function edit(Siswa $siswa)
    {
        return $this->tampilkan('siswa.edit', array_merge(
            ['siswa' => $siswa],
            $this->pilihan(),
        ));
    }

    /**
     * Perbarui data siswa.
     */
    public function update(Request $request, Siswa $siswa): RedirectResponse
    {
        $data = $request->validate(
            $this->aturan($siswa),
            $this->pesan(),
        );

        $siswa->update($data);

        return redirect()->route('siswa.index')->with('sukses', 'Siswa berhasil diperbarui.');
    }

    /**
     * Hapus siswa.
     *
     * Siswa yang sudah punya tagihan tidak dihapus, supaya riwayat tagihan
     * tidak ikut terhapus lewat cascade.
     */
    public function destroy(Siswa $siswa): RedirectResponse
    {
        if ($siswa->tagihan()->exists()) {
            return redirect()->route('siswa.index')
                ->with('gagal', 'Siswa ini sudah punya tagihan, jadi belum bisa dihapus.');
        }

        $siswa->delete();

        return redirect()->route('siswa.index')->with('sukses', 'Siswa berhasil dihapus.');
    }

    /**
     * Aturan validasi siswa.
     *
     * NIS dan NISN wajib unik. Saat update, baris yang sedang diubah
     * dikecualikan supaya tidak dianggap bentrok dengan dirinya sendiri.
     */
    private function aturan(?Siswa $siswa = null): array
    {
        $nis = Rule::unique('siswa', 'nis');
        $nisn = Rule::unique('siswa', 'nisn');

        if ($siswa !== null) {
            $nis = $nis->ignore($siswa->id_siswa, 'id_siswa');
            $nisn = $nisn->ignore($siswa->id_siswa, 'id_siswa');
        }

        return [
            'nis' => ['required', 'string', 'max:255', $nis],
            'nisn' => ['required', 'string', 'max:255', $nisn],
            'nama' => ['required', 'string', 'max:255'],
            'jenis_kelamin' => ['required', Rule::in(['L', 'P'])],
            'tempat_lahir' => ['required', 'string', 'max:255'],
            'tanggal_lahir' => ['required', 'date'],
            'alamat' => ['required', 'string'],
            'no_hp' => ['nullable', 'string', 'max:255'],
            'nama_orang_tua' => ['nullable', 'string', 'max:255'],
            'id_kelas' => ['required', 'exists:kelas,id_kelas'],
            'id_tahun_ajaran' => ['required', 'exists:tahun_ajaran,id_tahun_ajaran'],
            'id_user' => ['nullable', 'exists:users,id_user'],
            'status' => ['required', Rule::in(['aktif', 'lulus', 'keluar'])],
        ];
    }

    /**
     * Pesan validasi dalam bahasa Indonesia.
     */
    private function pesan(): array
    {
        return [
            'nis.required' => 'NIS wajib diisi.',
            'nis.unique' => 'NIS ini sudah dipakai siswa lain.',
            'nisn.required' => 'NISN wajib diisi.',
            'nisn.unique' => 'NISN ini sudah dipakai siswa lain.',
            'nama.required' => 'Nama wajib diisi.',
            'jenis_kelamin.required' => 'Jenis kelamin wajib dipilih.',
            'jenis_kelamin.in' => 'Jenis kelamin harus L atau P.',
            'tempat_lahir.required' => 'Tempat lahir wajib diisi.',
            'tanggal_lahir.required' => 'Tanggal lahir wajib diisi.',
            'tanggal_lahir.date' => 'Tanggal lahir harus berupa tanggal yang sah.',
            'alamat.required' => 'Alamat wajib diisi.',
            'id_kelas.required' => 'Kelas wajib dipilih.',
            'id_kelas.exists' => 'Kelas yang dipilih tidak ditemukan.',
            'id_tahun_ajaran.required' => 'Tahun ajaran wajib dipilih.',
            'id_tahun_ajaran.exists' => 'Tahun ajaran yang dipilih tidak ditemukan.',
            'id_user.exists' => 'Akun yang dipilih tidak ditemukan.',
            'status.in' => 'Status harus aktif, lulus, atau keluar.',
        ];
    }

    /**
     * Data pilihan untuk form.
     *
     * Daftar akun hanya menampilkan user berrole siswa yang belum dipakai
     * siswa lain, supaya satu akun tidak tertaut ke dua data siswa.
     */
    private function pilihan(): array
    {
        $akunTerpakai = Siswa::whereNotNull('id_user')->pluck('id_user');

        return [
            'daftar_kelas' => Kelas::with('jurusan')->orderBy('nama_kelas')->get(),
            'daftar_tahun_ajaran' => TahunAjaran::orderByDesc('tahun_ajaran')->get(),
            'daftar_akun' => User::where('role', 'siswa')
                ->whereNotIn('id_user', $akunTerpakai)
                ->orderBy('nama')
                ->get(),
        ];
    }
}
