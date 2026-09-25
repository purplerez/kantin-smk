<?php

namespace App\Livewire\Buyer;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\Review;
use App\Services\AuditLogger;
use App\Services\OrderStatusService;
use App\Services\Realtime;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;

#[Layout('components.layouts.app')]
class OrderDetail extends Component
{
    use AuthorizesRequests;

    public Order $order;

    public int $buyerId;

    public string $lastStatus;

    public int $rating = 5;

    public string $comment = '';

    public function mount(Order $order): void
    {
        $this->authorize('view', $order);
        $this->order = $order;
        $this->buyerId = auth()->id();
        $this->lastStatus = $order->status->value;
    }

    /** Push instan dari Reverb. */
    #[On('echo-private:buyer.{buyerId},OrderUpdated')]
    public function onOrderUpdated(): void
    {
        $this->tick();
    }

    /** Fallback polling + deteksi transisi ke "Siap Diambil". */
    public function tick(): void
    {
        $this->order->refresh();
        $current = $this->order->status->value;

        if ($current !== $this->lastStatus && $this->order->status === OrderStatus::Ready) {
            $this->dispatch('order-ready', code: $this->order->code, tenant: $this->order->tenant->name, url: route('orders.show', $this->order));
        }

        $this->lastStatus = $current;
    }

    public function cancel(OrderStatusService $service): void
    {
        $this->authorize('cancel', $this->order);
        $service->cancel($this->order, 'Dibatalkan oleh pembeli');
        $this->order->refresh();
        $this->dispatch('toast', message: 'Pesanan dibatalkan.');
    }

    public function submitReview(): void
    {
        $this->authorize('review', $this->order);
        $this->validate(['rating' => ['required', 'integer', 'between:1,5'], 'comment' => ['nullable', 'string', 'max:300']]);

        $review = Review::create([
            'order_id' => $this->order->id,
            'buyer_id' => auth()->id(),
            'tenant_id' => $this->order->tenant_id,
            'rating' => $this->rating,
            'comment' => $this->comment ?: null,
        ]);
        AuditLogger::log('review.created', $review, ['rating' => $this->rating]);

        $this->order->refresh();
        $this->dispatch('toast', message: 'Terima kasih atas ulasanmu!');
    }

    public function render()
    {
        $this->order->load('tenant', 'items', 'invoice', 'review');

        return view('livewire.buyer.order-detail', [
            'timeline' => OrderStatus::timeline(),
            'currentIndex' => array_search($this->order->status, OrderStatus::timeline(), true),
            'pollSeconds' => Realtime::enabled() ? 60 : 8,
        ])->title('Pesanan '.$this->order->code);
    }
}
