{{-- [JS #3] wire:poll: fallback polling (10 dtk tanpa Reverb, 60 dtk bila Reverb aktif) + [JS #5] push instan via Echo/Reverb --}}
<div wire:poll.{{ $pollSeconds }}s="tick" data-testid="orders-page" data-realtime="{{ \App\Services\Realtime::enabled() ? 'reverb' : 'poll' }}">
    <x-page-header title="Pesanan saya" eyebrow="Riwayat belanja">
        <x-slot:actions>
            <span class="flex items-center gap-1.5 rounded-full bg-white px-3 py-1.5 text-[11px] font-bold text-slate-600 ring-1 ring-slate-200"><span class="h-2 w-2 animate-pulse rounded-full bg-brand"></span> Live</span>
        </x-slot:actions>
    </x-page-header>

    <div class="mb-5 flex gap-2">
        <button wire:click="setTab('aktif')" class="rounded-full px-4 py-2 text-xs font-bold transition {{ $tab === 'aktif' ? 'bg-brand text-white' : 'bg-white text-slate-600 ring-1 ring-slate-200' }}" data-testid="tab-aktif">Aktif @if ($activeCount) <span class="ml-1 rounded-full bg-white/25 px-1.5">{{ $activeCount }}</span> @endif</button>
        <button wire:click="setTab('riwayat')" class="rounded-full px-4 py-2 text-xs font-bold transition {{ $tab === 'riwayat' ? 'bg-brand text-white' : 'bg-white text-slate-600 ring-1 ring-slate-200' }}" data-testid="tab-riwayat">Riwayat</button>
    </div>

    @if ($orders->isEmpty())
        <x-empty-state icon="list" title="Belum ada pesanan" text="Pesanan yang kamu buat akan tampil di sini.">
            <a href="{{ route('catalog') }}" wire:navigate class="mt-5 rounded-full bg-brand px-6 py-2.5 text-sm font-bold text-white">Mulai jajan</a>
        </x-empty-state>
    @else
        <div class="grid gap-3 md:grid-cols-2">
            @foreach ($orders as $order)
                <a href="{{ route('orders.show', $order) }}" wire:navigate class="block rounded-2xl border border-slate-200 bg-white p-4 transition hover:border-brand hover:shadow-sm" data-testid="order-card-{{ $order->id }}" wire:key="order-{{ $order->id }}">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <strong class="block truncate text-sm">{{ $order->tenant->name }}</strong>
                            <span class="text-xs text-slate-500">{{ $order->code }} · {{ $order->created_at->translatedFormat('d M, H:i') }}</span>
                        </div>
                        <x-status-badge :status="$order->status" />
                    </div>
                    <p class="mt-2 truncate text-xs text-slate-600">{{ $order->items->map(fn ($i) => "{$i->qty}× {$i->product_name}")->implode(', ') }}</p>
                    <div class="mt-3 flex items-center justify-between">
                        <strong class="text-sm font-extrabold">@rupiah($order->subtotal)</strong>
                        <span class="text-[11px] font-semibold {{ $order->isPaid() ? 'text-brand-dark' : 'text-amber-700' }}">{{ $order->invoice->payment_method->label() }} · {{ $order->payment_status->label() }}</span>
                    </div>
                    @if ($order->status === \App\Enums\OrderStatus::Pending)
                        <span class="mt-3 block rounded-xl bg-amber-50 px-3 py-2 text-center text-xs font-bold text-amber-800">Selesaikan pembayaran →</span>
                    @elseif ($order->status === \App\Enums\OrderStatus::Ready)
                        <span class="mt-3 block rounded-xl bg-brand-light px-3 py-2 text-center text-xs font-bold text-brand-dark">Pesanan siap, ambil sekarang!</span>
                    @endif
                </a>
            @endforeach
        </div>
        <div class="mt-6">{{ $orders->links() }}</div>
    @endif
</div>
