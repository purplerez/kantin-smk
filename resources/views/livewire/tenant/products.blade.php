<div>
    <x-page-header title="Kelola menu" eyebrow="Katalog tenant">
        <x-slot:actions>
            <button wire:click="create" class="flex items-center gap-1.5 rounded-full bg-brand px-4 py-2.5 text-xs font-bold text-white hover:bg-brand-dark" data-testid="add-menu-button"><x-icon name="plus" class="h-4 w-4" /> Tambah menu</button>
        </x-slot:actions>
    </x-page-header>

    <label class="mb-4 flex items-center gap-2 rounded-2xl border border-slate-200 bg-white px-4 py-2.5">
        <x-icon name="search" class="h-4 w-4 text-slate-400" />
        <input type="search" wire:model.live.debounce.300ms="search" placeholder="Cari menu..." class="w-full bg-transparent text-sm outline-none" data-testid="product-search-input">
    </label>

    @if ($products->isEmpty())
        <x-empty-state icon="store" title="Belum ada menu" text="Tambahkan menu pertama untuk mulai berjualan." />
    @else
        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500">
                    <tr><th class="px-4 py-3">Menu</th><th class="hidden px-4 py-3 md:table-cell">Kategori</th><th class="px-4 py-3">Harga</th><th class="px-4 py-3">Tersedia</th><th class="px-4 py-3 text-right">Aksi</th></tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($products as $product)
                        <tr data-testid="product-row-{{ $product->id }}" wire:key="prod-{{ $product->id }}" class="{{ $product->is_available_today ? '' : 'opacity-60' }}">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <img src="{{ $product->image_url }}" alt="" class="h-11 w-11 rounded-lg bg-slate-100 object-cover">
                                    <div class="min-w-0"><strong class="block truncate">{{ $product->name }}</strong><span class="block truncate text-xs text-slate-500 md:hidden">{{ $product->category?->name }}</span></div>
                                </div>
                            </td>
                            <td class="hidden px-4 py-3 text-slate-600 md:table-cell">{{ $product->category?->name ?? '—' }}</td>
                            <td class="px-4 py-3 font-semibold">@rupiah($product->price)</td>
                            <td class="px-4 py-3">
                                <button wire:click="toggleAvailability({{ $product->id }})" class="relative inline-flex h-6 w-11 items-center rounded-full transition {{ $product->is_available_today ? 'bg-brand' : 'bg-slate-300' }}" data-testid="toggle-availability-{{ $product->id }}" role="switch" aria-checked="{{ $product->is_available_today ? 'true' : 'false' }}">
                                    <span class="inline-block h-5 w-5 transform rounded-full bg-white shadow transition {{ $product->is_available_today ? 'translate-x-5' : 'translate-x-0.5' }}"></span>
                                </button>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <button wire:click="edit({{ $product->id }})" class="rounded-full px-3 py-1.5 text-xs font-bold text-slate-700 hover:bg-slate-100" data-testid="edit-product-{{ $product->id }}">Edit</button>
                                <button wire:click="delete({{ $product->id }})" wire:confirm="Hapus menu {{ $product->name }}?" class="rounded-full px-3 py-1.5 text-xs font-bold text-rose-600 hover:bg-rose-50" data-testid="delete-product-{{ $product->id }}">Hapus</button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    {{-- Modal form (dikontrol server-side oleh $showForm, tanpa JS custom) --}}
    @if ($showForm)
        <div class="fixed inset-0 z-40 flex items-end justify-center bg-slate-900/50 p-0 md:items-center md:p-6" data-testid="product-form-modal">
            <form wire:submit="save" class="max-h-[92vh] w-full max-w-lg overflow-y-auto rounded-t-3xl bg-white p-6 md:rounded-3xl">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-extrabold">{{ $editingId ? 'Edit menu' : 'Tambah menu' }}</h2>
                    <button type="button" wire:click="$set('showForm', false)" class="grid h-9 w-9 place-items-center rounded-full hover:bg-slate-100" data-testid="close-form-button"><x-icon name="x" class="h-5 w-5" /></button>
                </div>

                <label class="mt-5 block text-xs font-bold">Nama menu
                    <input wire:model="name" class="mt-1.5 w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm outline-none focus:border-brand" data-testid="product-name-input">
                    @error('name') <span class="text-xs font-normal text-rose-600">{{ $message }}</span> @enderror
                </label>
                <div class="mt-4 grid grid-cols-2 gap-3">
                    <label class="block text-xs font-bold">Harga (Rp)
                        <input type="number" wire:model="price" min="500" step="500" class="mt-1.5 w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm outline-none focus:border-brand" data-testid="product-price-input">
                        @error('price') <span class="text-xs font-normal text-rose-600">{{ $message }}</span> @enderror
                    </label>
                    <label class="block text-xs font-bold">Kategori
                        <select wire:model="category_id" class="mt-1.5 w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm outline-none focus:border-brand" data-testid="product-category-select">
                            <option value="">— Pilih —</option>
                            @foreach ($categories as $cat) <option value="{{ $cat->id }}">{{ $cat->name }}</option> @endforeach
                        </select>
                    </label>
                </div>
                <label class="mt-4 block text-xs font-bold">URL gambar
                    <input wire:model="image_url" placeholder="https://..." class="mt-1.5 w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm outline-none focus:border-brand" data-testid="product-image-input">
                    @error('image_url') <span class="text-xs font-normal text-rose-600">{{ $message }}</span> @enderror
                </label>
                <label class="mt-4 block text-xs font-bold">Deskripsi
                    <textarea wire:model="description" rows="2" class="mt-1.5 w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm outline-none focus:border-brand" data-testid="product-description-input"></textarea>
                </label>
                <label class="mt-4 flex items-center gap-2 text-sm"><input type="checkbox" wire:model="is_available_today" class="h-4 w-4 accent-brand" data-testid="product-available-checkbox"> Tersedia hari ini</label>

                <button class="mt-6 w-full rounded-full bg-brand py-3 text-sm font-bold text-white hover:bg-brand-dark" data-testid="save-product-button">Simpan</button>
            </form>
        </div>
    @endif
</div>
