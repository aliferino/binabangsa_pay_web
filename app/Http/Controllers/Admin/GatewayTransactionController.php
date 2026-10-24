<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentGatewayTransaction;
use App\Models\Student;
use Illuminate\Http\Request;

/**
 * Payment gateway transactions.
 *
 * Read-only. Rows are written by the gateway callback, never by an operator,
 * so there is no store, update, or destroy here.
 *
 * One table, two entry points:
 *   index()  every gateway row, including ones without a virtual account
 *   va()     only rows attached to a virtual account
 */
class GatewayTransactionController extends Controller
{
    public function index(Request $request)
    {
        return $this->listing(
            $request,
            onlyVa: false,
            indexRoute: 'gateway-transactions.index',
            title: 'Transaksi Payment Gateway',
            description: 'Seluruh catatan transaksi dari payment gateway, termasuk yang belum menempel pada virtual account.'
        );
    }

    /**
     * Transactions attached to a virtual account.
     *
     * Rows without a virtual account are still recorded, just not listed here:
     * this page exists to trace a single payment number.
     */
    public function va(Request $request)
    {
        return $this->listing(
            $request,
            onlyVa: true,
            indexRoute: 'va-transactions.index',
            title: 'Transaksi Virtual Account',
            description: 'Transaksi yang sudah menempel pada satu nomor virtual account siswa.'
        );
    }

    /**
     * Detail of one transaction, including the raw gateway response.
     */
    public function show(PaymentGatewayTransaction $transaction)
    {
        return $this->render('admin.gateway-transactions.show', [
            'transaction' => $transaction->load('siswa.kelas', 'virtualAccount'),
            'indexRoute' => 'gateway-transactions.index',
        ]);
    }

    /**
     * Build the list and its summary cards.
     *
     * @param  bool  $onlyVa  restrict to rows that carry a virtual_account_id
     * @param  string  $indexRoute  route back to the list this row came from
     */
    private function listing(
        Request $request,
        bool $onlyVa,
        string $indexRoute,
        string $title,
        string $description
    ) {
        $query = PaymentGatewayTransaction::with('siswa.kelas', 'virtualAccount');

        if ($onlyVa) {
            $query->whereNotNull('virtual_account_id');
        }

        if ($request->filled('dari')) {
            $query->whereDate('created_at', '>=', $request->input('dari'));
        }

        if ($request->filled('sampai')) {
            $query->whereDate('created_at', '<=', $request->input('sampai'));
        }

        if ($request->filled('status_transaksi')) {
            $query->where('status_transaksi', $request->input('status_transaksi'));
        }

        if ($request->filled('siswa_id')) {
            $query->where('siswa_id', $request->input('siswa_id'));
        }

        $rows = $query->orderByDesc('id')->get();

        /*
            Summary counts come from the filtered result set, not the whole
            table, so the cards always agree with the rows underneath them.
        */
        $summary = [
            'total' => $rows->count(),
            'succeeded_amount' => $rows->where('status_transaksi', 'berhasil')->sum('nominal'),
            'pending' => $rows->where('status_transaksi', 'pending')->count(),
            'failed' => $rows->whereIn('status_transaksi', ['gagal', 'kedaluwarsa'])->count(),
        ];

        return $this->render('admin.gateway-transactions.index', [
            'transactions' => $rows,
            'summary' => $summary,
            'filter' => $request->only(['dari', 'sampai', 'status_transaksi', 'siswa_id']),
            'students' => Student::orderBy('nama')->get(),
            'indexRoute' => $indexRoute,
            'title' => $title,
            'description' => $description,
        ]);
    }
}
