<div>
    <x-page-header title="Platform Control" :eyebrow="now()->translatedFormat('l, d F Y')" />

    <div class="grid grid-cols-2 gap-3 md:grid-cols-3 xl:grid-cols-6">
        @foreach ([
            ['Pesanan hari ini', $ordersToday, 'orders-today'],
            ['Pendapatan lunas', 'Rp'.number_format($revenueToday, 0, ',', '.'), 'revenue-today'],
            ['Invoice belum bayar', $pendingInvoices, 'pending-invoices'],
            ['Tenant aktif', $activeTenants, 'active-tenants'],
            ['Tenant menunggu', $pendingTenants, 'pending-tenants'],
            ['Pengguna aktif', $totalUsers, 'active-users'],
        ] as [$label, $value, $id])
            <div class="rounded-2xl border border-slate-200 bg-white p-4" data-testid="metric-{{ $id }}">
                <p class="text-xs text-slate-500">{{ $label }}</p>
                <strong class="mt-1 block text-2xl font-extrabold tracking-tight">{{ $value }}</strong>
            </div>
        @endforeach
    </div>

    @if ($pendingTenants)
        <a href="{{ route('admin.tenants') }}" wire:navigate class="mt-4 flex items-center justify-between rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-semibold text-amber-800" data-testid="pending-tenants-alert">{{ $pendingTenants }} tenant menunggu persetujuan <span>→</span></a>
    @endif

    <div class="mt-6 grid gap-6 lg:grid-cols-2">
        <section class="rounded-2xl border border-slate-200 bg-white">
            <header class="border-b border-slate-100 px-4 py-3"><h2 class="text-base font-extrabold">Performa tenant hari ini</h2></header>
            <table class="w-full text-sm">
                <thead class="text-left text-[11px] font-bold uppercase tracking-wider text-slate-400"><tr><th class="px-4 py-2">Tenant</th><th class="px-4 py-2">Order</th><th class="px-4 py-2 text-right">Lunas</th></tr></thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($perTenant as $row)
                        <tr data-testid="tenant-perf-row"><td class="px-4 py-2.5 font-semibold">{{ $row->name }}</td><td class="px-4 py-2.5">{{ $row->orders_count }}</td><td class="px-4 py-2.5 text-right font-semibold text-brand-dark">@rupiah($row->paid_amount)</td></tr>
                    @empty
                        <tr><td colspan="3" class="px-4 py-8 text-center text-slate-500">Belum ada transaksi hari ini.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </section>

        <section class="rounded-2xl border border-slate-200 bg-white">
            <header class="flex items-center justify-between border-b border-slate-100 px-4 py-3">
                <h2 class="text-base font-extrabold">Transaksi terbaru</h2>
                <a href="{{ route('admin.transactions') }}" wire:navigate class="text-xs font-bold text-brand-dark">Lihat semua →</a>
            </header>
            <ul class="divide-y divide-slate-100 text-sm">
                @foreach ($recent as $order)
                    <li class="flex items-center gap-3 px-4 py-2.5" data-testid="recent-order-{{ $order->id }}">
                        <div class="min-w-0 flex-1"><strong class="block truncate">{{ $order->tenant->name }}</strong><span class="text-xs text-slate-500">{{ $order->buyer->name }} · {{ $order->code }}</span></div>
                        <strong>@rupiah($order->subtotal)</strong>
                        <x-status-badge :status="$order->status" />
                    </li>
                @endforeach
            </ul>
        </section>
    </div>
</div>
