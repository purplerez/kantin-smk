<div>
    <x-page-header title="Konfirmasi pesanan" eyebrow="Langkah terakhir" :back="route('cart')" />

    <form wire:submit="placeOrder" class="grid gap-6 lg:grid-cols-[1fr_380px]" data-testid="checkout-form">
        <div class="space-y-5">
            <div class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-xs text-amber-800" data-testid="demo-alert">
                <strong>DEMO PAYMENT</strong> — Gateway pembayaran belum terhubung. Pilihan di bawah hanya simulasi untuk alur pesanan.
            </div>

            <section class="rounded-2xl border border-slate-200 bg-white p-4">
                <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-400">Ambil di</p>
                <div class="mt-2 flex items-center gap-3">
                    <span class="grid h-10 w-10 place-items-center rounded-xl bg-brand-light text-brand"><x-icon name="store" class="h-5 w-5" /></span>
                    <div>
                        <strong class="block text-sm">{{ $groups->map(fn ($l) => $l->first()['product']->tenant->name)->implode(' & ') }}</strong>
                        <span class="text-xs text-slate-500">Area kantin sekolah · Pickup langsung</span>
                    </div>
                </div>
            </section>

            <section class="rounded-2xl border border-slate-200 bg-white p-4">
                <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-400">Metode pembayaran</p>
                <div class="mt-3 space-y-2">
                    @foreach ($methods as $method)
                        <label class="flex cursor-pointer items-center gap-3 rounded-xl border p-3 transition {{ $paymentMethod === $method->value ? 'border-brand bg-brand-light/60 ring-2 ring-brand/30' : 'border-slate-200 hover:border-slate-300' }}" data-testid="payment-{{ $method->value }}-option">
                            <input type="radio" wire:model.live="paymentMethod" value="{{ $method->value }}" class="h-4 w-4 accent-brand">
                            <span class="grid h-9 w-9 place-items-center rounded-lg bg-slate-100 text-slate-700"><x-icon :name="$method === \App\Enums\PaymentMethod::Qris ? 'qr' : ($method === \App\Enums\PaymentMethod::Cash ? 'cash' : 'list')" class="h-5 w-5" /></span>
                            <span class="flex-1">
                                <strong class="block text-sm">{{ $method->label() }}</strong>
                                <small class="text-xs text-slate-500">{{ $method->description() }}</small>
                            </span>
                        </label>
                    @endforeach
                </div>
                @error('paymentMethod') <p class="mt-2 text-xs text-rose-600">{{ $message }}</p> @enderror
            </section>

            <section class="rounded-2xl border border-slate-200 bg-white p-4">
                <label class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-400">Catatan untuk tenant (opsional)
                    <textarea wire:model="note" rows="2" maxlength="200" placeholder="Contoh: ambil jam istirahat kedua" class="mt-2 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm normal-case tracking-normal outline-none focus:border-brand" data-testid="checkout-note-input"></textarea>
                </label>
                @error('note') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            </section>
        </div>

        <aside class="space-y-4 lg:sticky lg:top-24 lg:self-start">
            <section class="rounded-2xl border border-slate-200 bg-white p-4">
                <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-400">Ringkasan</p>
                @foreach ($groups as $tenantId => $lines)
                    <div class="mt-3 border-t border-slate-100 pt-3 first:border-0" wire:key="sum-{{ $tenantId }}">
                        <strong class="text-xs text-brand-dark">{{ $lines->first()['product']->tenant->name }}</strong>
                        <ul class="mt-1 space-y-1 text-sm">
                            @foreach ($lines as $line)
                                <li class="flex justify-between gap-3"><span class="text-slate-600">{{ $line['qty'] }}× {{ $line['product']->name }}</span><span>@rupiah($line['line_total'])</span></li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach
                <div class="mt-4 flex items-center justify-between border-t border-slate-200 pt-3">
                    <span class="text-sm font-semibold">Total bayar</span>
                    <strong class="text-xl font-extrabold" data-testid="checkout-total">@rupiah($total)</strong>
                </div>
            </section>

            @error('cart') <p class="rounded-xl bg-rose-50 px-4 py-3 text-xs font-semibold text-rose-700" data-testid="checkout-error">{{ $message }}</p> @enderror

            <button type="submit" wire:loading.attr="disabled" class="w-full rounded-full bg-brand py-3.5 text-sm font-bold text-white shadow-lg shadow-brand/30 transition hover:bg-brand-dark disabled:opacity-60" data-testid="confirm-order-button">
                <span wire:loading.remove wire:target="placeOrder">Buat pesanan</span>
                <span wire:loading wire:target="placeOrder">Memproses...</span>
            </button>
        </aside>
    </form>
</div>
