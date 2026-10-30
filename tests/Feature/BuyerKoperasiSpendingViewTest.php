<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\Role;
use App\Livewire\Buyer\KoperasiSpending;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Position;
use App\Models\SpendingAdjustment;
use App\Models\SpendingClosing;
use App\Models\SpendingClosingItem;
use App\Models\SpendingTarget;
use App\Models\Tenant;
use App\Models\User;
use Carbon\Carbon;
use Livewire\Livewire;
use Tests\TestCase;

class BuyerKoperasiSpendingViewTest extends TestCase
{
    private Tenant $koperasi;
    private Position $posGuru;
    private User $guru;
    private User $siswa;
    private User $umum;

    protected function setUp(): void
    {
        parent::setUp();

        $this->koperasi = $this->tenantKopsis();
        $this->posGuru = Position::where('name', 'Guru Tetap')->firstOrFail();
        $this->guru = User::where('email', 'guru@smkgo.id')->firstOrFail();
        $this->siswa = User::where('email', 'siswa@smkgo.id')->firstOrFail();
        $this->umum = User::where('email', 'tamu@smkgo.id')->firstOrFail();
    }

    private function createOrder(
        User $buyer,
        Tenant $tenant,
        int $subtotal,
        PaymentStatus $paymentStatus = PaymentStatus::Paid,
        OrderStatus $orderStatus = OrderStatus::Confirmed,
        ?Carbon $paidAt = null,
        string $source = 'app',
        array $items = [],
    ): Order {
        $invoice = Invoice::create([
            'code' => Invoice::generateCode(),
            'buyer_id' => $buyer->id,
            'total' => $subtotal,
            'payment_method' => PaymentMethod::Cash,
            'payment_status' => $paymentStatus,
            'paid_at' => $paidAt,
        ]);

        $order = Order::create([
            'code' => Order::generateCode(),
            'invoice_id' => $invoice->id,
            'buyer_id' => $buyer->id,
            'tenant_id' => $tenant->id,
            'status' => $orderStatus,
            'payment_status' => $paymentStatus,
            'subtotal' => $subtotal,
            'source' => $source,
            'paid_at' => $paidAt,
        ]);

        foreach ($items as $item) {
            OrderItem::create([
                'order_id' => $order->id,
                'product_name' => $item['name'],
                'unit_price' => $item['price'],
                'qty' => $item['qty'],
                'line_total' => $item['price'] * $item['qty'],
            ]);
        }

        return $order;
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('koperasi.spending'))->assertRedirect(route('login'));
    }

    public function test_non_buyer_role_is_redirected_by_role_middleware(): void
    {
        $staff = $this->staff();
        $this->actingAs($staff)->get(route('koperasi.spending'))->assertRedirect(route('tenant.orders'));

        $admin = $this->admin();
        $this->actingAs($admin)->get(route('koperasi.spending'))->assertRedirect(route('admin.dashboard'));
    }

    public function test_employee_sees_own_spending_progress_and_target(): void
    {
        $currentMonth = now('Asia/Jakarta')->format('Y-m');
        $paidTime = now('Asia/Jakarta');

        // Belanja Rp 60.000 dari target Rp 150.000
        $this->createOrder(
            $this->guru,
            $this->koperasi,
            60000,
            PaymentStatus::Paid,
            OrderStatus::Confirmed,
            $paidTime,
            'app',
            [['name' => 'Buku Catatan Guru', 'price' => 30000, 'qty' => 2]],
        );

        Livewire::actingAs($this->guru)
            ->test(KoperasiSpending::class, ['month' => $currentMonth])
            ->assertOk()
            ->assertSee('Koperasi Siswa')
            ->assertSee('Guru Tetap')
            ->assertSee('Rp60.000') // total verified
            ->assertSee('Rp150.000') // target
            ->assertSee('Rp90.000') // remaining
            ->assertSee('Belum Terpenuhi')
            ->assertSee('Buku Catatan Guru');
    }

    public function test_employee_sees_target_met_when_spending_reaches_target(): void
    {
        $currentMonth = now('Asia/Jakarta')->format('Y-m');
        $paidTime = now('Asia/Jakarta');

        // Belanja Rp 160.000 (melebihi target Rp 150.000)
        $this->createOrder(
            $this->guru,
            $this->koperasi,
            160000,
            PaymentStatus::Paid,
            OrderStatus::Confirmed,
            $paidTime,
        );

        Livewire::actingAs($this->guru)
            ->test(KoperasiSpending::class, ['month' => $currentMonth])
            ->assertOk()
            ->assertSee('Target Terpenuhi')
            ->assertSee('Rp0') // remaining
            ->assertSee('Rp160.000');
    }

    public function test_siswa_and_general_buyer_see_free_target_badge(): void
    {
        $currentMonth = now('Asia/Jakarta')->format('Y-m');
        $paidTime = now('Asia/Jakarta');

        $this->createOrder(
            $this->siswa,
            $this->koperasi,
            25000,
            PaymentStatus::Paid,
            OrderStatus::Confirmed,
            $paidTime,
        );

        Livewire::actingAs($this->siswa)
            ->test(KoperasiSpending::class, ['month' => $currentMonth])
            ->assertOk()
            ->assertSee('Bebas Target')
            ->assertSee('Rp25.000')
            ->assertDontSee('Sisa Kekurangan');

        // Pengguna umum tanpa NIP
        $this->createOrder(
            $this->umum,
            $this->koperasi,
            45000,
            PaymentStatus::Paid,
            OrderStatus::Confirmed,
            $paidTime,
        );

        Livewire::actingAs($this->umum)
            ->test(KoperasiSpending::class, ['month' => $currentMonth])
            ->assertOk()
            ->assertSee('Bebas Target')
            ->assertSee('Rp45.000')
            ->assertDontSee('Sisa Kekurangan');
    }

    public function test_pending_orders_are_displayed_in_warning_alert_and_not_in_total(): void
    {
        $currentMonth = now('Asia/Jakarta')->format('Y-m');
        $paidTime = now('Asia/Jakarta');

        // Order lunas Rp 40.000
        $this->createOrder($this->guru, $this->koperasi, 40000, PaymentStatus::Paid, OrderStatus::Confirmed, $paidTime);

        // Order pending Rp 35.000
        $this->createOrder($this->guru, $this->koperasi, 35000, PaymentStatus::Pending, OrderStatus::Pending, null);

        Livewire::actingAs($this->guru)
            ->test(KoperasiSpending::class, ['month' => $currentMonth])
            ->assertOk()
            ->assertSee('Rp40.000') // total verified
            ->assertSee('Menunggu Verifikasi: Rp35.000')
            ->assertSee('Rp110.000'); // remaining tetap 150.000 - 40.000
    }

    public function test_counter_order_is_displayed_with_kasir_badge(): void
    {
        $currentMonth = now('Asia/Jakarta')->format('Y-m');
        $paidTime = now('Asia/Jakarta');

        $this->createOrder(
            $this->guru,
            $this->koperasi,
            55000,
            PaymentStatus::Paid,
            OrderStatus::Confirmed,
            $paidTime,
            'counter',
        );

        Livewire::actingAs($this->guru)
            ->test(KoperasiSpending::class, ['month' => $currentMonth])
            ->assertOk()
            ->assertSee('Belanja Kasir Langsung')
            ->assertSee('Kasir')
            ->assertSee('Rp55.000');
    }

    public function test_month_navigation_loads_correct_period_data(): void
    {
        $pastMonth = '2026-05';
        $paidTimePast = Carbon::parse('2026-05-10 10:00:00', 'Asia/Jakarta');

        $this->createOrder($this->guru, $this->koperasi, 80000, PaymentStatus::Paid, OrderStatus::Confirmed, $paidTimePast);

        Livewire::actingAs($this->guru)
            ->test(KoperasiSpending::class, ['month' => '2026-06'])
            ->assertSee('Rp0') // Juni belum ada belanja
            ->call('previousMonth')
            ->assertSet('month', '2026-05')
            ->assertSee('Rp80.000') // Mei ada belanja Rp 80.000
            ->call('nextMonth')
            ->assertSet('month', '2026-06')
            ->assertSee('Rp0');
    }

    public function test_closed_month_displays_notice_and_locked_data(): void
    {
        $period = '2026-04';
        $closing = SpendingClosing::create([
            'tenant_id' => $this->koperasi->id,
            'period' => $period,
            'closed_at' => Carbon::parse('2026-05-01 02:00:00', 'Asia/Jakarta'),
        ]);

        SpendingClosingItem::create([
            'closing_id' => $closing->id,
            'user_id' => $this->guru->id,
            'position_id' => $this->posGuru->id,
            'target_amount' => 150000,
            'total_amount' => 150000,
            'is_met' => true,
        ]);

        Livewire::actingAs($this->guru)
            ->test(KoperasiSpending::class, ['month' => $period])
            ->assertOk()
            ->assertSee('Periode Buku Ditutup')
            ->assertSee('Target Terpenuhi')
            ->assertSee('Rp150.000');
    }

    public function test_account_page_contains_link_to_koperasi_spending(): void
    {
        $this->actingAs($this->guru)
            ->get(route('account'))
            ->assertOk()
            ->assertSee(route('koperasi.spending'))
            ->assertSee('Belanja Koperasi');
    }
}

