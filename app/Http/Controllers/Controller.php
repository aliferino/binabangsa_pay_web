<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;

abstract class Controller
{
    /**
     * Kembalikan tampilan kalau berkas Blade-nya sudah ada.
     *
     * Tugas ini hanya membuat controller dan route, jadi berkas Blade belum
     * dibuat. Supaya route tetap bisa dipanggil tanpa error, data dikirim
     * sebagai JSON selama tampilan belum tersedia. Begitu Blade ditambahkan
     * nanti, method ini otomatis memakai tampilan itu tanpa perlu diubah.
     */
    protected function tampilkan(string $view, array $data = [], int $status = 200): View|JsonResponse
    {
        if (view()->exists($view)) {
            return view($view, $data);
        }

        return response()->json([
            'pesan' => 'Tampilan belum tersedia, ini data mentahnya.',
            'tampilan' => $view,
            'data' => $data,
        ], $status);
    }
}
