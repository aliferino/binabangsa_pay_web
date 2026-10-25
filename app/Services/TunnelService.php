<?php

namespace App\Services;

use RuntimeException;
use Symfony\Component\Process\Process;

/**
 * Menyalakan SSH reverse tunnel Pinggy dari dalam aplikasi.
 *
 * Tujuannya supaya Midtrans bisa menghubungi aplikasi yang jalan di localhost.
 * Sebelum ini, tunnel harus dinyalakan manual dari terminal.
 *
 * Catatan soal pelepasan proses. Proses ssh harus tetap hidup setelah request
 * PHP selesai. Symfony Process secara bawaan mematikan proses di destructor,
 * jadi kita pakai opsi create_new_console. Dengan opsi itu destructor hanya
 * menutup pipa, prosesnya dibiarkan jalan.
 *
 * Opsi -N sengaja TIDAK dipakai. Dengan -N, ssh tidak mencetak URL tunnel,
 * sehingga URL tidak pernah bisa dibaca. Ini sudah diuji di mesin ini.
 */
class TunnelService
{
    /**
     * Binary ssh yang dipakai. Versi Git for Windows, OpenSSH 10.0p2.
     * Jangan diganti ke /usr/bin/ssh.exe, versinya usang.
     */
    private const SSH = 'C:/Program Files/Git/usr/bin/ssh.exe';

    private const HOST = 'free@a.pinggy.io';

    private const PORT = '443';

    /**
     * Port aplikasi lokal yang dibuka ke publik.
     */
    private const LOCAL_PORT = '8000';

    /**
     * Batas tunggu URL muncul, dalam detik.
     */
    private const BATAS_URL = 30;

    /**
     * Jumlah percobaan menyalakan tunnel.
     *
     * Penyelesaian nama host ke a.pinggy.io kadang meleset sekali di mesin ini.
     * Gejalanya ssh langsung berhenti dengan "Could not resolve hostname".
     * Percobaan berikutnya biasanya berhasil, jadi dicoba beberapa kali dulu
     * sebelum menyerah.
     */
    private const PERCOBAAN = 3;

    /**
     * Umur tunnel Pinggy gratis, dalam menit.
     */
    private const UMUR_MENIT = 60;

    /**
     * Berkas known_hosts sendiri. Jangan pakai known_hosts bawaan user supaya
     * kunci host Pinggy tidak menumpuk di berkas pribadi.
     *
     * Ditaruh di storage/app supaya tidak mengotori folder repo dan otomatis
     * tercakup gitignore lewat pola storage.
     */
    private function knownHosts(): string
    {
        $folder = storage_path('app');

        if (! is_dir($folder)) {
            @mkdir($folder, 0775, true);
        }

        return $folder.DIRECTORY_SEPARATOR.'known_hosts_pinggy';
    }

    private function berkasKeadaan(): string
    {
        return storage_path('app/tunnel.json');
    }

    /**
     * Daftar putih variabel lingkungan untuk proses ssh.
     *
     * Kalau Process dibiarkan mewarisi seluruh lingkungan proses PHP, ssh
     * kadang gagal menyelesaikan nama host a.pinggy.io di mesin ini, dengan
     * pesan "Could not resolve hostname". Lingkungan yang dipersempit ke
     * variabel Windows yang diperlukan saja membuatnya stabil. Variabel
     * aplikasi (DB, session, dan sejenisnya) juga tidak perlu ikut ke proses
     * luar.
     *
     * @return array<string, string>
     */
    private function lingkungan(): array
    {
        $ambil = [
            'SystemRoot', 'windir', 'SystemDrive', 'ComSpec', 'PATH', 'PATHEXT',
            'TEMP', 'TMP', 'USERPROFILE', 'HOMEDRIVE', 'HOMEPATH', 'APPDATA',
            'LOCALAPPDATA', 'USERNAME', 'USERDOMAIN', 'NUMBER_OF_PROCESSORS',
            'PROCESSOR_ARCHITECTURE', 'PROCESSOR_IDENTIFIER', 'OS',
        ];

        $lingkungan = [];

        foreach ($ambil as $nama) {
            $nilai = getenv($nama);

            if ($nilai !== false && $nilai !== '') {
                $lingkungan[$nama] = $nilai;
            }
        }

        // Symfony Process menulis berkas sementara di TMP/TEMP. Kalau nilainya
        // menunjuk folder repo, berkas sf_proc_* bermunculan di root proyek.
        // Diarahkan ke storage/app supaya rapi dan tercakup gitignore.
        $temp = storage_path('app');

        if (! is_dir($temp)) {
            @mkdir($temp, 0775, true);
        }

        $lingkungan['TMP'] = $temp;
        $lingkungan['TEMP'] = $temp;

        // SystemRoot wajib ada, kalau tidak OpenSSH tidak bisa memuat konfigurasi.
        if (! isset($lingkungan['SystemRoot'])) {
            $lingkungan['SystemRoot'] = 'C:\\Windows';
        }

        return $lingkungan;
    }

