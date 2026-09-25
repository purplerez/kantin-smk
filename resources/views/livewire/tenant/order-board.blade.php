{{-- [JS #3] wire:poll fallback (8 dtk / 60 dtk bila Reverb aktif) + [JS #5] push instan via Echo/Reverb --}}
<div wire:poll.{{ $pollSeconds }}s="tick" data-testid="order-board" data-realtime="{{ \App\Services\Realtime::enabled() ? 'reverb' : 'poll' }}">
    <x-page-header title="Pesanan masuk" eyebrow="Operasional hari ini">
        <x-slot:actions>
            <label class="flex items-center gap-2 rounded-full border border-slate-200 bg-white px-3 py-2">
                <x-icon name="search" class="h-4 w-4 text-slate-400" />
                <input type="search" wire:model.live.debounce.300ms="search" placeholder="Kode / nama pembeli" class="w-36 bg-transparent text-xs outline-none md:w-56" data-testid="order-search-input">
            </label>
        </x-slot:actions>
    </x-page-header>

    <div class="no-scrollbar -mx-4 mb-5 flex gap-2 overflow-x-auto px-4 md:mx-0 md:px-0">
        @foreach ($tabs as $key => $label)
            <button wire:click="setStatus('{{ $key }}')" class="flex shrink-0 items-center gap-1.5 rounded-full px-4 py-2 text-xs font-bold transition {{ $status === $key ? 'bg-slate-900 text-white' : 'bg-white text-slate-600 ring-1 ring-slate-200 hover:bg-slate-50' }}" data-testid="status-tab-{{ $key }}">
                {{ $label }}
                @if (isset($counts[$key]) && $counts[$key] > 0) <span class="rounded-full px-1.5 text-[10px] {{ $status === $key ? 'bg-white/20' : 'bg-brand-light text-brand-dark' }}">{{ $counts[$key] }}</span> @endif
            </button>
        @endforeach
    </div>

    @if ($orders->isEmpty())
        <x-empty-state icon="list" title="Tidak ada pesanan" text="Pesanan dengan status ini akan muncul di sini secara otomatis." />
    @else
        <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-3">
            @foreach ($orders as $order)
                <article class="flex flex-col rounded-2xl border border-slate-200 bg-white p-4 shadow-sm" data-testid="tenant-order-{{ $order->id }}" wire:key="t-order-{{ $order->id }}">
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <strong class="block font-mono text-sm">{{ $order->code }}</strong>
                            <span class="text-xs text-slate-500">{{ $order->buyer->name }} · {{ $order->created_at->translatedFormat('H:i') }} ({{ $order->created_at->diffForHumans(short: true) }})</span>
                        </div>
                        <x-status-badge :status="$order->status" />
                    </div>

                    <ul class="mt-3 space-y-1 text-sm">
                        @foreach ($order->items as $item)
                            <li class="flex justify-between gap-2"><span><b class="text-brand-dark">{{ $item->qty }}×</b> {{ $item->product_name }}@if ($item->note) <em class="block text-xs text-slate-500">“{{ $item->note }}”</em>@endif</span><span class="text-slate-500">@rupiah($item->line_total)</span></li>
                        @endforeach
                    </ul>
                    @if ($order->note) <p class="mt-2 rounded-lg bg-amber-50 px-2.5 py-1.5 text-xs text-amber-800">Catatan: {{ $order->note }}</p> @endif

                    <div class="mt-3 flex items-center justify-between border-t border-slate-100 pt-3">
                        <strong class="text-base font-extrabold">@rupiah($order->subtotal)</strong>
                        <span class="rounded-full px-2.5 py-1 text-[11px] font-bold {{ $order->isPaid() ? 'bg-brand-light text-brand-dark' : 'bg-amber-100 text-amber-800' }}" data-testid="tenant-order-payment-{{ $order->id }}">{{ $order->invoice->payment_method->label() }} · {{ $order->payment_status->label() }}</span>
                    </div>

                    <div class="mt-3 flex flex-wrap gap-2">
                        @can('advance', $order)
                            <button wire:click="advance({{ $order->id }})" wire:loading.attr="disabled" class="flex-1 rounded-full bg-brand px-4 py-2.5 text-xs font-bold text-white hover:bg-brand-dark disabled:opacity-60" data-testid="advance-order-{{ $order->id }}-button">
                                Tandai {{ $order->status->next()?->label() }}
                            </button>
                        @endcan
                        @if (! $order->isPaid() && ! $order->status->isFinal())
                            @can('verifyPayment', $order)
                                <button wire:click="markPaid({{ $order->id }})" wire:confirm="Konfirmasi pembayaran tunai diterima?" class="rounded-full border border-brand px-4 py-2.5 text-xs font-bold text-brand-dark hover:bg-brand-light" data-testid="mark-paid-{{ $order->id }}-button">Terima bayar</button>
                            @endcan
                        @endif
                        @can('cancel', $order)
                            <button wire:click="cancel({{ $order->id }})" wire:confirm="Batalkan pesanan {{ $order->code }}?" class="rounded-full px-3 py-2.5 text-xs font-bold text-rose-600 hover:bg-rose-50" data-testid="cancel-order-{{ $order->id }}-button">Batalkan</button>
                        @endcan
                    </div>
                    @error('status') <p class="mt-2 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
                </article>
            @endforeach
        </div>
        <div class="mt-6">{{ $orders->links() }}</div>
    @endif
</div>
