<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\Role;
use App\Livewire\Tenant\Cashier;
use App\Models\AuditLog;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Position;
use App\Models\SpendingClosing;
use App\Models\SpendingTarget;
use App\Models\Tenant;
use App\Models\User;
use App\Services\OrderStatusService;
use App\Services\SpendingTotalCalculator;
use Carbon\Carbon;
use Livewire\Livewire;
use Tests\TestCase;

class KoperasiCashierTest extends TestCase
{
    private Tenant $koperasi;
    private Tenant $kantinLain;
    private User $koperasiAdmin;
    private User $koperasiStaff;
    private User $rinaStaff;
    private User $guru;
    private User $siswa;

    protected function setUp(): void
    {
        parent::setUp();

        $this->koperasi = $this->tenantKopsis();
        $this->kantinLain = $this->tenantRina();

        $this->koperasiAdmin = User::where('email', 'owner@kopsis.id')->firstOrFail();

        // Staf koperasi
        $this->koperasiStaff = User::firstOrCreate(
            ['email' => 'staff@kopsis.id'],
            [
                'name' => 'Siti Kasir Koperasi',
                'password' => bcrypt('password'),
                'role' => Role::TenantStaf,
                'tenant_id' => $this->koperasi->id,
                'is_active' => true,
            ],
        );

        // Staf tenant lain (bukan koperasi)
        $this->rinaStaff = User::where('email', 'staff@bu-rina.id')->firstOrFail();

        $this->guru = User::where('email', 'guru@smkgo.id')->firstOrFail();
        $this->siswa = User::where('email', 'siswa@smkgo.id')->firstOrFail();
    }

    public function test_koperasi_staff_and_admin_can_access_cashier_page(): void
    {
        Livewire::actingAs($this->koperasiStaff)
            ->test(Cashier::class)
            ->assertOk()
            ->assertSee('Kasir Cepat Koperasi');

        Livewire::actingAs($this->koperasiAdmin)
            ->test(Cashier::class)
            ->assertOk();
    }

    public function test_non_koperasi_tenant_is_forbidden_from_cashier(): void
    {
        Livewire::actingAs($this->rinaStaff)
            ->test(Cashier::class)
            ->assertForbidden();
    }

    public function test_cashier_can_search_and_select_employee_or_guest(): void
    {
        Livewire::actingAs($this->koperasiStaff)
            ->test(Cashier::class)
            ->set('search', 'Hendra')
            ->assertSee('Pak Hendra')
            ->call('selectUser', $this->guru->id)
            ->assertSet('selectedUserId', $this->guru->id)
            ->assertSee('Guru Tetap')
            ->call('clearSelectedUser')
            ->assertSet('selectedUserId', null)
            ->call('selectGuest')
            ->assertSet('selectedUserId', fn ($id) => $id !== null)
            ->assertSee('Pelanggan Umum');
    }

    public function test_open_app_order_is_detected_and_can_be_marked_paid_without_creating_new_order(): void
    {
        // Simulasikan pesanan app yang dibuat guru di koperasi dan belum bayar
        $invoice = Invoice::create([
            'code' => Invoice::generateCode(),
            'buyer_id' => $this->guru->id,
            'total' => 20000,
            'payment_method' => PaymentMethod::Cash,
            'payment_status' => PaymentStatus::Pending,
        ]);

        $appOrder = Order::create([
            'code' => Order::generateCode(),
            'invoice_id' => $invoice->id,
            'buyer_id' => $this->guru->id,
            'tenant_id' => $this->koperasi->id,
            'status' => OrderStatus::Pending,
            'payment_status' => PaymentStatus::Pending,
            'subtotal' => 20000,
            'source' => 'app',
        ]);

        $initialOrdersCount = Order::count();

        Livewire::actingAs($this->koperasiStaff)
            ->test(Cashier::class)
            ->call('selectUser', $this->guru->id)
            ->assertSee('Pesanan Aplikasi Terbuka Terdeteksi!')
            ->assertSee($appOrder->code)
            ->assertSee('Rp20.000')
            ->call('verifyAppOrder', $appOrder->id, app(OrderStatusService::class))
            ->assertDispatched('toast');

        // Pastikan order app menjadi lunas
        $appOrder->refresh();
        $this->assertSame(PaymentStatus::Paid, $appOrder->payment_status);
        $this->assertSame(OrderStatus::Confirmed, $appOrder->status);
        $this->assertNotNull($appOrder->paid_at);

        // Pastikan tidak ada order baru yang dibuat (mencegah hitungan ganda)
        $this->assertSame($initialOrdersCount, Order::count());
    }

