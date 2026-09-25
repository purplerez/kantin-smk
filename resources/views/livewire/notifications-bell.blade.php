<div class="relative" x-data="{ open: false }" wire:poll.15s data-testid="notifications-bell">
    <button type="button" @click="open = !open" class="relative grid h-9 w-9 place-items-center rounded-full text-slate-500 hover:bg-slate-100" data-testid="notifications-toggle" aria-label="Notifikasi">
        <x-icon name="bell" class="h-5 w-5" />
        @if ($unread > 0)
            <span class="absolute -right-0.5 -top-0.5 grid h-4 min-w-4 place-items-center rounded-full bg-rose-500 px-1 text-[10px] font-bold text-white" data-testid="notifications-unread-count">{{ $unread > 9 ? '9+' : $unread }}</span>
        @endif
    </button>

    <div x-show="open" x-cloak @click.outside="open = false" x-transition
         class="absolute right-0 z-50 mt-2 w-80 max-w-[90vw] overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-xl"
         data-testid="notifications-panel">
        <div class="flex items-center justify-between border-b border-slate-100 px-4 py-2.5">
            <strong class="text-sm">Notifikasi</strong>
            @if ($unread > 0)
                <button wire:click="markAllRead" class="text-xs font-semibold text-brand-dark hover:underline" data-testid="notifications-mark-all">Tandai dibaca</button>
            @endif
        </div>
        <ul class="max-h-96 divide-y divide-slate-100 overflow-y-auto">
            @forelse ($items as $n)
                <li wire:key="notif-{{ $n->id }}" wire:click="markRead({{ $n->id }})"
                    class="cursor-pointer px-4 py-3 text-sm hover:bg-slate-50 {{ $n->isUnread() ? 'bg-brand-light/40' : '' }}"
                    data-testid="notification-{{ $n->id }}">
                    <div class="flex items-start gap-2">
                        @if ($n->isUnread())<span class="mt-1.5 h-2 w-2 shrink-0 rounded-full bg-brand"></span>@else<span class="mt-1.5 h-2 w-2 shrink-0"></span>@endif
                        <div class="min-w-0">
                            <p class="font-semibold">
                                @switch($n->type)
                                    @case('order.new') Pesanan baru masuk @break
                                    @case('order.status') Status pesanan diperbarui @break
                                    @case('order.cancelled') Pesanan dibatalkan @break
                                    @case('payment.received') Pembayaran diterima @break
                                    @default {{ $n->type }}
                                @endswitch
                            </p>
                            <p class="text-xs text-slate-500">
                                @if(isset($n->payload['order'])){{ $n->payload['order'] }} @endif
                                @if(isset($n->payload['status']))· {{ $n->payload['status'] }}@endif
                                @if(isset($n->payload['buyer']))· {{ $n->payload['buyer'] }}@endif
                                @if(isset($n->payload['amount']))· Rp{{ number_format((int) $n->payload['amount'], 0, ',', '.') }}@endif
                            </p>
                            <p class="mt-0.5 text-[11px] text-slate-400">{{ $n->created_at?->diffForHumans() }}</p>
                        </div>
                    </div>
                </li>
            @empty
                <li class="px-4 py-10 text-center text-sm text-slate-400" data-testid="notifications-empty">Belum ada notifikasi.</li>
            @endforelse
        </ul>
    </div>
</div>
