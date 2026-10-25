<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentGatewayTransaction;
use App\Services\IntegrationSettings;
use App\Services\MidtransNotifier;
use App\Services\TunnelService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Pengaturan integrasi: nyalakan tunnel Pinggy dan uji notifikasi Midtrans.
 *
 * Halaman ini menggantikan langkah manual yang dulu dikerjakan dari terminal.
 */
class IntegrationController extends Controller
{
    public function __construct(
        private readonly TunnelService $tunnel,
        private readonly IntegrationSettings $pengaturan,
    ) {
    }

    public function index(): View
    {
        $tunnel = $this->tunnel->status();

        return view('admin.integration.index', [
            'tunnel' => $tunnel,
            'urlCallback' => $tunnel === null ? null : $tunnel['url'].'/callback/midtrans',
            'midtransSiap' => (string) config('midtrans.server_key') !== '',
            'midtransProduksi' => (bool) config('midtrans.is_production'),
            'xenditSiap' => (string) config('services.xendit.callback_token') !== '',
            // Variabel lama, tetap dikirim karena view sudah memakainya.
            'sandiTersamar' => $this->pengaturan->tersamar((string) config('midtrans.server_key')),
            'sumberKunci' => $this->pengaturan->sumber(),
            'jsonPratinjau' => $this->pratinjau(),
        ]);
    }

    /**
     * Simpan kunci API ke berkas pengaturan.
     */
    public function simpanKunci(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'midtrans_server_key' => ['nullable', 'string', 'max:255'],
            'midtrans_client_key' => ['nullable', 'string', 'max:255'],
            'midtrans_is_production' => ['nullable', Rule::in(['0', '1', 0, 1])],
        ]);

        if (blank($data['midtrans_server_key'] ?? null)
            && blank($data['midtrans_client_key'] ?? null)
            && blank($data['midtrans_is_production'] ?? null)) {
            return back()->with('gagal', 'Tidak ada yang diubah.');
        }

        try {
            $this->pengaturan->simpan([
                'midtrans_server_key' => $data['midtrans_server_key'] ?? null,
                'midtrans_client_key' => $data['midtrans_client_key'] ?? null,
                'midtrans_is_production' => $data['midtrans_is_production'] ?? null,
            ]);
        } catch (\Throwable $e) {
            return back()->with('gagal', 'Kunci gagal disimpan. '.$e->getMessage());
        }

        return back()->with('sukses', 'Kunci disimpan ke berkas pengaturan.');
    }

    /**
     * Hapus berkas pengaturan supaya kembali ke nilai bawaan .env.
     */
    public function resetKunci(): RedirectResponse
    {
        try {
            $this->pengaturan->reset();
        } catch (\Throwable $e) {
            return back()->with('gagal', 'Kunci gagal dikembalikan. '.$e->getMessage());
        }

        return back()->with('sukses', 'Kunci kembali ke bawaan .env.');
    }

    /**
     * Bentuk berkas pengaturan untuk ditampilkan sebagai pratinjau JSON.
     * Nilai kuncinya sudah tersamar, kunci penuh tidak pernah dikirim ke view.
     *
     * @return array<string, mixed>
     */
    private function pratinjau(): array
    {
        $nilai = $this->pengaturan->semua();

        return [
            'midtrans_server_key' => $this->pengaturan->tersamar($nilai['midtrans_server_key']),
            'midtrans_client_key' => $this->pengaturan->tersamar($nilai['midtrans_client_key']),
            'midtrans_is_production' => (bool) $nilai['midtrans_is_production'],
        ];
    }

    /**
     * Nyalakan tunnel, redirect balik dengan URL hasilnya.
     */
    public function nyalakanTunnel(): RedirectResponse
    {
        try {
            $keadaan = $this->tunnel->nyalakan();
        } catch (\Throwable $e) {
            return back()->with('gagal', 'Tunnel gagal dinyalakan. '.$e->getMessage());
        }

        return back()->with('sukses', 'Tunnel menyala di '.$keadaan['url']);
    }

    public function matikanTunnel(): RedirectResponse
    {
        try {
            $this->tunnel->matikan();
        } catch (\Throwable $e) {
            return back()->with('gagal', 'Tunnel gagal dimatikan. '.$e->getMessage());
        }

        return back()->with('sukses', 'Tunnel dimatikan.');
    }

    /**
     * Kirim notifikasi Midtrans tiruan ke callback kita sendiri.
     *
     * Ini pengganti tombol Test Payment di dashboard Midtrans. Payload yang
     * dikirim disusun persis seperti notifikasi Midtrans, termasuk signature_key
     * yang dihitung dari server key di .env. Jadi yang diuji benar-benar jalur
     * produksi, bukan jalan pintas.
     */
    public function ujiNotifikasi(Request $request): RedirectResponse
    {
        $serverKey = (string) config('midtrans.server_key');

        if ($serverKey === '') {
            return back()->with('gagal', 'Server key Midtrans belum diisi, notifikasi uji tidak bisa dikirim.');
        }

        $transaksi = PaymentGatewayTransaction::where('status_transaksi', 'pending')
            ->whereNotNull('virtual_account_id')
            ->latest('id')
            ->first();

        if ($transaksi === null) {
            return back()->with('gagal', 'Tidak ada transaksi pending. Buat Virtual Account Midtrans dulu di menu Virtual Account.');
        }

        $payload = MidtransNotifier::payload($transaksi, null, $serverKey);
        $hasil = MidtransNotifier::kirim($payload);

        $pesan = (string) ($hasil['body']['pesan'] ?? '');

        if (($hasil['body']['dibuat'] ?? false) === true) {
            return back()->with('sukses', 'Notifikasi uji diterima: '.$pesan);
        }

        // Sudah pernah diproses tetap dianggap berhasil, karena jalur callback
        // memang idempoten.
        if (str_contains($pesan, 'sudah pernah diproses')) {
            return back()->with('sukses', 'Notifikasi uji diterima: '.$pesan);
        }

        return back()->with('gagal', 'Notifikasi uji ditolak. Status '.$hasil['status'].'. '.$pesan);
    }
}
