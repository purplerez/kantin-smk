@php($isCancelled = $order->status === \App\Enums\OrderStatus::Cancelled)
{{-- [JS #3] wire:poll fallback (8 dtk / 60 dtk bila Reverb aktif) + [JS #5] push instan via Echo/Reverb --}}
<div @unless($order->status->isFinal()) wire:poll.{{ $pollSeconds }}s="tick" @endunless data-testid="order-detail-page">
    <x-page-header :title="$order->tenant->name" :eyebrow="$order->code" :back="route('orders')">
        <x-slot:actions><x-status-badge :status="$order->status" class="text-xs" data-testid="order-status" /></x-slot:actions>
    </x-page-header>

    <div class="grid gap-6 lg:grid-cols-[1fr_380px]">
        <div class="space-y-5">
            {{-- Timeline --}}
            <section class="rounded-2xl border border-slate-200 bg-white p-5" data-testid="order-timeline">
                @if ($isCancelled)
                    <div class="flex items-center gap-3 text-rose-700">
                        <span class="grid h-10 w-10 place-items-center rounded-full bg-rose-100"><x-icon name="x" class="h-5 w-5" /></span>
                        <div><strong class="block text-sm">Pesanan dibatalkan</strong><span class="text-xs">{{ $order->cancel_reason }} · {{ $order->cancelled_at?->translatedFormat('d M H:i') }}</span></div>
                    </div>
                @elseif ($order->status === \App\Enums\OrderStatus::PreOrder)
                    {{-- State Pre-Order: toko belum buka --}}
                    <div class="flex flex-col items-center gap-3 py-4 text-center" data-testid="po-state">
                        <span class="grid h-14 w-14 place-items-center rounded-full bg-violet-100 text-violet-600">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </span>
                        <div>
                            <strong class="block text-sm text-violet-800">Pre-Order diterima</strong>
                            <p class="mt-1 text-xs text-slate-500">
                                Pesananmu sudah masuk. Toko <strong>{{ $order->tenant->name }}</strong> belum buka —
                                pesanan akan dikonfirmasi otomatis ketika toko mulai beroperasi.
                            </p>
                            @if ($order->scheduled_for)
                                <p class="mt-2 inline-flex items-center gap-1 rounded-full bg-violet-50 px-3 py-1 text-xs font-semibold text-violet-700">
                                    ⏰ Perkiraan: {{ $order->scheduled_for->setTimezone('Asia/Jakarta')->isoFormat('dddd, D MMM · HH:mm') }}
                                </p>
                            @endif
                        </div>
                    </div>
                @else
                    @if ($order->status === \App\Enums\OrderStatus::Pending)
                        <a href="{{ route('invoice.show', $order->invoice) }}" wire:navigate class="mb-5 flex items-center justify-between rounded-xl bg-amber-50 px-4 py-3 text-sm font-bold text-amber-800" data-testid="pay-now-link">Menunggu pembayaran — bayar sekarang <span>→</span></a>
                    @endif
                    <ol class="grid grid-cols-4 gap-1">
                        @foreach ($timeline as $i => $step)
                            @php($done = $currentIndex !== false && $i <= $currentIndex)
                            <li class="flex flex-col items-center text-center" data-testid="timeline-step-{{ $step->value }}">
                                <div class="flex w-full items-center">
                                    <span class="h-0.5 flex-1 {{ $i === 0 ? 'bg-transparent' : ($done ? 'bg-brand' : 'bg-slate-200') }}"></span>
                                    <span class="grid h-8 w-8 shrink-0 place-items-center rounded-full text-xs font-bold {{ $done ? 'bg-brand text-white' : 'bg-slate-100 text-slate-400' }} {{ $i === $currentIndex ? 'ring-4 ring-brand/25' : '' }}">
                                        @if ($done) <x-icon name="check" class="h-4 w-4" /> @else {{ $i + 1 }} @endif
                                    </span>
                                    <span class="h-0.5 flex-1 {{ $loop->last ? 'bg-transparent' : ($currentIndex !== false && $i < $currentIndex ? 'bg-brand' : 'bg-slate-200') }}"></span>
                                </div>
                                <small class="mt-2 text-[10px] font-semibold leading-tight {{ $done ? 'text-slate-900' : 'text-slate-400' }}">{{ $step->label() }}</small>
                            </li>
                        @endforeach
                    </ol>
                    @if ($order->status === \App\Enums\OrderStatus::Ready)
                        <p class="mt-5 rounded-xl bg-brand-light px-4 py-3 text-center text-sm font-bold text-brand-dark">Pesanan siap! Tunjukkan kode <span class="font-mono">{{ $order->code }}</span> ke kasir {{ $order->tenant->name }}.</p>
                    @endif
                @endif
            </section>

            {{-- Item --}}
            <section class="rounded-2xl border border-slate-200 bg-white">
                <header class="border-b border-slate-100 px-4 py-3 text-[11px] font-bold uppercase tracking-[0.18em] text-slate-400">Rincian pesanan</header>
                <ul class="divide-y divide-slate-100">
                    @foreach ($order->items as $item)
                        <li class="flex items-start justify-between gap-3 px-4 py-3 text-sm" data-testid="order-item-{{ $item->id }}">
                            <div>
                                <strong>{{ $item->qty }}× {{ $item->product_name }}</strong>
                                @if ($item->note) <p class="text-xs text-slate-500">Catatan: {{ $item->note }}</p> @endif
                            </div>
                            <span>@rupiah($item->line_total)</span>
                        </li>
                    @endforeach
                </ul>
                <footer class="flex items-center justify-between border-t border-slate-200 px-4 py-3">
                    <span class="text-sm font-semibold">Subtotal</span>
                    <strong class="text-lg font-extrabold" data-testid="order-subtotal">@rupiah($order->subtotal)</strong>
                </footer>
            </section>

            {{-- Ulasan --}}
            @if ($order->review)
                <section class="rounded-2xl border border-slate-200 bg-white p-4" data-testid="order-review">
                    <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-400">Ulasanmu</p>
                    <div class="mt-2 flex text-amber-400">@for ($i = 1; $i <= 5; $i++)<x-icon name="star" class="h-5 w-5 {{ $i <= $order->review->rating ? 'fill-amber-400' : 'text-slate-300' }}" />@endfor</div>
                    @if ($order->review->comment) <p class="mt-2 text-sm text-slate-600">{{ $order->review->comment }}</p> @endif
                </section>
            @elseif ($order->canBeReviewed())
                <form wire:submit="submitReview" class="rounded-2xl border border-slate-200 bg-white p-4" data-testid="review-form">
                    <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-400">Beri ulasan untuk {{ $order->tenant->name }}</p>
                    <div class="mt-3 flex gap-1">
                        @for ($i = 1; $i <= 5; $i++)
                            <button type="button" wire:click="$set('rating', {{ $i }})" class="text-amber-400 transition hover:scale-110" data-testid="rating-{{ $i }}-button" aria-label="{{ $i }} bintang">
                                <x-icon name="star" class="h-8 w-8 {{ $i <= $rating ? 'fill-amber-400' : 'text-slate-300' }}" />
                            </button>
                        @endfor
                    </div>
                    <textarea wire:model="comment" rows="2" maxlength="300" placeholder="Ceritakan pengalamanmu (opsional)" class="mt-3 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm outline-none focus:border-brand" data-testid="review-comment-input"></textarea>
                    @error('comment') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    <button class="mt-3 rounded-full bg-brand px-6 py-2.5 text-sm font-bold text-white hover:bg-brand-dark" data-testid="submit-review-button">Kirim ulasan</button>
                </form>
            @endif
        </div>

        <aside class="space-y-3 lg:sticky lg:top-24 lg:self-start">
            @if ($order->invoice->orders->count() > 1)
                <section class="rounded-2xl border border-slate-200 bg-white p-4 text-sm" data-testid="sibling-orders-card">
                    <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-400">Order Lain dalam Invoice Ini</p>
                    <div class="mt-2 space-y-2">
                        @foreach ($order->invoice->orders as $sibling)
                            @if ($sibling->id === $order->id)
                                <div class="flex items-center justify-between rounded-xl bg-brand-light/50 p-2.5 text-xs font-semibold text-brand-dark">
                                    <span>{{ $sibling->tenant->name }} (Sedang dibuka)</span>
                                    <x-status-badge :status="$sibling->status" class="text-[10px]" />
                                </div>
                            @else
                                <a href="{{ route('orders.show', $sibling) }}" wire:navigate class="flex items-center justify-between rounded-xl border border-slate-100 p-2.5 text-xs transition hover:border-brand hover:bg-slate-50">
                                    <div class="text-left">
                                        <strong class="block text-slate-800">{{ $sibling->tenant->name }}</strong>
                                        <span class="text-[10px] text-slate-500">{{ $sibling->code }} · @rupiah($sibling->subtotal)</span>
                                    </div>
                                    <x-status-badge :status="$sibling->status" class="text-[10px]" />
                                </a>
                            @endif
                        @endforeach
                    </div>
                </section>
            @endif

            <section class="rounded-2xl border border-slate-200 bg-white p-4 text-sm">
                <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-400">Pembayaran</p>
                <dl class="mt-2 space-y-1.5">
                    <div class="flex justify-between"><dt class="text-slate-500">Metode</dt><dd class="font-semibold">{{ $order->invoice->payment_method->label() }}</dd></div>
                    <div class="flex justify-between"><dt class="text-slate-500">Status</dt><dd class="font-semibold {{ $order->isPaid() ? 'text-brand-dark' : 'text-amber-700' }}" data-testid="order-payment-status">{{ $order->payment_status->label() }}</dd></div>
                    <div class="flex justify-between"><dt class="text-slate-500">Invoice</dt><dd><a href="{{ route('invoice.show', $order->invoice) }}" wire:navigate class="font-semibold text-brand-dark underline-offset-2 hover:underline" data-testid="invoice-link">{{ $order->invoice->code }}</a></dd></div>
                    <div class="flex justify-between"><dt class="text-slate-500">Dipesan</dt><dd class="font-semibold">{{ $order->created_at->translatedFormat('d M Y, H:i') }}</dd></div>
                </dl>
                @if ($order->note) <p class="mt-3 rounded-xl bg-slate-50 px-3 py-2 text-xs"><span class="text-slate-500">Catatan:</span> {{ $order->note }}</p> @endif
            </section>

            @can('cancel', $order)
                <button wire:click="cancel" wire:confirm="{{ $order->isPaid() ? 'Pesanan ini sudah dibayar. Jika dibatalkan, pengembalian dana dilakukan manual oleh pihak kantin. Yakin ingin membatalkan pesanan?' : 'Batalkan pesanan ini?' }}" class="w-full rounded-full border border-rose-200 py-3 text-sm font-bold text-rose-600 hover:bg-rose-50" data-testid="cancel-order-button">Batalkan pesanan</button>
            @endcan
        </aside>
    </div>
</div>
