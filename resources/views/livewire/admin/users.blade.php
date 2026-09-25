<div>
    <x-page-header title="Kelola pengguna" eyebrow="Provisioning akun">
        <x-slot:actions>
            <div class="flex gap-2">
                <button wire:click="$toggle('showImport')" class="rounded-full border border-slate-300 bg-white px-4 py-2.5 text-xs font-bold text-slate-700 hover:bg-slate-50" data-testid="import-users-button">Import massal</button>
                <button wire:click="create" class="flex items-center gap-1.5 rounded-full bg-brand px-4 py-2.5 text-xs font-bold text-white hover:bg-brand-dark" data-testid="add-user-button"><x-icon name="plus" class="h-4 w-4" /> Akun baru</button>
            </div>
        </x-slot:actions>
    </x-page-header>

    @if ($activationLink)
        <div class="mb-5 rounded-2xl border border-brand/40 bg-brand-light/60 p-4" data-testid="activation-link-panel">
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0 flex-1">
                    <h2 class="text-sm font-extrabold text-brand-dark">Link aktivasi sekali-pakai</h2>
                    <p class="mt-1 text-xs text-slate-600">Bagikan ke pengguna agar mereka mengatur password sendiri. Kedaluwarsa otomatis & hanya bisa dipakai satu kali.</p>
                    <input readonly value="{{ $activationLink }}" onclick="this.select()" class="mt-2 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 font-mono text-xs outline-none" data-testid="activation-link-input">
                </div>
                <button wire:click="$set('activationLink','')" class="shrink-0 text-xs font-semibold text-slate-500 hover:text-slate-700" data-testid="dismiss-activation-link">Tutup</button>
            </div>
        </div>
    @endif

    @if ($showImport)
        <form wire:submit="import" class="mb-5 rounded-2xl border border-slate-200 bg-white p-4" data-testid="import-form">
            <h2 class="text-sm font-extrabold">Import pembeli (siswa/guru) massal</h2>
            <p class="mt-1 text-xs text-slate-500">Satu akun per baris: <code class="rounded bg-slate-100 px-1">Nama Lengkap;email@sekolah.id;NIS/NIP;password</code> — password opsional (dibuat acak jika kosong).</p>
            <textarea wire:model="importText" rows="5" placeholder="Raka Wijaya;raka@smkgo.id;2024002;raka123&#10;Sinta Dewi;sinta@smkgo.id;2024003" class="mt-3 w-full rounded-xl border border-slate-300 px-3 py-2 font-mono text-xs outline-none focus:border-brand" data-testid="import-textarea"></textarea>
            @error('importText') <p class="text-xs text-rose-600">{{ $message }}</p> @enderror
            <div class="mt-3 flex items-center gap-3">
                <button class="rounded-full bg-slate-900 px-5 py-2.5 text-xs font-bold text-white" data-testid="import-submit-button">Proses import</button>
                @if ($importResult)
                    <span class="text-xs font-semibold text-brand-dark" data-testid="import-result">{{ $importResult['created'] }} dibuat, {{ count($importResult['skipped']) }} dilewati</span>
                @endif
            </div>
            @if (! empty($importResult['skipped']))
                <ul class="mt-2 list-inside list-disc text-xs text-amber-700">@foreach ($importResult['skipped'] as $s) <li>{{ $s }}</li> @endforeach</ul>
            @endif
        </form>
    @endif

    <div class="mb-4 grid gap-2 md:grid-cols-[1fr_220px]">
        <label class="flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2">
            <x-icon name="search" class="h-4 w-4 text-slate-400" />
            <input type="search" wire:model.live.debounce.300ms="search" placeholder="Nama, email, NIS/NIP" class="w-full bg-transparent text-sm outline-none" data-testid="user-search-input">
        </label>
        <select wire:model.live="roleFilter" class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm" data-testid="user-role-filter">
            <option value="">Semua peran</option>
            @foreach ($roles as $r) <option value="{{ $r->value }}">{{ $r->label() }}</option> @endforeach
        </select>
    </div>

    <div class="overflow-x-auto rounded-2xl border border-slate-200 bg-white">
        <table class="w-full min-w-[720px] text-sm">
            <thead class="bg-slate-50 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500">
                <tr><th class="px-4 py-3">Nama</th><th class="px-4 py-3">Email / ID</th><th class="px-4 py-3">Peran</th><th class="px-4 py-3">Tenant</th><th class="px-4 py-3">Status</th><th class="px-4 py-3 text-right">Aksi</th></tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach ($users as $user)
                    <tr data-testid="user-row-{{ $user->id }}" wire:key="user-{{ $user->id }}" class="{{ $user->is_active ? '' : 'opacity-60' }}">
                        <td class="px-4 py-3 font-semibold">{{ $user->name }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $user->email }}@if ($user->identifier)<span class="block text-xs text-slate-400">{{ $user->identifier }}</span>@endif</td>
                        <td class="px-4 py-3"><span class="rounded-full bg-slate-100 px-2.5 py-1 text-[11px] font-bold">{{ $user->role->label() }}</span></td>
                        <td class="px-4 py-3 text-slate-600">{{ $user->tenant?->name ?? '—' }}</td>
                        <td class="px-4 py-3">
                            <button wire:click="toggleActive({{ $user->id }})" @disabled($user->id === auth()->id()) class="rounded-full px-2.5 py-1 text-[11px] font-bold {{ $user->is_active ? 'bg-brand-light text-brand-dark' : 'bg-rose-100 text-rose-700' }} disabled:cursor-not-allowed" data-testid="toggle-active-{{ $user->id }}">{{ $user->is_active ? 'Aktif' : 'Nonaktif' }}</button>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <button wire:click="generateLink({{ $user->id }})" class="rounded-full px-3 py-1.5 text-xs font-bold text-brand-dark hover:bg-brand-light" data-testid="activation-link-{{ $user->id }}">Aktivasi</button>
                            <button wire:click="edit({{ $user->id }})" class="rounded-full px-3 py-1.5 text-xs font-bold text-slate-700 hover:bg-slate-100" data-testid="edit-user-{{ $user->id }}">Edit</button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $users->links() }}</div>

    @if ($showForm)
        <div class="fixed inset-0 z-40 flex items-end justify-center bg-slate-900/50 md:items-center md:p-6" data-testid="user-form-modal">
            <form wire:submit="save" class="max-h-[92vh] w-full max-w-lg overflow-y-auto rounded-t-3xl bg-white p-6 md:rounded-3xl">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-extrabold">{{ $editingId ? 'Edit akun' : 'Akun baru' }}</h2>
                    <button type="button" wire:click="$set('showForm', false)" class="grid h-9 w-9 place-items-center rounded-full hover:bg-slate-100" data-testid="close-form-button"><x-icon name="x" class="h-5 w-5" /></button>
                </div>
                <label class="mt-5 block text-xs font-bold">Nama lengkap<input wire:model="name" class="mt-1.5 w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm outline-none focus:border-brand" data-testid="user-name-input">@error('name')<span class="text-xs font-normal text-rose-600">{{ $message }}</span>@enderror</label>
                <div class="mt-4 grid grid-cols-2 gap-3">
                    <label class="block text-xs font-bold">Email<input type="email" wire:model="email" class="mt-1.5 w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm outline-none focus:border-brand" data-testid="user-email-input">@error('email')<span class="text-xs font-normal text-rose-600">{{ $message }}</span>@enderror</label>
                    <label class="block text-xs font-bold">NIS / NIP<input wire:model="identifier" class="mt-1.5 w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm outline-none focus:border-brand" data-testid="user-identifier-input">@error('identifier')<span class="text-xs font-normal text-rose-600">{{ $message }}</span>@enderror</label>
                </div>
                <div class="mt-4 grid grid-cols-2 gap-3">
                    <label class="block text-xs font-bold">Peran
                        <select wire:model.live="role" class="mt-1.5 w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm outline-none focus:border-brand" data-testid="user-role-select">
                            @foreach ($roles as $r) <option value="{{ $r->value }}">{{ $r->label() }}</option> @endforeach
                        </select>
                    </label>
                    @if (\App\Enums\Role::from($role)->isTenant())
                        <label class="block text-xs font-bold">Tenant
                            <select wire:model="tenant_id" class="mt-1.5 w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm outline-none focus:border-brand" data-testid="user-tenant-select">
                                <option value="">— Pilih —</option>
                                @foreach ($tenants as $t) <option value="{{ $t->id }}">{{ $t->name }}</option> @endforeach
                            </select>
                            @error('tenant_id')<span class="text-xs font-normal text-rose-600">{{ $message }}</span>@enderror
                        </label>
                    @endif
                </div>
                <label class="mt-4 block text-xs font-bold">{{ $editingId ? 'Password baru (kosongkan jika tidak diubah)' : 'Password awal' }}<input type="text" wire:model="password" autocomplete="off" class="mt-1.5 w-full rounded-xl border border-slate-300 px-3 py-2.5 font-mono text-sm outline-none focus:border-brand" data-testid="user-password-input">@error('password')<span class="text-xs font-normal text-rose-600">{{ $message }}</span>@enderror</label>
                <button class="mt-6 w-full rounded-full bg-brand py-3 text-sm font-bold text-white hover:bg-brand-dark" data-testid="save-user-button">Simpan</button>
            </form>
        </div>
    @endif
</div>
