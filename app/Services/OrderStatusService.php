<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Services\NotificationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderStatusService
{
    public function __construct(private NotificationService $notifications) {}

    /** Majukan status: confirmed -> preparing -> ready -> completed. */
    public function advance(Order $order): Order
    {
        $next = $order->status->next();
        if (! $next) {
            throw ValidationException::withMessages(['status' => 'Status pesanan tidak bisa dimajukan lagi.']);
        }

        if ($next === OrderStatus::Completed && ! $order->isPaid()) {
            throw ValidationException::withMessages(['status' => 'Pembayaran belum diverifikasi. Tandai lunas dulu sebelum menyelesaikan pesanan.']);
        }

        $from = $order->status;
        $order->update([
            'status' => $next,
            'ready_at' => $next === OrderStatus::Ready ? now() : $order->ready_at,
            'completed_at' => $next === OrderStatus::Completed ? now() : null,
        ]);

        AuditLogger::log('order.status', $order, ['from' => $from->value, 'to' => $next->value]);
        $this->notifications->notifyUser((int) $order->buyer_id, 'order.status', [
            'order' => $order->code,
            'status' => $next->label(),
        ]);

        return $order;
    }

    public function cancel(Order $order, ?string $reason = null): Order
    {
        DB::transaction(function () use ($order, $reason) {
            $refunded = $order->isPaid();
            $order->update([
                'status' => OrderStatus::Cancelled,
                'cancelled_at' => now(),
                'cancel_reason' => $reason,
                'payment_status' => $refunded ? PaymentStatus::Refunded : PaymentStatus::Cancelled,
            ]);

            $this->syncInvoice($order);
            AuditLogger::log('order.cancelled', $order, ['reason' => $reason, 'refund' => $refunded]);
            $this->notifications->notifyUser((int) $order->buyer_id, 'order.cancelled', [
                'order' => $order->code,
                'refund' => $refunded,
                'reason' => $reason,
            ]);
        });

        return $order;
    }

    /** Tenant admin memverifikasi pembayaran tunai saat pembeli datang ke kasir. */
    public function markPaid(Order $order): Order
    {
        DB::transaction(function () use ($order) {
            $order->update(['payment_status' => PaymentStatus::Paid]);
            $this->syncInvoice($order);
            AuditLogger::log('order.payment_verified', $order);
            $this->notifications->notifyUser((int) $order->buyer_id, 'payment.received', [
                'order' => $order->code,
                'amount' => $order->subtotal,
            ]);
        });

        return $order;
    }

    /** Invoice + payment lunas ketika seluruh order aktifnya sudah lunas. */
    private function syncInvoice(Order $order): void
    {
        $invoice = $order->invoice()->with('orders', 'payment')->first();
        if (! $invoice) {
            return;
        }

        $active = $invoice->orders->where('status', '!=', OrderStatus::Cancelled);

        if ($active->isEmpty()) {
            $invoice->update(['payment_status' => PaymentStatus::Cancelled]);
            $invoice->payment?->update(['status' => PaymentStatus::Cancelled]);
        } elseif ($active->every(fn (Order $o) => $o->isPaid())) {
            $invoice->update(['payment_status' => PaymentStatus::Paid, 'paid_at' => $invoice->paid_at ?? now()]);
            $invoice->payment?->update(['status' => PaymentStatus::Paid, 'paid_at' => $invoice->payment->paid_at ?? now()]);
        } elseif ($invoice->payment_status === PaymentStatus::Paid && $active->contains(fn (Order $o) => $o->payment_status === PaymentStatus::Refunded)) {
            $invoice->update(['payment_status' => PaymentStatus::Refunded]);
            $invoice->payment?->update(['status' => PaymentStatus::Refunded]);
        }
    }
}
