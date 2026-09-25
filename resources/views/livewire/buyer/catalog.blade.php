<div>
    {{-- Sapaan --}}
    <div class="flex items-center justify-between gap-4">
        <div>
            <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-brand-dark">{{ now()->translatedFormat('l, d F Y') }}</p>
            <h1 class="mt-1 text-2xl font-extrabold tracking-tight md:text-3xl">Halo, {{ explode(' ', auth()->user()->name)[0] }} 👋</h1>
            <p class="text-sm text-slate-500">Mau jajan apa hari ini?</p>
        </div>
        <a href="{{ route('account') }}" wire:navigate class="grid h-11 w-11 place-items-center rounded-full bg-accent/15 text-sm font-bold text-accent md:hidden" data-testid="account-avatar">{{ auth()->user()->initials() }}</a>
    </div>

    {{-- Pencarian --}}
    <label class="mt-5 flex items-center gap-3 rounded-2xl border border-slate-200 bg-white px-4 py-3 shadow-sm focus-within:border-brand focus-within:ring-4 focus-within:ring-brand/20">
        <x-icon name="search" class="h-5 w-5 text-slate-400" />
        <input type="search" wire:model.live.debounce.300ms="search" placeholder="Cari menu atau tenant..." class="w-full bg-transparent text-sm outline-none" data-testid="catalog-search-input">
    </label>

    {{-- Kategori --}}
    <div class="no-scrollbar -mx-4 mt-4 flex gap-2 overflow-x-auto px-4">
        <button wire:click="$set('category', '')" class="shrink-0 rounded-full px-4 py-2 text-xs font-bold transition {{ $category === '' ? 'bg-brand text-white' : 'bg-white text-slate-600 ring-1 ring-slate-200' }}" data-testid="category-all-button">Semua</button>
        @foreach ($categories as $cat)
            <button wire:click="$set('category', '{{ $cat->slug }}')" class="shrink-0 rounded-full px-4 py-2 text-xs font-bold transition {{ $category === $cat->slug ? 'bg-brand text-white' : 'bg-white text-slate-600 ring-1 ring-slate-200' }}" data-testid="category-{{ $cat->slug }}-button">{{ $cat->name }}</button>
        @endforeach
    </div>

    {{-- Tenant --}}
    @if ($search === '' && $category === '')
        <section class="mt-8">
            <div class="mb-3 flex items-end justify-between">
                <div>
                    <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-400">Tenant kantin</p>
                    <h2 class="text-base font-extrabold md:text-lg">Pilih kantin</h2>
                </div>
            </div>
            <div class="no-scrollbar -mx-4 flex gap-3 overflow-x-auto px-4 pb-1">
                @foreach ($tenants as $tenant)
                    <a href="{{ route('tenant.menu', $tenant) }}" wire:navigate class="group relative w-44 shrink-0 overflow-hidden rounded-2xl bg-slate-900 text-white shadow-sm" data-testid="tenant-card-{{ $tenant->id }}">
                        <img src="{{ $tenant->image_url }}" alt="{{ $tenant->name }}" class="h-28 w-full object-cover opacity-80 transition group-hover:scale-105 group-hover:opacity-90">
                        <div class="absolute inset-0 bg-gradient-to-t from-slate-900 via-slate-900/30 to-transparent"></div>
                        <div class="absolute inset-x-3 bottom-3">
                            <div class="text-sm font-bold leading-tight">{{ $tenant->name }}</div>
                            <div class="mt-0.5 flex items-center gap-1 text-[11px] text-slate-300">
                                <span class="h-1.5 w-1.5 rounded-full {{ $tenant->is_open ? 'bg-emerald-400' : 'bg-rose-400' }}"></span>
                                {{ $tenant->is_open ? 'Buka' : 'Tutup' }} · {{ $tenant->products_count }} menu
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    {{-- Produk --}}
    <section class="mt-8">
        <div class="mb-3 flex items-end justify-between">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-400">Pilihan untukmu</p>
                <h2 class="text-base font-extrabold md:text-lg">Menu kantin</h2>
            </div>
            <span class="text-xs font-semibold text-slate-500" data-testid="result-count">{{ $products->count() }} menu</span>
        </div>

        @if ($products->isEmpty())
            <x-empty-state icon="search" title="Menu tidak ditemukan" text="Coba kata kunci lain atau pilih kategori berbeda." />
        @else
            <div class="grid grid-cols-2 gap-3 md:grid-cols-3 md:gap-5 lg:grid-cols-4">
                @foreach ($products as $product)
                    <x-product-card :product="$product" :key="$product->id" />
                @endforeach
            </div>
        @endif
    </section>
</div>
