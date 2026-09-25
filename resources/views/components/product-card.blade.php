@props(['product', 'showTenant' => true])
@php($sellable = $product->isSellable())
<article class="group flex flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-md" data-testid="product-card-{{ $product->id }}" wire:key="product-{{ $product->id }}">
    <div class="relative aspect-[4/3] overflow-hidden bg-slate-100">
        @if ($product->image_url)
            <img src="{{ $product->image_url }}" alt="{{ $product->name }}" loading="lazy" class="h-full w-full object-cover transition duration-300 group-hover:scale-105 {{ $sellable ? '' : 'grayscale' }}">
        @endif
        @unless ($sellable)
            <span class="absolute left-2 top-2 rounded-full bg-slate-900/80 px-2.5 py-1 text-[10px] font-bold text-white" data-testid="sold-out-badge">{{ $product->tenant?->canSell() ? 'Habis hari ini' : 'Tenant tutup' }}</span>
        @endunless
        <button type="button"
                wire:click="addToCart({{ $product->id }})"
                wire:loading.attr="disabled"
                wire:target="addToCart({{ $product->id }})"
                @disabled(! $sellable)
                class="absolute bottom-2 right-2 grid h-9 w-9 place-items-center rounded-full bg-brand text-white shadow-lg shadow-brand/40 transition hover:bg-brand-dark active:scale-90 disabled:cursor-not-allowed disabled:bg-slate-300 disabled:shadow-none"
                data-testid="add-product-{{ $product->id }}-button" aria-label="Tambah {{ $product->name }}">
            <x-icon name="plus" class="h-5 w-5" />
        </button>
    </div>
    <div class="flex flex-1 flex-col p-3">
        @if ($product->category)
            <span class="text-[10px] font-bold uppercase tracking-wider text-brand-dark">{{ $product->category->name }}</span>
        @endif
        <h3 class="mt-0.5 line-clamp-2 text-sm font-bold leading-snug">{{ $product->name }}</h3>
        @if ($showTenant)
            <p class="mt-0.5 truncate text-xs text-slate-500">{{ $product->tenant?->name }}</p>
        @endif
        <strong class="mt-auto pt-2 text-sm font-extrabold {{ $sellable ? 'text-slate-900' : 'text-slate-400 line-through' }}">@rupiah($product->price)</strong>
    </div>
</article>