    public function test_cashier_can_record_new_counter_entry_with_audit_log_and_invoice(): void
    {
        Livewire::actingAs($this->koperasiStaff)
            ->test(Cashier::class)
            ->call('selectUser', $this->guru->id)
            ->set('amount', 35000)
            ->set('paymentMethod', 'cash')
            ->set('note', 'Beli seragam & badge')
            ->call('save', app(\App\Services\NotificationService::class))
            ->assertHasNoErrors()
            ->assertDispatched('toast');

        // Cek order kasir yang tersimpan
        $order = Order::where('source', 'counter')->latest()->firstOrFail();
        $this->assertSame(35000, $order->subtotal);
        $this->assertSame($this->guru->id, $order->buyer_id);
        $this->assertSame($this->koperasi->id, $order->tenant_id);
        $this->assertSame($this->koperasiStaff->id, $order->entered_by);
        $this->assertSame(PaymentStatus::Paid, $order->payment_status);
        $this->assertSame(OrderStatus::Completed, $order->status);
        $this->assertNotNull($order->paid_at);
        $this->assertSame('Beli seragam & badge', $order->note);

        // Cek invoice dan order item
        $this->assertNotNull($order->invoice);
        $this->assertSame(35000, $order->invoice->total);
        $this->assertSame(PaymentStatus::Paid, $order->invoice->payment_status);
        $this->assertSame(1, $order->items()->count());
        $this->assertSame('Belanja Kasir Koperasi', $order->items->first()->product_name);

        // Cek audit log
        $log = AuditLog::where('action', 'order.counter_entry')->latest()->first();
        $this->assertNotNull($log);
        $this->assertSame($this->koperasiStaff->id, $log->actor_id);
        $this->assertSame(35000, $log->meta['amount']);

        // Verifikasi kalkulasi belanja bulanan pegawai langsung terupdate
        $calc = app(SpendingTotalCalculator::class)->calculateForUser($this->guru, $this->koperasi, now('Asia/Jakarta')->format('Y-m'));
        $this->assertSame(35000, $calc['total_verified']);
    }

    public function test_duplicate_entry_within_two_minutes_triggers_warning_and_requires_confirmation(): void
    {
        // Entri pertama
        Livewire::actingAs($this->koperasiStaff)
            ->test(Cashier::class)
            ->call('selectUser', $this->guru->id)
            ->set('amount', 50000)
            ->call('save', app(\App\Services\NotificationService::class));

        $this->assertSame(1, Order::where('source', 'counter')->count());

        // Entri kedua dengan pegawai & nominal sama langsung dalam 2 menit
        $test = Livewire::actingAs($this->koperasiStaff)
            ->test(Cashier::class)
            ->call('selectUser', $this->guru->id)
            ->set('amount', 50000)
            ->call('save', app(\App\Services\NotificationService::class))
            ->assertSee('Peringatan: Ditemukan transaksi kasir dengan nominal sama')
            ->assertSet('confirmDuplicate', true);

        // Order belum bertambah
        $this->assertSame(1, Order::where('source', 'counter')->count());

        // Kasir mengonfirmasi
        $test->call('save', app(\App\Services\NotificationService::class));

        // Sekarang order bertambah menjadi 2
        $this->assertSame(2, Order::where('source', 'counter')->count());
    }

    public function test_amount_exceeding_max_transaction_amount_is_rejected(): void
    {
        config(['koperasi.max_transaction_amount' => 100000]);

        Livewire::actingAs($this->koperasiStaff)
            ->test(Cashier::class)
            ->call('selectUser', $this->guru->id)
            ->set('amount', 150000)
            ->call('save', app(\App\Services\NotificationService::class))
            ->assertHasErrors(['amount']);
    }

