<div class="mx-auto max-w-xl pb-12" data-testid="koperasi-spending-page">
    {{-- Header --}}
    <div class="mb-4 flex items-center justify-between gap-3">
        <a href="{{ route('account') }}" wire:navigate class="inline-flex items-center gap-1.5 rounded-full border border-slate-200 bg-white px-3 py-1.5 text-xs font-bold text-slate-700 transition hover:bg-slate-50" data-testid="back-to-account">
            <x-icon name="back" class="h-4 w-4" />
            <span>Kembali</span>
        </a>
        <div class="text-right">
            <span class="inline-flex items-center gap-1 rounded-full bg-brand/10 px-2.5 py-0.5 text-[11px] font-bold text-brand-dark">
                <x-icon name="store" class="h-3.5 w-3.5" />
                {{ $koperasi->name }}
            </span>
        </div>
    </div>

    {{-- Month Navigator --}}
    <div class="mb-5 flex items-center justify-between rounded-2xl border border-slate-200 bg-white p-2.5 shadow-sm">
        <button type="button" wire:click="previousMonth" class="grid h-9 w-9 place-items-center rounded-xl text-slate-600 transition hover:bg-slate-100 active:scale-95" title="Bulan Sebelumnya" data-testid="prev-month-button">
            <x-icon name="back" class="h-5 w-5" />
        </button>

        <div class="text-center">
            <strong class="block text-sm font-extrabold text-slate-800" data-testid="current-month-label">{{ $monthLabel }}</strong>
            @if (! $isCurrentMonth)
                <button type="button" wire:click="currentMonth" class="text-[11px] font-semibold text-brand hover:underline" data-testid="goto-current-month">
                    Kembali ke Bulan Ini
                </button>
            @endif
        </div>

        <button type="button" wire:click="nextMonth" class="grid h-9 w-9 place-items-center rounded-xl text-slate-600 transition hover:bg-slate-100 active:scale-95" title="Bulan Berikutnya" data-testid="next-month-button">
            <x-icon name="forward" class="h-5 w-5" />
        </button>
    </div>

    {{-- Closed Month Notice --}}
    @if ($summary['is_closed'])
        <div class="mb-5 flex items-start gap-3 rounded-2xl border border-slate-300 bg-slate-100 p-4 text-xs text-slate-700" data-testid="closed-month-notice">
            <x-icon name="shield" class="mt-0.5 h-4 w-4 shrink-0 text-slate-500" />
            <div>
                <strong class="block font-bold">Periode Buku Ditutup</strong>
                <span>Rekapitulasi belanja untuk bulan ini telah dikunci pada {{ $summary['closed_at']?->setTimezone('Asia/Jakarta')->translatedFormat('d F Y H:i') }} WIB.</span>
            </div>
        </div>
    @endif

    {{-- Summary Hero Card --}}
    <section class="overflow-hidden rounded-3xl bg-slate-900 p-6 text-white shadow-lg">
        <div class="flex items-center justify-between gap-2">
            <div class="flex items-center gap-2">
                <span class="grid h-8 w-8 place-items-center rounded-xl bg-white/10 text-brand-light">
                    <x-icon name="chart" class="h-4 w-4" />
                </span>
                <span class="text-xs font-bold text-slate-300 uppercase tracking-wider">Rekap Belanja Koperasi</span>
            </div>

            @if ($summary['has_target'])
                <span class="rounded-full px-2.5 py-0.5 text-[11px] font-bold {{ $summary['is_met'] ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-400/30' : 'bg-amber-500/20 text-amber-300 border border-amber-400/30' }}" data-testid="status-target-badge">
                    {{ $summary['is_met'] ? 'Target Terpenuhi' : 'Belum Terpenuhi' }}
                </span>
            @else
                <span class="rounded-full bg-sky-500/20 border border-sky-400/30 px-2.5 py-0.5 text-[11px] font-bold text-sky-300" data-testid="non-employee-badge">
                    Bebas Target
                </span>
            @endif
        </div>

        <div class="mt-4">
            <p class="text-xs font-semibold text-slate-400">Total Belanja Terverifikasi</p>
            <h1 class="text-3xl font-black tracking-tight text-white" data-testid="total-verified-amount">
                @rupiah($summary['total_verified'])
            </h1>
        </div>

        @if ($summary['has_target'])
            @php($pct = min(100, round(($summary['total_verified'] / max(1, $summary['target'])) * 100)))
            {{-- Progress bar --}}
            <div class="mt-5">
                <div class="mb-1.5 flex justify-between text-xs font-bold">
                    <span class="text-slate-300">Jabatan: <span class="text-white">{{ $summary['position_name'] }}</span></span>
                    <span class="{{ $summary['is_met'] ? 'text-emerald-400' : 'text-amber-400' }}">{{ $pct }}%</span>
                </div>
                <div class="h-2.5 w-full overflow-hidden rounded-full bg-slate-800">
                    <div class="h-full rounded-full transition-all duration-500 {{ $summary['is_met'] ? 'bg-emerald-500' : 'bg-brand' }}" style="width: {{ $pct }}%"></div>
                </div>
            </div>

            <div class="mt-5 grid grid-cols-2 gap-3 border-t border-slate-800 pt-4 text-center">
                <div class="rounded-2xl bg-white/5 p-3">
                    <p class="text-[11px] font-semibold text-slate-400">Target Bulanan</p>
                    <strong class="mt-0.5 block text-base font-extrabold text-white" data-testid="target-amount">@rupiah($summary['target'])</strong>
                </div>
                <div class="rounded-2xl bg-white/5 p-3">
                    <p class="text-[11px] font-semibold text-slate-400">Sisa Kekurangan</p>
                    <strong class="mt-0.5 block text-base font-extrabold {{ $summary['is_met'] ? 'text-emerald-400' : 'text-amber-400' }}" data-testid="remaining-amount">
                        @if ($summary['is_met'])
                            Rp0
                        @else
                            @rupiah($summary['remaining'])
                        @endif
                    </strong>
                </div>
            </div>
        @else
            <div class="mt-4 rounded-2xl bg-white/5 p-3 text-xs text-slate-300">
                <span class="font-bold text-white block mb-0.5">{{ $summary['position_name'] }}</span>
                <span>Akun Anda terdaftar sebagai siswa atau pembeli umum, sehingga tidak dikenakan kewajiban target belanja bulanan.</span>
            </div>
        @endif
    </section>

    {{-- Pending Verification Alert --}}
    @if ($summary['pending_verification'] > 0)
        <div class="mt-4 flex items-start gap-3 rounded-2xl border border-amber-200 bg-amber-50 p-4 text-amber-900 shadow-sm" data-testid="pending-verification-alert">
            <x-icon name="clock" class="mt-0.5 h-5 w-5 shrink-0 text-amber-600" />
            <div>
                <strong class="block text-sm font-extrabold">Menunggu Verifikasi: @rupiah($summary['pending_verification'])</strong>
                <p class="mt-0.5 text-xs text-amber-700 leading-relaxed">
                    Terdapat {{ $summary['pending_orders']->count() }} pesanan yang pembayarannya belum diverifikasi oleh petugas koperasi. Nominal ini belum masuk ke total resmi.
                </p>
            </div>
        </div>
    @endif

    {{-- Transaction History --}}
    <div class="mt-6">
        <h2 class="text-sm font-extrabold text-slate-800 uppercase tracking-wider mb-3">
            Riwayat Transaksi ({{ $summary['orders']->count() + $summary['adjustments']->count() }})
        </h2>

        @if ($summary['orders']->isEmpty() && $summary['adjustments']->isEmpty() && $summary['pending_orders']->isEmpty())
            <div class="rounded-3xl border border-slate-200 bg-white p-8 text-center" data-testid="empty-transactions">
                <span class="mx-auto grid h-12 w-12 place-items-center rounded-2xl bg-slate-100 text-slate-400">
                    <x-icon name="store" class="h-6 w-6" />
                </span>
                <strong class="mt-3 block text-sm font-bold text-slate-700">Belum Ada Belanja</strong>
                <p class="mt-1 text-xs text-slate-500">Tidak ada transaksi di koperasi untuk periode {{ $monthLabel }}.</p>
            </div>
        @else
            <div class="space-y-3">
                {{-- Orders Lunas --}}
                @foreach ($summary['orders'] as $order)
                    <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm" data-testid="order-row-{{ $order->id }}">
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex items-start gap-3">
                                <span class="grid h-9 w-9 shrink-0 place-items-center rounded-xl {{ $order->source === 'counter' ? 'bg-emerald-50 text-emerald-600' : 'bg-brand-light text-brand-dark' }}">
                                    @if ($order->source === 'counter')
                                        <x-icon name="cash" class="h-4 w-4" />
                                    @else
                                        <x-icon name="bag" class="h-4 w-4" />
                                    @endif
                                </span>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <strong class="text-sm font-bold text-slate-800">
                                            {{ $order->source === 'counter' ? 'Belanja Kasir Langsung' : $order->code }}
                                        </strong>
                                        <span class="rounded-md px-1.5 py-0.5 text-[10px] font-bold uppercase {{ $order->source === 'counter' ? 'bg-emerald-100 text-emerald-800' : 'bg-brand/10 text-brand-dark' }}">
                                            {{ $order->source === 'counter' ? 'Kasir' : 'App' }}
                                        </span>
                                    </div>
                                    <p class="text-xs text-slate-500 mt-0.5">
                                        {{ $order->paid_at?->setTimezone('Asia/Jakarta')->translatedFormat('d M Y, H:i') }} WIB
                                        @if ($order->enteredBy)
                                            · Dicatat oleh {{ $order->enteredBy->name }}
                                        @endif
                                    </p>
                                </div>
                            </div>
                            <strong class="text-sm font-extrabold text-slate-900 shrink-0">
                                @rupiah($order->subtotal)
                            </strong>
                        </div>

                        {{-- Rincian item jika ada --}}
                        @if ($order->items->isNotEmpty() && $order->source === 'app')
                            <div class="mt-3 border-t border-slate-100 pt-2 text-xs text-slate-600">
                                <ul class="divide-y divide-slate-50">
                                    @foreach ($order->items as $item)
                                        <li class="flex justify-between py-1">
                                            <span>{{ $item->qty }}x {{ $item->product_name }}</span>
                                            <span class="font-semibold text-slate-700">@rupiah($item->line_total)</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </div>
                @endforeach

                {{-- Penyesuaian (Adjustments) --}}
                @foreach ($summary['adjustments'] as $adj)
                    <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm" data-testid="adj-row-{{ $adj->id }}">
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex items-start gap-3">
                                <span class="grid h-9 w-9 shrink-0 place-items-center rounded-xl {{ $adj->amount >= 0 ? 'bg-sky-50 text-sky-600' : 'bg-rose-50 text-rose-600' }}">
                                    <x-icon name="chart" class="h-4 w-4" />
                                </span>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <strong class="text-sm font-bold text-slate-800">{{ $adj->reason }}</strong>
                                        <span class="rounded-md bg-slate-100 px-1.5 py-0.5 text-[10px] font-bold text-slate-600 uppercase">
                                            Koreksi
                                        </span>
                                    </div>
                                    <p class="text-xs text-slate-500 mt-0.5">
                                        {{ $adj->created_at?->setTimezone('Asia/Jakarta')->translatedFormat('d M Y, H:i') }} WIB
                                    </p>
                                </div>
                            </div>
                            <strong class="text-sm font-extrabold {{ $adj->amount >= 0 ? 'text-sky-600' : 'text-rose-600' }} shrink-0">
                                {{ $adj->amount >= 0 ? '+' : '' }}@rupiah($adj->amount)
                            </strong>
                        </div>
                    </div>
                @endforeach

                {{-- Pending Orders --}}
                @if ($summary['pending_orders']->isNotEmpty())
                    <div class="pt-4">
                        <h3 class="text-xs font-bold text-amber-800 uppercase tracking-wider mb-2">
                            Pesanan Belum Diverifikasi ({{ $summary['pending_orders']->count() }})
                        </h3>
                        <div class="space-y-2">
                            @foreach ($summary['pending_orders'] as $pOrder)
                                <div class="rounded-2xl border border-dashed border-amber-300 bg-amber-50/50 p-3.5" data-testid="pending-order-{{ $pOrder->id }}">
                                    <div class="flex items-center justify-between gap-2">
                                        <div>
                                            <strong class="text-xs font-bold text-amber-950">{{ $pOrder->code }}</strong>
                                            <p class="text-[11px] text-amber-700 mt-0.5">
                                                {{ $pOrder->created_at?->setTimezone('Asia/Jakarta')->translatedFormat('d M Y, H:i') }} WIB
                                            </p>
                                        </div>
                                        <div class="text-right">
                                            <strong class="text-xs font-extrabold text-amber-900">@rupiah($pOrder->subtotal)</strong>
                                            <span class="block text-[10px] font-semibold text-amber-600">Menunggu</span>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        @endif
    </div>
</div>

