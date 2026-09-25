<div>
    <x-page-header :title="'Selamat datang, '.explode(' ', auth()->user()->name)[0]" :eyebrow="$tenant->name">
        <x-slot:actions>
            <button wire:click="toggleOpen" class="flex items-center gap-2 rounded-full px-4 py-2 text-xs font-bold ring-1 transition {{ $tenant->is_open ? 'bg-brand-light text-brand-dark ring-brand/30' : 'bg-rose-50 text-rose-700 ring-rose-200' }}" data-testid="toggle-open-button">
                <span class="h-2 w-2 rounded-full {{ $tenant->is_open ? 'bg-brand' : 'bg-rose-500' }}"></span>
                {{ $tenant->is_open ? 'Tenant Buka' : 'Tenant Tutup' }}
            </button>
        </x-slot:actions>
    </x-page-header>

    <div class="grid grid-cols-2 gap-3 md:grid-cols-4">
        @foreach ([
            ['Pesanan hari ini', $ordersToday, 'orders-today'],
            ['Pendapatan (lunas)', 'Rp'.number_format($revenueToday, 0, ',', '.'), 'revenue-today'],
            ['Pesanan aktif', $activeOrders, 'active-orders'],
            ['Menunggu bayar tunai', $pendingPayments, 'pending-payments'],
        ] as [$label, $value, $id])
            <div class="rounded-2xl border border-slate-200 bg-white p-4" data-testid="metric-{{ $id }}">
                <p class="text-xs text-slate-500">{{ $label }}</p>
                <strong class="mt-1 block text-2xl font-extrabold tracking-tight">{{ $value }}</strong>
            </div>
        @endforeach
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-[1fr_360px]">
        <section class="rounded-2xl border border-slate-200 bg-white">
            <header class="flex items-center justify-between border-b border-slate-100 px-4 py-3">
                <h2 class="text-base font-extrabold">Pesanan terbaru</h2>
                <a href="{{ route('tenant.orders') }}" wire:navigate class="text-xs font-bold text-brand-dark" data-testid="see-all-orders-link">Lihat semua →</a>
            </header>
            <ul class="divide-y divide-slate-100">
                @forelse ($recent as $order)
                    <li class="flex items-center gap-3 px-4 py-3 text-sm" data-testid="recent-order-{{ $order->id }}">
                        <div class="min-w-0 flex-1">
                            <strong class="block truncate">{{ $order->buyer->name }} <span class="font-mono text-xs text-slate-400">{{ $order->code }}</span></strong>
                            <span class="block truncate text-xs text-slate-500">{{ $order->items->map(fn ($i) => "{$i->qty}× {$i->product_name}")->implode(', ') }}</span>
                        </div>
                        <strong class="text-sm">@rupiah($order->subtotal)</strong>
                        <x-status-badge :status="$order->status" />
                    </li>
                @empty
                    <li class="px-4 py-10 text-center text-sm text-slate-500">Belum ada pesanan.</li>
                @endforelse
            </ul>
        </section>

        <div class="space-y-4">
            <section class="rounded-2xl bg-slate-900 p-5 text-white">
                <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-emerald-300">Rating tenant</p>
                <div class="mt-2 flex items-end gap-2">
                    <strong class="text-4xl font-extrabold" data-testid="tenant-rating">{{ $rating ?? '–' }}</strong>
                    <span class="mb-1 text-sm text-slate-300">/ 5 dari {{ $tenant->reviews()->count() }} ulasan</span>
                </div>
                @if ($unavailable) <p class="mt-4 rounded-xl bg-white/10 px-3 py-2 text-xs">{{ $unavailable }} menu ditandai habis hari ini. <a href="{{ route('tenant.products') }}" wire:navigate class="font-bold underline">Kelola</a></p> @endif
            </section>

            <section class="rounded-2xl border border-slate-200 bg-white p-4">
                <h2 class="text-sm font-extrabold">Menu terlaris</h2>
                <ol class="mt-3 space-y-2 text-sm">
                    @foreach ($topProducts as $i => $p)
                        <li class="flex items-center gap-3"><span class="grid h-6 w-6 place-items-center rounded-full bg-brand-light text-[11px] font-bold text-brand-dark">{{ $i + 1 }}</span><span class="flex-1 truncate">{{ $p->name }}</span><span class="text-xs font-semibold text-slate-500">{{ (int) $p->sold }} terjual</span></li>
                    @endforeach
                </ol>
            </section>
        </div>
    </div>
</div>
