<?php

namespace App\Livewire\Tenant;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\TenantSettlement;
use App\Services\CheckoutService;
use App\Services\OrderStatusService;
use App\Services\SettlementService;
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

    /** Verifikasi manual pembayaran transfer (prepay) — gated PaymentPolicy (tenant_admin/admin). */
    public function confirmTransfer(int $invoiceId, CheckoutService $checkout): void
    {
        $invoice = Invoice::with('payment', 'orders')->findOrFail($invoiceId);
        $this->authorize('confirm', $invoice->payment);
        $checkout->markInvoicePaid($invoice, auth()->user());
        $this->dispatch('toast', message: "Transfer {$invoice->code} diverifikasi lunas");
    }

    public function generateSettlement(SettlementService $service): void
    {
        $service->generateForDate($this->to, auth()->user());
        $this->dispatch('toast', message: "Settlement {$this->to} dibuat");
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

        // Transfer (prepay) yang menunggu verifikasi manual tenant/admin.
        $pendingTransfers = Order::with('buyer', 'invoice')
            ->whereHas('invoice', fn ($q) => $q
                ->where('payment_method', PaymentMethod::Transfer->value)
                ->where('payment_status', PaymentStatus::Pending->value))
            ->where('status', OrderStatus::Pending)
            ->get()
            ->groupBy('invoice_id');

        // Settlement harian tersimpan (tenant_settlements) untuk tenant ini.
        $stored = TenantSettlement::where('tenant_id', auth()->user()->tenant_id)
            ->whereBetween('date', [$this->from, $this->to])
            ->orderByDesc('date')
            ->get();

        return view('livewire.tenant.payments', compact('pendingCash', 'settlement', 'refunds', 'pendingTransfers', 'stored'));
    }
}
