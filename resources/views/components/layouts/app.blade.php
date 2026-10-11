@props(['title' => null])
@php($user = auth()->user())
@php($nav = [
    ['route' => 'catalog', 'label' => 'Beranda', 'icon' => 'home', 'active' => request()->routeIs('catalog', 'tenant.menu')],
    ['route' => 'cart', 'label' => 'Keranjang', 'icon' => 'bag', 'active' => request()->routeIs('cart', 'checkout')],
    ['route' => 'orders', 'label' => 'Pesanan', 'icon' => 'list', 'active' => request()->routeIs('orders', 'orders.show', 'invoice.show')],
    ['route' => 'account', 'label' => 'Akun', 'icon' => 'user', 'active' => request()->routeIs('account')],
])
<x-layouts.base :title="$title">
    {{-- Top bar (desktop nav) --}}
    <header class="sticky top-0 z-30 border-b border-slate-200 bg-white/90 backdrop-blur">
        <div class="mx-auto flex h-16 max-w-6xl items-center gap-4 px-4">
            <a href="{{ route('catalog') }}" class="flex items-center gap-2" data-testid="brand-link">
                <span class="grid h-9 w-9 place-items-center rounded-xl bg-brand text-lg font-extrabold text-white">K</span>
                <span class="text-lg font-extrabold tracking-tight">Kantin<span class="text-brand">SMK</span> Go</span>
            </a>
            <div class="ml-auto flex items-center gap-1.5 md:hidden">
                <livewire:notifications-bell key="app-bell-mobile" />
                <x-sound-toggle data-testid="sound-toggle-mobile" />
                <form method="POST" action="{{ route('logout') }}" class="inline">
                    @csrf
                    <button type="submit" class="rounded-lg border border-slate-200 bg-white px-2.5 py-1 text-xs font-bold text-slate-600 active:bg-rose-50 active:text-rose-600" data-testid="logout-button-mobile" title="Keluar">
                        Keluar
                    </button>
                </form>
            </div>
            <nav class="ml-auto hidden items-center gap-1 md:flex">
                @foreach ($nav as $item)
                    <a href="{{ route($item['route']) }}" wire:navigate
                       class="relative flex items-center gap-2 rounded-full px-4 py-2 text-sm font-semibold transition {{ $item['active'] ? 'bg-brand-light text-brand-dark' : 'text-slate-600 hover:bg-slate-100' }}"
                       data-testid="desktop-nav-{{ $item['route'] }}">
                        <x-icon :name="$item['icon']" class="h-4 w-4" />
                        {{ $item['label'] }}
                        @if ($item['route'] === 'cart') <livewire:buyer.cart-badge /> @endif
                    </a>
                @endforeach
            </nav>
            <div class="hidden items-center gap-3 md:flex">
                <livewire:notifications-bell key="app-bell-desktop" />
                <x-sound-toggle />
                <div class="flex items-center gap-2 border-l border-slate-200 pl-3">
                    <span class="grid h-8 w-8 place-items-center rounded-full bg-accent/15 text-xs font-bold text-accent">{{ $user->initials() }}</span>
                    <span class="max-w-[130px] truncate text-xs font-bold text-slate-700">{{ $user->name }}</span>
                    <form method="POST" action="{{ route('logout') }}" class="inline ml-1">
                        @csrf
                        <button type="submit" class="flex items-center gap-1 rounded-lg border border-slate-200 bg-white px-2.5 py-1 text-xs font-bold text-slate-600 transition hover:border-rose-300 hover:bg-rose-50 hover:text-rose-600" data-testid="logout-button-desktop" title="Keluar dari akun">
                            <span>Keluar</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </header>

    <x-flash />

    <main class="mx-auto w-full max-w-6xl px-4 pb-28 pt-4 md:pb-12 md:pt-8">
        {{ $slot }}
    </main>

    {{-- Sticky bottom nav (mobile) --}}
    <nav class="safe-bottom fixed inset-x-0 bottom-0 z-30 border-t border-slate-200 bg-white md:hidden">
        <div class="grid grid-cols-4">
            @foreach ($nav as $item)
                <a href="{{ route($item['route']) }}" wire:navigate
                   class="relative flex flex-col items-center gap-1 py-2.5 text-[11px] font-semibold {{ $item['active'] ? 'text-brand' : 'text-slate-500' }}"
                   data-testid="bottom-nav-{{ $item['route'] }}">
                    <x-icon :name="$item['icon']" class="h-5 w-5" />
                    {{ $item['label'] }}
                    @if ($item['route'] === 'cart') <livewire:buyer.cart-badge position="absolute" /> @endif
                </a>
            @endforeach
        </div>
    </nav>
</x-layouts.base>
