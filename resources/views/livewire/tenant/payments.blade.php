<div>
    <x-page-header title="Pembayaran & settlement" eyebrow="Keuangan tenant" />

    <div class="grid gap-6 lg:grid-cols-[1fr_1fr]">
        {{-- Verifikasi tunai --}}
        <section class="rounded-2xl border border-slate-200 bg-white">
            <header class="border-b border-slate-100 px-4 py-3">
                <h2 class="text-base font-extrabold">Menunggu verifikasi tunai <span class="ml-1 rounded-full bg-amber-100 px-2 py-0.5 text-xs text-amber-800" data-testid="pending-cash-count">{{ $pendingCash->count() }}</span></h2>
                <p class="text-xs text-slate-500">Tandai lunas saat pembeli membayar di kasir.</p>
            </header>
            <ul class="divide-y divide-slate-100">
                @forelse ($pendingCash as $order)
                    <li class="flex items-center gap-3 px-4 py-3 text-sm" data-testid="pending-cash-{{ $order->id }}" wire:key="cash-{{ $order->id }}">
                        <div class="min-w-0 flex-1">
                            <strong class="block font-mono text-xs">{{ $order->code }}</strong>
                            <span class="block truncate text-xs text-slate-500">{{ $order->buyer->name }} · {{ $order->invoice->payment_method->label() }} · {{ $order->created_at->translatedFormat('d M H:i') }}</span>
                        </div>
                        <strong>@rupiah($order->subtotal)</strong>
                        <button wire:click="markPaid({{ $order->id }})" wire:confirm="Konfirmasi pembayaran diterima?" class="rounded-full bg-brand px-3 py-1.5 text-xs font-bold text-white hover:bg-brand-dark" data-testid="verify-payment-{{ $order->id }}">Lunas</button>
                        <button wire:click="refund({{ $order->id }})" wire:confirm="Batalkan pesanan ini?" class="rounded-full px-2 py-1.5 text-xs font-bold text-rose-600 hover:bg-rose-50" data-testid="refund-{{ $order->id }}">Batal</button>
                    </li>
                @empty
                    <li class="px-4 py-10 text-center text-sm text-slate-500">Tidak ada pembayaran tunai yang menunggu.</li>
                @endforelse
            </ul>
        </section>

        {{-- Settlement harian --}}
        <section class="rounded-2xl border border-slate-200 bg-white">
            <header class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-100 px-4 py-3">
                <h2 class="text-base font-extrabold">Settlement harian</h2>
                <div class="flex items-center gap-1 text-xs">
                    <input type="date" wire:model.live="from" class="rounded-lg border border-slate-200 px-2 py-1" data-testid="settlement-from"> –
                    <input type="date" wire:model.live="to" class="rounded-lg border border-slate-200 px-2 py-1" data-testid="settlement-to">
                </div>
            </header>
            @if ($settlement->isEmpty())
                <p class="px-4 py-10 text-center text-sm text-slate-500">Belum ada transaksi lunas pada rentang ini.</p>
            @else
                <ul class="divide-y divide-slate-100">
                    @foreach ($settlement as $day => $rows)
                        <li class="px-4 py-3" data-testid="settlement-day-{{ $day }}">
                            <div class="flex items-center justify-between">
                                <strong class="text-sm">{{ \Carbon\Carbon::parse($day)->translatedFormat('l, d M Y') }}</strong>
                                <strong class="text-sm text-brand-dark">@rupiah($rows->sum('amount'))</strong>
                            </div>
                            <ul class="mt-1 flex flex-wrap gap-2 text-[11px] text-slate-500">
                                @foreach ($rows as $row)
                                    <li class="rounded-full bg-slate-100 px-2 py-0.5">{{ \App\Enums\PaymentMethod::from($row->method)->label() }}: {{ $row->orders_count }} order · @rupiah($row->amount)</li>
                                @endforeach
                            </ul>
                        </li>
                    @endforeach
                </ul>
                <footer class="flex items-center justify-between border-t border-slate-200 px-4 py-3">
                    <span class="text-sm font-semibold">Total periode</span>
                    <strong class="text-lg font-extrabold" data-testid="settlement-total">@rupiah($settlement->flatten(1)->sum('amount'))</strong>
                </footer>
            @endif
        </section>
    </div>

    @if ($refunds->isNotEmpty())
        <section class="mt-6 rounded-2xl border border-slate-200 bg-white">
            <header class="border-b border-slate-100 px-4 py-3"><h2 class="text-base font-extrabold">Refund terakhir</h2></header>
            <ul class="divide-y divide-slate-100 text-sm">
                @foreach ($refunds as $order)
                    <li class="flex items-center justify-between px-4 py-3" data-testid="refund-row-{{ $order->id }}">
                        <span><strong class="font-mono text-xs">{{ $order->code }}</strong> · {{ $order->buyer->name }} <em class="text-xs text-slate-500">— {{ $order->cancel_reason }}</em></span>
                        <span class="font-semibold text-rose-600">-@rupiah($order->subtotal)</span>
                    </li>
                @endforeach
            </ul>
        </section>
    @endif
</div>
