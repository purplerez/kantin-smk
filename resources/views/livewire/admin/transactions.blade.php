<div>
    <x-page-header title="Monitor transaksi" eyebrow="Seluruh tenant" />

    <div class="mb-4 grid gap-2 md:grid-cols-4">
        <label class="flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2">
            <x-icon name="search" class="h-4 w-4 text-slate-400" />
            <input type="search" wire:model.live.debounce.300ms="search" placeholder="Kode order / invoice / pembeli" class="w-full bg-transparent text-sm outline-none" data-testid="tx-search-input">
        </label>
        <select wire:model.live="status" class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm" data-testid="tx-status-filter">
            <option value="">Semua status</option>
            @foreach ($statuses as $s) <option value="{{ $s->value }}">{{ $s->label() }}</option> @endforeach
        </select>
        <select wire:model.live="tenant" class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm" data-testid="tx-tenant-filter">
            <option value="">Semua tenant</option>
            @foreach ($tenants as $t) <option value="{{ $t->id }}">{{ $t->name }}</option> @endforeach
        </select>
        <input type="date" wire:model.live="date" class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm" data-testid="tx-date-filter">
    </div>

    <div class="overflow-x-auto rounded-2xl border border-slate-200 bg-white">
        <table class="w-full min-w-[760px] text-sm">
            <thead class="bg-slate-50 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500">
                <tr><th class="px-4 py-3">Order</th><th class="px-4 py-3">Tenant</th><th class="px-4 py-3">Pembeli</th><th class="px-4 py-3">Pembayaran</th><th class="px-4 py-3">Status</th><th class="px-4 py-3 text-right">Total</th><th class="px-4 py-3"></th></tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($orders as $order)
                    <tr data-testid="tx-row-{{ $order->id }}" wire:key="tx-{{ $order->id }}">
                        <td class="px-4 py-3"><strong class="block font-mono text-xs">{{ $order->code }}</strong><span class="text-[11px] text-slate-500">{{ $order->invoice->code }} · {{ $order->created_at->translatedFormat('d M H:i') }}</span></td>
                        <td class="px-4 py-3 font-semibold">{{ $order->tenant->name }}</td>
                        <td class="px-4 py-3">{{ $order->buyer->name }}</td>
                        <td class="px-4 py-3"><span class="text-xs {{ $order->isPaid() ? 'text-brand-dark' : 'text-amber-700' }} font-semibold">{{ $order->invoice->payment_method->label() }} · {{ $order->payment_status->label() }}</span></td>
                        <td class="px-4 py-3"><x-status-badge :status="$order->status" /></td>
                        <td class="px-4 py-3 text-right font-bold">@rupiah($order->subtotal)</td>
                        <td class="px-4 py-3 text-right">
                            @can('cancel', $order)
                                <button wire:click="cancel({{ $order->id }})" wire:confirm="Batalkan {{ $order->code }}?" class="text-xs font-bold text-rose-600 hover:underline" data-testid="admin-cancel-{{ $order->id }}">Batalkan</button>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-4 py-10 text-center text-slate-500">Tidak ada transaksi.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $orders->links() }}</div>
</div>
