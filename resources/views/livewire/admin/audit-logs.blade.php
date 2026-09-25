<div>
    <x-page-header title="Audit log" eyebrow="Jejak aktivitas">
        <x-slot:actions>
            <label class="flex items-center gap-2 rounded-full border border-slate-200 bg-white px-3 py-2">
                <x-icon name="search" class="h-4 w-4 text-slate-400" />
                <input type="search" wire:model.live.debounce.300ms="search" placeholder="Aksi / aktor / subjek" class="w-40 bg-transparent text-xs outline-none md:w-64" data-testid="audit-search-input">
            </label>
        </x-slot:actions>
    </x-page-header>

    <div class="overflow-x-auto rounded-2xl border border-slate-200 bg-white">
        <table class="w-full min-w-[720px] text-sm">
            <thead class="bg-slate-50 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500">
                <tr><th class="px-4 py-3">Waktu</th><th class="px-4 py-3">Aktor</th><th class="px-4 py-3">Aksi</th><th class="px-4 py-3">Subjek</th><th class="px-4 py-3">Detail</th><th class="px-4 py-3">IP</th></tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($logs as $log)
                    <tr data-testid="audit-row-{{ $log->id }}" wire:key="log-{{ $log->id }}">
                        <td class="whitespace-nowrap px-4 py-2.5 text-xs text-slate-500">{{ $log->created_at->translatedFormat('d M Y H:i:s') }}</td>
                        <td class="px-4 py-2.5 font-semibold">{{ $log->actor?->name ?? 'Sistem' }}<span class="block text-[11px] font-normal text-slate-400">{{ $log->actor?->role->label() }}</span></td>
                        <td class="px-4 py-2.5"><code class="rounded bg-slate-100 px-1.5 py-0.5 text-xs">{{ $log->action }}</code></td>
                        <td class="px-4 py-2.5 text-slate-600">{{ $log->subject_type }} @if ($log->subject_id)<span class="text-slate-400">#{{ $log->subject_id }}</span>@endif</td>
                        <td class="max-w-xs truncate px-4 py-2.5 font-mono text-[11px] text-slate-500" title="{{ json_encode($log->meta) }}">{{ $log->meta ? json_encode($log->meta, JSON_UNESCAPED_UNICODE) : '—' }}</td>
                        <td class="px-4 py-2.5 text-xs text-slate-400">{{ $log->ip }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-10 text-center text-slate-500">Belum ada catatan.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $logs->links() }}</div>
</div>
