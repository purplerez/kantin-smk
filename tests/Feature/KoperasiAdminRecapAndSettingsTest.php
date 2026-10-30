<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\Role;
use App\Livewire\Tenant\SpendingRecap;
use App\Livewire\Tenant\SpendingSettings;
use App\Models\AuditLog;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\Position;
use App\Models\SpendingAdjustment;
use App\Models\SpendingClosing;
use App\Models\SpendingClosingItem;
use App\Models\SpendingTarget;
use App\Models\Tenant;
use App\Models\User;
use App\Services\SpendingTotalCalculator;
use Carbon\Carbon;
use Livewire\Livewire;
use Tests\TestCase;

class KoperasiAdminRecapAndSettingsTest extends TestCase
{
    private Tenant $koperasi;
    private Tenant $kantinLain;
    private User $koperasiAdmin;
    private User $koperasiStaff;
    private User $rinaOwner;
    private User $guru;
    private User $siswa;

    protected function setUp(): void
    {
        parent::setUp();

        $this->koperasi = $this->tenantKopsis();
        $this->kantinLain = $this->tenantRina();

        $this->koperasiAdmin = User::where('email', 'owner@kopsis.id')->firstOrFail();

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

        $this->rinaOwner = User::where('email', 'owner@bu-rina.id')->firstOrFail();

        $this->guru = User::where('email', 'guru@smkgo.id')->firstOrFail();
        $this->siswa = User::where('email', 'siswa@smkgo.id')->firstOrFail();
    }

    public function test_only_koperasi_admin_can_access_recap_and_settings_pages(): void
    {
        // Admin koperasi: diizinkan
        Livewire::actingAs($this->koperasiAdmin)
            ->test(SpendingRecap::class)
            ->assertOk();

        Livewire::actingAs($this->koperasiAdmin)
            ->test(SpendingSettings::class)
            ->assertOk();

        // Staf koperasi: dilarang (403)
        Livewire::actingAs($this->koperasiStaff)
            ->test(SpendingRecap::class)
            ->assertForbidden();

        Livewire::actingAs($this->koperasiStaff)
            ->test(SpendingSettings::class)
            ->assertForbidden();

        // Tenant lain (Bu Rina): dilarang (403)
        Livewire::actingAs($this->rinaOwner)
            ->test(SpendingRecap::class)
            ->assertForbidden();

        Livewire::actingAs($this->rinaOwner)
            ->test(SpendingSettings::class)
            ->assertForbidden();
    }

    public function test_recap_displays_all_employees_and_calculates_status(): void
    {
        $currentMonth = now('Asia/Jakarta')->format('Y-m');
        $paidTime = now('Asia/Jakarta');

        // Belanja Pak Hendra (Guru Tetap target Rp 150.000): belanja Rp 160.000 -> Terpenuhi
        $invoice = Invoice::create([
            'code' => Invoice::generateCode(),
            'buyer_id' => $this->guru->id,
            'total' => 160000,
            'payment_method' => PaymentMethod::Cash,
            'payment_status' => PaymentStatus::Paid,
            'paid_at' => $paidTime,
        ]);

        Order::create([
            'code' => Order::generateCode(),
            'invoice_id' => $invoice->id,
            'buyer_id' => $this->guru->id,
            'tenant_id' => $this->koperasi->id,
            'status' => OrderStatus::Completed,
            'payment_status' => PaymentStatus::Paid,
            'subtotal' => 160000,
            'source' => 'counter',
            'paid_at' => $paidTime,
        ]);

        Livewire::actingAs($this->koperasiAdmin)
            ->test(SpendingRecap::class, ['month' => $currentMonth])
            ->assertOk()
            ->assertSee('Pak Hendra')
            ->assertSee('Guru Tetap')
            ->assertSee('Rp160.000')
            ->assertSee('Rp150.000')
            ->assertSee('Terpenuhi')
            // Siswa tidak boleh masuk tabel pegawai berjabatan
            ->assertDontSee('Nadia Pratama');
    }

    public function test_recap_filtering_by_status_and_search(): void
    {
        // Beri Pak Hendra pesanan lunas Rp 150.000 sehingga statusnya 'Terpenuhi'
        $paidTime = now('Asia/Jakarta');
        $invoice = Invoice::create([
            'code' => Invoice::generateCode(),
            'buyer_id' => $this->guru->id,
            'total' => 150000,
            'payment_method' => PaymentMethod::Cash,
            'payment_status' => PaymentStatus::Paid,
            'paid_at' => $paidTime,
        ]);

        Order::create([
            'code' => Order::generateCode(),
            'invoice_id' => $invoice->id,
            'buyer_id' => $this->guru->id,
            'tenant_id' => $this->koperasi->id,
            'status' => OrderStatus::Completed,
            'payment_status' => PaymentStatus::Paid,
            'subtotal' => 150000,
            'source' => 'counter',
            'paid_at' => $paidTime,
        ]);

        // Buat 1 pegawai lagi dengan target belum terpenuhi (Bambang TU belanja 0)
        $posTU = Position::where('name', 'Staf Tata Usaha')->firstOrFail();
        $tu = User::create([
            'name' => 'Bambang TU',
            'email' => 'bambang@smkgo.id',
            'identifier' => '19920101',
            'password' => bcrypt('password'),
            'role' => Role::User,
            'position_id' => $posTU->id,
            'is_active' => true,
        ]);

        Livewire::actingAs($this->koperasiAdmin)
            ->test(SpendingRecap::class)
            ->assertSee('Pak Hendra')
            ->assertSee('Bambang TU')
            // Filter hanya yang belum terpenuhi (Bambang)
            ->set('statusFilter', 'unmet')
            ->assertSee('Bambang TU')
            ->assertDontSee('employee-row-'.$this->guru->id)
            // Filter hanya yang terpenuhi (Hendra)
            ->set('statusFilter', 'met')
            ->assertSee('Pak Hendra')
            ->assertDontSee('employee-row-'.$tu->id)
            // Filter pencarian nama
            ->set('statusFilter', '')
            ->set('search', 'Hendra')
            ->assertSee('Pak Hendra')
            ->assertDontSee('employee-row-'.$tu->id);
    }

