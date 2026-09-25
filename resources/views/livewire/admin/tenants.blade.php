<div>
    <x-page-header title="Kelola tenant" eyebrow="Persetujuan & status">
        <x-slot:actions>
            <button wire:click="create" class="flex items-center gap-1.5 rounded-full bg-brand px-4 py-2.5 text-xs font-bold text-white hover:bg-brand-dark" data-testid="add-tenant-button"><x-icon name="plus" class="h-4 w-4" /> Tenant baru</button>
        </x-slot:actions>
    </x-page-header>

    <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-3">
        @foreach ($tenants as $tenant)
            <article class="overflow-hidden rounded-2xl border border-slate-200 bg-white" data-testid="tenant-row-{{ $tenant->id }}" wire:key="tenant-{{ $tenant->id }}">
                <div class="relative h-28 bg-slate-200">
                    @if ($tenant->image_url) <img src="{{ $tenant->image_url }}" alt="" class="h-full w-full object-cover"> @endif
                    <span class="absolute left-3 top-3 rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider {{ ['pending' => 'bg-amber-400 text-amber-950', 'active' => 'bg-brand text-white', 'suspended' => 'bg-rose-500 text-white'][$tenant->status] }}" data-testid="tenant-status-{{ $tenant->id }}">{{ $tenant->status }}</span>
                </div>
                <div class="p-4">
                    <h3 class="text-base font-extrabold">{{ $tenant->name }}</h3>
                    <p class="mt-0.5 line-clamp-2 text-xs text-slate-500">{{ $tenant->description }}</p>
                    <div class="mt-3 flex gap-3 text-xs text-slate-600">
                        <span><b>{{ $tenant->products_count }}</b> menu</span><span><b>{{ $tenant->staff_count }}</b> staf</span><span><b>{{ $tenant->orders_count }}</b> order</span>
                        <span class="ml-auto {{ $tenant->is_open ? 'text-brand-dark' : 'text-slate-400' }}">{{ $tenant->is_open ? 'Buka' : 'Tutup' }}</span>
                    </div>
                    <div class="mt-4 flex flex-wrap gap-2">
                        @if ($tenant->status !== 'active')
                            <button wire:click="setStatus({{ $tenant->id }}, 'active')" class="rounded-full bg-brand px-3 py-1.5 text-xs font-bold text-white hover:bg-brand-dark" data-testid="approve-tenant-{{ $tenant->id }}">{{ $tenant->status === 'pending' ? 'Setujui' : 'Aktifkan' }}</button>
                        @endif
                        @if ($tenant->status === 'active')
                            <button wire:click="setStatus({{ $tenant->id }}, 'suspended')" wire:confirm="Tangguhkan tenant {{ $tenant->name }}? Staf tidak bisa masuk panel." class="rounded-full border border-rose-200 px-3 py-1.5 text-xs font-bold text-rose-600 hover:bg-rose-50" data-testid="suspend-tenant-{{ $tenant->id }}">Tangguhkan</button>
                        @endif
                        <button wire:click="edit({{ $tenant->id }})" class="rounded-full px-3 py-1.5 text-xs font-bold text-slate-700 hover:bg-slate-100" data-testid="edit-tenant-{{ $tenant->id }}">Edit</button>
                    </div>
                </div>
            </article>
        @endforeach
    </div>

    @if ($showForm)
        <div class="fixed inset-0 z-40 flex items-end justify-center bg-slate-900/50 md:items-center md:p-6" data-testid="tenant-form-modal">
            <form wire:submit="save" class="max-h-[92vh] w-full max-w-lg overflow-y-auto rounded-t-3xl bg-white p-6 md:rounded-3xl">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-extrabold">{{ $editingId ? 'Edit tenant' : 'Tenant baru' }}</h2>
                    <button type="button" wire:click="$set('showForm', false)" class="grid h-9 w-9 place-items-center rounded-full hover:bg-slate-100" data-testid="close-form-button"><x-icon name="x" class="h-5 w-5" /></button>
                </div>
                @foreach ([['name', 'Nama tenant', 'text'], ['image_url', 'URL gambar', 'url'], ['bank_name', 'Bank', 'text'], ['bank_account', 'No. rekening', 'text'], ['bank_holder', 'Atas nama', 'text']] as [$field, $label, $type])
                    <label class="mt-4 block text-xs font-bold">{{ $label }}
                        <input type="{{ $type }}" wire:model="{{ $field }}" class="mt-1.5 w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm outline-none focus:border-brand" data-testid="tenant-{{ str_replace('_', '-', $field) }}-input">
                        @error($field) <span class="text-xs font-normal text-rose-600">{{ $message }}</span> @enderror
                    </label>
                @endforeach
                <label class="mt-4 block text-xs font-bold">Deskripsi
                    <textarea wire:model="description" rows="2" class="mt-1.5 w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm outline-none focus:border-brand" data-testid="tenant-description-input"></textarea>
                </label>
                <p class="mt-3 text-xs text-slate-500">Tenant baru berstatus <b>pending</b> sampai disetujui. Tambahkan akun Tenant Admin di menu Pengguna.</p>
                <button class="mt-6 w-full rounded-full bg-brand py-3 text-sm font-bold text-white hover:bg-brand-dark" data-testid="save-tenant-button">Simpan</button>
            </form>
        </div>
    @endif
</div>
