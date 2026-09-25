<?php

namespace App\Livewire\Tenant;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Services\OrderStatusService;
use App\Services\Realtime;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.panel')]
#[Title('Pesanan masuk — Tenant')]
class OrderBoard extends Component
{
    use AuthorizesRequests, WithPagination;

    #[Url]
    public string $status = 'confirmed';

    #[Url(as: 'q')]
    public string $search = '';

    public int $tenantId;

    /** Batas waktu terakhir pengecekan pesanan baru (ISO 8601). */
    public string $since;

    public function mount(): void
    {
        $this->tenantId = (int) auth()->user()->tenant_id;
        $this->since = now()->toIso8601String();
    }

    /** Push instan dari Reverb (kanal privat tenant). */
    #[On('echo-private:tenant.{tenantId},OrderUpdated')]
    public function onOrderUpdated(): void
    {
        $this->tick();
    }

    /** Fallback polling: deteksi pesanan yang baru dikonfirmasi (sudah bayar / tunai) sejak pengecekan terakhir. */
    public function tick(): void
    {
        $fresh = Order::with('buyer')->where('status', OrderStatus::Confirmed)->where('confirmed_at', '>', $this->since)->get();

        if ($fresh->isNotEmpty()) {
            $this->dispatch('new-order', count: $fresh->count(), text: $fresh->map(fn ($o) => $o->buyer->name.' · '.$o->code)->implode(', '));
        }

        $this->since = now()->toIso8601String();
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
        $this->resetPage();
    }

    public function advance(int $orderId, OrderStatusService $service): void
    {
        $this->resetErrorBag();
        $order = Order::findOrFail($orderId);
        $this->authorize('advance', $order);
        $service->advance($order);
        $this->dispatch('toast', message: "{$order->code} → {$order->status->label()}");
    }

    public function markPaid(int $orderId, OrderStatusService $service): void
    {
        $order = Order::findOrFail($orderId);
        $this->authorize('verifyPayment', $order);
        $service->markPaid($order);
        $this->dispatch('toast', message: "Pembayaran {$order->code} ditandai lunas");
    }

    public function cancel(int $orderId, OrderStatusService $service): void
    {
        $order = Order::findOrFail($orderId);
        $this->authorize('cancel', $order);
        $service->cancel($order, 'Dibatalkan oleh tenant');
        $this->dispatch('toast', message: "{$order->code} dibatalkan", tone: 'error');
    }

    public function render()
    {
        $counts = Order::query()
            ->selectRaw('status, count(*) as total')
            ->whereDate('created_at', today())
            ->groupBy('status')
            ->pluck('total', 'status');

        $orders = Order::query()
            ->with(['buyer', 'items', 'invoice'])
            ->when($this->status !== 'semua', fn ($q) => $q->where('status', $this->status))
            ->when($this->search !== '', fn ($q) => $q->where(fn ($w) => $w
                ->where('code', 'like', "%{$this->search}%")
                ->orWhereHas('buyer', fn ($b) => $b->where('name', 'like', "%{$this->search}%"))))
            ->orderBy('created_at', in_array($this->status, ['completed', 'cancelled', 'semua']) ? 'desc' : 'asc')
            ->paginate(12);

        return view('livewire.tenant.order-board', [
            'orders' => $orders,
            'counts' => $counts,
            'pollSeconds' => Realtime::enabled() ? 60 : 8,
            'tabs' => [
                'pending' => 'Belum bayar',
                'confirmed' => 'Baru',
                'preparing' => 'Disiapkan',
                'ready' => 'Siap diambil',
                'completed' => 'Selesai',
                'cancelled' => 'Batal',
                'semua' => 'Semua',
            ],
        ]);
    }
}
