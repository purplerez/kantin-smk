<div class="mx-auto max-w-xl">
    <section class="rounded-3xl bg-slate-900 p-6 text-center text-white">
        <span class="mx-auto grid h-20 w-20 place-items-center rounded-full bg-accent text-2xl font-extrabold">{{ $user->initials() }}</span>
        <p class="mt-4 text-[11px] font-bold uppercase tracking-[0.18em] text-emerald-300">Akun aktif</p>
        <h1 class="mt-1 text-2xl font-extrabold" data-testid="account-name">{{ $user->name }}</h1>
        <p class="text-sm text-slate-300">{{ $user->email }}@if ($user->identifier) · {{ $user->identifier }}@endif</p>
    </section>

    <div class="mt-4 grid grid-cols-2 gap-3">
        <div class="rounded-2xl border border-slate-200 bg-white p-4"><p class="text-xs text-slate-500">Total pesanan</p><strong class="text-2xl font-extrabold" data-testid="account-total-orders">{{ $totalOrders }}</strong></div>
        <div class="rounded-2xl border border-slate-200 bg-white p-4"><p class="text-xs text-slate-500">Total belanja</p><strong class="text-2xl font-extrabold" data-testid="account-total-spent">@rupiah($totalSpent)</strong></div>
    </div>

    <ul class="mt-4 divide-y divide-slate-100 rounded-2xl border border-slate-200 bg-white text-sm">
        <li class="flex items-center gap-3 px-4 py-3"><x-icon name="shield" class="h-5 w-5 text-brand" /><span><strong class="block">Peran akses</strong><small class="text-slate-500">{{ $user->role->label() }}</small></span></li>
        <li class="flex items-center gap-3 px-4 py-3"><x-icon name="store" class="h-5 w-5 text-brand" /><span><strong class="block">Pickup sekolah</strong><small class="text-slate-500">Semua pesanan diambil langsung di tenant</small></span></li>
        <li class="flex items-center gap-3 px-4 py-3"><x-icon name="cash" class="h-5 w-5 text-brand" /><span><strong class="block">Pembayaran</strong><small class="text-slate-500">QRIS, transfer, tunai (mode demo)</small></span></li>
    </ul>

    <form method="POST" action="{{ route('logout') }}" class="mt-6">
        @csrf
        <button class="w-full rounded-full border border-slate-300 bg-white py-3 text-sm font-bold text-slate-700 hover:bg-slate-50" data-testid="logout-button">Keluar</button>
    </form>
</div>
