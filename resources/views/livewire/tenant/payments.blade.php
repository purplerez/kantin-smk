<div>
    <x-page-header title="Pembayaran & settlement" eyebrow="Keuangan tenant" />

    @if ($pendingTransfers->isNotEmpty())
        <section class="mb-6 rounded-2xl border border-sky-200 bg-sky-50/60" data-testid="pending-transfers">
            <header class="border-b border-sky-100 px-4 py-3">
                <h2 class="text-base font-extrabold">Transfer menunggu verifikasi <span class="ml-1 rounded-full bg-sky-100 px-2 py-0.5 text-xs text-sky-800" data-testid="pending-transfer-count">{{ $pendingTransfers->count() }}</span></h2>
                <p class="text-xs text-slate-500">Cek mutasi rekening lalu tandai lunas. Hanya tenant admin / platform admin (backend policy).</p>
            </header>
            <ul class="divide-y divide-sky-100">
                @foreach ($pendingTransfers as $invoiceId => $orders)
                    @php($inv = $orders->first()->invoice)
                    <li class="flex items-center gap-3 px-4 py-3 text-sm" wire:key="transfer-{{ $invoiceId }}" data-testid="pending-transfer-{{ $invoiceId }}">
                        <div class="min-w-0 flex-1">
                            <strong class="block font-mono text-xs">{{ $inv->code }}</strong>
                            <span class="block truncate text-xs text-slate-500">{{ $orders->first()->buyer->name }} · VA {{ $inv->payment_reference }}</span>
                        </div>
                        <strong>@rupiah($orders->sum('subtotal'))</strong>
                        <button wire:click="confirmTransfer({{ $invoiceId }})" wire:confirm="Konfirmasi transfer sudah masuk?" class="rounded-full bg-brand px-3 py-1.5 text-xs font-bold text-white hover:bg-brand-dark" data-testid="verify-transfer-{{ $invoiceId }}">Verifikasi</button>
                    </li>
                @endforeach
            </ul>
        </section>
    @endif

    <div class="grid gap-6 lg:grid-cols-[1fr_1fr]">
        {{-- Verifikasi tunai --}}
        <section class="rounded-2xl border border-slate-200 bg-white">
            <header class="border-b border-slate-100 px-4 py-3">
                <h2 class="text-base font-extrabold">Menunggu verifikasi tunai <span class="ml-1 rounded-full bg-amber-100 px-2 py-0.5 text-xs text-amber-800" data-testid="pending-cash-count">{{ $pendingCash->count() }}</span></h2>
                <p class="text-xs text-slate-500">Tandai lunas saat pembeli membayar di kasir.</p>
            </header>
            <ul class="divide-y divide-slate-100">
                @forelse ($pendingCash as $order)
                    <li class="flex items-center gap-3 px-4 py-3 text-sm" data-testid="pending-cash-{{ $order->id }}" wire:key="cash-{{ $order->id }}">
                        <div class="min-w-0 flex-1">
                            <strong class="block font-mono text-xs">{{ $order->code }}</strong>
                            <span class="block truncate text-xs text-slate-500">{{ $order->buyer->name }} · {{ $order->invoice->payment_method->label() }} · {{ $order->created_at->translatedFormat('d M H:i') }}</span>
                        </div>
                        <strong>@rupiah($order->subtotal)</strong>
                        <button wire:click="markPaid({{ $order->id }})" wire:confirm="Konfirmasi pembayaran diterima?" class="rounded-full bg-brand px-3 py-1.5 text-xs font-bold text-white hover:bg-brand-dark" data-testid="verify-payment-{{ $order->id }}">Lunas</button>
                        <button wire:click="refund({{ $order->id }})" wire:confirm="Batalkan pesanan ini?" class="rounded-full px-2 py-1.5 text-xs font-bold text-rose-600 hover:bg-rose-50" data-testid="refund-{{ $order->id }}">Batal</button>
                    </li>
                @empty
                    <li class="px-4 py-10 text-center text-sm text-slate-500">Tidak ada pembayaran tunai yang menunggu.</li>
                @endforelse
            </ul>
        </section>

        {{-- Settlement harian --}}
        <section class="rounded-2xl border border-slate-200 bg-white">
            <header class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-100 px-4 py-3">
                <h2 class="text-base font-extrabold">Settlement harian</h2>
                <div class="flex items-center gap-1 text-xs">
                    <input type="date" wire:model.live="from" class="rounded-lg border border-slate-200 px-2 py-1" data-testid="settlement-from"> –
                    <input type="date" wire:model.live="to" class="rounded-lg border border-slate-200 px-2 py-1" data-testid="settlement-to">
                    <button wire:click="generateSettlement" class="ml-1 rounded-full bg-slate-900 px-3 py-1.5 font-bold text-white" data-testid="generate-settlement-button">Generate</button>
                </div>
            </header>
            @if ($settlement->isEmpty())
                <p class="px-4 py-10 text-center text-sm text-slate-500">Belum ada transaksi lunas pada rentang ini.</p>
            @else
                <ul class="divide-y divide-slate-100">
                    @foreach ($settlement as $day => $rows)
                        <li class="px-4 py-3" data-testid="settlement-day-{{ $day }}">
                            <div class="flex items-center justify-between">
                                <strong class="text-sm">{{ \Carbon\Carbon::parse($day)->translatedFormat('l, d M Y') }}</strong>
                                <strong class="text-sm text-brand-dark">@rupiah($rows->sum('amount'))</strong>
                            </div>
                            <ul class="mt-1 flex flex-wrap gap-2 text-[11px] text-slate-500">
                                @foreach ($rows as $row)
                                    <li class="rounded-full bg-slate-100 px-2 py-0.5">{{ \App\Enums\PaymentMethod::from($row->method)->label() }}: {{ $row->orders_count }} order · @rupiah($row->amount)</li>
                                @endforeach
                            </ul>
                        </li>
                    @endforeach
                </ul>
                <footer class="flex items-center justify-between border-t border-slate-200 px-4 py-3">
                    <span class="text-sm font-semibold">Total periode</span>
                    <strong class="text-lg font-extrabold" data-testid="settlement-total">@rupiah($settlement->flatten(1)->sum('amount'))</strong>
                </footer>
            @endif
        </section>
    </div>

    @if ($stored->isNotEmpty())
        <section class="mt-6 rounded-2xl border border-slate-200 bg-white" data-testid="stored-settlements">
            <header class="border-b border-slate-100 px-4 py-3"><h2 class="text-base font-extrabold">Settlement tersimpan</h2><p class="text-xs text-slate-500">Hasil perhitungan harian (tabel tenant_settlements).</p></header>
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500"><tr><th class="px-4 py-2">Tanggal</th><th class="px-4 py-2">Order</th><th class="px-4 py-2 text-right">Bruto</th><th class="px-4 py-2 text-right">Komisi</th><th class="px-4 py-2 text-right">Neto</th></tr></thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($stored as $s)
                        <tr wire:key="stored-{{ $s->id }}" data-testid="stored-settlement-{{ $s->date->toDateString() }}">
                            <td class="px-4 py-2">{{ $s->date->translatedFormat('d M Y') }}</td>
                            <td class="px-4 py-2">{{ $s->orders_count }}</td>
                            <td class="px-4 py-2 text-right">@rupiah($s->gross)</td>
                            <td class="px-4 py-2 text-right text-rose-600">@rupiah($s->commission)</td>
                            <td class="px-4 py-2 text-right font-bold text-brand-dark">@rupiah($s->net)</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </section>
    @endif

    @if ($refunds->isNotEmpty())
        <section class="mt-6 rounded-2xl border border-slate-200 bg-white">
            <header class="border-b border-slate-100 px-4 py-3"><h2 class="text-base font-extrabold">Refund terakhir</h2></header>
            <ul class="divide-y divide-slate-100 text-sm">
                @foreach ($refunds as $order)
                    <li class="flex items-center justify-between px-4 py-3" data-testid="refund-row-{{ $order->id }}">
                        <span><strong class="font-mono text-xs">{{ $order->code }}</strong> · {{ $order->buyer->name }} <em class="text-xs text-slate-500">— {{ $order->cancel_reason }}</em></span>
                        <span class="font-semibold text-rose-600">-@rupiah($order->subtotal)</span>
                    </li>
                @endforeach
            </ul>
        </section>
    @endif
</div>
