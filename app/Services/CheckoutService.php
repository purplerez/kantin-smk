<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Split checkout: 1 keranjang multi-tenant -> 1 Invoice + 1 Payment + N Order (satu per tenant).
 * Semua di dalam satu DB transaction — kegagalan salah satu tenant me-rollback seluruh invoice.
 */
class CheckoutService
{
    public function __construct(
        private CartService $cart,
        private NotificationService $notifications,
    ) {}

    public function checkout(User $buyer, PaymentMethod $method, ?string $note = null): Invoice
    {
        $lines = $this->cart->lines();

        if ($lines->isEmpty()) {
            throw ValidationException::withMessages(['cart' => 'Keranjang masih kosong.']);
        }

        $productIds = $lines->keys()->all();

        $invoice = DB::transaction(function () use ($buyer, $method, $note, $lines, $productIds) {
            // Anti-race: kunci baris produk & re-validasi ketersediaan + harga dari DB (jangan percaya klien).
            $fresh = Product::with('tenant')
                ->whereIn('id', $productIds)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $unsellable = [];
            $serverLines = [];
            foreach ($lines as $productId => $line) {
                $product = $fresh->get($productId);
                if (! $product || ! $product->isSellable()) {
                    $unsellable[] = $product->name ?? $line['product']->name;

                    continue;
                }
                $qty = max(1, (int) $line['qty']);
                $serverLines[] = [
                    'product' => $product,
                    'tenant_id' => $product->tenant_id,
                    'qty' => $qty,
                    'note' => $line['note'] ?: null,
                    'line_total' => (int) $product->price * $qty, // harga dihitung ulang dari DB
                ];
            }

            if (! empty($unsellable)) {
                throw ValidationException::withMessages([
                    'cart' => 'Menu tidak tersedia saat ini: '.implode(', ', $unsellable).'. Perbarui keranjang untuk lanjut.',
                ]);
            }

            $serverTotal = (int) collect($serverLines)->sum('line_total');
            $initialStatus = $method->requiresPrepayment() ? OrderStatus::Pending : OrderStatus::Confirmed;

            $invoice = Invoice::create([
                'code' => Invoice::generateCode(),
                'buyer_id' => $buyer->id,
                'total' => $serverTotal,
                'payment_method' => $method,
                'payment_status' => PaymentStatus::Pending,
                'payment_reference' => $method === PaymentMethod::Transfer ? $this->virtualAccount($buyer) : null,
                'note' => $note,
            ]);

            // Payment terpisah — seam bersih untuk Midtrans (webhook) nanti.
            Payment::create([
                'invoice_id' => $invoice->id,
                'method' => $method,
                'status' => PaymentStatus::Pending,
                'amount' => $serverTotal,
                'reference' => $invoice->payment_reference,
            ]);

            foreach (collect($serverLines)->groupBy('tenant_id') as $tenantId => $tenantLines) {
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
                        'note' => $line['note'],
                    ]);
                }

                // Tunai langsung actionable -> beri tahu tenant. Prepay diberi tahu saat lunas.
                if (! $method->requiresPrepayment()) {
                    $this->notifications->notifyTenant((int) $tenantId, 'order.new', [
                        'order' => $order->code,
                        'buyer' => $buyer->name,
                        'amount' => $order->subtotal,
                    ]);
                }
            }

            // Kosongkan keranjang di dalam transaction yang sama.
            $this->cart->clear();

            AuditLogger::log('checkout', $invoice, ['method' => $method->value, 'total' => $invoice->total, 'orders' => $invoice->orders()->count()]);

            return $invoice;
        });

        return $invoice->load('orders.items', 'orders.tenant');
    }

    /**
     * Titik integrasi tunggal untuk menandai invoice lunas — dipanggil oleh:
     *  - simulasi QRIS (demo pembeli),
     *  - verifikasi manual transfer oleh tenant_admin/admin,
     *  - PaymentWebhookController (Midtrans, nanti).
     */
    public function markInvoicePaid(Invoice $invoice, ?User $verifier = null): void
    {
        if ($invoice->isPaid() || ! $invoice->payment_method->requiresPrepayment()) {
            return;
        }

        DB::transaction(function () use ($invoice, $verifier) {
            $invoice->update(['payment_status' => PaymentStatus::Paid, 'paid_at' => now()]);

            $invoice->payment()->update([
                'status' => PaymentStatus::Paid,
                'paid_at' => now(),
                'verified_by' => $verifier?->id,
            ]);

            $invoice->orders()->where('status', OrderStatus::Pending)->each(function (Order $order) {
                $order->update([
                    'payment_status' => PaymentStatus::Paid,
                    'status' => OrderStatus::Confirmed,
                    'confirmed_at' => now(),
                ]);

                $this->notifications->notifyTenant((int) $order->tenant_id, 'order.new', [
                    'order' => $order->code,
                    'buyer' => $order->buyer->name ?? '—',
                    'amount' => $order->subtotal,
                ]);
            });

            $this->notifications->notifyUser((int) $invoice->buyer_id, 'payment.received', [
                'invoice' => $invoice->code,
                'amount' => $invoice->total,
            ]);

            AuditLogger::log('payment.confirmed', $invoice, [
                'method' => $invoice->payment_method->value,
                'by' => $verifier?->id ? $verifier->email : 'demo/system',
            ]);
        });
    }

    private function virtualAccount(User $buyer): string
    {
        return '8808'.str_pad((string) $buyer->id, 4, '0', STR_PAD_LEFT).str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }
}
