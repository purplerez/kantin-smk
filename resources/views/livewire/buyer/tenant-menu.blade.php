<div>
    <x-page-header :title="$tenant->name" eyebrow="Tenant kantin" :back="route('catalog')" />

    <div class="relative overflow-hidden rounded-3xl bg-slate-900 text-white">
        <img src="{{ $tenant->image_url }}" alt="" class="h-44 w-full object-cover opacity-70 md:h-60">
        <div class="absolute inset-0 bg-gradient-to-t from-slate-900 via-slate-900/40 to-transparent"></div>
        <div class="absolute inset-x-5 bottom-5 flex flex-wrap items-end justify-between gap-3">
            <div>
                <h2 class="text-xl font-extrabold md:text-2xl">{{ $tenant->name }}</h2>
                <p class="mt-1 max-w-md text-sm text-slate-300">{{ $tenant->description }}</p>
            </div>
            <div class="flex items-center gap-2 text-xs font-bold">
                <span class="rounded-full px-3 py-1.5 {{ $tenant->is_open ? 'bg-brand text-white' : 'bg-rose-500 text-white' }}" data-testid="tenant-open-status">{{ $tenant->is_open ? 'Buka' : 'Tutup' }}</span>
                @if ($rating)
                    <span class="flex items-center gap-1 rounded-full bg-white/15 px-3 py-1.5" data-testid="tenant-rating"><x-icon name="star" class="h-3.5 w-3.5 fill-amber-400 text-amber-400" /> {{ $rating }}</span>
                @endif
            </div>
        </div>
    </div>

    <section class="mt-8">
        <h2 class="mb-3 text-base font-extrabold md:text-lg">Menu ({{ $products->count() }})</h2>
        @if ($products->isEmpty())
            <x-empty-state title="Belum ada menu" text="Tenant ini belum menambahkan menu." />
        @else
            <div class="grid grid-cols-2 gap-3 md:grid-cols-3 md:gap-5 lg:grid-cols-4">
                @foreach ($products as $product)
                    <x-product-card :product="$product->setRelation('tenant', $tenant)" :show-tenant="false" />
                @endforeach
            </div>
        @endif
    </section>

    @if ($reviews->isNotEmpty())
        <section class="mt-10">
            <h2 class="mb-3 text-base font-extrabold md:text-lg">Ulasan pembeli</h2>
            <div class="grid gap-3 md:grid-cols-2">
                @foreach ($reviews as $review)
                    <div class="rounded-2xl border border-slate-200 bg-white p-4" data-testid="review-{{ $review->id }}">
                        <div class="flex items-center justify-between">
                            <strong class="text-sm">{{ $review->buyer->name }}</strong>
                            <span class="flex text-amber-400">@for ($i = 1; $i <= 5; $i++)<x-icon name="star" class="h-3.5 w-3.5 {{ $i <= $review->rating ? 'fill-amber-400' : 'text-slate-300' }}" />@endfor</span>
                        </div>
                        @if ($review->comment) <p class="mt-2 text-sm text-slate-600">{{ $review->comment }}</p> @endif
                        <p class="mt-2 text-[11px] text-slate-400">{{ $review->created_at->diffForHumans() }}</p>
                    </div>
                @endforeach
            </div>
        </section>
    @endif
</div>
