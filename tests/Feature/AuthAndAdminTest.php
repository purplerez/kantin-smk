<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Livewire\Admin\Tenants;
use App\Livewire\Admin\Users;
use App\Models\AuditLog;
use App\Models\Tenant;
use App\Models\User;
use Livewire\Livewire;
use Tests\TestCase;

class AuthAndAdminTest extends TestCase
{
    public function test_login_redirects_each_role_to_its_home(): void
    {
        $cases = [
            ['siswa@smkgo.id', 'buyer123', route('catalog')],
            ['staff@bu-rina.id', 'staff123', route('tenant.orders')],
            ['owner@bu-rina.id', 'owner123', route('tenant.dashboard')],
            ['admin@smkgo.id', 'admin123', route('admin.dashboard')],
        ];

        foreach ($cases as [$email, $password, $home]) {
            $this->post('/login', ['email' => $email, 'password' => $password])->assertRedirect($home);
            $this->assertAuthenticated();
            $this->post('/logout');
        }
    }

    public function test_wrong_password_and_inactive_account_are_rejected(): void
    {
        $this->post('/login', ['email' => 'siswa@smkgo.id', 'password' => 'salah'])->assertSessionHasErrors('email');
        $this->assertGuest();

        User::where('email', 'siswa@smkgo.id')->update(['is_active' => false]);
        $this->post('/login', ['email' => 'siswa@smkgo.id', 'password' => 'buyer123'])->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_login_is_rate_limited_after_five_attempts(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', ['email' => 'siswa@smkgo.id', 'password' => 'salah']);
        }
        $this->post('/login', ['email' => 'siswa@smkgo.id', 'password' => 'salah'])->assertStatus(429);
    }

    public function test_role_middleware_redirects_wrong_role_to_its_home(): void
    {
        $this->actingAs($this->buyer())->get('/admin')->assertRedirect(route('catalog'));
        $this->actingAs($this->staff())->get('/tenant-panel/menu')->assertRedirect(route('tenant.orders'));
        $this->actingAs($this->staff())->get('/')->assertRedirect(route('tenant.orders'));
        $this->actingAs($this->admin())->get('/admin/pengguna')->assertOk();
        auth()->logout();
        $this->get('/pesanan')->assertRedirect('/login');
    }

    public function test_no_public_registration_route(): void
    {
        $this->get('/register')->assertNotFound();
    }

    public function test_admin_provisions_tenant_and_tenant_admin_account(): void
    {
        Livewire::actingAs($this->admin())->test(Tenants::class)
            ->call('create')->set('name', 'Warung Pak Tono')->set('description', 'Bakso & mie ayam')
            ->call('save')->assertHasNoErrors();

        $tenant = Tenant::where('slug', 'warung-pak-tono')->firstOrFail();
        $this->assertSame(Tenant::STATUS_PENDING, $tenant->status);

        Livewire::actingAs($this->admin())->test(Tenants::class)->call('setStatus', $tenant->id, 'active');
        $this->assertSame(Tenant::STATUS_ACTIVE, $tenant->refresh()->status);

        Livewire::actingAs($this->admin())->test(Users::class)
            ->call('create')
            ->set('name', 'Pak Tono')->set('email', 'tono@warung.id')->set('role', 'tenant_admin')->set('password', 'tono1234')
            ->call('save')->assertHasErrors('tenant_id')
            ->set('tenant_id', $tenant->id)
            ->call('save')->assertHasNoErrors();

        $tono = User::where('email', 'tono@warung.id')->firstOrFail();
        $this->assertSame(Role::TenantAdmin, $tono->role);
        $this->assertSame($tenant->id, $tono->tenant_id);
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('tono1234', $tono->password));

        $this->post('/logout');
        $this->post('/login', ['email' => 'tono@warung.id', 'password' => 'tono1234'])->assertRedirect(route('tenant.dashboard'));
        $this->assertTrue(AuditLog::where('action', 'user.provisioned')->exists());
    }

    public function test_admin_bulk_imports_buyers_and_skips_duplicates(): void
    {
        Livewire::actingAs($this->admin())->test(Users::class)
            ->set('importText', "Raka Wijaya;raka@smkgo.id;2024002;raka123\nSinta Dewi;sinta@smkgo.id;2024003\nNadia Lagi;siswa@smkgo.id;x\nbaris rusak")
            ->call('import')
            ->assertSet('importResult.created', 2);

        $this->assertTrue(User::where('email', 'raka@smkgo.id')->where('role', Role::User)->exists());
        $this->assertSame('2024003', User::where('email', 'sinta@smkgo.id')->value('identifier'));
        $this->assertSame(1, User::where('email', 'siswa@smkgo.id')->count());
    }

    public function test_admin_cannot_deactivate_self_but_can_deactivate_others(): void
    {
        $admin = $this->admin();
        Livewire::actingAs($admin)->test(Users::class)->call('toggleActive', $admin->id)->assertStatus(422);

        $buyer = $this->buyer();
        Livewire::actingAs($admin)->test(Users::class)->call('toggleActive', $buyer->id);
        $this->assertFalse($buyer->refresh()->is_active);
    }

    public function test_suspended_tenant_staff_is_blocked_from_panel(): void
    {
        $this->tenantRina()->update(['status' => Tenant::STATUS_SUSPENDED]);
        $this->actingAs($this->staff())->get('/tenant-panel/pesanan')->assertForbidden();
    }
}
