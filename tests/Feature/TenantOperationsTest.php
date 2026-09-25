<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Livewire\Buyer\Checkout;
use App\Livewire\Buyer\OrderDetail;
use App\Livewire\Tenant\OrderBoard;
use App\Livewire\Tenant\Payments;
use App\Livewire\Tenant\Products;
use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use App\Services\CartService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Livewire\Livewire;
use Tests\TestCase;

class TenantOperationsTest extends TestCase
{
    /** Buat 1 invoice dengan order untuk Bu Rina + Koperasi Siswa. */
    private function seedOrders(string $method = 'qris'): void
    {
        $cart = app(CartService::class);
        $cart->add(Product::withoutGlobalScopes()->where('tenant_id', $this->tenantRina()->id)->where('is_available_today', true)->first()->id);
        $cart->add(Product::withoutGlobalScopes()->where('tenant_id', $this->tenantKopsis()->id)->where('is_available_today', true)->first()->id);
        Livewire::actingAs($this->buyer())->test(Checkout::class)->set('paymentMethod', $method)->call('placeOrder');
        if ($method !== 'cash') {
            app(\App\Services\CheckoutService::class)->markInvoicePaid(\App\Models\Invoice::first());
        }
    }

    public function test_tenant_scope_hides_other_tenants_orders(): void
    {
        $this->seedOrders();
        $this->assertSame(2, Order::withoutGlobalScopes()->count());

        $this->actingAs($this->staff());
        $this->assertSame(1, Order::count(), 'staf hanya melihat order tenantnya');
        $this->assertSame($this->tenantRina()->id, Order::first()->tenant_id);
        $this->assertTrue(Product::get()->every(fn ($p) => $p->tenant_id === $this->tenantRina()->id));

        $kopsisOrder = Order::withoutGlobalScopes()->where('tenant_id', $this->tenantKopsis()->id)->first();
        Livewire::actingAs($this->staff())->test(OrderBoard::class)
            ->assertSee(Order::first()->code)
            ->assertDontSee($kopsisOrder->code);
    }

    public function test_staff_advances_status_through_full_lifecycle_and_buyer_can_review(): void
    {
        $this->seedOrders();
        $order = Order::withoutGlobalScopes()->where('tenant_id', $this->tenantRina()->id)->first();
        $this->assertSame(OrderStatus::Confirmed, $order->status);

        $board = Livewire::actingAs($this->staff())->test(OrderBoard::class);
        foreach ([OrderStatus::Preparing, OrderStatus::Ready, OrderStatus::Completed] as $expected) {
            $board->call('advance', $order->id);
            $this->assertSame($expected, $order->refresh()->status);
        }
        $this->assertNotNull($order->completed_at);

        Livewire::actingAs($this->buyer())->test(OrderDetail::class, ['order' => $order])
            ->set('rating', 4)->set('comment', 'Enak!')
            ->call('submitReview')->assertHasNoErrors();
        $this->assertSame(1, Review::count());
        $this->assertSame(4.0, $this->tenantRina()->averageRating());
    }

    public function test_staff_cannot_touch_other_tenant_order(): void
    {
        $this->seedOrders();
        $kopsisOrder = Order::withoutGlobalScopes()->where('tenant_id', $this->tenantKopsis()->id)->first();

        try {
            Livewire::actingAs($this->staff())->test(OrderBoard::class)->call('advance', $kopsisOrder->id);
            $this->fail('Order tenant lain seharusnya tidak ditemukan (TenantScope).');
        } catch (ModelNotFoundException) {
        }
        $this->assertSame(OrderStatus::Confirmed, $kopsisOrder->refresh()->status);
    }

    public function test_cash_order_cannot_complete_before_payment_verified(): void
    {
        $this->seedOrders('cash');
        $order = Order::withoutGlobalScopes()->where('tenant_id', $this->tenantRina()->id)->first();

        $board = Livewire::actingAs($this->owner())->test(OrderBoard::class);
        $board->call('advance', $order->id); // preparing
        $board->call('advance', $order->id); // ready
        $board->call('advance', $order->id)->assertHasErrors('status'); // completed ditolak
        $this->assertSame(OrderStatus::Ready, $order->refresh()->status);

        Livewire::actingAs($this->owner())->test(Payments::class)
            ->assertSee($order->code)
            ->call('markPaid', $order->id);
        $this->assertSame(PaymentStatus::Paid, $order->refresh()->payment_status);

        $board->call('advance', $order->id)->assertHasNoErrors('status');
        $this->assertSame(OrderStatus::Completed, $order->refresh()->status);
    }

    public function test_staff_cannot_verify_payment_but_owner_can(): void
    {
        $this->seedOrders('cash');
        $order = Order::withoutGlobalScopes()->where('tenant_id', $this->tenantRina()->id)->first();

        Livewire::actingAs($this->staff())->test(OrderBoard::class)->call('markPaid', $order->id)->assertForbidden();
        Livewire::actingAs($this->owner())->test(OrderBoard::class)->call('markPaid', $order->id)->assertOk();
    }

    public function test_owner_manages_products_scoped_to_own_tenant(): void
    {
        $before = Product::withoutGlobalScopes()->where('tenant_id', $this->tenantRina()->id)->count();

        Livewire::actingAs($this->owner())->test(Products::class)
            ->call('create')
            ->set('name', 'Bakso Urat')->set('price', 12000)->set('category_id', 1)
            ->call('save')->assertHasNoErrors()->assertSet('showForm', false);

        $product = Product::withoutGlobalScopes()->where('name', 'Bakso Urat')->firstOrFail();
        $this->assertSame($this->tenantRina()->id, $product->tenant_id, 'tenant_id terisi otomatis');
        $this->assertSame($before + 1, Product::withoutGlobalScopes()->where('tenant_id', $this->tenantRina()->id)->count());

        $foreign = Product::withoutGlobalScopes()->where('tenant_id', $this->tenantKopsis()->id)->first();
        try {
            Livewire::actingAs($this->owner())->test(Products::class)->call('toggleAvailability', $foreign->id);
            $this->fail('Produk tenant lain seharusnya tidak ditemukan (TenantScope).');
        } catch (ModelNotFoundException) {
        }

        Livewire::actingAs($this->owner())->test(Products::class)->call('delete', $product->id);
        $this->assertSoftDeleted($product);
    }
}
