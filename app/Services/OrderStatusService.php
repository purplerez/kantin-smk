<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderStatusService
{
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
        Realtime::orderUpdated($order, 'status');

        return $order;
    }

    public function cancel(Order $order, ?string $reason = null): Order
    {
        DB::transaction(function () use ($order, $reason) {
            $order->update([
                'status' => OrderStatus::Cancelled,
                'cancelled_at' => now(),
                'cancel_reason' => $reason,
                'payment_status' => $order->isPaid() ? PaymentStatus::Refunded : PaymentStatus::Cancelled,
            ]);

            $this->syncInvoice($order);
            AuditLogger::log('order.cancelled', $order, ['reason' => $reason, 'refund' => $order->payment_status === PaymentStatus::Refunded]);
            Realtime::orderUpdated($order, 'cancelled');
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
            Realtime::orderUpdated($order, 'paid');
        });

        return $order;
    }

    /** Invoice lunas ketika seluruh order aktifnya sudah lunas. */
    private function syncInvoice(Order $order): void
    {
        $invoice = $order->invoice()->with('orders')->first();
        if (! $invoice) {
            return;
        }

        $active = $invoice->orders->where('status', '!=', OrderStatus::Cancelled);

        if ($active->isEmpty()) {
            $invoice->update(['payment_status' => PaymentStatus::Cancelled]);
        } elseif ($active->every(fn (Order $o) => $o->isPaid())) {
            $invoice->update(['payment_status' => PaymentStatus::Paid, 'paid_at' => $invoice->paid_at ?? now()]);
        }
    }
}
