<div class="grid min-h-screen place-items-center bg-slate-50 px-4 py-10">
    <div class="w-full max-w-md">
        <div class="mb-6 flex items-center justify-center gap-2">
            <span class="grid h-10 w-10 place-items-center rounded-xl bg-brand text-xl font-extrabold text-white">K</span>
            <span class="text-xl font-extrabold tracking-tight">Kantin<span class="text-brand">SMK</span> Go</span>
        </div>

        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <h1 class="text-lg font-extrabold">Ganti password dulu</h1>
            <p class="mt-1 text-sm text-slate-500">Demi keamanan, atur password baru sebelum melanjutkan.</p>

            <form wire:submit="submit" class="mt-5 space-y-4" data-testid="force-password-form">
                <label class="block text-xs font-bold">Password baru
                    <input type="password" wire:model="password" autocomplete="new-password" class="mt-1.5 w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm outline-none focus:border-brand" data-testid="force-password-input">
                    @error('password')<span class="text-xs font-normal text-rose-600">{{ $message }}</span>@enderror
                </label>
                <label class="block text-xs font-bold">Ulangi password
                    <input type="password" wire:model="password_confirmation" autocomplete="new-password" class="mt-1.5 w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm outline-none focus:border-brand" data-testid="force-password-confirm-input">
                </label>
                <button class="w-full rounded-full bg-brand py-3 text-sm font-bold text-white hover:bg-brand-dark" data-testid="force-password-submit">Simpan & lanjut</button>
            </form>
            <form method="POST" action="{{ route('logout') }}" class="mt-3 text-center">
                @csrf
                <button class="text-xs font-semibold text-slate-500 hover:text-slate-700" data-testid="force-password-logout">Keluar</button>
            </form>
        </div>
    </div>
</div>
