<?php

namespace App\Events;

use App\Models\Order;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/** Dipancarkan ke tenant & pembeli saat order baru dibuat (checkout) atau status/pembayarannya berubah. */
class OrderUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Order $order, public string $kind) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('tenant.'.$this->order->tenant_id),
            new PrivateChannel('buyer.'.$this->order->buyer_id),
        ];
    }

    public function broadcastWith(): array
    {
        $this->order->loadMissing('tenant');

        return [
            'id' => $this->order->id,
            'code' => $this->order->code,
            'kind' => $this->kind,
            'status' => $this->order->status->value,
            'status_label' => $this->order->status->label(),
            'payment_status' => $this->order->payment_status->value,
            'tenant' => $this->order->tenant->name,
            'subtotal' => $this->order->subtotal,
            'url' => route('orders.show', $this->order),
        ];
    }
}
