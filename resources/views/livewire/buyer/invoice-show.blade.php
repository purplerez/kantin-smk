@php($method = $invoice->payment_method)
<div>
    <x-page-header title="Pembayaran" :eyebrow="$invoice->code" :back="route('orders')" />

    <div class="grid gap-6 lg:grid-cols-[1fr_380px]">
        <div class="space-y-5">
            {{-- Status pembayaran --}}
            <section class="rounded-3xl border border-slate-200 bg-white p-5 text-center" data-testid="payment-panel">
                <p class="text-xs text-slate-500">Total tagihan</p>
                <strong class="mt-1 block text-3xl font-extrabold tracking-tight" data-testid="invoice-total">@rupiah($invoice->total)</strong>
                <span class="mt-3 inline-flex items-center gap-1 rounded-full px-3 py-1 text-xs font-bold {{ $invoice->isPaid() ? 'bg-brand-light text-brand-dark' : 'bg-amber-100 text-amber-800' }}" data-testid="invoice-payment-status">
                    {{ $method->label() }} · {{ $invoice->payment_status->label() }}
                </span>

                @if ($invoice->isPaid())
                    <div class="mt-6 flex flex-col items-center">
                        <span class="grid h-16 w-16 place-items-center rounded-full bg-brand text-white"><x-icon name="check" class="h-8 w-8" /></span>
                        <p class="mt-3 text-sm font-semibold">Pembayaran diterima {{ $invoice->paid_at?->translatedFormat('d M Y H:i') }}</p>
                        <p class="text-xs text-slate-500">Pantau status pesananmu di bawah.</p>
                    </div>
                @elseif ($method === \App\Enums\PaymentMethod::Qris)
                    <div class="mx-auto mt-6 w-56 rounded-2xl border-4 border-slate-900 bg-white p-3" data-testid="qris-code">
                        <svg viewBox="0 0 21 21" class="h-full w-full" shape-rendering="crispEdges">
                            @foreach ($this->qrCells() as [$x, $y])<rect x="{{ $x }}" y="{{ $y }}" width="1" height="1" fill="#0f172a" />@endforeach
                        </svg>
                    </div>
                    <p class="mt-3 text-xs text-slate-500">Scan dengan aplikasi e-wallet / m-banking (simulasi).</p>
                @elseif ($method === \App\Enums\PaymentMethod::Transfer)
                    <div class="mx-auto mt-6 max-w-xs rounded-2xl bg-slate-50 p-4 text-left" data-testid="va-panel">
                        <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Nomor Virtual Account</p>
                        <p class="mt-1 font-mono text-2xl font-bold tracking-wider" data-testid="va-number">{{ $invoice->payment_reference }}</p>
                        <p class="mt-2 text-xs text-slate-500">Bank BCA / Mandiri · a.n. KantinSMK Go</p>
                    </div>
                @else
                    <div class="mx-auto mt-6 max-w-xs rounded-2xl bg-slate-50 p-4 text-sm text-slate-600" data-testid="cash-panel">
                        Tunjukkan kode pesanan ke kasir tenant dan bayar <strong>@rupiah($invoice->total)</strong> saat mengambil pesanan. Tenant akan menandai lunas.
                    </div>
                @endif

                @if (! $invoice->isPaid() && $method->requiresPrepayment())
                    <button wire:click="simulatePayment" wire:loading.attr="disabled" class="mt-6 w-full rounded-full bg-brand py-3.5 text-sm font-bold text-white shadow-lg shadow-brand/30 hover:bg-brand-dark disabled:opacity-60 md:w-auto md:px-10" data-testid="simulate-payment-button">
                        Saya sudah bayar (simulasi demo)
                    </button>
                @endif
            </section>

            @if ($invoice->note)
                <p class="rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm"><span class="text-slate-500">Catatan:</span> {{ $invoice->note }}</p>
            @endif
        </div>

        {{-- Order per tenant --}}
        <aside class="space-y-3">
            <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-400">{{ $invoice->orders->count() }} order dalam invoice ini</p>
            @foreach ($invoice->orders as $order)
                <a href="{{ route('orders.show', $order) }}" wire:navigate class="block rounded-2xl border border-slate-200 bg-white p-4 transition hover:border-brand" data-testid="invoice-order-{{ $order->id }}" wire:key="inv-order-{{ $order->id }}">
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <strong class="block text-sm">{{ $order->tenant->name }}</strong>
                            <span class="text-xs text-slate-500">{{ $order->code }}</span>
                        </div>
                        <x-status-badge :status="$order->status" />
                    </div>
                    <ul class="mt-2 text-xs text-slate-600">
                        @foreach ($order->items as $item)
                            <li>{{ $item->qty }}× {{ $item->product_name }}</li>
                        @endforeach
                    </ul>
                    <div class="mt-2 text-right text-sm font-bold">@rupiah($order->subtotal)</div>
                </a>
            @endforeach
        </aside>
    </div>
</div>
