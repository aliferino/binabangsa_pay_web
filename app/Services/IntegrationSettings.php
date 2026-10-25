<?php

namespace App\Services;

/**
 * Pengaturan integrasi gateway yang bisa diedit dari halaman admin.
 *
 * Disimpan sebagai berkas JSON di storage/app/integration.json, bukan di
 * database, supaya kunci bisa diganti tanpa migrasi dan tanpa menyentuh
 * berkas .env.
 *
 * Isinya rahasia, jadi berkasnya tidak boleh masuk git dan tidak boleh
 * ditampilkan utuh ke layar. Gunakan tersamar() untuk keperluan tampilan.
 */
class IntegrationSettings
{
    /**
     * Kunci yang dikelola di sini, beserta nama config bawaannya.
     *
     * @var array<string, string>
     */
    private const KUNCI = [
        'midtrans_server_key' => 'midtrans.server_key',
        'midtrans_client_key' => 'midtrans.client_key',
        'midtrans_is_production' => 'midtrans.is_production',
    ];

    /**
     * Lokasi berkas pengaturan.
     */
    public function berkas(): string
    {
        return storage_path('app/integration.json');
    }

    /**
     * Semua nilai yang berlaku sekarang.
     *
     * Berkas menang kalau kuncinya ada dan terisi. Kalau berkas belum ada,
     * atau kuncinya kosong, nilai bawaan dari config dipakai.
     *
     * @return array<string, mixed>
     */
    public function semua(): array
    {
        $berkas = $this->bacaBerkas();
        $nilai = [];

        foreach (self::KUNCI as $nama => $config) {
            $dariBerkas = $berkas[$nama] ?? null;
            $bawaan = config($config);

            $nilai[$nama] = $nama === 'midtrans_is_production'
                ? $this->keBool($dariBerkas ?? $bawaan)
                : ((is_string($dariBerkas) && $dariBerkas !== '') ? $dariBerkas : (string) $bawaan);
        }

        return $nilai;
    }

    /**
     * Simpan nilai baru. String kosong berarti jangan diubah, jadi nilai lama
     * dipertahankan. Ini yang bikin form boleh mengosongkan field kunci.
     *
     * @param  array<string, mixed>  $baru
     */
    public function simpan(array $baru): void
    {
        $lama = $this->bacaBerkas();
        $hasil = [];

        foreach (array_keys(self::KUNCI) as $nama) {
            if (! array_key_exists($nama, $baru)) {
                // Tidak dikirim, berarti tidak diubah.
                if (array_key_exists($nama, $lama)) {
                    $hasil[$nama] = $lama[$nama];
                }

                continue;
            }

            $nilai = $baru[$nama];

            if ($nilai === null || $nilai === '') {
                // Sengaja dikosongkan, pertahankan yang lama kalau ada.
                if (array_key_exists($nama, $lama) && $lama[$nama] !== '') {
                    $hasil[$nama] = $lama[$nama];
                }

                continue;
            }

            $hasil[$nama] = $nama === 'midtrans_is_production'
                ? $this->keBool($nilai)
                : (string) $nilai;
        }

        // Gabung supaya kunci yang belum pernah diurus tidak hilang.
        $hasil = array_merge($lama, $hasil);

        $this->tulisAtomik($hasil);
    }

    /**
     * Hapus berkas supaya kembali memakai nilai bawaan .env.
     */
    public function reset(): void
    {
        if (is_file($this->berkas())) {
            @unlink($this->berkas());
        }
    }

    /**
     * Dari mana nilai yang berlaku berasal: 'berkas' atau 'env'.
     */
    public function sumber(): string
    {
        $berkas = $this->bacaBerkas();

        foreach (array_keys(self::KUNCI) as $nama) {
            if (array_key_exists($nama, $berkas) && $berkas[$nama] !== null && $berkas[$nama] !== '') {
                return 'berkas';
            }
        }

        return 'env';
    }

    /**
     * Bentuk tersamar untuk ditampilkan: 12 karakter awal, titik-titik, lalu
     * 4 karakter akhir. Kunci pendek jadi bintang, dan yang kosong diberi
     * keterangan supaya tidak tampak seperti rusak.
     */
    public function tersamar(?string $kunci): string
    {
        $kunci = (string) $kunci;

        if ($kunci === '') {
            return 'Belum diisi';
        }

        if (mb_strlen($kunci) < 16) {
            return str_repeat('*', mb_strlen($kunci));
        }

        return mb_substr($kunci, 0, 12).'...'.mb_substr($kunci, -4);
    }

    /**
     * Baca berkas. Isi yang rusak dianggap belum ada.
     *
     * @return array<string, mixed>
     */
    private function bacaBerkas(): array
    {
        $berkas = $this->berkas();

        if (! is_file($berkas)) {
            return [];
        }

        $isi = json_decode((string) @file_get_contents($berkas), true);

        return is_array($isi) ? $isi : [];
    }

    /**
     * Tulis lewat berkas sementara lalu rename, supaya berkas tidak pernah
     * setengah tertulis kalau prosesnya mati di tengah.
     *
     * @param  array<string, mixed>  $isi
     */
    private function tulisAtomik(array $isi): void
    {
        $folder = dirname($this->berkas());

        if (! is_dir($folder)) {
            @mkdir($folder, 0775, true);
        }

        $sementara = $folder.DIRECTORY_SEPARATOR.'integration.json.'.bin2hex(random_bytes(6)).'.tmp';

        $teks = json_encode($isi, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        if (@file_put_contents($sementara, $teks) === false) {
            @unlink($sementara);

            throw new \RuntimeException('Berkas pengaturan integrasi tidak bisa ditulis: '.$sementara);
        }

        // rename menimpa berkas tujuan, dan tetap aman di Windows.
        if (! @rename($sementara, $this->berkas())) {
            @unlink($sementara);

            throw new \RuntimeException('Berkas pengaturan integrasi tidak bisa diganti.');
        }
    }

    /**
     * Nilai dari form datang sebagai string '1' atau '0'.
     */
    private function keBool(mixed $nilai): bool
    {
        if (is_bool($nilai)) {
            return $nilai;
        }

        return in_array(strtolower((string) $nilai), ['1', 'true', 'ya', 'on'], true);
    }
}
