<div class="space-y-8" data-testid="spending-settings-page">
    {{-- Header --}}
    <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Kelola Jabatan & Target Belanja</h1>
            <p class="text-xs text-slate-500">Konfigurasi jabatan pegawai dan besaran kuota belanja bulanan yang berlaku di koperasi.</p>
        </div>
        <div>
            <a href="{{ route('tenant.spending.rekap') }}" wire:navigate class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs font-bold text-slate-700 shadow-xs hover:bg-slate-50 transition">
                <x-icon name="chart" class="h-4 w-4" />
                <span>Lihat Rekapitulasi</span>
            </a>
        </div>
    </div>

    {{-- Section 1: Jabatan Pegawai --}}
    <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm space-y-4">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-base font-extrabold text-slate-900">1. Daftar Jabatan Pegawai</h2>
                <p class="text-xs text-slate-500">Jabatan yang dapat ditetapkan pada akun pegawai sekolah.</p>
            </div>
            <button type="button" wire:click="openPositionModal" class="inline-flex items-center gap-1.5 rounded-xl bg-brand text-white px-3.5 py-2 text-xs font-bold shadow-xs hover:bg-brand-dark transition active:scale-95" data-testid="add-position-btn">
                <x-icon name="plus" class="h-4 w-4" />
                <span>Tambah Jabatan</span>
            </button>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-700">
                <thead class="bg-slate-50 text-[11px] font-extrabold uppercase tracking-wider text-slate-500 border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-4">Nama Jabatan</th>
                        <th class="py-3 px-4 text-center">Pegawai Terdaftar</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($positions as $pos)
                        <tr class="hover:bg-slate-50/60 transition" data-testid="position-row-{{ $pos->id }}">
                            <td class="py-3.5 px-4 font-bold text-slate-900 text-sm">
                                {{ $pos->name }}
                            </td>
                            <td class="py-3.5 px-4 text-center font-bold text-slate-700">
                                {{ $pos->users_count }} pegawai
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                @if ($pos->is_active)
                                    <span class="inline-flex items-center rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-bold text-emerald-800">Aktif</span>
                                @else
                                    <span class="inline-flex items-center rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-bold text-slate-500">Non-Aktif</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <button type="button" wire:click="togglePositionActive({{ $pos->id }})" class="rounded-lg border border-slate-200 px-2.5 py-1 text-xs font-semibold text-slate-600 hover:bg-slate-100 transition" data-testid="toggle-pos-btn-{{ $pos->id }}">
                                    {{ $pos->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-6 text-center text-slate-400">Belum ada data jabatan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Section 2: Target Belanja Bulanan Koperasi --}}
    <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm space-y-4">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-base font-extrabold text-slate-900">2. Target Belanja per Jabatan</h2>
                <p class="text-xs text-slate-500">Nominal minimum belanja bulanan yang berlaku menurut tanggal efektif.</p>
            </div>
            <button type="button" wire:click="openTargetModal" class="inline-flex items-center gap-1.5 rounded-xl bg-slate-900 text-white px-3.5 py-2 text-xs font-bold shadow-xs hover:bg-slate-800 transition active:scale-95" data-testid="add-target-btn">
                <x-icon name="plus" class="h-4 w-4 text-brand-light" />
                <span>Tetapkan Target Baru</span>
            </button>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-700">
                <thead class="bg-slate-50 text-[11px] font-extrabold uppercase tracking-wider text-slate-500 border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-4">Jabatan</th>
                        <th class="py-3 px-4 text-right">Target Bulanan</th>
                        <th class="py-3 px-4">Berlaku Mulai</th>
                        <th class="py-3 px-4">Berlaku Hingga</th>
                        <th class="py-3 px-4">Ditetapkan Oleh</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($targets as $target)
                        <tr class="hover:bg-slate-50/60 transition" data-testid="target-row-{{ $target->id }}">
                            <td class="py-3.5 px-4 font-bold text-slate-900">
                                {{ $target->position?->name ?? 'Jabatan Terhapus' }}
                            </td>
                            <td class="py-3.5 px-4 text-right font-black text-slate-900 text-sm">
                                @rupiah($target->amount)
                            </td>
                            <td class="py-3.5 px-4 text-slate-700 font-mono text-[11px]">
                                {{ $target->effective_from->format('d/m/Y') }}
                            </td>
                            <td class="py-3.5 px-4 text-slate-700 font-mono text-[11px]">
                                {{ $target->effective_to ? $target->effective_to->format('d/m/Y') : 'Seterusnya' }}
                            </td>
                            <td class="py-3.5 px-4 text-slate-500 text-[11px]">
                                {{ $target->creator?->name ?? 'Sistem' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-6 text-center text-slate-400">Belum ada target belanja yang dikonfigurasi.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Modal Tambah Jabatan --}}
    @if ($showPositionModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 p-4 backdrop-blur-xs" data-testid="position-modal">
            <form wire:submit="savePosition" class="w-full max-w-sm rounded-3xl bg-white p-6 shadow-2xl space-y-4">
                <h3 class="text-base font-extrabold text-slate-900">Tambah Jabatan Baru</h3>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Nama Jabatan</label>
                    <input type="text" wire:model="positionName" placeholder="Contoh: Guru Piket / Laboran..." class="w-full rounded-2xl border border-slate-200 p-2.5 text-xs text-slate-800 focus:border-brand focus:outline-none" data-testid="position-name-input" />
                    @error('positionName') <span class="text-xs font-bold text-rose-600 block mt-1">{{ $message }}</span> @enderror
                </div>
                <div class="flex items-center justify-end gap-2 pt-2">
                    <button type="button" wire:click="closePositionModal" class="rounded-xl border border-slate-200 px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-50 transition">
                        Batal
                    </button>
                    <button type="submit" class="rounded-xl bg-brand hover:bg-brand-dark text-white px-4 py-2 text-xs font-bold transition" data-testid="submit-position-btn">
                        Simpan Jabatan
                    </button>
                </div>
            </form>
        </div>
    @endif

    {{-- Modal Tambah Target Belanja --}}
    @if ($showTargetModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 p-4 backdrop-blur-xs" data-testid="target-modal">
            <form wire:submit="saveTarget" class="w-full max-w-md rounded-3xl bg-white p-6 shadow-2xl space-y-4">
                <h3 class="text-base font-extrabold text-slate-900">Tetapkan Target Belanja</h3>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Pilih Jabatan</label>
                    <select wire:model="targetPositionId" class="w-full rounded-2xl border border-slate-200 p-2.5 text-xs text-slate-800 focus:border-brand focus:outline-none" data-testid="target-pos-select">
                        <option value="">-- Pilih Jabatan --</option>
                        @foreach ($positions as $p)
                            <option value="{{ $p->id }}">{{ $p->name }}</option>
                        @endforeach
                    </select>
                    @error('targetPositionId') <span class="text-xs font-bold text-rose-600 block mt-1">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Nominal Target Bulanan (Rp)</label>
                    <input type="number" wire:model="targetAmount" placeholder="Contoh: 150000" class="w-full rounded-2xl border border-slate-200 p-2.5 text-xs text-slate-800 font-bold focus:border-brand focus:outline-none" data-testid="target-amount-input" />
                    @error('targetAmount') <span class="text-xs font-bold text-rose-600 block mt-1">{{ $message }}</span> @enderror
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Berlaku Mulai</label>
                        <input type="date" wire:model="targetEffectiveFrom" class="w-full rounded-2xl border border-slate-200 p-2.5 text-xs text-slate-800 focus:border-brand focus:outline-none" data-testid="target-from-input" />
                        @error('targetEffectiveFrom') <span class="text-xs font-bold text-rose-600 block mt-1">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Berlaku Hingga (Opsional)</label>
                        <input type="date" wire:model="targetEffectiveTo" class="w-full rounded-2xl border border-slate-200 p-2.5 text-xs text-slate-800 focus:border-brand focus:outline-none" data-testid="target-to-input" />
                        @error('targetEffectiveTo') <span class="text-xs font-bold text-rose-600 block mt-1">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2">
                    <button type="button" wire:click="closeTargetModal" class="rounded-xl border border-slate-200 px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-50 transition">
                        Batal
                    </button>
                    <button type="submit" class="rounded-xl bg-slate-900 hover:bg-slate-800 text-white px-4 py-2 text-xs font-bold transition" data-testid="submit-target-btn">
                        Simpan Target
                    </button>
                </div>
            </form>
        </div>
    @endif
</div>

