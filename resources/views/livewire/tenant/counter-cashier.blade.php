<div x-data="{ tab: 'products' }">
    <x-page-header title="Kasir Counter" eyebrow="{{ auth()->user()->tenant?->name }}" />

    <div class="grid gap-6 lg:grid-cols-[1fr_360px]">

        {{-- ============================================================ --}}
        {{-- KIRI: Daftar Produk --}}
        {{-- ============================================================ --}}
        <section class="space-y-4">
            {{-- Produk Grid --}}
            <div class="rounded-3xl border border-slate-200 bg-white shadow-sm">
                <header class="border-b border-slate-100 px-5 py-3.5">
                    <h2 class="text-sm font-bold text-slate-700">Pilih Menu</h2>
                    <p class="text-xs text-slate-400 mt-0.5">Hanya menu yang tersedia hari ini ditampilkan.</p>
                </header>

                @if ($products->isEmpty())
                    <div class="px-5 py-10 text-center text-sm text-slate-400">
                        Tidak ada menu tersedia hari ini.
                    </div>
                @else
                    <ul class="grid grid-cols-2 gap-3 p-4 sm:grid-cols-3">
                        @foreach ($products as $product)
                            @php($inCart = $cart[(string) $product->id] ?? 0)
                            <li class="flex flex-col overflow-hidden rounded-2xl border border-slate-100 bg-slate-50 transition hover:border-brand/40"
                                wire:key="prod-{{ $product->id }}" data-testid="product-{{ $product->id }}">
                                {{-- Product name + price --}}
                                <div class="flex-1 px-3 pt-3 pb-2">
                                    <p class="text-sm font-semibold leading-tight text-slate-800 line-clamp-2">{{ $product->name }}</p>
                                    <p class="mt-1 text-xs font-bold text-brand-dark">@rupiah($product->price)</p>
                                    @if ($product->category)
                                        <span class="mt-1 inline-block rounded-full bg-slate-200 px-2 py-0.5 text-[10px] text-slate-500">{{ $product->category->name }}</span>
                                    @endif
                                </div>

                                {{-- Add / qty controls --}}
                                <div class="border-t border-slate-100 px-3 py-2">
                                    @if ($inCart === 0)
                                        <button wire:click="addToCart({{ $product->id }})"
                                                class="w-full rounded-xl bg-brand py-1.5 text-xs font-bold text-white hover:bg-brand-dark transition"
                                                data-testid="add-to-cart-{{ $product->id }}">
                                            + Tambah
                                        </button>
                                    @else
                                        <div class="flex items-center justify-between gap-2">
                                            <button wire:click="decrementCart({{ $product->id }})"
                                                    class="grid h-7 w-7 place-items-center rounded-full bg-slate-200 text-sm font-bold hover:bg-rose-100 hover:text-rose-600 transition"
                                                    data-testid="decrement-{{ $product->id }}">−</button>
                                            <span class="text-sm font-bold text-brand-dark" data-testid="qty-{{ $product->id }}">{{ $inCart }}</span>
                                            <button wire:click="addToCart({{ $product->id }})"
                                                    class="grid h-7 w-7 place-items-center rounded-full bg-brand text-sm font-bold text-white hover:bg-brand-dark transition"
                                                    data-testid="increment-{{ $product->id }}">+</button>
                                        </div>
                                    @endif
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

            {{-- Riwayat transaksi counter hari ini --}}
            @if ($recentOrders->isNotEmpty())
                <div class="rounded-3xl border border-slate-200 bg-white shadow-sm">
                    <header class="border-b border-slate-100 px-5 py-3.5">
                        <h2 class="text-sm font-bold text-slate-700">Transaksi Terakhir</h2>
                    </header>
                    <ul class="divide-y divide-slate-100">
                        @foreach ($recentOrders as $recent)
                            <li class="flex items-center justify-between gap-3 px-5 py-3 text-sm" wire:key="recent-{{ $recent->id }}" data-testid="recent-order-{{ $recent->id }}">
                                <div class="min-w-0">
                                    <p class="font-semibold truncate">{{ $recent->buyer->name }}</p>
                                    <p class="text-xs text-slate-400">
                                        {{ $recent->items->pluck('product_name')->implode(', ') }}
                                        · {{ $recent->completed_at?->setTimezone('Asia/Jakarta')->format('H:i') }}
                                    </p>
                                </div>
                                <span class="shrink-0 font-bold text-brand-dark">@rupiah($recent->subtotal)</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </section>

        {{-- ============================================================ --}}
        {{-- KANAN: Keranjang + Checkout --}}
        {{-- ============================================================ --}}
        <aside class="space-y-4 lg:sticky lg:top-24 lg:self-start">

            {{-- Keranjang --}}
            <div class="rounded-3xl border border-slate-200 bg-white shadow-sm">
                <header class="flex items-center justify-between border-b border-slate-100 px-5 py-3.5">
                    <h2 class="text-sm font-bold text-slate-700">Keranjang</h2>
                    @if (! empty($cart))
                        <button wire:click="clearCart" class="text-xs font-semibold text-rose-500 hover:underline"
                                data-testid="clear-cart-button">Kosongkan</button>
                    @endif
                </header>

                @if (empty($cartLines))
                    <div class="px-5 py-8 text-center text-sm text-slate-400" data-testid="empty-cart-message">
                        Belum ada menu dipilih.
                    </div>
                @else
                    <ul class="divide-y divide-slate-100">
                        @foreach ($cartLines as $line)
                            <li class="flex items-center justify-between gap-3 px-5 py-2.5 text-sm"
                                wire:key="cart-line-{{ $line['product']->id }}"
                                data-testid="cart-item-{{ $line['product']->id }}">
                                <div class="min-w-0">
                                    <p class="font-semibold truncate">{{ $line['product']->name }}</p>
                                    <p class="text-xs text-slate-400">{{ $line['qty'] }} × @rupiah($line['product']->price)</p>
                                </div>
                                <div class="flex items-center gap-2 shrink-0">
                                    <span class="font-bold">@rupiah($line['lineTotal'])</span>
                                    <button wire:click="removeFromCart({{ $line['product']->id }})"
                                            class="grid h-6 w-6 place-items-center rounded-full text-slate-400 hover:bg-rose-100 hover:text-rose-600 transition text-xs"
                                            data-testid="remove-{{ $line['product']->id }}">✕</button>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                    <div class="flex items-center justify-between border-t border-slate-200 px-5 py-3">
                        <span class="text-sm font-semibold text-slate-600">Total</span>
                        <strong class="text-xl font-extrabold text-brand-dark" data-testid="cart-total">@rupiah($cartTotal)</strong>
                    </div>
                @endif

                @error('cart')
                    <p class="px-5 pb-3 text-xs text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Pembeli (opsional) --}}
            <div class="rounded-3xl border border-slate-200 bg-white shadow-sm">
                <header class="border-b border-slate-100 px-5 py-3.5">
                    <h2 class="text-sm font-bold text-slate-700">Pembeli <span class="text-slate-400 font-normal">(opsional)</span></h2>
                </header>
                <div class="p-4 space-y-2">
                    @if ($selectedBuyer)
                        <div class="flex items-center justify-between rounded-xl bg-brand-light/50 px-4 py-2.5" data-testid="selected-buyer">
                            <div>
                                <p class="text-sm font-bold text-brand-dark">{{ $selectedBuyer->name }}</p>
                                @if ($selectedBuyer->identifier)
                                    <p class="text-xs text-slate-500">{{ $selectedBuyer->identifier }}</p>
                                @endif
                            </div>
                            <button wire:click="clearBuyer" class="text-xs text-slate-500 hover:text-rose-600" data-testid="clear-buyer-button">✕</button>
                        </div>
                    @else
                        <p class="text-xs text-slate-400 italic">Tidak ada pembeli dipilih — akan dicatat sebagai <strong>Pelanggan Umum</strong>.</p>
                        <button wire:click="selectAnonymous" class="hidden" data-testid="select-anonymous-button">anonim</button>

                        <div class="relative">
                            <input type="text" wire:model.live.debounce.300ms="buyerSearch"
                                   placeholder="Cari nama / NIS / email..."
                                   class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm outline-none focus:border-brand"
                                   data-testid="buyer-search-input">
                        </div>

                        @if ($buyerResults->isNotEmpty())
                            <ul class="max-h-40 overflow-y-auto rounded-xl border border-slate-200 bg-white shadow-sm divide-y divide-slate-100">
                                @foreach ($buyerResults as $br)
                                    <li>
                                        <button wire:click="selectBuyer({{ $br->id }})"
                                                class="w-full px-4 py-2.5 text-left text-sm hover:bg-slate-50 transition"
                                                data-testid="buyer-result-{{ $br->id }}">
                                            <span class="font-semibold">{{ $br->name }}</span>
                                            @if ($br->identifier) <span class="text-xs text-slate-400">· {{ $br->identifier }}</span> @endif
                                        </button>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    @endif
                </div>
            </div>

            {{-- Metode Bayar --}}
            <div class="rounded-3xl border border-slate-200 bg-white shadow-sm p-4 space-y-3">
                <h2 class="text-sm font-bold text-slate-700">Metode Bayar</h2>
                <div class="grid grid-cols-2 gap-2">
                    <label class="flex cursor-pointer items-center gap-2 rounded-xl border px-4 py-3 text-sm font-semibold transition
                        {{ $paymentMethod === 'cash' ? 'border-brand bg-brand-light/40 text-brand-dark' : 'border-slate-200 text-slate-600 hover:border-slate-300' }}"
                        data-testid="payment-cash">
                        <input type="radio" wire:model.live="paymentMethod" value="cash" class="sr-only">
                        💵 Tunai
                    </label>
                    <label class="flex cursor-pointer items-center gap-2 rounded-xl border px-4 py-3 text-sm font-semibold transition
                        {{ $paymentMethod === 'qris' ? 'border-brand bg-brand-light/40 text-brand-dark' : 'border-slate-200 text-slate-600 hover:border-slate-300' }}"
                        data-testid="payment-qris">
                        <input type="radio" wire:model.live="paymentMethod" value="qris" class="sr-only">
                        📱 QRIS
                    </label>
                </div>

                <textarea wire:model="note" rows="2" maxlength="200" placeholder="Catatan (opsional)"
                          class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm outline-none focus:border-brand resize-none"
                          data-testid="counter-note-input"></textarea>

                <button wire:click="save" wire:loading.attr="disabled"
                        @if(empty($cart)) disabled @endif
                        class="w-full rounded-full py-4 text-base font-extrabold shadow-lg transition
                        {{ empty($cart)
                            ? 'bg-slate-100 text-slate-400 cursor-not-allowed'
                            : 'bg-brand text-white shadow-brand/25 hover:bg-brand-dark' }}"
                        data-testid="counter-pay-button">
                    <span wire:loading.remove>
                        {{ empty($cart) ? 'Pilih menu dahulu' : 'Bayar ' . (empty($cartTotal) ? '' : number_format($cartTotal, 0, ',', '.')) }}
                    </span>
                    <span wire:loading>Menyimpan…</span>
                </button>
            </div>

        </aside>
    </div>
</div>

