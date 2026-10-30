<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Livewire\Tenant\CounterCashier;
use App\Models\Invoice;
use App\Models\Notification;
use App\Models\Order;
use App\Models\Product;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CounterCashierTest extends TestCase
{
    use RefreshDatabase;

    private function makeTenant(): Tenant
    {
        return Tenant::create([
            'name'    => 'Kantin Bu Joko',
            'slug'    => 'kantin-bu-joko-' . uniqid(),
            'status'  => Tenant::STATUS_ACTIVE,
            'is_open' => true,
        ]);
    }

    private function makeStaff(Tenant $tenant, string $role = 'tenant_staf'): User
    {
        return User::create([
            'name'      => 'Staf Kantin',
            'email'     => 'staf-' . uniqid() . '@test.id',
            'password'  => bcrypt('password'),
            'role'      => $role,
            'tenant_id' => $tenant->id,
            'is_active' => true,
        ]);
    }

    private function makeBuyer(): User
    {
        return User::create([
            'name'       => 'Budi Siswa',
            'email'      => 'budi-' . uniqid() . '@test.id',
            'identifier' => 'NIS12345',
            'password'   => bcrypt('password'),
            'role'       => 'user',
            'is_active'  => true,
        ]);
    }

    private function makeProduct(Tenant $tenant, string $name = 'Nasi Uduk', int $price = 10000, bool $available = true): Product
    {
        return Product::create([
            'tenant_id'          => $tenant->id,
            'name'               => $name,
            'price'              => $price,
            'is_available_today' => $available,
        ]);
    }

    public function test_tenant_staff_can_access_counter_cashier(): void
    {
        $tenant = $this->makeTenant();
        $staff  = $this->makeStaff($tenant);

        $this->actingAs($staff)
            ->get(route('tenant.counter'))
            ->assertOk()
            ->assertSee('Kasir Counter')
            ->assertSee($tenant->name);
    }

    public function test_buyer_role_cannot_access_counter_cashier(): void
    {
        $buyer = $this->makeBuyer();

        $this->actingAs($buyer)
            ->get(route('tenant.counter'))
            ->assertRedirect();
    }

    public function test_counter_creates_completed_paid_order_with_items_anonymous(): void
    {
        $tenant = $this->makeTenant();
        $staff  = $this->makeStaff($tenant);
        $p1     = $this->makeProduct($tenant, 'Nasi Uduk', 10000);
        $p2     = $this->makeProduct($tenant, 'Es Teh', 3000);

        $this->actingAs($staff);

        Livewire::test(CounterCashier::class)
            ->call('addToCart', $p1->id)
            ->call('addToCart', $p1->id) // qty = 2
            ->call('addToCart', $p2->id) // qty = 1
            ->set('paymentMethod', 'cash')
            ->set('note', 'Bungkus terpisah')
            ->call('save')
            ->assertDispatched('toast');

        $order = Order::where('tenant_id', $tenant->id)->latest()->first();

        $this->assertNotNull($order);
        $this->assertEquals(OrderStatus::Completed, $order->status);
        $this->assertEquals(PaymentStatus::Paid, $order->payment_status);
        $this->assertEquals(23000, $order->subtotal); // 2*10000 + 1*3000
        $this->assertEquals('counter', $order->source);
        $this->assertEquals($staff->id, $order->entered_by);
        $this->assertEquals('Bungkus terpisah', $order->note);
        $this->assertNotNull($order->paid_at);
        $this->assertNotNull($order->completed_at);

        // Buyer is "Pelanggan Umum"
        $this->assertEquals('Pelanggan Umum', $order->buyer->name);

        // Order items
        $this->assertCount(2, $order->items);
        $item1 = $order->items->firstWhere('product_id', $p1->id);
        $this->assertEquals(2, $item1->qty);
        $this->assertEquals(20000, $item1->line_total);

        // Invoice & Payment
        $invoice = $order->invoice;
        $this->assertEquals(23000, $invoice->total);
        $this->assertEquals(PaymentMethod::Cash, $invoice->payment_method);
        $this->assertEquals(PaymentStatus::Paid, $invoice->payment_status);
    }

    public function test_counter_supports_registered_buyer_and_notifies(): void
    {
        $tenant = $this->makeTenant();
        $staff  = $this->makeStaff($tenant);
        $buyer  = $this->makeBuyer();
        $p1     = $this->makeProduct($tenant, 'Batagor', 12000);

        $this->actingAs($staff);

        Livewire::test(CounterCashier::class)
            ->call('addToCart', $p1->id)
            ->set('buyerSearch', 'Budi')
            ->call('selectBuyer', $buyer->id)
            ->set('paymentMethod', 'qris')
            ->call('save')
            ->assertDispatched('toast');

        $order = Order::where('tenant_id', $tenant->id)->latest()->first();
        $this->assertEquals($buyer->id, $order->buyer_id);
        $this->assertEquals(12000, $order->subtotal);
        $this->assertEquals(PaymentMethod::Qris, $order->invoice->payment_method);

        // Notification should exist for registered buyer
        $this->assertDatabaseHas('notifications', [
            'user_id' => $buyer->id,
            'type'    => 'payment.received',
        ]);
    }

    public function test_counter_rejects_empty_cart(): void
    {
        $tenant = $this->makeTenant();
        $staff  = $this->makeStaff($tenant);

        $this->actingAs($staff);

        Livewire::test(CounterCashier::class)
            ->call('save')
            ->assertHasErrors(['cart']);
    }

    public function test_counter_cart_manipulation_methods(): void
    {
        $tenant = $this->makeTenant();
        $staff  = $this->makeStaff($tenant);
        $p1     = $this->makeProduct($tenant, 'Mie Goreng', 8000);

        $this->actingAs($staff);

        $test = Livewire::test(CounterCashier::class)
            ->call('addToCart', $p1->id)
            ->call('addToCart', $p1->id)
            ->assertSet('cart.' . $p1->id, 2)
            ->call('decrementCart', $p1->id)
            ->assertSet('cart.' . $p1->id, 1)
            ->call('decrementCart', $p1->id)
            ->assertSet('cart', [])
            ->call('addToCart', $p1->id)
            ->call('removeFromCart', $p1->id)
            ->assertSet('cart', [])
            ->call('addToCart', $p1->id)
            ->call('clearCart')
            ->assertSet('cart', []);
    }
}

