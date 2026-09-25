@props(['title' => null])
@php($user = auth()->user())
@php($nav = $user->isAdmin()
    ? [
        ['route' => 'admin.dashboard', 'label' => 'Dashboard', 'icon' => 'chart'],
        ['route' => 'admin.transactions', 'label' => 'Transaksi', 'icon' => 'list'],
        ['route' => 'admin.tenants', 'label' => 'Tenant', 'icon' => 'store'],
        ['route' => 'admin.users', 'label' => 'Pengguna', 'icon' => 'users'],
        ['route' => 'admin.settlements', 'label' => 'Settlement', 'icon' => 'cash'],
        ['route' => 'admin.audit', 'label' => 'Audit Log', 'icon' => 'shield'],
    ]
    : array_values(array_filter([
        $user->isTenantAdmin() ? ['route' => 'tenant.dashboard', 'label' => 'Dashboard', 'icon' => 'chart'] : null,
        ['route' => 'tenant.orders', 'label' => 'Pesanan', 'icon' => 'list'],
        $user->isTenantAdmin() ? ['route' => 'tenant.products', 'label' => 'Menu', 'icon' => 'store'] : null,
        $user->isTenantAdmin() ? ['route' => 'tenant.payments', 'label' => 'Pembayaran', 'icon' => 'cash'] : null,
    ])))
<x-layouts.base :title="$title">
    <div class="flex min-h-full">
        {{-- Sidebar desktop --}}
        <aside class="hidden w-64 shrink-0 flex-col border-r border-slate-200 bg-white md:flex">
            <div class="flex h-16 items-center gap-2 px-5">
                <span class="grid h-9 w-9 place-items-center rounded-xl bg-brand text-lg font-extrabold text-white">K</span>
                <div class="leading-tight">
                    <div class="text-base font-extrabold">Kantin<span class="text-brand">SMK</span> Go</div>
                    <div class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">{{ $user->isAdmin() ? 'Platform Control' : $user->tenant?->name }}</div>
                </div>
            </div>
            <nav class="flex-1 space-y-1 px-3 py-4">
                @foreach ($nav as $item)
                    <a href="{{ route($item['route']) }}" wire:navigate
                       class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold transition {{ request()->routeIs($item['route']) ? 'bg-brand-light text-brand-dark' : 'text-slate-600 hover:bg-slate-100' }}"
                       data-testid="side-nav-{{ $item['route'] }}">
                        <x-icon :name="$item['icon']" class="h-5 w-5" /> {{ $item['label'] }}
                    </a>
                @endforeach
            </nav>
            <div class="border-t border-slate-200 p-4">
                <div class="flex items-center gap-3">
                    <span class="grid h-10 w-10 place-items-center rounded-full bg-accent/15 text-sm font-bold text-accent">{{ $user->initials() }}</span>
                    <div class="min-w-0 flex-1 leading-tight">
                        <div class="truncate text-sm font-bold">{{ $user->name }}</div>
                        <div class="text-xs text-slate-500">{{ $user->role->label() }}</div>
                    </div>
                    <livewire:notifications-bell />
                    <x-sound-toggle />
                </div>
                <form method="POST" action="{{ route('logout') }}" class="mt-3">
                    @csrf
                    <button class="w-full rounded-xl border border-slate-200 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-50" data-testid="logout-button">Keluar</button>
                </form>
            </div>
        </aside>

        <div class="flex min-w-0 flex-1 flex-col">
            {{-- Top bar mobile --}}
            <header class="sticky top-0 z-30 flex h-14 items-center gap-3 border-b border-slate-200 bg-white px-4 md:hidden">
                <span class="grid h-8 w-8 place-items-center rounded-lg bg-brand text-base font-extrabold text-white">K</span>
                <div class="flex-1 truncate text-sm font-bold">{{ $user->isAdmin() ? 'Platform Control' : $user->tenant?->name }}</div>
                <livewire:notifications-bell />
                <x-sound-toggle data-testid="sound-toggle-mobile" />
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="text-xs font-semibold text-slate-500" data-testid="logout-button-mobile">Keluar</button>
                </form>
            </header>

            <x-flash />

            <main class="mx-auto w-full max-w-6xl flex-1 px-4 pb-28 pt-4 md:px-8 md:pb-10 md:pt-8">
                {{ $slot }}
            </main>

            {{-- Bottom nav mobile --}}
            <nav class="safe-bottom fixed inset-x-0 bottom-0 z-30 border-t border-slate-200 bg-white md:hidden">
                <div class="grid" style="grid-template-columns: repeat({{ count($nav) }}, minmax(0, 1fr));">
                    @foreach ($nav as $item)
                        <a href="{{ route($item['route']) }}" wire:navigate
                           class="flex flex-col items-center gap-1 py-2.5 text-[11px] font-semibold {{ request()->routeIs($item['route']) ? 'text-brand' : 'text-slate-500' }}"
                           data-testid="bottom-nav-{{ $item['route'] }}">
                            <x-icon :name="$item['icon']" class="h-5 w-5" /> {{ $item['label'] }}
                        </a>
                    @endforeach
                </div>
            </nav>
        </div>
    </div>
</x-layouts.base>
