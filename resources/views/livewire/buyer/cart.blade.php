<div>
    <x-page-header title="Keranjang" eyebrow="Pesanan saya" :back="route('catalog')" />

    @if ($groups->isEmpty())
        <x-empty-state title="Keranjang masih kosong" text="Yuk pilih menu favoritmu dulu dari kantin.">
            <a href="{{ route('catalog') }}" wire:navigate class="mt-5 rounded-full bg-brand px-6 py-2.5 text-sm font-bold text-white" data-testid="go-catalog-button">Lihat menu</a>
        </x-empty-state>
    @else
        <div class="grid gap-6 lg:grid-cols-[1fr_360px]">
            <div class="space-y-4">
                <div class="rounded-2xl border border-sky-200 bg-sky-50 px-4 py-3 text-xs text-sky-800" data-testid="split-info">
                    Pesanan dari <strong>{{ $groups->count() }} tenant</strong> akan dipisah menjadi {{ $groups->count() }} order dengan <strong>satu pembayaran</strong>.
                </div>

                @foreach ($groups as $tenantId => $lines)
                    @php($tenant = $lines->first()['product']->tenant)
                    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white" data-testid="cart-group-{{ $tenantId }}" wire:key="group-{{ $tenantId }}">
                        <header class="flex items-center gap-2 border-b border-slate-100 px-4 py-3">
                            <x-icon name="store" class="h-4 w-4 text-brand" />
                            <strong class="text-sm">{{ $tenant->name }}</strong>
                            @unless ($tenant->canSell()) <span class="ml-auto rounded-full bg-rose-100 px-2 py-0.5 text-[10px] font-bold text-rose-700">Tenant tutup</span> @endunless
                        </header>
                        <ul class="divide-y divide-slate-100">
                            @foreach ($lines as $productId => $line)
                                @php($p = $line['product'])
                                <li class="p-4" data-testid="cart-item-{{ $p->id }}" wire:key="line-{{ $p->id }}">
                                    <div class="flex gap-3">
                                        <img src="{{ $p->image_url }}" alt="" class="h-16 w-16 shrink-0 rounded-xl object-cover {{ $line['sellable'] ? '' : 'grayscale' }}">
                                        <div class="min-w-0 flex-1">
                                            <div class="flex items-start justify-between gap-2">
                                                <div>
                                                    <strong class="block text-sm leading-tight">{{ $p->name }}</strong>
                                                    <span class="text-xs text-slate-500">@rupiah($p->price)</span>
                                                    @unless ($line['sellable']) <span class="ml-1 text-[10px] font-bold text-rose-600">Tidak tersedia</span> @endunless
                                                </div>
                                                <button wire:click="remove({{ $p->id }})" class="text-slate-400 hover:text-rose-600" data-testid="remove-{{ $p->id }}-button" aria-label="Hapus"><x-icon name="trash" class="h-4 w-4" /></button>
                                            </div>
                                            <div class="mt-2 flex items-center justify-between gap-3">
                                                <input type="text" wire:model.blur="notes.{{ $p->id }}" placeholder="Catatan (mis. tanpa sambal)" maxlength="120" class="w-full rounded-lg border border-slate-200 px-2.5 py-1.5 text-xs outline-none focus:border-brand" data-testid="note-{{ $p->id }}-input">
                                                <div class="flex shrink-0 items-center gap-1 rounded-full border border-slate-200 p-0.5">
                                                    <button wire:click="decrement({{ $p->id }})" class="grid h-7 w-7 place-items-center rounded-full hover:bg-slate-100" data-testid="decrease-{{ $p->id }}-button"><x-icon name="minus" class="h-3.5 w-3.5" /></button>
                                                    <b class="w-6 text-center text-sm" data-testid="qty-{{ $p->id }}">{{ $line['qty'] }}</b>
                                                    <button wire:click="increment({{ $p->id }})" class="grid h-7 w-7 place-items-center rounded-full bg-brand text-white hover:bg-brand-dark" data-testid="increase-{{ $p->id }}-button"><x-icon name="plus" class="h-3.5 w-3.5" /></button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                        <footer class="flex items-center justify-between bg-slate-50 px-4 py-2.5 text-sm">
                            <span class="text-slate-500">Subtotal {{ $tenant->name }}</span>
                            <strong data-testid="subtotal-{{ $tenantId }}">@rupiah($lines->sum('line_total'))</strong>
                        </footer>
                    </section>
                @endforeach
            </div>

            {{-- Ringkasan / bar checkout --}}
            <aside class="fixed inset-x-0 bottom-[64px] z-20 border-t border-slate-200 bg-white p-4 md:static md:rounded-2xl md:border md:p-5 lg:sticky lg:top-24 lg:self-start">
                <div class="flex items-center justify-between md:block">
                    <div>
                        <p class="text-xs text-slate-500">Total pembayaran</p>
                        <strong class="text-xl font-extrabold" data-testid="cart-total">@rupiah($total)</strong>
                    </div>
                    <a href="{{ route('checkout') }}" wire:navigate
                       @class(['rounded-full bg-brand px-6 py-3 text-sm font-bold text-white shadow-lg shadow-brand/30 transition hover:bg-brand-dark md:mt-4 md:block md:text-center', 'pointer-events-none opacity-40' => $hasUnsellable])
                       data-testid="checkout-button">Checkout</a>
                </div>
                @if ($hasUnsellable)
                    <p class="mt-2 text-xs font-semibold text-rose-600" data-testid="unsellable-warning">Hapus menu yang tidak tersedia untuk melanjutkan.</p>
                @endif
            </aside>
        </div>
    @endif
</div>
