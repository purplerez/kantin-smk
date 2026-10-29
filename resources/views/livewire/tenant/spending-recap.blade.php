<div class="space-y-6" data-testid="spending-recap-page">
    {{-- Header & Action Bar --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Rekap Belanja Pegawai</h1>
            <p class="text-xs text-slate-500">Laporan pemenuhan kewajiban target belanja bulanan pegawai di unit usaha koperasi.</p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <button type="button" wire:click="exportCsv" class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs font-bold text-slate-700 shadow-xs hover:bg-slate-50 transition active:scale-95" data-testid="export-csv-btn">
                <x-icon name="list" class="h-4 w-4 text-emerald-600" />
                <span>Ekspor CSV</span>
            </button>

            <button type="button" wire:click="openAdjustmentModal" class="inline-flex items-center gap-1.5 rounded-xl border border-sky-200 bg-sky-50 px-3.5 py-2 text-xs font-bold text-sky-800 hover:bg-sky-100 transition active:scale-95" data-testid="open-adj-modal-btn">
                <x-icon name="chart" class="h-4 w-4" />
                <span>+ Penyesuaian</span>
            </button>

            @if ($closing)
                <span class="inline-flex items-center gap-1.5 rounded-xl bg-slate-100 border border-slate-300 px-3.5 py-2 text-xs font-bold text-slate-600" data-testid="closed-status-badge">
                    <x-icon name="shield" class="h-4 w-4 text-slate-500" />
                    <span>Buku Ditutup</span>
                </span>
            @else
                <button type="button" wire:click="openCloseModal" class="inline-flex items-center gap-1.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white px-3.5 py-2 text-xs font-bold shadow-xs transition active:scale-95" data-testid="open-close-modal-btn">
                    <x-icon name="shield" class="h-4 w-4 text-brand-light" />
                    <span>Tutup Bulan</span>
                </button>
            @endif
        </div>
    </div>

    {{-- Month Navigator & Closed Notice --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 rounded-3xl border border-slate-200 bg-white p-4 shadow-sm">
        <div class="flex items-center gap-2">
            <button type="button" wire:click="previousMonth" class="grid h-9 w-9 place-items-center rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-100 transition" title="Bulan Sebelumnya">
                <x-icon name="back" class="h-4 w-4" />
            </button>
            <div class="px-2 text-center sm:text-left">
                <strong class="block text-base font-extrabold text-slate-900" data-testid="recap-month-label">{{ $monthLabel }}</strong>
                @if ($closing)
                    <span class="text-[11px] font-semibold text-emerald-700">
                        Ditutup pada {{ $closing->closed_at->setTimezone('Asia/Jakarta')->translatedFormat('d M Y, H:i') }} WIB
                    </span>
                @else
                    <span class="text-[11px] font-semibold text-slate-500">Bulan Berjalan (Terbuka)</span>
                @endif
            </div>
            <button type="button" wire:click="nextMonth" class="grid h-9 w-9 place-items-center rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-100 transition" title="Bulan Berikutnya">
                <x-icon name="forward" class="h-4 w-4" />
            </button>
        </div>

        @if (! $isCurrentMonth)
            <button type="button" wire:click="currentMonth" class="text-xs font-bold text-brand hover:underline self-center">
                Kembali ke Bulan Ini
            </button>
        @endif
    </div>

    {{-- KPI Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Total Pegawai</p>
            <strong class="mt-1 block text-3xl font-black text-slate-900" data-testid="total-employees-kpi">{{ $totalEmployees }}</strong>
            <span class="text-[11px] text-slate-400">Pegawai berjabatan aktif</span>
        </div>

        <div class="rounded-3xl border border-emerald-100 bg-emerald-50/50 p-5 shadow-sm">
            <p class="text-xs font-bold text-emerald-800 uppercase tracking-wider">Target Terpenuhi</p>
            <strong class="mt-1 block text-3xl font-black text-emerald-700" data-testid="met-employees-kpi">{{ $metCount }}</strong>
            <span class="text-[11px] text-emerald-600">
                {{ $totalEmployees > 0 ? round(($metCount / $totalEmployees) * 100) : 0 }}% dari total pegawai
            </span>
        </div>

        <div class="rounded-3xl border border-amber-100 bg-amber-50/50 p-5 shadow-sm">
            <p class="text-xs font-bold text-amber-800 uppercase tracking-wider">Belum Terpenuhi</p>
            <strong class="mt-1 block text-3xl font-black text-amber-700" data-testid="unmet-employees-kpi">{{ $unmetCount }}</strong>
            <span class="text-[11px] text-amber-600">Masih di bawah target</span>
        </div>
    </div>

    {{-- Filter Bar --}}
    <div class="flex flex-col sm:flex-row items-center gap-3">
        <div class="relative w-full sm:flex-1">
            <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-slate-400">
                <x-icon name="search" class="h-4 w-4" />
            </span>
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari nama atau NIP..." class="w-full rounded-2xl border border-slate-200 bg-white pl-10 pr-4 py-2.5 text-xs font-medium text-slate-800 placeholder-slate-400 focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/20 transition" data-testid="recap-search-input" />
        </div>

        <select wire:model.live="positionFilter" class="w-full sm:w-48 rounded-2xl border border-slate-200 bg-white px-3.5 py-2.5 text-xs font-medium text-slate-700 focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/20 transition" data-testid="recap-position-filter">
            <option value="">Semua Jabatan</option>
            @foreach ($positions as $pos)
                <option value="{{ $pos->id }}">{{ $pos->name }}</option>
            @endforeach
        </select>

        <select wire:model.live="statusFilter" class="w-full sm:w-44 rounded-2xl border border-slate-200 bg-white px-3.5 py-2.5 text-xs font-medium text-slate-700 focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/20 transition" data-testid="recap-status-filter">
            <option value="">Semua Status</option>
            <option value="met">Terpenuhi</option>
            <option value="unmet">Belum Terpenuhi</option>
        </select>
    </div>

    {{-- Tabel Rekapitulasi Pegawai --}}
    <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-700">
                <thead class="bg-slate-50 text-[11px] font-extrabold uppercase tracking-wider text-slate-500 border-b border-slate-200">
                    <tr>
                        <th class="py-3.5 pl-6 pr-3">Pegawai</th>
                        <th class="py-3.5 px-3">Jabatan</th>
                        <th class="py-3.5 px-3 text-right">Total Belanja</th>
                        <th class="py-3.5 px-3 text-right">Target</th>
                        <th class="py-3.5 px-3 text-right">Sisa Kekurangan</th>
                        <th class="py-3.5 px-3 text-center">Status</th>
                        <th class="py-3.5 pl-3 pr-6 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($recapList as $row)
                        @php($emp = $row['user'])
                        @php($calc = $row['calc'])
                        <tr class="hover:bg-slate-50/70 transition" data-testid="employee-row-{{ $emp->id }}">
                            <td class="py-3.5 pl-6 pr-3">
                                <div class="flex items-center gap-3">
                                    <span class="grid h-8 w-8 shrink-0 place-items-center rounded-full bg-slate-100 font-bold text-slate-700">
                                        {{ $emp->initials() }}
                                    </span>
                                    <div>
                                        <strong class="block text-sm font-bold text-slate-900">{{ $emp->name }}</strong>
                                        <span class="text-[11px] text-slate-500 font-mono">{{ $emp->identifier ?: '-' }}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3.5 px-3">
                                <span class="rounded-lg bg-slate-100 px-2 py-0.5 font-bold text-slate-700 text-[11px]">
                                    {{ $calc['position_name'] }}
                                </span>
                            </td>
                            <td class="py-3.5 px-3 text-right font-black text-slate-900 text-sm">
                                @rupiah($calc['total_verified'])
                            </td>
                            <td class="py-3.5 px-3 text-right text-slate-600 font-semibold">
                                @rupiah($calc['target'])
                            </td>
                            <td class="py-3.5 px-3 text-right font-bold {{ $calc['is_met'] ? 'text-slate-400' : 'text-amber-600' }}">
                                @if ($calc['is_met'])
                                    Rp0
                                @else
                                    @rupiah($calc['remaining'])
                                @endif
                            </td>
                            <td class="py-3.5 px-3 text-center">
                                @if ($calc['is_met'])
                                    <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2.5 py-0.5 text-[10px] font-extrabold text-emerald-800">
                                        <x-icon name="check" class="h-3 w-3" />
                                        Terpenuhi
                                    </span>
                                @else
                                    <span class="inline-flex items-center rounded-full bg-amber-100 px-2.5 py-0.5 text-[10px] font-extrabold text-amber-800">
                                        Kurang @rupiah($calc['remaining'])
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 pl-3 pr-6 text-center">
                                <button type="button" wire:click="openAdjustmentModal({{ $emp->id }})" class="rounded-lg border border-slate-200 px-2 py-1 text-[11px] font-bold text-slate-600 hover:border-brand hover:bg-brand/5 hover:text-brand transition" data-testid="adj-btn-{{ $emp->id }}">
                                    + Koreksi
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-10 text-center text-slate-400">
                                <p class="text-xs">Tidak ada data pegawai yang cocok dengan filter.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Modal Tutup Bulan --}}
    @if ($showCloseModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 p-4 backdrop-blur-xs" data-testid="close-month-modal">
            <div class="w-full max-w-md rounded-3xl bg-white p-6 shadow-2xl space-y-4">
                <div class="flex items-center gap-3 text-slate-900">
                    <span class="grid h-10 w-10 place-items-center rounded-2xl bg-brand/10 text-brand">
                        <x-icon name="shield" class="h-5 w-5" />
                    </span>
                    <div>
                        <h3 class="text-base font-extrabold">Tutup Buku Periode {{ $monthLabel }}</h3>
                        <p class="text-xs text-slate-500">Kunci snapshot pembukuan belanja bulanan.</p>
                    </div>
                </div>

                <div class="rounded-2xl border border-amber-200 bg-amber-50 p-4 text-xs text-amber-900 space-y-2">
                    <p class="font-bold">Konsekuensi Penutupan Buku:</p>
                    <ul class="list-disc pl-4 space-y-1 text-amber-800">
                        <li>Snapshot capaian belanja seluruh pegawai akan disimpan permanen.</li>
                        <li>Entri kasir baru dan pembatalan (void) untuk bulan {{ $monthLabel }} akan <b>terkunci</b>.</li>
                        <li>Koreksi susulan setelah penutupan harus dibuat melalui fitur <b>Penyesuaian</b>.</li>
                    </ul>
                </div>

                @error('close') <span class="text-xs font-bold text-rose-600 block">{{ $message }}</span> @enderror

                <div class="flex items-center justify-end gap-2 pt-2">
                    <button type="button" wire:click="closeCloseModal" class="rounded-xl border border-slate-200 px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-50 transition">
                        Batal
                    </button>
                    <button type="button" wire:click="executeCloseMonth" class="rounded-xl bg-slate-900 hover:bg-slate-800 text-white px-4 py-2 text-xs font-bold transition" data-testid="confirm-close-month-btn">
                        Ya, Tutup Buku Sekarang
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal Penyesuaian (Adjustment) --}}
    @if ($showAdjustmentModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 p-4 backdrop-blur-xs" data-testid="adjustment-modal">
            <form wire:submit="saveAdjustment" class="w-full max-w-md rounded-3xl bg-white p-6 shadow-2xl space-y-4">
                <div class="flex items-center gap-3">
                    <span class="grid h-10 w-10 place-items-center rounded-2xl bg-sky-100 text-sky-600">
                        <x-icon name="chart" class="h-5 w-5" />
                    </span>
                    <div>
                        <h3 class="text-base font-extrabold text-slate-900">Buat Penyesuaian Belanja</h3>
                        <p class="text-xs text-slate-500">Koreksi saldo belanja pegawai (positif / negatif).</p>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Pilih Pegawai</label>
                    <select wire:model="adjUserId" class="w-full rounded-2xl border border-slate-200 p-2.5 text-xs text-slate-800 focus:border-brand focus:outline-none" data-testid="adj-user-select">
                        <option value="">-- Pilih Pegawai --</option>
                        @foreach ($allActiveEmployees as $e)
                            <option value="{{ $e->id }}">{{ $e->name }} ({{ $e->position?->name }})</option>
                        @endforeach
                    </select>
                    @error('adjUserId') <span class="text-xs font-bold text-rose-600 block mt-1">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Periode Bulan (YYYY-MM)</label>
                    <input type="text" wire:model="adjPeriod" placeholder="2026-10" class="w-full rounded-2xl border border-slate-200 p-2.5 text-xs text-slate-800 font-mono focus:border-brand focus:outline-none" data-testid="adj-period-input" />
                    @error('adjPeriod') <span class="text-xs font-bold text-rose-600 block mt-1">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Nominal Penyesuaian (Rp)</label>
                    <input type="number" wire:model="adjAmount" placeholder="Contoh: 20000 atau -5000" class="w-full rounded-2xl border border-slate-200 p-2.5 text-xs text-slate-800 focus:border-brand focus:outline-none" data-testid="adj-amount-input" />
                    <span class="text-[11px] text-slate-500 mt-0.5 block">Gunakan angka negatif (mis. -5000) untuk pengurangan/retur.</span>
                    @error('adjAmount') <span class="text-xs font-bold text-rose-600 block mt-1">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Alasan Penyesuaian (Wajib)</label>
                    <textarea wire:model="adjReason" rows="2" placeholder="Contoh: Koreksi kasir offline / retur fisik..." class="w-full rounded-2xl border border-slate-200 p-2.5 text-xs text-slate-800 focus:border-brand focus:outline-none" data-testid="adj-reason-input"></textarea>
                    @error('adjReason') <span class="text-xs font-bold text-rose-600 block mt-1">{{ $message }}</span> @enderror
                </div>

                <div class="flex items-center justify-end gap-2 pt-2">
                    <button type="button" wire:click="closeAdjustmentModal" class="rounded-xl border border-slate-200 px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-50 transition">
                        Batal
                    </button>
                    <button type="submit" class="rounded-xl bg-sky-600 hover:bg-sky-700 text-white px-4 py-2 text-xs font-bold transition" data-testid="submit-adj-btn">
                        Simpan Penyesuaian
                    </button>
                </div>
            </form>
        </div>
    @endif
</div>

