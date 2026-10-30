<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Livewire\Tenant\OperatingHours;
use App\Models\Order;
use App\Models\Tenant;
use App\Models\TenantHours;
use App\Models\User;
use App\Services\CheckoutService;
use App\Services\PreOrderService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PreOrderTest extends TestCase
{
    use RefreshDatabase;

    // ----------------------------------------------------------------
    // Helpers
    // ----------------------------------------------------------------

    private function makeTenant(array $attrs = []): Tenant
    {
        return Tenant::create(array_merge([
            'name'   => 'Toko PO',
            'slug'   => 'toko-po-'.uniqid(),
            'status' => Tenant::STATUS_ACTIVE,
            'is_open' => true,
        ], $attrs));
    }

    /** Set 7-hari jadwal operasional pada tenant (semua hari buka jam $open–$close). */
    private function setHours(Tenant $tenant, string $open, string $close, ?int $closedDay = null): void
    {
        for ($d = 0; $d <= 6; $d++) {
            TenantHours::updateOrCreate(
                ['tenant_id' => $tenant->id, 'day_of_week' => $d],
                [
                    'is_open'  => $d !== $closedDay,
                    'open_at'  => $open . ':00',
                    'close_at' => $close . ':00',
                ],
            );
        }
        $tenant->unsetRelation('hours');
    }

    private function makeBuyer(): User
    {
        return User::create([
            'name'     => 'Pembeli Test',
            'email'    => 'buyer-po-' . uniqid() . '@test.id',
            'password' => bcrypt('password'),
            'role'     => 'user',
            'is_active' => true,
        ]);
    }

    private function makeProduct(Tenant $tenant): \App\Models\Product
    {
        return $tenant->products()->create([
            'name'               => 'Menu PO',
            'price'              => 10000,
            'is_available_today' => true,
        ]);
    }

    private function makeStaff(Tenant $tenant, string $role = 'tenant_admin'): User
    {
        return User::create([
            'name'      => 'Admin PO',
            'email'     => 'admin-po-' . uniqid() . '@test.id',
            'password'  => bcrypt('password'),
            'role'      => $role,
            'tenant_id' => $tenant->id,
            'is_active' => true,
        ]);
    }

    // ----------------------------------------------------------------
    // 1. Checkout: PO saat di luar jam operasional
    // ----------------------------------------------------------------

    public function test_checkout_outside_hours_creates_pre_order(): void
    {
        $tenant  = $this->makeTenant();
        $product = $this->makeProduct($tenant);
        $buyer   = $this->makeBuyer();

        // Jam operasional 07:00–15:00; kita set Carbon ke 20:00 (di luar jam)
        $this->setHours($tenant, '07:00', '15:00');

        Carbon::setTestNow(Carbon::today('Asia/Jakarta')->setTime(20, 0, 0));

        $this->actingAs($buyer);
        app(\App\Services\CartService::class)->add($product->id);

        $invoice = app(CheckoutService::class)->checkout($buyer, PaymentMethod::Cash);

        Carbon::setTestNow(); // reset

        $order = $invoice->orders->first();
        $this->assertEquals(OrderStatus::PreOrder, $order->status);
        $this->assertNotNull($order->scheduled_for);
    }

    public function test_checkout_within_hours_skips_pre_order(): void
    {
        $tenant  = $this->makeTenant();
        $product = $this->makeProduct($tenant);
        $buyer   = $this->makeBuyer();

        $this->setHours($tenant, '07:00', '15:00');

        // Jam 10:00 → dalam jadwal
        Carbon::setTestNow(Carbon::today('Asia/Jakarta')->setTime(10, 0, 0));

        $this->actingAs($buyer);
        app(\App\Services\CartService::class)->add($product->id);

        $invoice = app(CheckoutService::class)->checkout($buyer, PaymentMethod::Cash);

        Carbon::setTestNow();

        $order = $invoice->orders->first();
        $this->assertEquals(OrderStatus::Confirmed, $order->status);
        $this->assertNull($order->scheduled_for);
    }

    public function test_tenant_without_hours_uses_is_open_flag(): void
    {
        $tenant  = $this->makeTenant(['is_open' => true]);
        $product = $this->makeProduct($tenant);
        $buyer   = $this->makeBuyer();

        // Tidak ada TenantHours → backward-compat: ikut is_open=true → confirmed
        $this->actingAs($buyer);
        app(\App\Services\CartService::class)->add($product->id);

        $invoice = app(CheckoutService::class)->checkout($buyer, PaymentMethod::Cash);

        $order = $invoice->orders->first();
        $this->assertEquals(OrderStatus::Confirmed, $order->status);
    }

    public function test_tenant_closed_flag_without_hours_creates_pre_order(): void
    {
        $tenant  = $this->makeTenant(['is_open' => false]);
        $product = $this->makeProduct($tenant);
        $buyer   = $this->makeBuyer();

        // is_open=false, tidak ada jadwal → PO
        $this->actingAs($buyer);
        app(\App\Services\CartService::class)->add($product->id);

        $invoice = app(CheckoutService::class)->checkout($buyer, PaymentMethod::Cash);

        $order = $invoice->orders->first();
        $this->assertEquals(OrderStatus::PreOrder, $order->status);
    }

    public function test_prepay_outside_hours_also_creates_pre_order(): void
    {
        $tenant  = $this->makeTenant();
        $product = $this->makeProduct($tenant);
        $buyer   = $this->makeBuyer();

        $this->setHours($tenant, '07:00', '15:00');
        Carbon::setTestNow(Carbon::today('Asia/Jakarta')->setTime(22, 0, 0));

        $this->actingAs($buyer);
        app(\App\Services\CartService::class)->add($product->id);

        $invoice = app(CheckoutService::class)->checkout($buyer, PaymentMethod::Qris);

        Carbon::setTestNow();

        $order = $invoice->orders->first();
        $this->assertEquals(OrderStatus::PreOrder, $order->status);
    }

    // ----------------------------------------------------------------
    // 2. PreOrderService: auto-confirm
    // ----------------------------------------------------------------

    public function test_auto_confirm_promotes_pre_order_when_within_hours(): void
    {
        $tenant  = $this->makeTenant();
        $buyer   = $this->makeBuyer();
        $product = $this->makeProduct($tenant);

        $this->setHours($tenant, '07:00', '15:00');

        // Buat PO langsung di DB
        $order = Order::create([
            'code'           => Order::generateCode(),
            'invoice_id'     => \App\Models\Invoice::create([
                'code'           => \App\Models\Invoice::generateCode(),
                'buyer_id'       => $buyer->id,
                'total'          => 10000,
                'payment_method' => PaymentMethod::Cash,
                'payment_status' => PaymentStatus::Pending,
            ])->id,
            'buyer_id'       => $buyer->id,
            'tenant_id'      => $tenant->id,
            'status'         => OrderStatus::PreOrder,
            'payment_status' => PaymentStatus::Pending,
            'subtotal'       => 10000,
        ]);

        // Waktu: jam 08:00 → dalam jadwal → harus di-confirm
        $at = Carbon::today('Asia/Jakarta')->setTime(8, 0, 0);
        $confirmed = app(PreOrderService::class)->autoConfirmDue($at);

        $this->assertEquals(1, $confirmed);
        $this->assertEquals(OrderStatus::Confirmed, $order->fresh()->status);
        $this->assertNotNull($order->fresh()->confirmed_at);
    }

    public function test_auto_confirm_skips_when_still_closed(): void
    {
        $tenant  = $this->makeTenant();
        $buyer   = $this->makeBuyer();
        $product = $this->makeProduct($tenant);

        $this->setHours($tenant, '07:00', '15:00');

        $order = Order::create([
            'code'           => Order::generateCode(),
            'invoice_id'     => \App\Models\Invoice::create([
                'code'           => \App\Models\Invoice::generateCode(),
                'buyer_id'       => $buyer->id,
                'total'          => 10000,
                'payment_method' => PaymentMethod::Cash,
                'payment_status' => PaymentStatus::Pending,
            ])->id,
            'buyer_id'       => $buyer->id,
            'tenant_id'      => $tenant->id,
            'status'         => OrderStatus::PreOrder,
            'payment_status' => PaymentStatus::Pending,
            'subtotal'       => 10000,
        ]);

        // Jam 05:00 → sebelum buka → tidak dikonfirmasi
        $at = Carbon::today('Asia/Jakarta')->setTime(5, 0, 0);
        $confirmed = app(PreOrderService::class)->autoConfirmDue($at);

        $this->assertEquals(0, $confirmed);
        $this->assertEquals(OrderStatus::PreOrder, $order->fresh()->status);
    }

    public function test_auto_confirm_skips_closed_day(): void
    {
        $tenant  = $this->makeTenant();
        $buyer   = $this->makeBuyer();

        // Hari Minggu (0) ditutup
        $this->setHours($tenant, '07:00', '15:00', closedDay: 0);

        $order = Order::create([
            'code'           => Order::generateCode(),
            'invoice_id'     => \App\Models\Invoice::create([
                'code'           => \App\Models\Invoice::generateCode(),
                'buyer_id'       => $buyer->id,
                'total'          => 10000,
                'payment_method' => PaymentMethod::Cash,
                'payment_status' => PaymentStatus::Pending,
            ])->id,
            'buyer_id'       => $buyer->id,
            'tenant_id'      => $tenant->id,
            'status'         => OrderStatus::PreOrder,
            'payment_status' => PaymentStatus::Pending,
            'subtotal'       => 10000,
        ]);

        // Cari hari Minggu berikutnya jam 10:00
        $now = Carbon::now('Asia/Jakarta');
        $sunday = $now->copy()->next(Carbon::SUNDAY)->setTime(10, 0, 0);

        $confirmed = app(PreOrderService::class)->autoConfirmDue($sunday);

        $this->assertEquals(0, $confirmed);
        $this->assertEquals(OrderStatus::PreOrder, $order->fresh()->status);
    }

    // ----------------------------------------------------------------
    // 3. Buyer: bisa cancel PO
    // ----------------------------------------------------------------

    public function test_buyer_can_cancel_pre_order(): void
    {
        $tenant  = $this->makeTenant();
        $buyer   = $this->makeBuyer();

        $invoice = \App\Models\Invoice::create([
            'code'           => \App\Models\Invoice::generateCode(),
            'buyer_id'       => $buyer->id,
            'total'          => 10000,
            'payment_method' => PaymentMethod::Cash,
            'payment_status' => PaymentStatus::Pending,
        ]);

        $order = Order::create([
            'code'           => Order::generateCode(),
            'invoice_id'     => $invoice->id,
            'buyer_id'       => $buyer->id,
            'tenant_id'      => $tenant->id,
            'status'         => OrderStatus::PreOrder,
            'payment_status' => PaymentStatus::Pending,
            'subtotal'       => 10000,
        ]);

        $this->assertTrue($order->canBeCancelledByBuyer());

        $this->actingAs($buyer);
        app(\App\Services\OrderStatusService::class)->cancel($order, 'Dibatalkan oleh pembeli');

        $this->assertEquals(OrderStatus::Cancelled, $order->fresh()->status);
    }

    // ----------------------------------------------------------------
    // 4. Tenant admin: CRUD jam operasional
    // ----------------------------------------------------------------

    public function test_tenant_admin_can_view_operating_hours_page(): void
    {
        $tenant = $this->makeTenant();
        $admin  = $this->makeStaff($tenant, 'tenant_admin');

        $this->actingAs($admin);

        Livewire::test(OperatingHours::class)
            ->assertSee('Jadwal Buka per Hari')
            ->assertSee('Senin')
            ->assertSee('Minggu');
    }

    public function test_tenant_admin_can_save_operating_hours(): void
    {
        $tenant = $this->makeTenant();
        $admin  = $this->makeStaff($tenant, 'tenant_admin');

        $hours = [];
        for ($d = 0; $d <= 6; $d++) {
            $hours[$d] = [
                'is_open'  => $d !== 0, // Minggu tutup
                'open_at'  => '08:00',
                'close_at' => '16:00',
            ];
        }

        $this->actingAs($admin);

        Livewire::test(OperatingHours::class)
            ->set('hours', $hours)
            ->call('save')
            ->assertDispatched('toast');

        // Pastikan tersimpan di DB
        $this->assertDatabaseHas('tenant_hours', [
            'tenant_id'  => $tenant->id,
            'day_of_week' => 1, // Senin
            'open_at'    => '08:00:00',
            'close_at'   => '16:00:00',
            'is_open'    => true,
        ]);

        $this->assertDatabaseHas('tenant_hours', [
            'tenant_id'  => $tenant->id,
            'day_of_week' => 0, // Minggu tutup
            'is_open'    => false,
        ]);
    }

    public function test_save_hours_fails_if_close_before_open(): void
    {
        $tenant = $this->makeTenant();
        $admin  = $this->makeStaff($tenant, 'tenant_admin');

        $hours = [];
        for ($d = 0; $d <= 6; $d++) {
            $hours[$d] = [
                'is_open'  => true,
                'open_at'  => '15:00',
                'close_at' => '07:00', // invalid
            ];
        }

        $this->actingAs($admin);

        Livewire::test(OperatingHours::class)
            ->set('hours', $hours)
            ->call('save')
            ->assertHasErrors(['hours.0.close_at']);
    }

    public function test_tenant_staf_cannot_access_operating_hours(): void
    {
        $tenant = $this->makeTenant();
        $staf   = $this->makeStaff($tenant, 'tenant_staf');

        $this->actingAs($staf);

        // Route berada di balik middleware role:tenant_admin → staf di-redirect (302), bukan 403
        $this->get(route('tenant.operating.hours'))
            ->assertRedirect();
    }

    // ----------------------------------------------------------------
    // 5. Tenant model: isWithinOperatingHours & nextOpenAt
    // ----------------------------------------------------------------

    public function test_is_within_operating_hours_returns_true_inside_window(): void
    {
        $tenant = $this->makeTenant();
        $this->setHours($tenant, '07:00', '15:00');
        $tenant->unsetRelation('hours');

        $at = Carbon::today('Asia/Jakarta')->setTime(10, 0, 0);
        $this->assertTrue($tenant->isWithinOperatingHours($at));
    }

    public function test_is_within_operating_hours_returns_false_outside_window(): void
    {
        $tenant = $this->makeTenant();
        $this->setHours($tenant, '07:00', '15:00');
        $tenant->unsetRelation('hours');

        $atBefore = Carbon::today('Asia/Jakarta')->setTime(6, 59, 0);
        $atAfter  = Carbon::today('Asia/Jakarta')->setTime(15, 0, 0); // exactly close_at → tutup

        $this->assertFalse($tenant->isWithinOperatingHours($atBefore));
        $this->assertFalse($tenant->isWithinOperatingHours($atAfter));
    }

    public function test_next_open_at_returns_correct_next_slot(): void
    {
        $tenant = $this->makeTenant();
        $this->setHours($tenant, '07:00', '15:00');
        $tenant->unsetRelation('hours');

        // Jam 20:00 → nextOpenAt seharusnya besok jam 07:00
        $now    = Carbon::today('Asia/Jakarta')->setTime(20, 0, 0);
        $nextAt = $tenant->nextOpenAt($now);

        $this->assertNotNull($nextAt);
        $this->assertEquals('07:00', $nextAt->format('H:i'));
    }
}
