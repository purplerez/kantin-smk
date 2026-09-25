<?php

namespace App\Livewire\Admin;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\Tenant;
use App\Services\OrderStatusService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.panel')]
#[Title('Transaksi — Platform')]
class Transactions extends Component
{
    use AuthorizesRequests, WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url]
    public string $status = '';

    #[Url]
    public string $tenant = '';

    #[Url]
    public string $date = '';

    public function updated($prop): void
    {
        $this->resetPage();
    }

    public function cancel(int $orderId, OrderStatusService $service): void
    {
        $order = Order::findOrFail($orderId);
        $this->authorize('cancel', $order);
        $service->cancel($order, 'Dibatalkan oleh admin platform');
        $this->dispatch('toast', message: "{$order->code} dibatalkan");
    }

    public function render()
    {
        $orders = Order::with(['buyer', 'tenant', 'invoice', 'items'])
            ->when($this->search !== '', fn ($q) => $q->where(fn ($w) => $w
                ->where('code', 'like', "%{$this->search}%")
                ->orWhereHas('invoice', fn ($i) => $i->where('code', 'like', "%{$this->search}%"))
                ->orWhereHas('buyer', fn ($b) => $b->where('name', 'like', "%{$this->search}%"))))
            ->when($this->status !== '', fn ($q) => $q->where('status', $this->status))
            ->when($this->tenant !== '', fn ($q) => $q->where('tenant_id', $this->tenant))
            ->when($this->date !== '', fn ($q) => $q->whereDate('created_at', $this->date))
            ->latest()
            ->paginate(15);

        return view('livewire.admin.transactions', [
            'orders' => $orders,
            'tenants' => Tenant::orderBy('name')->get(),
            'statuses' => OrderStatus::cases(),
        ]);
    }
}
