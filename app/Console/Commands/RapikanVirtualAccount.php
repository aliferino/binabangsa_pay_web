<?php

namespace App\Console\Commands;

use App\Models\VirtualAccount;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Merapikan nomor Virtual Account lama supaya seragam 12 digit berakhiran NIS.
 *
 * Dijalankan sebagai perintah, bukan migrasi, supaya bisa diulang kapan saja
 * tanpa mengunci tabel dan tanpa mengubah data yang sudah benar. Aman
 * dijalankan berkali-kali: baris yang sudah rapi tidak disentuh.
 */
class RapikanVirtualAccount extends Command
{
    protected $signature = 'va:rapikan {--dry-run : Tampilkan yang akan diubah tanpa menulis}';

    protected $description = 'Rapikan nomor Virtual Account lama jadi 12 digit berakhiran NIS';

    public function handle(): int
    {
        $dry = (bool) $this->option('dry-run');
        $diubah = 0;
        $dilewati = 0;

        $daftar = VirtualAccount::with('student')->orderBy('id')->get();

        foreach ($daftar as $va) {
            $nis = (string) ($va->student?->nis ?? '');

            if ($nis === '') {
                $this->warn("VA id={$va->id} dilewati: siswa tidak ditemukan.");
                $dilewati++;

                continue;
            }

            $seharusnya = VirtualAccount::nomorLengkap($nis);

            $sudahRapi = $va->nomor_va === $seharusnya
                && $va->account_number === $seharusnya
                && $va->prefix === VirtualAccount::PREFIX_MIDTRANS;

            if ($sudahRapi) {
                $dilewati++;

                continue;
            }

            $this->line(sprintf(
                'VA id=%s siswa=%s NIS=%s : nomor_va=%s prefix=%s account_number=%s -> %s',
                $va->id,
                $va->student?->nama ?? '-',
                $nis,
                $va->nomor_va,
                $va->prefix ?? '(kosong)',
                $va->account_number ?? '(kosong)',
                $seharusnya
            ));

            if (! $dry) {
                DB::table('virtual_account')->where('id', $va->id)->update([
                    'nomor_va' => $seharusnya,
                    'prefix' => VirtualAccount::PREFIX_MIDTRANS,
                    'account_number' => $seharusnya,
                ]);

                // Transaksi lama menyimpan nomor yang sudah tidak dipakai.
                // Dirapikan juga supaya pencarian nomor tidak gagal.
                DB::table('transaksi_payment_gateway')
                    ->where('virtual_account_id', $va->id)
                    ->where('reference_number', '!=', $seharusnya)
                    ->update(['reference_number' => $seharusnya]);
            }

            $diubah++;
        }

        $this->info(($dry ? '[dry-run] ' : '')."Diubah: {$diubah}, dilewati: {$dilewati}.");

        return self::SUCCESS;
    }
}