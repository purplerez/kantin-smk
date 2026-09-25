<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\HomeController;
use App\Livewire\Admin\AuditLogs as AdminAuditLogs;
use App\Livewire\Admin\Dashboard as AdminDashboard;
use App\Livewire\Admin\Tenants as AdminTenants;
use App\Livewire\Admin\Transactions as AdminTransactions;
use App\Livewire\Admin\Users as AdminUsers;
use App\Livewire\Buyer\Account;
use App\Livewire\Buyer\Cart;
use App\Livewire\Buyer\Catalog;
use App\Livewire\Buyer\Checkout;
use App\Livewire\Buyer\InvoiceShow;
use App\Livewire\Buyer\OrderDetail;
use App\Livewire\Buyer\Orders;
use App\Livewire\Buyer\TenantMenu;
use App\Livewire\Tenant\Dashboard as TenantDashboard;
use App\Livewire\Tenant\OrderBoard;
use App\Livewire\Tenant\Payments as TenantPayments;
use App\Livewire\Tenant\Products as TenantProducts;
use Illuminate\Support\Facades\Route;

// ---------- Auth (tanpa registrasi publik) ----------
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'show'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->middleware('throttle:5,1')->name('login.store');
});
Route::post('/logout', [LoginController::class, 'destroy'])->middleware('auth')->name('logout');

// ---------- Pembeli (siswa / guru / staf sekolah) ----------
Route::middleware(['auth', 'role:user'])->group(function () {
    Route::get('/', Catalog::class)->name('catalog');
    Route::get('/tenant/{tenant:slug}', TenantMenu::class)->name('tenant.menu');
    Route::get('/keranjang', Cart::class)->name('cart');
    Route::get('/checkout', Checkout::class)->name('checkout');
    Route::get('/invoice/{invoice}', InvoiceShow::class)->name('invoice.show');
    Route::get('/pesanan', Orders::class)->name('orders');
    Route::get('/pesanan/{order}', OrderDetail::class)->name('orders.show');
    Route::get('/akun', Account::class)->name('account');
});

// ---------- Tenant (admin & staf) ----------
Route::prefix('tenant-panel')->name('tenant.')->middleware(['auth', 'role:tenant_admin,tenant_staf'])->group(function () {
    Route::get('/pesanan', OrderBoard::class)->name('orders');

    Route::middleware('role:tenant_admin')->group(function () {
        Route::get('/', TenantDashboard::class)->name('dashboard');
        Route::get('/menu', TenantProducts::class)->name('products');
        Route::get('/pembayaran', TenantPayments::class)->name('payments');
    });
});

// ---------- Platform admin ----------
Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/', AdminDashboard::class)->name('dashboard');
    Route::get('/transaksi', AdminTransactions::class)->name('transactions');
    Route::get('/tenant', AdminTenants::class)->name('tenants');
    Route::get('/pengguna', AdminUsers::class)->name('users');
    Route::get('/audit-log', AdminAuditLogs::class)->name('audit');
});

// Redirect sesuai role setelah login / saat akses root oleh non-pembeli
Route::get('/beranda', HomeController::class)->middleware('auth')->name('home');
