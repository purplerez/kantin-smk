<div>
    <x-page-header title="Settlement platform" eyebrow="Keuangan semua tenant">
        <x-slot:actions>
            <div class="flex items-center gap-2">
                <input type="date" wire:model.live="date" class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm" data-testid="settlement-date">
                <button wire:click="generate" class="rounded-full bg-brand px-4 py-2.5 text-xs font-bold text-white hover:bg-brand-dark" data-testid="generate-settlement-button">Generate</button>
            </div>
        </x-slot:actions>
    </x-page-header>

    <div class="mb-5 grid grid-cols-2 gap-3 md:grid-cols-4">
        <div class="rounded-2xl border border-slate-200 bg-white p-4"><p class="text-xs text-slate-500">Order lunas</p><strong class="text-xl font-extrabold" data-testid="totals-orders">{{ $totals['orders'] }}</strong></div>
        <div class="rounded-2xl border border-slate-200 bg-white p-4"><p class="text-xs text-slate-500">Bruto</p><strong class="text-xl font-extrabold">@rupiah($totals['gross'])</strong></div>
        <div class="rounded-2xl border border-slate-200 bg-white p-4"><p class="text-xs text-slate-500">Komisi ({{ $commissionPercent }}%)</p><strong class="text-xl font-extrabold text-rose-600">@rupiah($totals['commission'])</strong></div>
        <div class="rounded-2xl border border-slate-200 bg-white p-4"><p class="text-xs text-slate-500">Neto tenant</p><strong class="text-xl font-extrabold text-brand-dark">@rupiah($totals['net'])</strong></div>
    </div>

    <div class="overflow-x-auto rounded-2xl border border-slate-200 bg-white">
        <table class="w-full min-w-[640px] text-sm">
            <thead class="bg-slate-50 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500">
                <tr><th class="px-4 py-3">Tenant</th><th class="px-4 py-3">Order</th><th class="px-4 py-3 text-right">Bruto</th><th class="px-4 py-3 text-right">Komisi</th><th class="px-4 py-3 text-right">Neto</th></tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($settlements as $s)
                    <tr wire:key="settlement-{{ $s->id }}" data-testid="settlement-row-{{ $s->tenant_id }}">
                        <td class="px-4 py-3 font-semibold">{{ $s->tenant->name }}</td>
                        <td class="px-4 py-3">{{ $s->orders_count }}</td>
                        <td class="px-4 py-3 text-right">@rupiah($s->gross)</td>
                        <td class="px-4 py-3 text-right text-rose-600">@rupiah($s->commission)</td>
                        <td class="px-4 py-3 text-right font-bold text-brand-dark">@rupiah($s->net)</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-10 text-center text-sm text-slate-500" data-testid="settlement-empty">Belum ada settlement untuk tanggal ini. Klik <strong>Generate</strong> untuk menghitung.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
