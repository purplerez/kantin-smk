<?php

namespace App\Livewire\Buyer;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Services\Realtime;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
#[Title('Pesanan saya — KantinSMK Go')]
class Orders extends Component
{
    use WithPagination;

    #[Url]
    public string $tab = 'aktif';

    public int $buyerId;

    /** ID order yang sudah diketahui berstatus "ready" — untuk mendeteksi transisi baru. */
    public array $knownReady = [];

    public function mount(): void
    {
        $this->buyerId = auth()->id();
        $this->knownReady = $this->readyOrders()->pluck('id')->all();
    }

    public function setTab(string $tab): void
    {
        $this->tab = $tab;
        $this->resetPage();
    }

    /** Push instan dari Reverb (kanal privat pembeli). */
    #[On('echo-private:buyer.{buyerId},OrderUpdated')]
    public function onOrderUpdated(): void
    {
        $this->tick();
    }

    /** Fallback polling: dipanggil wire:poll. */
    public function tick(): void
    {
        $ready = $this->readyOrders();

        foreach ($ready->whereNotIn('id', $this->knownReady) as $order) {
            $this->dispatch('order-ready', code: $order->code, tenant: $order->tenant->name, url: route('orders.show', $order));
        }

        $this->knownReady = $ready->pluck('id')->all();
    }

    private function readyOrders()
    {
        return Order::with('tenant')->where('buyer_id', $this->buyerId)->where('status', OrderStatus::Ready)->get();
    }

    public function render()
    {
        $active = [OrderStatus::Pending, OrderStatus::Confirmed, OrderStatus::Preparing, OrderStatus::Ready];

        $orders = Order::query()
            ->with(['tenant', 'items', 'invoice'])
            ->where('buyer_id', $this->buyerId)
            ->when($this->tab === 'aktif', fn ($q) => $q->whereIn('status', $active))
            ->when($this->tab === 'riwayat', fn ($q) => $q->whereNotIn('status', $active))
            ->latest()
            ->paginate(10);

        return view('livewire.buyer.orders', [
            'orders' => $orders,
            'activeCount' => Order::where('buyer_id', $this->buyerId)->whereIn('status', $active)->count(),
            'pollSeconds' => Realtime::enabled() ? 60 : 10,
        ]);
    }
}
