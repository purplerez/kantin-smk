<?php

namespace App\Livewire\Tenant;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Services\OrderStatusService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.panel')]
#[Title('Pembayaran & Settlement — Tenant')]
class Payments extends Component
{
    use AuthorizesRequests;

    public string $from = '';

    public string $to = '';

    public function mount(): void
    {
        $this->from = now()->subDays(6)->toDateString();
        $this->to = now()->toDateString();
    }

    public function markPaid(int $orderId, OrderStatusService $service): void
    {
        $order = Order::findOrFail($orderId);
        $this->authorize('verifyPayment', $order);
        $service->markPaid($order);
        $this->dispatch('toast', message: "{$order->code} ditandai lunas");
    }

    public function refund(int $orderId, OrderStatusService $service): void
    {
        $order = Order::findOrFail($orderId);
        $this->authorize('cancel', $order);
        $service->cancel($order, 'Dibatalkan & dana dikembalikan oleh tenant');
        $this->dispatch('toast', message: "{$order->code} dibatalkan, status refund dicatat", tone: 'error');
    }

    public function render()
    {
        $pendingCash = Order::with('buyer', 'invoice')
            ->where('payment_status', PaymentStatus::Pending)
            ->whereIn('status', [OrderStatus::Confirmed, OrderStatus::Preparing, OrderStatus::Ready])
            ->orderBy('created_at')
            ->get();

        $settlement = Order::query()
            ->join('invoices', 'invoices.id', '=', 'orders.invoice_id')
            ->where('orders.payment_status', PaymentStatus::Paid)
            ->where('orders.status', '!=', OrderStatus::Cancelled)
            ->whereBetween(DB::raw('DATE(orders.created_at)'), [$this->from, $this->to])
            ->selectRaw('DATE(orders.created_at) as day, invoices.payment_method as method, COUNT(*) as orders_count, SUM(orders.subtotal) as amount')
            ->groupBy('day', 'method')
            ->orderByDesc('day')
            ->get()
            ->groupBy('day');

        $refunds = Order::with('buyer')->where('payment_status', PaymentStatus::Refunded)->latest('cancelled_at')->take(10)->get();

        return view('livewire.tenant.payments', compact('pendingCash', 'settlement', 'refunds'));
    }
}