    /**
     * Susun argumen ssh.
     */
    private function argumen(): array
    {
        return [
            self::SSH,
            '-p', self::PORT,
            '-o', 'StrictHostKeyChecking=no',
            '-o', 'UserKnownHostsFile='.$this->knownHosts(),
            // Gagal cepat kalau nama host tidak bisa diselesaikan, supaya
            // percobaan berikutnya tidak menunggu lama.
            '-o', 'ConnectTimeout=8',
            '-R0:localhost:'.self::LOCAL_PORT,
            self::HOST,
        ];
    }

    /**
     * Nyalakan tunnel, tunggu sampai URL muncul, simpan keadaan.
     *
     * @return array{pid:int,url:string,mulai:string,kedaluwarsa:string}
     */
    public function nyalakan(): array
    {
        if ($this->aktif()) {
            throw new RuntimeException(
                'Tunnel sudah menyala. Matikan dulu sebelum menyalakan yang baru.'
            );
        }

        if (! is_file(self::SSH)) {
            throw new RuntimeException('Binary ssh tidak ditemukan di '.self::SSH);
        }

        $url = null;
        $proses = null;
        $galat = null;

        // Catat dulu ssh yang sudah ada, supaya yang baru bisa dikenali.
        $sshSebelum = $this->pidSsh();

        for ($percobaan = 1; $percobaan <= self::PERCOBAAN; $percobaan++) {
            $proses = new Process($this->argumen());
            $proses->setEnv($this->lingkungan());
            $proses->setInput(null);
            $proses->setOptions(['create_new_console' => true]);
            $proses->setTimeout(null);
            $proses->start();

            try {
                $url = $this->bacaUrl($proses);

                break;
            } catch (RuntimeException $e) {
                $galat = $e->getMessage().' Keluaran mentah: '.$this->keluaranMentah($proses);

                // Proses yang gagal dimatikan dulu, kalau tidak sisa prosesnya
                // menumpuk tiap percobaan.
                $this->bunuh($proses->getPid());

                if ($percobaan < self::PERCOBAAN) {
                    sleep(1);
                }
            }
        }

        if ($url === null) {
            throw new RuntimeException(
                'Tunnel gagal dinyalakan setelah '.self::PERCOBAAN.' percobaan. '.$galat
            );
        }

        $pid = $this->pidSebenarnya($sshSebelum, $proses);

        if ($pid === null) {
            // Tunnel sudah jalan tapi prosesnya tidak bisa dikenali, jadi
            // dibunuh lagi daripada tertinggal tanpa bisa dimatikan.
            $this->bunuhSemuaSshBaru($sshSebelum);

            throw new RuntimeException('Proses ssh jalan, tetapi PID-nya tidak bisa dikenali, jadi tunnel dibatalkan.');
        }

        $mulai = now();

        $keadaan = [
            'pid' => $pid,
            'url' => $url,
            'mulai' => $mulai->toIso8601String(),
            'kedaluwarsa' => $mulai->copy()->addMinutes(self::UMUR_MENIT)->toIso8601String(),
        ];

        file_put_contents(
            $this->berkasKeadaan(),
            json_encode($keadaan, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
        );

        // Lepaskan objek Process tanpa mematikan proses ssh. Dengan
        // create_new_console, destructor hanya menutup pipa.
        unset($proses);

        return $keadaan;
    }

    /**
     * Baca stdout bertahap sampai baris https:// pertama muncul.
     */
    private function bacaUrl(Process $proses): string
    {
        $tenggat = microtime(true) + self::BATAS_URL;

        while (microtime(true) < $tenggat) {
            if (! $proses->isRunning()) {
                throw new RuntimeException('Proses ssh berhenti sebelum URL tunnel muncul.');
            }

            foreach (preg_split('/\R/', $proses->getIncrementalOutput()) as $baris) {
                $baris = trim($baris);

                if (str_starts_with($baris, 'https://')) {
                    return $baris;
                }
            }

            usleep(250000);
        }

        throw new RuntimeException(
            'URL tunnel tidak muncul dalam '.self::BATAS_URL.' detik.'
        );
    }

    /**
     * Keluaran yang sudah terbaca, untuk pesan galat. Dipotong supaya log rapi.
     */
    private function keluaranMentah(Process $proses): string
    {
        $teks = trim($proses->getOutput()."\n".$proses->getErrorOutput());
        $teks = preg_replace('/\s+/', ' ', $teks);

        return $teks === '' ? '(kosong)' : mb_substr($teks, 0, 500);
    }

    /**
     * Keadaan tunnel yang tersimpan, atau null kalau belum pernah dinyalakan
     * atau PID-nya sudah mati. Berkas keadaan yang menunjuk proses mati dibuang.
     */
    public function status(): ?array
    {
        $berkas = $this->berkasKeadaan();

        if (! is_file($berkas)) {
            return null;
        }

        $keadaan = json_decode((string) file_get_contents($berkas), true);

        if (! is_array($keadaan) || empty($keadaan['pid']) || empty($keadaan['url'])) {
            @unlink($berkas);

            return null;
        }

        if (! $this->pidHidup((int) $keadaan['pid'])) {
            @unlink($berkas);

            return null;
        }

        return $keadaan;
    }

    public function aktif(): bool
    {
        return $this->status() !== null;
    }

    /**
     * Matikan tunnel dan hapus berkas keadaan.
     *
     * Aman dipanggil walau tunnel sudah mati.
     */
    public function matikan(): void
    {
        $keadaan = $this->status();

        if ($keadaan !== null) {
            $this->bunuh((int) $keadaan['pid']);
        }

        if (is_file($this->berkasKeadaan())) {
            @unlink($this->berkasKeadaan());
        }
    }

    /**
     * Semua PID proses ssh.exe yang sedang jalan.
     *
     * Dipakai untuk menemukan PID ssh yang sebenarnya. getPid() dari Symfony
     * TIDAK bisa dipakai di Windows dengan create_new_console: angka yang
     * dikembalikan bukan proses ssh yang bertahan, jadi mematikannya dengan
     * taskkill tidak mengenai apa pun dan prosesnya tertinggal.
     *
     * @return list<int>
     */
    private function pidSsh(): array
    {
        $daftar = new Process(['tasklist', '/FI', 'IMAGENAME eq ssh.exe', '/FO', 'CSV', '/NH']);
        $daftar->setEnv($this->lingkungan());
        $daftar->setTimeout(15);

        try {
            $daftar->run();
        } catch (\Throwable) {
            return [];
        }

        $pid = [];

        foreach (preg_split('/\R/', $daftar->getOutput()) as $baris) {
            // Format CSV: "ssh.exe","1234","Console","1","12.345 K"
            if (preg_match('/^"ssh\.exe","(\d+)"/i', trim($baris), $cocok) === 1) {
                $pid[] = (int) $cocok[1];
            }
        }

        return $pid;
    }

    /**
     * PID proses ssh yang baru saja lahir.
     *
     * Diambil dari selisih daftar ssh sebelum dan sesudah dijalankan. Cara ini
     * yang benar di Windows; getPid() dari Symfony menunjuk proses perantara,
     * bukan ssh yang bertahan.
     */
    private function pidSebenarnya(array $sebelum, Process $proses): ?int
    {
        // URL bisa muncul sedikit lebih dulu daripada proses ssh terdaftar di
        // daftar proses, jadi dicari beberapa kali dulu, bukan sekali.
        for ($putaran = 0; $putaran < 15; $putaran++) {
            $baru = array_values(array_diff($this->pidSsh(), $sebelum));

            if ($baru !== []) {
                // Kalau ada lebih dari satu, yang terbesar biasanya yang
                // terakhir lahir, dan itu milik percobaan ini.
                return max($baru);
            }

            usleep(200000);
        }

        $pid = $proses->getPid();

        return $pid !== null && $this->pidHidup($pid) ? $pid : null;
    }

    /**
     * Bunuh semua ssh yang lahir sejak pencatatan awal.
     */
    private function bunuhSemuaSshBaru(array $sebelum): void
    {
        foreach (array_diff($this->pidSsh(), $sebelum) as $pid) {
            $this->bunuh($pid);
        }
    }

    /**
     * PID masih hidup? tasklist dipakai supaya tidak perlu ekstensi tambahan.
     */
    private function pidHidup(int $pid): bool
    {
        if ($pid <= 0) {
            return false;
        }

        $cek = new Process(['tasklist', '/FI', 'PID eq '.$pid, '/NH']);
        $cek->setEnv($this->lingkungan());
        $cek->setTimeout(10);

        try {
            $cek->run();
        } catch (\Throwable) {
            return false;
        }

        return str_contains($cek->getOutput(), (string) $pid);
    }

    /**
     * Bunuh proses beserta anaknya. /T penting: ssh.exe bisa punya anak proses.
     */
    private function bunuh(?int $pid): void
    {
        if ($pid === null || $pid <= 0) {
            return;
        }

        $bunuh = new Process(['taskkill', '/PID', (string) $pid, '/T', '/F']);
        $bunuh->setEnv($this->lingkungan());
        $bunuh->setTimeout(15);

        try {
            $bunuh->run();
        } catch (\Throwable) {
            // Proses sudah mati sendiri, tidak perlu jadi galat.
        }
    }
}
