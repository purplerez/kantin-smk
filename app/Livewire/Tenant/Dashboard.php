<?php

namespace App\Livewire\Tenant;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\Product;
use App\Models\Tenant;
use App\Services\AuditLogger;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.panel')]
#[Title('Dashboard Tenant')]
class Dashboard extends Component
{
    public function toggleOpen(): void
    {
        $tenant = auth()->user()->tenant;
        $tenant->update(['is_open' => ! $tenant->is_open]);
        AuditLogger::log('tenant.toggle_open', $tenant, ['is_open' => $tenant->is_open]);
        $this->dispatch('toast', message: $tenant->is_open ? 'Tenant sekarang BUKA' : 'Tenant sekarang TUTUP');
    }

    public function render()
    {
        /** @var Tenant $tenant */
        $tenant = auth()->user()->tenant()->first();
        $today = Order::whereDate('created_at', today());

        return view('livewire.tenant.dashboard', [
            'tenant' => $tenant,
            'ordersToday' => (clone $today)->count(),
            'revenueToday' => (int) (clone $today)->where('payment_status', PaymentStatus::Paid)->where('status', '!=', OrderStatus::Cancelled)->sum('subtotal'),
            'pendingPayments' => Order::where('payment_status', PaymentStatus::Pending)->whereNotIn('status', [OrderStatus::Pending, OrderStatus::Cancelled])->count(),
            'activeOrders' => Order::whereIn('status', [OrderStatus::Confirmed, OrderStatus::Preparing, OrderStatus::Ready])->count(),
            'rating' => $tenant->averageRating(),
            'unavailable' => Product::where('is_available_today', false)->count(),
            'recent' => Order::with('buyer', 'items')->latest()->take(6)->get(),
            'topProducts' => Product::query()
                ->withSum('orderItemsCompleted as sold', 'qty')
                ->orderByDesc('sold')->take(5)->get(),
        ]);
    }
}
