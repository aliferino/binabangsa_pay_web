<?php

namespace App\Http\Controllers\Kepsek;

use App\Http\Controllers\Admin\ReportController as AdminReportController;

/**
 * Laporan untuk kepala sekolah.
 *
 * Isi laporan kepala sekolah sama persis dengan laporan admin: filter, kolom,
 * dan perhitungannya tidak berbeda. Karena route laporan dipakai bersama oleh
 * admin, teller, dan kepala sekolah lewat satu nama route yang sama
 * (laporan.pembayaran dan laporan.tunggakan), logikanya sengaja tidak
 * diduplikasi ke berkas terpisah. Kalau nanti kepala sekolah butuh kolom atau
 * filter sendiri, method-nya tinggal ditimpa di kelas ini tanpa menyentuh
 * laporan admin.
 */
class ReportController extends AdminReportController
{
}
