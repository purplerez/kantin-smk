<?php

namespace Tests\Feature;

use App\Events\OrderUpdated;
use App\Livewire\Buyer\Checkout;
use App\Livewire\Buyer\OrderDetail;
use App\Livewire\Buyer\Orders;
use App\Livewire\Tenant\OrderBoard;
use App\Models\Order;
use App\Models\Product;
use App\Services\CartService;
use App\Services\CheckoutService;
use App\Services\OrderStatusService;
use Illuminate\Support\Facades\Event;
use Livewire\Livewire;
use Tests\TestCase;

class RealtimeAndPickupAlertTest extends TestCase
{
    private function placeRinaOrder(string $method = 'cash'): Order
    {
        app(CartService::class)->add(Product::withoutGlobalScopes()->where('tenant_id', $this->tenantRina()->id)->where('is_available_today', true)->first()->id);
        Livewire::actingAs($this->buyer())->test(Checkout::class)->set('paymentMethod', $method)->call('placeOrder');

        return Order::withoutGlobalScopes()->latest('id')->firstOrFail();
    }

    public function test_events_are_broadcast_on_private_channels_when_reverb_enabled(): void
    {
        config(['broadcasting.default' => 'reverb', 'broadcasting.connections.reverb.key' => 'test-key']);
        Event::fake([OrderUpdated::class]);

        $order = $this->placeRinaOrder('cash');

        Event::assertDispatched(OrderUpdated::class, function (OrderUpdated $e) use ($order) {
            $channels = collect($e->broadcastOn())->map->name->all();

            return $e->kind === 'created'
                && $e->order->id === $order->id
                && $channels === ['private-tenant.'.$order->tenant_id, 'private-buyer.'.$order->buyer_id]
                && $e->broadcastWith()['status'] === 'confirmed';
        });

        $this->actingAs($this->staff());
        app(OrderStatusService::class)->advance($order);
        Event::assertDispatched(OrderUpdated::class, fn ($e) => $e->kind === 'status' && $e->broadcastWith()['status'] === 'preparing');
    }

    public function test_no_broadcast_when_reverb_disabled(): void
    {
        config(['broadcasting.default' => 'log']);
        Event::fake([OrderUpdated::class]);
        $this->placeRinaOrder();
        Event::assertNotDispatched(OrderUpdated::class);
    }

    public function test_channel_authorization_is_scoped(): void
    {
        $rina = $this->tenantRina()->id;
        $kopsis = $this->tenantKopsis()->id;

        $this->actingAs($this->staff())->post('/broadcasting/auth', ['channel_name' => "private-tenant.{$rina}", 'socket_id' => '1.1'])->assertOk();
        $this->actingAs($this->staff())->post('/broadcasting/auth', ['channel_name' => "private-tenant.{$kopsis}", 'socket_id' => '1.1'])->assertForbidden();
        $this->actingAs($this->admin())->post('/broadcasting/auth', ['channel_name' => "private-tenant.{$kopsis}", 'socket_id' => '1.1'])->assertOk();

        $buyerId = $this->buyer()->id;
        $this->actingAs($this->buyer())->post('/broadcasting/auth', ['channel_name' => "private-buyer.{$buyerId}", 'socket_id' => '1.1'])->assertOk();
        $this->actingAs($this->staff())->post('/broadcasting/auth', ['channel_name' => "private-buyer.{$buyerId}", 'socket_id' => '1.1'])->assertForbidden();
    }

    public function test_buyer_orders_page_fires_pickup_alert_only_on_transition_to_ready(): void
    {
        $order = $this->placeRinaOrder();
        $page = Livewire::actingAs($this->buyer())->test(Orders::class);

        $page->call('tick')->assertNotDispatched('order-ready');

        $this->actingAs($this->staff());
        $svc = app(OrderStatusService::class);
        $svc->advance($order); // preparing
        $page->call('tick')->assertNotDispatched('order-ready');

        $svc->advance($order); // ready
        $page->call('onOrderUpdated')->assertDispatched('order-ready', code: $order->code, tenant: 'Kantin Bu Rina');
        $page->call('tick')->assertNotDispatched('order-ready'); // tidak berulang
    }

    public function test_order_detail_fires_pickup_alert_once(): void
    {
        $order = $this->placeRinaOrder();
        $detail = Livewire::actingAs($this->buyer())->test(OrderDetail::class, ['order' => $order]);

        $this->actingAs($this->staff());
        $svc = app(OrderStatusService::class);
        $svc->advance($order);
        $svc->advance($order);

        $detail->call('tick')->assertDispatched('order-ready')->assertSee('Siap Diambil');
        $detail->call('tick')->assertNotDispatched('order-ready');
    }

    public function test_tenant_board_fires_new_order_alert_for_confirmed_orders_only(): void
    {
        $board = Livewire::actingAs($this->staff())->test(OrderBoard::class);
        $board->call('tick')->assertNotDispatched('new-order');

        // QRIS belum bayar -> status pending -> tidak ada alert
        $pending = $this->placeRinaOrder('qris');
        $board->call('tick')->assertNotDispatched('new-order');

        // Setelah dibayar -> confirmed -> alert
        app(CheckoutService::class)->markInvoicePaid($pending->invoice);
        $board->call('onOrderUpdated')->assertDispatched('new-order', count: 1);
        $board->call('tick')->assertNotDispatched('new-order');

        // Tunai -> langsung confirmed -> alert; order tenant lain tidak memicu
        $this->placeRinaOrder('cash');
        app(CartService::class)->add(Product::withoutGlobalScopes()->where('tenant_id', $this->tenantKopsis()->id)->first()->id);
        Livewire::actingAs($this->buyer())->test(Checkout::class)->set('paymentMethod', 'cash')->call('placeOrder');
        $board->call('tick')->assertDispatched('new-order', count: 1);
    }
}
