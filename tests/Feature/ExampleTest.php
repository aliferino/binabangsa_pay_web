<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * Tamu yang membuka halaman akar melihat halaman depan, bukan dialihkan.
     *
     * Halaman depan sengaja bisa dibuka tanpa login, supaya pengunjung tahu
     * produknya dulu sebelum masuk. Middleware auth baru berlaku mulai
     * /dashboard.
     */
    public function test_tamu_melihat_halaman_depan(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('BinaBangsa Pay');
    }

    /**
     * Pengguna yang sudah punya sesi langsung dilempar ke dashboard,
     * supaya tidak perlu mengetik ulang alamat setiap kali.
     */
    public function test_pengguna_masuk_dialihkan_ke_dashboard(): void
    {
        $user = User::where('role', 'admin')->first();

        if ($user === null) {
            $this->markTestSkipped('Tidak ada akun admin di database uji.');
        }

        $response = $this->actingAs($user)->get('/');

        $response->assertRedirect(route('dashboard'));
    }
}