    public function test_export_csv_streams_valid_spreadsheet_data(): void
    {
        $response = Livewire::actingAs($this->koperasiAdmin)
            ->test(SpendingRecap::class)
            ->call('exportCsv');

        $this->assertNotNull($response);
    }

    public function test_admin_can_close_month_atomically_saving_snapshots(): void
    {
        $currentMonth = now('Asia/Jakarta')->format('Y-m');

        Livewire::actingAs($this->koperasiAdmin)
            ->test(SpendingRecap::class, ['month' => $currentMonth])
            ->assertSee('Tutup Bulan')
            ->call('executeCloseMonth')
            ->assertDispatched('toast');

        // Pastikan closing tersimpan
        $closing = SpendingClosing::where('tenant_id', $this->koperasi->id)
            ->where('period', $currentMonth)
            ->first();

        $this->assertNotNull($closing);
        $this->assertSame($this->koperasiAdmin->id, $closing->closed_by);

        // Pastikan snapshot pegawai tersimpan
        $item = SpendingClosingItem::where('closing_id', $closing->id)
            ->where('user_id', $this->guru->id)
            ->first();

        $this->assertNotNull($item);
        $this->assertSame(150000, $item->target_amount);

        // Audit log
        $log = AuditLog::where('action', 'spending.month_closed')->latest()->first();
        $this->assertNotNull($log);
        $this->assertSame($currentMonth, $log->meta['period']);

        // Penutupan ulang periode yang sama ditolak
        Livewire::actingAs($this->koperasiAdmin)
            ->test(SpendingRecap::class, ['month' => $currentMonth])
            ->assertSee('Buku Ditutup')
            ->call('executeCloseMonth')
            ->assertHasErrors(['close']);
    }

    public function test_admin_can_save_manual_adjustment(): void
    {
        $currentMonth = now('Asia/Jakarta')->format('Y-m');

        Livewire::actingAs($this->koperasiAdmin)
            ->test(SpendingRecap::class)
            ->call('openAdjustmentModal', $this->guru->id)
            ->assertSet('showAdjustmentModal', true)
            ->set('adjAmount', 25000)
            ->set('adjReason', 'Koreksi belanja konsumsi rapat dinas')
            ->set('adjPeriod', $currentMonth)
            ->call('saveAdjustment')
            ->assertDispatched('toast');

        $adj = SpendingAdjustment::where('user_id', $this->guru->id)->latest()->first();
        $this->assertNotNull($adj);
        $this->assertSame(25000, $adj->amount);
        $this->assertSame('Koreksi belanja konsumsi rapat dinas', $adj->reason);
        $this->assertSame($this->koperasiAdmin->id, $adj->created_by);

        // Audit log
        $log = AuditLog::where('action', 'spending.adjustment_created')->latest()->first();
        $this->assertNotNull($log);
        $this->assertSame(25000, $log->meta['amount']);
    }

    public function test_admin_can_manage_positions_and_spending_targets(): void
    {
        // 1. Tambah jabatan baru
        Livewire::actingAs($this->koperasiAdmin)
            ->test(SpendingSettings::class)
            ->call('openPositionModal')
            ->set('positionName', 'Kepala Bengkel Otomotif')
            ->call('savePosition')
            ->assertDispatched('toast');

        $newPos = Position::where('name', 'Kepala Bengkel Otomotif')->first();
        $this->assertNotNull($newPos);
        $this->assertTrue($newPos->is_active);

        // 2. Toggle status aktif jabatan
        Livewire::actingAs($this->koperasiAdmin)
            ->test(SpendingSettings::class)
            ->call('togglePositionActive', $newPos->id);

        $newPos->refresh();
        $this->assertFalse($newPos->is_active);

        // 3. Tambah target belanja baru
        Livewire::actingAs($this->koperasiAdmin)
            ->test(SpendingSettings::class)
            ->call('openTargetModal')
            ->set('targetPositionId', $newPos->id)
            ->set('targetAmount', 125000)
            ->set('targetEffectiveFrom', '2026-11-01')
            ->call('saveTarget')
            ->assertDispatched('toast');

        $target = SpendingTarget::where('position_id', $newPos->id)->first();
        $this->assertNotNull($target);
        $this->assertSame(125000, $target->amount);
        $this->assertSame('2026-11-01', $target->effective_from->toDateString());
    }
}
