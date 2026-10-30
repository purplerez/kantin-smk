<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\Role;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\Position;
use App\Models\SpendingAdjustment;
use App\Models\SpendingClosing;
use App\Models\SpendingClosingItem;
use App\Models\SpendingTarget;
use App\Models\Tenant;
use App\Models\User;
use App\Services\OrderStatusService;
use App\Services\SpendingTotalCalculator;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KoperasiSpendingCalculationTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $koperasi;
    private Tenant $kantinLain;
    private Position $posGuru;
    private User $guru;
    private User $siswa;
    private User $umum;
    private SpendingTotalCalculator $calculator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->calculator = app(SpendingTotalCalculator::class);
        $this->koperasi = $this->tenantKopsis();
        $this->kantinLain = $this->tenantRina();

        $this->guru = User::where('email', 'guru@smkgo.id')->firstOrFail();
        $this->siswa = User::where('email', 'siswa@smkgo.id')->firstOrFail();
        $this->umum = User::where('email', 'tamu@smkgo.id')->firstOrFail();

        $this->posGuru = $this->guru->position;
    }

    private function createOrder(
        User $buyer,
        Tenant $tenant,
        int $subtotal,
        PaymentStatus $paymentStatus = PaymentStatus::Paid,
        OrderStatus $orderStatus = OrderStatus::Confirmed,
        ?Carbon $paidAt = null,
        ?Carbon $createdAt = null,
    ): Order {
        $invoice = Invoice::create([
            'code' => Invoice::generateCode(),
            'buyer_id' => $buyer->id,
            'total' => $subtotal,
            'payment_method' => PaymentMethod::Cash,
            'payment_status' => $paymentStatus,
            'paid_at' => $paidAt,
            'created_at' => $createdAt ?? now(),
        ]);

        return Order::create([
            'code' => Order::generateCode(),
            'invoice_id' => $invoice->id,
            'buyer_id' => $buyer->id,
            'tenant_id' => $tenant->id,
            'status' => $orderStatus,
            'payment_status' => $paymentStatus,
            'subtotal' => $subtotal,
            'source' => 'app',
            'paid_at' => $paidAt,
            'created_at' => $createdAt ?? now(),
        ]);
    }

    public function test_total_only_counts_paid_orders_of_koperasi_tenant(): void
    {
        $paidTime = Carbon::parse('2026-10-15 10:00:00', 'Asia/Jakarta');

        // Order 1: Koperasi lunas Rp 50.000 (dihitung)
        $this->createOrder($this->guru, $this->koperasi, 50000, PaymentStatus::Paid, OrderStatus::Confirmed, $paidTime);

        // Order 2: Kantin lain lunas Rp 40.000 (TIDAK dihitung)
        $this->createOrder($this->guru, $this->kantinLain, 40000, PaymentStatus::Paid, OrderStatus::Confirmed, $paidTime);

        // Order 3: Koperasi tetapi dibatalkan (TIDAK dihitung)
        $this->createOrder($this->guru, $this->koperasi, 20000, PaymentStatus::Cancelled, OrderStatus::Cancelled, null);

        // Order 4: Koperasi lunas tetapi direfund / void (TIDAK dihitung)
        $this->createOrder($this->guru, $this->koperasi, 15000, PaymentStatus::Refunded, OrderStatus::Cancelled, $paidTime);

        $result = $this->calculator->calculateForUser($this->guru, $this->koperasi, '2026-10');

        $this->assertSame(50000, $result['total_verified']);
        $this->assertSame(50000, $result['orders_subtotal']);
        $this->assertSame(150000, $result['target']);
        $this->assertSame(100000, $result['remaining']);
        $this->assertFalse($result['is_met']);
    }

    public function test_multi_tenant_invoice_counts_only_koperasi_order_subtotal(): void
    {
        $paidTime = Carbon::parse('2026-10-10 12:00:00', 'Asia/Jakarta');

        // Simulasikan 1 invoice checkout gabungan
        $invoice = Invoice::create([
            'code' => Invoice::generateCode(),
            'buyer_id' => $this->guru->id,
            'total' => 75000, // Rp 25.000 koperasi + Rp 50.000 kantin lain
            'payment_method' => PaymentMethod::Qris,
            'payment_status' => PaymentStatus::Paid,
            'paid_at' => $paidTime,
        ]);

        // Order koperasi
        Order::create([
            'code' => Order::generateCode(),
            'invoice_id' => $invoice->id,
            'buyer_id' => $this->guru->id,
            'tenant_id' => $this->koperasi->id,
            'status' => OrderStatus::Confirmed,
            'payment_status' => PaymentStatus::Paid,
            'subtotal' => 25000,
            'paid_at' => $paidTime,
        ]);

        // Order tenant lain dalam invoice yang sama
        Order::create([
            'code' => Order::generateCode(),
            'invoice_id' => $invoice->id,
            'buyer_id' => $this->guru->id,
            'tenant_id' => $this->kantinLain->id,
            'status' => OrderStatus::Confirmed,
            'payment_status' => PaymentStatus::Paid,
            'subtotal' => 50000,
            'paid_at' => $paidTime,
        ]);

        $result = $this->calculator->calculateForUser($this->guru, $this->koperasi, '2026-10');

        $this->assertSame(25000, $result['total_verified']);
        $this->assertSame(25000, $result['orders_subtotal']);
    }

    public function test_month_boundary_based_on_paid_at_in_asia_jakarta_timezone(): void
    {
        // Kasus: Pesanan dibuat tgl 31 Okt 2026 23:58 WIB, lunas tgl 1 Nov 2026 00:02 WIB
        $orderTime = Carbon::parse('2026-10-31 23:58:00', 'Asia/Jakarta');
        $paidTimeNextMonth = Carbon::parse('2026-11-01 00:02:00', 'Asia/Jakarta');

        $this->createOrder(
            $this->guru,
            $this->koperasi,
            60000,
            PaymentStatus::Paid,
            OrderStatus::Confirmed,
            $paidTimeNextMonth,
            $orderTime,
        );

        // Kasus: Pesanan lunas tepat sebelum batas bulan (31 Okt 2026 23:55 WIB)
        $paidTimeSameMonth = Carbon::parse('2026-10-31 23:55:00', 'Asia/Jakarta');
        $this->createOrder(
            $this->guru,
            $this->koperasi,
            40000,
            PaymentStatus::Paid,
            OrderStatus::Confirmed,
            $paidTimeSameMonth,
            $paidTimeSameMonth,
        );

        // Hitung bulan Oktober 2026: hanya order yang lunas sebelum 1 Nov 00:00 WIB
        $october = $this->calculator->calculateForUser($this->guru, $this->koperasi, '2026-10');
        $this->assertSame(40000, $october['total_verified']);

        // Hitung bulan November 2026: order yang lunas tgl 1 Nov masuk ke sini
        $november = $this->calculator->calculateForUser($this->guru, $this->koperasi, '2026-11');
        $this->assertSame(60000, $november['total_verified']);
    }

    public function test_pending_orders_are_reported_separately_as_pending_verification(): void
    {
        $paidTime = Carbon::parse('2026-10-05 09:00:00', 'Asia/Jakarta');

        // Order lunas
        $this->createOrder($this->guru, $this->koperasi, 70000, PaymentStatus::Paid, OrderStatus::Confirmed, $paidTime);

        // Order pending verifikasi (tunai/prepay belum diverifikasi)
        $this->createOrder($this->guru, $this->koperasi, 30000, PaymentStatus::Pending, OrderStatus::Pending, null);

        $result = $this->calculator->calculateForUser($this->guru, $this->koperasi, '2026-10');

        $this->assertSame(70000, $result['total_verified']);
        $this->assertSame(30000, $result['pending_verification']);
        $this->assertSame(80000, $result['remaining']); // target 150.000 - 70.000
    }

    public function test_non_nip_users_siswa_and_general_public_are_supported_without_target_mandate(): void
    {
        $paidTime = Carbon::parse('2026-10-12 14:00:00', 'Asia/Jakarta');

        // Siswa berbelanja di koperasi
        $this->createOrder($this->siswa, $this->koperasi, 35000, PaymentStatus::Paid, OrderStatus::Confirmed, $paidTime);

        $siswaResult = $this->calculator->calculateForUser($this->siswa, $this->koperasi, '2026-10');
        $this->assertSame(35000, $siswaResult['total_verified']);
        $this->assertFalse($siswaResult['has_target']);
        $this->assertSame(0, $siswaResult['target']);
        $this->assertSame(0, $siswaResult['remaining']);
        $this->assertTrue($siswaResult['is_met']);

        // Pengguna umum tanpa NIP berbelanja di koperasi
        $this->createOrder($this->umum, $this->koperasi, 50000, PaymentStatus::Paid, OrderStatus::Confirmed, $paidTime);

        $umumResult = $this->calculator->calculateForUser($this->umum, $this->koperasi, '2026-10');
        $this->assertSame(50000, $umumResult['total_verified']);
        $this->assertFalse($umumResult['has_target']);
        $this->assertSame(0, $umumResult['target']);
        $this->assertTrue($umumResult['is_met']);
    }

    public function test_target_amount_resolved_by_effective_date_range(): void
    {
        // Target baru mulai 1 Juli 2026: Rp 200.000
        SpendingTarget::create([
            'position_id' => $this->posGuru->id,
            'tenant_id' => $this->koperasi->id,
            'amount' => 200000,
            'effective_from' => '2026-07-01',
            'effective_to' => null,
        ]);

        // Mei 2026 masih memakai target Jan 2026 (Rp 150.000)
        $mei = $this->calculator->calculateForUser($this->guru, $this->koperasi, '2026-05');
        $this->assertSame(150000, $mei['target']);

        // Juli 2026 sudah memakai target baru (Rp 200.000)
        $juli = $this->calculator->calculateForUser($this->guru, $this->koperasi, '2026-07');
        $this->assertSame(200000, $juli['target']);
    }

    public function test_adjustments_are_added_and_subtracted_correctly(): void
    {
        $paidTime = Carbon::parse('2026-10-10 10:00:00', 'Asia/Jakarta');
        $this->createOrder($this->guru, $this->koperasi, 100000, PaymentStatus::Paid, OrderStatus::Confirmed, $paidTime);

        // Penyesuaian positif +Rp 20.000 (misal koreksi kasir offline)
        SpendingAdjustment::create([
            'user_id' => $this->guru->id,
            'tenant_id' => $this->koperasi->id,
            'period' => '2026-10',
            'amount' => 20000,
            'reason' => 'Koreksi transaksi offline',
        ]);

        // Penyesuaian negatif -Rp 5.000 (misal retur barang fisik)
        SpendingAdjustment::create([
            'user_id' => $this->guru->id,
            'tenant_id' => $this->koperasi->id,
            'period' => '2026-10',
            'amount' => -5000,
            'reason' => 'Retur barang rusak',
        ]);

        $result = $this->calculator->calculateForUser($this->guru, $this->koperasi, '2026-10');

        $this->assertSame(100000, $result['orders_subtotal']);
        $this->assertSame(15000, $result['adjustments_total']);
        $this->assertSame(115000, $result['total_verified']);
        $this->assertSame(35000, $result['remaining']); // 150.000 - 115.000
    }

    public function test_closed_month_returns_immutable_snapshot_data(): void
    {
        // Buat closing untuk periode 2026-09
        $closing = SpendingClosing::create([
            'tenant_id' => $this->koperasi->id,
            'period' => '2026-09',
            'closed_at' => Carbon::parse('2026-10-01 01:00:00', 'Asia/Jakarta'),
        ]);

        SpendingClosingItem::create([
            'closing_id' => $closing->id,
            'user_id' => $this->guru->id,
            'position_id' => $this->posGuru->id,
            'target_amount' => 150000,
            'total_amount' => 160000,
            'is_met' => true,
        ]);

        // Walau ada order baru yang disisipkan di September setelahnya, snapshot closing tetap diprioritaskan
        $paidTime = Carbon::parse('2026-09-15 10:00:00', 'Asia/Jakarta');
        $this->createOrder($this->guru, $this->koperasi, 50000, PaymentStatus::Paid, OrderStatus::Confirmed, $paidTime);

        $result = $this->calculator->calculateForUser($this->guru, $this->koperasi, '2026-09');

        $this->assertTrue($result['is_closed']);
        $this->assertSame(160000, $result['total_verified']);
        $this->assertSame(150000, $result['target']);
        $this->assertSame(0, $result['remaining']);
        $this->assertTrue($result['is_met']);
    }

    public function test_mark_paid_sets_order_paid_at_timestamp(): void
    {
        $order = $this->createOrder($this->guru, $this->koperasi, 25000, PaymentStatus::Pending, OrderStatus::Pending, null);
        $this->assertNull($order->paid_at);

        /** @var OrderStatusService $service */
        $service = app(OrderStatusService::class);
        $service->markPaid($order);

        $order->refresh();
        $this->assertSame(PaymentStatus::Paid, $order->payment_status);
        $this->assertNotNull($order->paid_at);
    }
}
