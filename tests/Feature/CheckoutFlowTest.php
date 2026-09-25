<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Livewire\Buyer\Catalog;
use App\Livewire\Buyer\Checkout;
use App\Livewire\Buyer\InvoiceShow;
use App\Livewire\Buyer\OrderDetail;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\Product;
use App\Services\CartService;
use Livewire\Livewire;
use Tests\TestCase;

class CheckoutFlowTest extends TestCase
{
    private function productOf(string $slug): Product
    {
        return Product::withoutGlobalScopes()->whereHas('tenant', fn ($q) => $q->where('slug', $slug))->where('is_available_today', true)->firstOrFail();
    }

    public function test_multi_tenant_cart_is_split_into_one_invoice_and_orders_per_tenant(): void
    {
        $buyer = $this->buyer();
        $rina = $this->productOf('kantin-bu-rina');
        $kopsis = $this->productOf('koperasi-siswa');

        Livewire::actingAs($buyer)->test(Catalog::class)
            ->call('addToCart', $rina->id)
            ->call('addToCart', $rina->id)
            ->call('addToCart', $kopsis->id)
            ->assertDispatched('cart-updated');

        $this->assertSame(3, app(CartService::class)->count());

        Livewire::actingAs($buyer)->test(Checkout::class)
            ->set('paymentMethod', 'qris')
            ->set('note', 'Ambil istirahat kedua')
            ->call('placeOrder')
            ->assertHasNoErrors()
            ->assertRedirect();

        $invoice = Invoice::with('orders.items')->firstOrFail();
        $this->assertSame(2, $invoice->orders->count(), 'satu order per tenant');
        $this->assertSame($rina->price * 2 + $kopsis->price, $invoice->total);
        $this->assertSame(PaymentStatus::Pending, $invoice->payment_status);
        $this->assertTrue($invoice->orders->every(fn ($o) => $o->status === OrderStatus::Pending));
        $this->assertSame(0, app(CartService::class)->count(), 'keranjang dikosongkan');

        $rinaOrder = $invoice->orders->firstWhere('tenant_id', $rina->tenant_id);
        $this->assertSame(2, $rinaOrder->items->first()->qty);
        $this->assertSame($rina->price, $rinaOrder->items->first()->unit_price, 'harga di-snapshot');
    }

    public function test_simulated_qris_payment_confirms_all_orders(): void
    {
        $buyer = $this->buyer();
        app(CartService::class)->add($this->productOf('kantin-bu-rina')->id);
        app(CartService::class)->add($this->productOf('jajan-corner')->id);
        Livewire::actingAs($buyer)->test(Checkout::class)->set('paymentMethod', 'qris')->call('placeOrder');

        $invoice = Invoice::firstOrFail();
        Livewire::actingAs($buyer)->test(InvoiceShow::class, ['invoice' => $invoice])
            ->assertSee('Saya sudah bayar')
            ->call('simulatePayment')
            ->assertDontSee('Saya sudah bayar');

        $invoice->refresh();
        $this->assertSame(PaymentStatus::Paid, $invoice->payment_status);
        $this->assertNotNull($invoice->paid_at);
        $this->assertTrue(Order::withoutGlobalScopes()->get()->every(fn ($o) => $o->status === OrderStatus::Confirmed && $o->isPaid()));
    }

    public function test_cash_checkout_confirms_immediately_but_unpaid(): void
    {
        app(CartService::class)->add($this->productOf('kantin-bu-rina')->id);
        Livewire::actingAs($this->buyer())->test(Checkout::class)->set('paymentMethod', 'cash')->call('placeOrder');

        $order = Order::withoutGlobalScopes()->firstOrFail();
        $this->assertSame(OrderStatus::Confirmed, $order->status);
        $this->assertSame(PaymentStatus::Pending, $order->payment_status);
        $this->assertSame(PaymentMethod::Cash, $order->invoice->payment_method);
    }

    public function test_checkout_rejects_unavailable_product(): void
    {
        $soldOut = Product::withoutGlobalScopes()->where('is_available_today', false)->firstOrFail();
        app(CartService::class)->add($soldOut->id);

        Livewire::actingAs($this->buyer())->test(Checkout::class)
            ->set('paymentMethod', 'qris')
            ->call('placeOrder')
            ->assertHasErrors('cart');

        $this->assertSame(0, Invoice::count());
    }

    public function test_buyer_can_cancel_pending_order_and_cannot_see_others(): void
    {
        app(CartService::class)->add($this->productOf('kantin-bu-rina')->id);
        Livewire::actingAs($this->buyer())->test(Checkout::class)->set('paymentMethod', 'transfer')->call('placeOrder');
        $order = Order::withoutGlobalScopes()->firstOrFail();

        Livewire::actingAs($this->buyer())->test(OrderDetail::class, ['order' => $order])->call('cancel');
        $order->refresh();
        $this->assertSame(OrderStatus::Cancelled, $order->status);
        $this->assertSame(PaymentStatus::Cancelled, $order->invoice->payment_status, 'invoice ikut batal');

        $other = \App\Models\User::where('email', 'guru@smkgo.id')->first();
        Livewire::actingAs($other)->test(OrderDetail::class, ['order' => $order])->assertForbidden();
    }
}
