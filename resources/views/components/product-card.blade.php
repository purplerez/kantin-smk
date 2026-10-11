@props(['product', 'showTenant' => true, 'cartQty' => 0])
@php($sellable = $product->isSellable())
<article {{ $attributes->merge(['class' => 'group flex flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-md']) }} data-testid="product-card-{{ $product->id }}">
    <div class="relative aspect-[4/3] overflow-hidden bg-slate-100">
        @if ($product->image_url)
            <img src="{{ $product->image_url }}" alt="{{ $product->name }}" loading="lazy" class="h-full w-full object-cover transition duration-300 group-hover:scale-105 {{ $sellable ? '' : 'grayscale' }}">
        @else
            <div class="grid h-full w-full place-items-center bg-slate-100 text-slate-300">
                <x-icon name="store" class="h-10 w-10" />
            </div>
        @endif

        @unless ($sellable)
            <span class="absolute left-2 top-2 rounded-full bg-slate-900/80 px-2.5 py-1 text-[10px] font-bold text-white" data-testid="sold-out-badge">{{ $product->tenant?->canSell() ? 'Habis hari ini' : 'Tenant tutup' }}</span>
        @endunless

        @if ($cartQty > 0)
            <span class="absolute right-2 top-2 rounded-full bg-brand px-2.5 py-0.5 text-[11px] font-extrabold text-white shadow-md">
                {{ $cartQty }} di keranjang
            </span>
        @endif
    </div>

    <div class="flex flex-1 flex-col p-3.5">
        @if ($product->category)
            <span class="text-[10px] font-bold uppercase tracking-wider text-brand-dark">{{ $product->category->name }}</span>
        @endif
        <h3 class="mt-0.5 line-clamp-2 text-sm font-bold leading-snug text-slate-800">{{ $product->name }}</h3>
        @if ($showTenant)
            <p class="mt-0.5 truncate text-xs text-slate-500">{{ $product->tenant?->name }}</p>
        @endif

        <div class="mt-auto flex items-center justify-between gap-2 pt-3 border-t border-slate-100/80">
            <strong class="text-sm font-extrabold {{ $sellable ? 'text-slate-900' : 'text-slate-400 line-through' }}">@rupiah($product->price)</strong>

            @if (! $sellable)
                <span class="rounded-lg bg-slate-100 px-2.5 py-1 text-[11px] font-bold text-slate-400">Habis</span>
            @elseif ($cartQty > 0)
                <div class="flex items-center gap-1.5 rounded-xl border border-brand/30 bg-brand-light p-0.5">
                    <button type="button"
                            wire:click.stop="removeFromCart({{ $product->id }})"
                            wire:loading.attr="disabled"
                            wire:target="removeFromCart({{ $product->id }})"
                            class="grid h-7 w-7 place-items-center rounded-lg bg-white text-brand shadow-xs transition hover:bg-slate-100 active:scale-90"
                            title="Kurangi"
                            aria-label="Kurangi {{ $product->name }}">
                        <x-icon name="minus" class="h-3.5 w-3.5 stroke-[2.5]" />
                    </button>
                    <span class="min-w-[18px] text-center text-xs font-extrabold text-brand-dark">{{ $cartQty }}</span>
                    <button type="button"
                            wire:click.stop="addToCart({{ $product->id }})"
                            wire:loading.attr="disabled"
                            wire:target="addToCart({{ $product->id }})"
                            class="grid h-7 w-7 place-items-center rounded-lg bg-brand text-white shadow-xs transition hover:bg-brand-dark active:scale-90"
                            title="Tambah lagi"
                            aria-label="Tambah {{ $product->name }}">
                        <x-icon name="plus" class="h-3.5 w-3.5 stroke-[2.5]" />
                    </button>
                </div>
            @else
                <button type="button"
                        wire:click.stop="addToCart({{ $product->id }})"
                        wire:loading.attr="disabled"
                        wire:target="addToCart({{ $product->id }})"
                        class="flex items-center gap-1 rounded-xl bg-brand px-3 py-1.5 text-xs font-bold text-white shadow-sm shadow-brand/30 transition hover:bg-brand-dark active:scale-95 disabled:cursor-not-allowed disabled:bg-slate-300"
                        data-testid="add-product-{{ $product->id }}-button"
                        aria-label="Tambah {{ $product->name }}">
                    <x-icon name="plus" class="h-3.5 w-3.5 stroke-[2.5]" />
                    <span>Tambah</span>
                </button>
            @endif
        </div>
    </div>
</article>