    public function test_new_counter_entry_is_rejected_if_month_is_closed(): void
    {
        $currentPeriod = now('Asia/Jakarta')->format('Y-m');

        SpendingClosing::create([
            'tenant_id' => $this->koperasi->id,
            'period' => $currentPeriod,
            'closed_at' => now(),
        ]);

        Livewire::actingAs($this->koperasiStaff)
            ->test(Cashier::class)
            ->call('selectUser', $this->guru->id)
            ->set('amount', 25000)
            ->call('save', app(\App\Services\NotificationService::class))
            ->assertHasErrors(['amount']);
    }

    public function test_cashier_can_void_counter_entry_with_required_reason(): void
    {
        // Buat order kasir yang sudah ada
        $invoice = Invoice::create([
            'code' => Invoice::generateCode(),
            'buyer_id' => $this->guru->id,
            'total' => 40000,
            'payment_method' => PaymentMethod::Cash,
            'payment_status' => PaymentStatus::Paid,
            'paid_at' => now(),
        ]);

        $order = Order::create([
            'code' => Order::generateCode(),
            'invoice_id' => $invoice->id,
            'buyer_id' => $this->guru->id,
            'tenant_id' => $this->koperasi->id,
            'status' => OrderStatus::Completed,
            'payment_status' => PaymentStatus::Paid,
            'subtotal' => 40000,
            'source' => 'counter',
            'entered_by' => $this->koperasiStaff->id,
            'paid_at' => now(),
        ]);

        Livewire::actingAs($this->koperasiStaff)
            ->test(Cashier::class)
            ->call('openVoidModal', $order->id)
            ->assertSet('showVoidModal', true)
            ->set('voidReason', 'Salah ketik nominal harusnya 4.000')
            ->call('confirmVoid')
            ->assertDispatched('toast');

        $order->refresh();
        $this->assertSame(OrderStatus::Cancelled, $order->status);
        $this->assertSame(PaymentStatus::Cancelled, $order->payment_status);
        $this->assertSame('Salah ketik nominal harusnya 4.000', $order->cancel_reason);

        // Pastikan invoice juga tercancel
        $this->assertSame(PaymentStatus::Cancelled, $order->invoice->payment_status);

        // Cek audit log void
        $log = AuditLog::where('action', 'order.counter_void')->latest()->first();
        $this->assertNotNull($log);
        $this->assertSame('Salah ketik nominal harusnya 4.000', $log->meta['reason']);

        // Pastikan total belanja bulanan pegawai berkurang kembali ke 0
        $calc = app(SpendingTotalCalculator::class)->calculateForUser($this->guru, $this->koperasi, now('Asia/Jakarta')->format('Y-m'));
        $this->assertSame(0, $calc['total_verified']);
    }

    public function test_void_fails_if_month_is_already_closed(): void
    {
        $pastPeriod = '2026-03';
        $paidTime = Carbon::parse('2026-03-15 10:00:00', 'Asia/Jakarta');

        $closing = SpendingClosing::create([
            'tenant_id' => $this->koperasi->id,
            'period' => $pastPeriod,
            'closed_at' => Carbon::parse('2026-04-01 02:00:00', 'Asia/Jakarta'),
        ]);

        $invoice = Invoice::create([
            'code' => Invoice::generateCode(),
            'buyer_id' => $this->guru->id,
            'total' => 30000,
            'payment_method' => PaymentMethod::Cash,
            'payment_status' => PaymentStatus::Paid,
            'paid_at' => $paidTime,
        ]);

        $order = Order::create([
            'code' => Order::generateCode(),
            'invoice_id' => $invoice->id,
            'buyer_id' => $this->guru->id,
            'tenant_id' => $this->koperasi->id,
            'status' => OrderStatus::Completed,
            'payment_status' => PaymentStatus::Paid,
            'subtotal' => 30000,
            'source' => 'counter',
            'entered_by' => $this->koperasiStaff->id,
            'paid_at' => $paidTime,
        ]);

        Livewire::actingAs($this->koperasiStaff)
            ->test(Cashier::class)
            ->call('openVoidModal', $order->id)
            ->set('voidReason', 'Koreksi terlambat')
            ->call('confirmVoid')
            ->assertHasErrors(['voidReason']);

        // Order tidak boleh berubah
        $order->refresh();
        $this->assertSame(OrderStatus::Completed, $order->status);
    }
}

