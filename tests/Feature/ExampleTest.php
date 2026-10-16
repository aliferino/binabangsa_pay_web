<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * Halaman akar sekarang mengarahkan ke dashboard.
     *
     * Tamu yang belum login akan diteruskan lagi ke halaman login oleh
     * middleware auth. Jadi jawabannya berupa pengalihan, bukan halaman 200.
     */
    public function test_halaman_akar_mengarahkan_ke_dashboard(): void
    {
        $response = $this->get('/');

        $response->assertRedirect(route('dashboard'));
    }
}
