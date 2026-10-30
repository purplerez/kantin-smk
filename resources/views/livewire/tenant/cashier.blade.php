<div class="space-y-6" data-testid="koperasi-cashier-page">
    {{-- Header --}}
    <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Kasir Cepat Koperasi</h1>
            <p class="text-xs text-slate-500">Pencatatan transaksi langsung di konter fisik koperasi (Jalur B) & verifikasi pesanan app.</p>
        </div>
        <div class="flex items-center gap-2">
            <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 border border-emerald-200 px-3 py-1 text-xs font-bold text-emerald-800">
                <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                Mode Kasir Aktif
            </span>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-12">
        {{-- Sisi Kiri: Form Transaksi Kasir --}}
        <div class="space-y-6 lg:col-span-7">
            {{-- 1. Identifikasi Pembeli --}}
            <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between mb-3">
                    <label class="block text-xs font-extrabold uppercase tracking-wider text-slate-700">
                        1. Pilih Pelanggan / Pegawai
                    </label>
                    @if (! $selectedUser)
                        <button type="button" wire:click="selectGuest" class="inline-flex items-center gap-1 text-xs font-bold text-brand hover:underline" data-testid="select-guest-btn">
                            <x-icon name="user" class="h-3.5 w-3.5" />
                            + Pelanggan Umum (Walk-in)
                        </button>
                    @endif
                </div>

                @if ($selectedUser)
                    {{-- Pelanggan Terpilih --}}
                    <div class="flex items-center justify-between rounded-2xl border border-brand/30 bg-brand/5 p-3.5" data-testid="selected-user-card">
                        <div class="flex items-center gap-3">
                            <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-brand text-sm font-black text-white">
                                {{ $selectedUser->initials() }}
                            </span>
                            <div>
                                <strong class="block text-sm font-bold text-slate-900">{{ $selectedUser->name }}</strong>
                                <div class="flex flex-wrap items-center gap-2 text-xs text-slate-600 mt-0.5">
                                    @if ($selectedUser->identifier)
                                        <span class="font-mono">{{ $selectedUser->identifier }}</span>
                                        <span>·</span>
                                    @endif
                                    @if ($selectedUser->position)
                                        <span class="font-bold text-brand-dark">{{ $selectedUser->position->name }}</span>
                                    @else
                                        <span class="text-slate-500">Siswa / Non-Pegawai</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <button type="button" wire:click="clearSelectedUser" class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-200 hover:text-slate-700 transition" title="Ganti Pelanggan" data-testid="clear-user-btn">
                            <x-icon name="x" class="h-5 w-5" />
                        </button>
                    </div>
                @else
                    {{-- Input Pencarian --}}
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-slate-400">
                            <x-icon name="search" class="h-4 w-4" />
                        </span>
                        <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari nama, NIP, atau NIS..." class="w-full rounded-2xl border border-slate-200 bg-slate-50 pl-10 pr-4 py-2.5 text-sm font-medium text-slate-800 placeholder-slate-400 focus:bg-white focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/20 transition" data-testid="customer-search-input" />
                    </div>

                    {{-- Hasil Pencarian Dropdown --}}
                    @if ($searchResults->isNotEmpty())
                        <div class="mt-2 divide-y divide-slate-100 rounded-2xl border border-slate-200 bg-white shadow-lg overflow-hidden" data-testid="search-results-list">
                            @foreach ($searchResults as $u)
                                <button type="button" wire:click="selectUser({{ $u->id }})" class="w-full flex items-center justify-between p-3 text-left transition hover:bg-brand/5" data-testid="user-option-{{ $u->id }}">
                                    <div>
                                        <strong class="block text-sm font-bold text-slate-800">{{ $u->name }}</strong>
                                        <span class="text-xs text-slate-500">
                                            {{ $u->identifier ?: 'Tanpa NIP/NIS' }}
                                            @if ($u->position) · <b class="text-brand">{{ $u->position->name }}</b> @endif
                                        </span>
                                    </div>
                                    <span class="text-xs font-bold text-brand">Pilih</span>
                                </button>
                            @endforeach
                        </div>
                    @elseif (strlen(trim($search)) >= 2)
                        <div class="mt-2 rounded-xl border border-dashed border-slate-200 p-3 text-center text-xs text-slate-500">
                            Tidak ditemukan pegawai/siswa dengan kata kunci "{{ $search }}".
                        </div>
                    @endif
                @endif
            </div>

            {{-- 2. Deteksi Pesanan App Terbuka --}}
            @if ($openAppOrders->isNotEmpty())
                <div class="rounded-3xl border-2 border-amber-400 bg-amber-50/80 p-5 shadow-sm" data-testid="open-app-orders-warning">
                    <div class="flex items-start gap-3">
                        <span class="grid h-8 w-8 shrink-0 place-items-center rounded-xl bg-amber-500 text-white">
                            <x-icon name="bell" class="h-4 w-4" />
                        </span>
                        <div class="flex-1">
                            <strong class="block text-sm font-extrabold text-amber-950">Pesanan Aplikasi Terbuka Terdeteksi!</strong>
                            <p class="text-xs text-amber-800 mt-0.5 leading-relaxed">
                                Pelanggan ini memiliki pesanan di aplikasi yang belum dibayar. Jika pelanggan melunasi pesanan tersebut, <b>verifikasi langsung pesanan di bawah</b> agar tidak tercatat ganda.
                            </p>
                        </div>
                    </div>

                    <div class="mt-3 space-y-2">
                        @foreach ($openAppOrders as $appOrder)
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 rounded-2xl bg-white p-3.5 border border-amber-200 shadow-xs" data-testid="open-app-order-{{ $appOrder->id }}">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <strong class="text-sm font-bold text-slate-800">{{ $appOrder->code }}</strong>
                                        <span class="rounded bg-amber-100 px-1.5 py-0.5 text-[10px] font-bold text-amber-800 uppercase">Menunggu Bayar</span>
                                    </div>
                                    <p class="text-xs text-slate-500 mt-0.5">
                                        @foreach ($appOrder->items as $it)
                                            {{ $it->qty }}x {{ $it->product_name }}@if (! $loop->last), @endif
                                        @endforeach
                                    </p>
                                </div>
                                <div class="flex items-center justify-between sm:justify-end gap-3 pt-2 sm:pt-0 border-t sm:border-t-0 border-slate-100">
                                    <strong class="text-sm font-extrabold text-slate-900">@rupiah($appOrder->subtotal)</strong>
                                    <button type="button" wire:click="verifyAppOrder({{ $appOrder->id }})" class="rounded-xl bg-emerald-600 hover:bg-emerald-700 px-3 py-1.5 text-xs font-bold text-white transition active:scale-95" data-testid="mark-paid-btn-{{ $appOrder->id }}">
                                        Verifikasi Lunas
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- 3. Form Entri Kasir Baru --}}
            <form wire:submit="save" class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm space-y-4" data-testid="cashier-entry-form">
                <label class="block text-xs font-extrabold uppercase tracking-wider text-slate-700">
                    2. Input Nominal Belanja Kasir
                </label>

                {{-- Nominal --}}
                <div>
                    <label class="block text-xs font-bold text-slate-600 mb-1">Nominal Transaksi (Rp)</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-base font-extrabold text-slate-400">Rp</span>
                        <input type="number" wire:model="amount" step="500" min="1000" placeholder="0" class="w-full rounded-2xl border border-slate-200 bg-slate-50 pl-12 pr-4 py-3 text-2xl font-black text-slate-900 focus:bg-white focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/20 transition" data-testid="amount-input" />
                    </div>
                    @error('amount') <span class="text-xs font-bold text-rose-600 mt-1 block">{{ $message }}</span> @enderror

                    {{-- Tombol Preset Cepat --}}
                    <div class="mt-2.5 flex flex-wrap gap-1.5">
                        @foreach ([5000, 10000, 15000, 20000, 25000, 50000] as $preset)
                            <button type="button" wire:click="$set('amount', {{ $preset }})" class="rounded-xl border border-slate-200 bg-slate-50 px-2.5 py-1 text-xs font-bold text-slate-600 hover:border-brand hover:bg-brand/5 hover:text-brand transition active:scale-95">
                                +@rupiah($preset)
                            </button>
                        @endforeach
                    </div>
                </div>

                {{-- Metode Pembayaran --}}
                <div>
                    <label class="block text-xs font-bold text-slate-600 mb-1">Metode Pembayaran</label>
                    <div class="grid grid-cols-2 gap-3">
                        <label class="flex items-center gap-3 rounded-2xl border p-3 cursor-pointer transition {{ $paymentMethod === 'cash' ? 'border-brand bg-brand/5 text-brand-dark font-bold' : 'border-slate-200 bg-white text-slate-600' }}">
                            <input type="radio" wire:model.live="paymentMethod" value="cash" class="sr-only" />
                            <x-icon name="cash" class="h-5 w-5" />
                            <span class="text-xs">Tunai (Cash)</span>
                        </label>
                        <label class="flex items-center gap-3 rounded-2xl border p-3 cursor-pointer transition {{ $paymentMethod === 'qris' ? 'border-brand bg-brand/5 text-brand-dark font-bold' : 'border-slate-200 bg-white text-slate-600' }}">
                            <input type="radio" wire:model.live="paymentMethod" value="qris" class="sr-only" />
                            <x-icon name="qr" class="h-5 w-5" />
                            <span class="text-xs">QRIS Koperasi</span>
                        </label>
                    </div>
                </div>

                {{-- Catatan Opsional --}}
                <div>
                    <label class="block text-xs font-bold text-slate-600 mb-1">Catatan Tambahan (Opsional)</label>
                    <input type="text" wire:model="note" maxlength="200" placeholder="Misal: ATK / Snack / Roti..." class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-3.5 py-2 text-xs font-medium text-slate-800 placeholder-slate-400 focus:bg-white focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/20 transition" />
                </div>

                {{-- Peringatan Duplikasi 2 Menit --}}
                @if ($duplicateWarningMessage)
                    <div class="rounded-2xl border border-amber-300 bg-amber-50 p-4 text-xs text-amber-900 space-y-2" data-testid="duplicate-warning">
                        <p class="font-semibold">{{ $duplicateWarningMessage }}</p>
                        <label class="flex items-center gap-2 cursor-pointer font-bold text-amber-950">
                            <input type="checkbox" wire:model.live="confirmDuplicate" class="rounded border-amber-400 text-brand focus:ring-brand" data-testid="confirm-duplicate-checkbox" />
                            <span>Saya yakin ini adalah transaksi berbeda, lanjutkan simpan.</span>
                        </label>
                    </div>
                @endif

                {{-- Tombol Submit --}}
                <div class="pt-2">
                    <button type="submit" @if (! $selectedUserId || ! $amount) disabled @endif class="w-full flex items-center justify-center gap-2 rounded-2xl py-3.5 text-sm font-extrabold text-white transition {{ ! $selectedUserId || ! $amount ? 'bg-slate-300 cursor-not-allowed' : 'bg-brand hover:bg-brand-dark shadow-md active:scale-98' }}" data-testid="submit-counter-btn">
                        <x-icon name="check" class="h-5 w-5" />
                        <span>Simpan Transaksi Kasir</span>
                    </button>
                </div>
            </form>
        </div>

        {{-- Sisi Kanan: Riwayat Entri Kasir Hari Ini & Aksi Void --}}
        <div class="space-y-4 lg:col-span-5">
            <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xs font-extrabold uppercase tracking-wider text-slate-700">
                        Entri Kasir Terbaru ({{ $recentEntries->count() }})
                    </h2>
                </div>

                @if ($recentEntries->isEmpty())
                    <div class="py-10 text-center text-slate-400">
                        <x-icon name="store" class="mx-auto h-8 w-8 mb-2 opacity-50" />
                        <p class="text-xs">Belum ada transaksi kasir hari ini.</p>
                    </div>
                @else
                    <div class="divide-y divide-slate-100 max-h-[600px] overflow-y-auto pr-1">
                        @foreach ($recentEntries as $entry)
                            <div class="py-3 flex items-start justify-between gap-3 text-xs" data-testid="recent-entry-{{ $entry->id }}">
                                <div>
                                    <div class="flex items-center gap-1.5">
                                        <strong class="font-bold text-slate-900">{{ $entry->buyer?->name ?? 'Pelanggan' }}</strong>
                                        @if ($entry->status->value === 'cancelled')
                                            <span class="rounded bg-rose-100 text-rose-800 px-1 py-0.2 text-[10px] font-bold">VOID</span>
                                        @endif
                                    </div>
                                    <p class="text-slate-500 mt-0.5">
                                        {{ $entry->created_at->format('H:i') }} WIB · {{ $entry->code }}
                                    </p>
                                    @if ($entry->status->value === 'cancelled' && $entry->cancel_reason)
                                        <p class="text-rose-600 text-[11px] mt-0.5">Alasan: {{ $entry->cancel_reason }}</p>
                                    @endif
                                </div>
                                <div class="text-right shrink-0">
                                    <strong class="block font-black {{ $entry->status->value === 'cancelled' ? 'line-through text-slate-400' : 'text-slate-900' }}">
                                        @rupiah($entry->subtotal)
                                    </strong>
                                    @if ($entry->status->value !== 'cancelled')
                                        <button type="button" wire:click="openVoidModal({{ $entry->id }})" class="mt-1 text-[11px] font-bold text-rose-600 hover:text-rose-800 hover:underline" data-testid="void-btn-{{ $entry->id }}">
                                            Void
                                        </button>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Modal Konfirmasi Void --}}
    @if ($showVoidModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 p-4 backdrop-blur-xs" data-testid="void-modal">
            <div class="w-full max-w-md rounded-3xl bg-white p-6 shadow-2xl space-y-4">
                <div class="flex items-center gap-3 text-rose-600">
                    <span class="grid h-10 w-10 place-items-center rounded-2xl bg-rose-100">
                        <x-icon name="trash" class="h-5 w-5" />
                    </span>
                    <div>
                        <h3 class="text-base font-extrabold text-slate-900">Void Transaksi Kasir</h3>
                        <p class="text-xs text-slate-500">Koreksi entri salah. Transaksi akan dibatalkan.</p>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Alasan Pembatalan (Wajib)</label>
                    <textarea wire:model="voidReason" rows="3" placeholder="Contoh: Salah input nominal / Pelanggan salah pilih item..." class="w-full rounded-2xl border border-slate-200 p-3 text-xs text-slate-800 focus:border-rose-400 focus:outline-none focus:ring-2 focus:ring-rose-200" data-testid="void-reason-input"></textarea>
                    @error('voidReason') <span class="text-xs font-bold text-rose-600 block mt-1">{{ $message }}</span> @enderror
                </div>

                <div class="flex items-center justify-end gap-2 pt-2">
                    <button type="button" wire:click="closeVoidModal" class="rounded-xl border border-slate-200 px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-50 transition">
                        Batal
                    </button>
                    <button type="button" wire:click="confirmVoid" class="rounded-xl bg-rose-600 px-4 py-2 text-xs font-bold text-white hover:bg-rose-700 transition" data-testid="confirm-void-btn">
                        Konfirmasi Void
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>

