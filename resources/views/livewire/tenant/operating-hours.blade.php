<div>
    <x-page-header title="Jam Operasional" eyebrow="Pengaturan Toko" />

    <div class="mx-auto max-w-2xl">
        {{-- Info card --}}
        <div class="mb-6 rounded-2xl border border-violet-200 bg-violet-50 p-4 text-sm text-violet-800">
            <strong>Cara kerja Pre-Order:</strong> Pesanan yang masuk di luar jam operasional akan berstatus
            <span class="inline-flex items-center rounded-full bg-violet-100 px-2 py-0.5 text-xs font-bold text-violet-800">Pre-Order</span>
            dan akan dikonfirmasi otomatis ketika toko mulai buka.
        </div>

        <form wire:submit="save" class="rounded-3xl border border-slate-200 bg-white shadow-sm">
            <header class="border-b border-slate-100 px-6 py-4">
                <h2 class="text-base font-bold">Jadwal Buka per Hari</h2>
                <p class="mt-0.5 text-xs text-slate-500">Hari yang tidak dicentang "Buka" dianggap libur — tidak menerima pesanan.</p>
            </header>

            <ul class="divide-y divide-slate-100">
                @foreach ($dayNames as $d => $dayName)
                    <li class="flex flex-wrap items-center gap-4 px-6 py-4" wire:key="day-{{ $d }}">
                        {{-- Nama hari --}}
                        <span class="w-16 text-sm font-bold text-slate-700">{{ $dayName }}</span>

                        {{-- Toggle Buka --}}
                        <label class="flex items-center gap-2 text-sm">
                            <input type="checkbox" wire:model.live="hours.{{ $d }}.is_open"
                                   class="h-4 w-4 rounded border-slate-300 accent-brand"
                                   data-testid="hours-open-{{ $d }}">
                            <span class="font-semibold {{ $hours[$d]['is_open'] ? 'text-brand-dark' : 'text-slate-400' }}">
                                {{ $hours[$d]['is_open'] ? 'Buka' : 'Tutup' }}
                            </span>
                        </label>

                        {{-- Jam --}}
                        @if ($hours[$d]['is_open'])
                            <div class="flex items-center gap-2 text-sm">
                                <input type="time" wire:model="hours.{{ $d }}.open_at"
                                       class="rounded-lg border border-slate-200 px-3 py-1.5 text-sm outline-none focus:border-brand"
                                       data-testid="hours-open-at-{{ $d }}">
                                <span class="text-slate-400">—</span>
                                <input type="time" wire:model="hours.{{ $d }}.close_at"
                                       class="rounded-lg border border-slate-200 px-3 py-1.5 text-sm outline-none focus:border-brand"
                                       data-testid="hours-close-at-{{ $d }}">
                            </div>
                            @error("hours.{$d}.close_at")
                                <span class="w-full text-xs text-rose-600">{{ $message }}</span>
                            @enderror
                        @else
                            <span class="text-xs text-slate-400 italic">Libur seharian</span>
                        @endif
                    </li>
                @endforeach
            </ul>

            <footer class="flex items-center justify-end gap-3 border-t border-slate-100 px-6 py-4">
                <button type="submit"
                        class="rounded-full bg-brand px-8 py-2.5 text-sm font-bold text-white shadow-md shadow-brand/20 hover:bg-brand-dark"
                        data-testid="save-hours-button">
                    Simpan Jadwal
                </button>
            </footer>
        </form>

        {{-- Status sekarang --}}
        @php
            $tenant = auth()->user()->tenant;
            $tenant->setRelation('hours', collect(array_map(
                fn ($d, $row) => (object) array_merge($row, ['day_of_week' => $d]),
                array_keys($hours), $hours
            )));
        @endphp
    </div>
</div>

