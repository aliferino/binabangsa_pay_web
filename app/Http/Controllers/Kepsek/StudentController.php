<?php

namespace App\Http\Controllers\Kepsek;

use App\Http\Controllers\Controller;
use App\Models\Classroom;
use App\Models\AcademicYear;
use App\Models\Student;
use Illuminate\Http\Request;

/**
 * Daftar siswa untuk kepala sekolah.
 *
 * Kepala sekolah hanya boleh melihat. Tidak ada method store, update, atau
 * destroy di sini, sebab kepala sekolah tidak boleh menambah, mengubah, atau
 * menghapus data siswa.
 */
class StudentController extends Controller
{
    /**
     * Daftar siswa, bisa difilter per kelas dan status.
     */
    public function index(Request $request)
    {
        $kueri = Student::with('kelas.jurusan', 'tahunAjaran', 'user');

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

        return $this->render('kepsek.students.index', [
            'students' => $kueri->orderBy('nama')->get(),
            'filter' => $request->only(['id_kelas', 'id_tahun_ajaran', 'status', 'cari']),
        ] + $this->pilihan());
    }

    /**
     * Detail satu siswa, termasuk daftar tagihannya.
     */
    public function show(Student $siswa)
    {
        return $this->render('kepsek.students.show', [
            'student' => $siswa->load('kelas.jurusan', 'tahunAjaran', 'user', 'tagihan'),
        ]);
    }

    /**
     * Data pilihan untuk filter daftar siswa.
     */
    private function pilihan(): array
    {
        return [
            'classrooms' => Classroom::with('jurusan')->orderBy('nama_kelas')->get(),
            'academicYears' => AcademicYear::orderByDesc('tahun_ajaran')->get(),
        ];
    }
}
