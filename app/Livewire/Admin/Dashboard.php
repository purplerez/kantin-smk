<?php

namespace App\Livewire\Admin;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\Tenant;
use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.panel')]
#[Title('Platform Dashboard')]
class Dashboard extends Component
{
    public function render()
    {
        $today = Order::whereDate('created_at', today());

        $perTenant = Order::query()
            ->join('tenants', 'tenants.id', '=', 'orders.tenant_id')
            ->whereDate('orders.created_at', today())
            ->where('orders.status', '!=', OrderStatus::Cancelled)
            ->selectRaw('tenants.name, COUNT(*) as orders_count, SUM(CASE WHEN orders.payment_status = ? THEN orders.subtotal ELSE 0 END) as paid_amount', [PaymentStatus::Paid->value])
            ->groupBy('tenants.id', 'tenants.name')
            ->orderByDesc('paid_amount')
            ->get();

        return view('livewire.admin.dashboard', [
            'ordersToday' => (clone $today)->count(),
            'revenueToday' => (int) (clone $today)->where('payment_status', PaymentStatus::Paid)->where('status', '!=', OrderStatus::Cancelled)->sum('subtotal'),
            'pendingInvoices' => Invoice::where('payment_status', PaymentStatus::Pending)->count(),
            'activeTenants' => Tenant::where('status', Tenant::STATUS_ACTIVE)->count(),
            'pendingTenants' => Tenant::where('status', Tenant::STATUS_PENDING)->count(),
            'totalUsers' => User::where('is_active', true)->count(),
            'perTenant' => $perTenant,
            'recent' => Order::with('buyer', 'tenant')->latest()->take(8)->get(),
        ]);
    }
}
