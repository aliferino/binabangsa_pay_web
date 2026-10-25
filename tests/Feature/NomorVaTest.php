<?php

namespace Tests\Feature;

use App\Models\VirtualAccount;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Nomor Virtual Account harus 12 digit dan berakhiran NIS.
 *
 * Nomor yang dikirim ke Midtrans tetap 7 digit karena Midtrans menempelkan
 * prefix merchant sendiri. Yang 12 digit adalah nomor yang ditampilkan dan
 * disimpan.
 */
class NomorVaTest extends TestCase
{
    public function test_nomor_kirim_ke_midtrans_tujuh_digit(): void
    {
        $this->assertSame('0012345', VirtualAccount::generateNumber('12345'));
        $this->assertSame('0012345', VirtualAccount::generateNomorVa('12345'));
    }

    public function test_nomor_lengkap_dua_belas_digit_berakhiran_nis(): void
    {
        $nomor = VirtualAccount::nomorLengkap('12345');

        $this->assertSame('777010012345', $nomor);
        $this->assertSame(12, strlen($nomor));
        $this->assertTrue(str_starts_with($nomor, '77701'));
        $this->assertTrue(str_ends_with($nomor, '12345'));
    }

    /**
     * @return array<string, array{0:string, 1:string}>
     */
    public static function nisProvider(): array
    {
        return [
            'nis lima digit' => ['12345', '12345'],
            'nis satu digit' => ['7', '00007'],
            'nis pendek tiga digit' => ['123', '00123'],
            'nis panjang' => ['2612345', '12345'],
            'nis dengan huruf' => ['12A34B5', '12345'],
            'nis dengan spasi dan strip' => ['12 345-6', '23456'],
        ];
    }

    #[DataProvider('nisProvider')]
    public function test_lima_digit_terakhir_nis_selalu_di_belakang(string $nis, string $ekor): void
    {
        $nomor = VirtualAccount::nomorLengkap($nis);

        $this->assertSame(12, strlen($nomor), 'nomor untuk NIS '.$nis.' harus 12 digit');
        $this->assertSame($ekor, substr($nomor, -5), 'lima digit terakhir harus dari NIS');
        $this->assertSame($ekor, substr(VirtualAccount::generateNumber($nis), -5));
    }

    public function test_prefix_bisa_diganti_tapi_tetap_dua_belas_digit(): void
    {
        $this->assertSame('123450012345', VirtualAccount::nomorLengkap('12345', '12345'));
    }

    public function test_nomor_tampil_dikelompokkan_empat_empat_empat(): void
    {
        $va = new VirtualAccount([
            'nomor_va' => '777010012345',
            'account_number' => '777010012345',
        ]);

        $this->assertSame('7770 1001 2345', $va->nomorTampil());
    }

    public function test_nomor_tampil_jatuh_ke_nomor_va_kalau_account_number_kosong(): void
    {
        $va = new VirtualAccount([
            'nomor_va' => '777010012345',
            'account_number' => '',
        ]);

        $this->assertSame('7770 1001 2345', $va->nomorTampil());
    }
}