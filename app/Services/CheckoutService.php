<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Split checkout: 1 keranjang multi-tenant -> 1 Invoice + N Order (satu per tenant).
 */
class CheckoutService
{
    public function __construct(private CartService $cart) {}

    public function checkout(User $buyer, PaymentMethod $method, ?string $note = null): Invoice
    {
        $lines = $this->cart->lines();

        if ($lines->isEmpty()) {
            throw ValidationException::withMessages(['cart' => 'Keranjang masih kosong.']);
        }

        $unsellable = $lines->reject(fn ($l) => $l['sellable']);
        if ($unsellable->isNotEmpty()) {
            $names = $unsellable->map(fn ($l) => $l['product']->name)->implode(', ');
            throw ValidationException::withMessages(['cart' => "Menu tidak tersedia saat ini: {$names}. Hapus dari keranjang untuk lanjut."]);
        }

        $invoice = DB::transaction(function () use ($buyer, $method, $note, $lines) {
            $initialStatus = $method->requiresPrepayment() ? OrderStatus::Pending : OrderStatus::Confirmed;

            $invoice = Invoice::create([
                'code' => Invoice::generateCode(),
                'buyer_id' => $buyer->id,
                'total' => (int) $lines->sum('line_total'),
                'payment_method' => $method,
                'payment_status' => PaymentStatus::Pending,
                'payment_reference' => $method === PaymentMethod::Transfer ? $this->virtualAccount($buyer) : null,
                'note' => $note,
            ]);

            foreach ($lines->groupBy(fn ($l) => $l['product']->tenant_id) as $tenantId => $tenantLines) {
                $order = Order::create([
                    'code' => Order::generateCode(),
                    'invoice_id' => $invoice->id,
                    'buyer_id' => $buyer->id,
                    'tenant_id' => $tenantId,
                    'status' => $initialStatus,
                    'payment_status' => PaymentStatus::Pending,
                    'subtotal' => (int) $tenantLines->sum('line_total'),
                    'note' => $note,
                    'confirmed_at' => $initialStatus === OrderStatus::Confirmed ? now() : null,
                ]);

                foreach ($tenantLines as $line) {
                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $line['product']->id,
                        'product_name' => $line['product']->name,
                        'unit_price' => $line['product']->price,
                        'qty' => $line['qty'],
                        'line_total' => $line['line_total'],
                        'note' => $line['note'] ?: null,
                    ]);
                }

                Realtime::orderUpdated($order, 'created');
            }

            AuditLogger::log('checkout', $invoice, ['method' => $method->value, 'total' => $invoice->total, 'orders' => $invoice->orders()->count()]);

            return $invoice;
        });

        $this->cart->clear();

        return $invoice->load('orders.items', 'orders.tenant');
    }

    /** Simulasi DEMO: pembeli menekan "Saya sudah bayar" untuk QRIS/Transfer. */
    public function markInvoicePaid(Invoice $invoice): void
    {
        if ($invoice->isPaid() || ! $invoice->payment_method->requiresPrepayment()) {
            return;
        }

        DB::transaction(function () use ($invoice) {
            $invoice->update(['payment_status' => PaymentStatus::Paid, 'paid_at' => now()]);

            $invoice->orders()->where('status', OrderStatus::Pending)->each(function (Order $order) {
                $order->update([
                    'payment_status' => PaymentStatus::Paid,
                    'status' => OrderStatus::Confirmed,
                    'confirmed_at' => now(),
                ]);
                Realtime::orderUpdated($order, 'paid');
            });

            AuditLogger::log('payment.simulated_paid', $invoice, ['method' => $invoice->payment_method->value]);
        });
    }

    private function virtualAccount(User $buyer): string
    {
        return '8808'.str_pad((string) $buyer->id, 4, '0', STR_PAD_LEFT).str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }
}
