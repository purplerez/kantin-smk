<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Services\CheckoutService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Titik integrasi TUNGGAL untuk payment gateway (mis. Midtrans) di masa depan.
 * Saat siap: verifikasi signature gateway di sini, lalu panggil markInvoicePaid().
 * Sekarang masih stub aman: tidak melakukan apa-apa selain memanggil seam bila dipanggil.
 */
class PaymentWebhookController extends Controller
{
    public function handle(Request $request, Invoice $invoice, CheckoutService $checkout): JsonResponse
    {
        // TODO(Midtrans): validasi signature_key = sha512(order_id+status_code+gross_amount+ServerKey)
        // dan status transaksi ('settlement'/'capture') sebelum menandai lunas.

        $checkout->markInvoicePaid($invoice);

        return response()->json(['ok' => true, 'invoice' => $invoice->code, 'status' => $invoice->fresh()->payment_status->value]);
    }
}
