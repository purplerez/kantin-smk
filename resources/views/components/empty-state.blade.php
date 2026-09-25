@props(['icon' => 'bag', 'title', 'text' => null])
<div class="flex flex-col items-center rounded-3xl border border-dashed border-slate-300 bg-white px-6 py-14 text-center" data-testid="empty-state">
    <span class="grid h-14 w-14 place-items-center rounded-2xl bg-slate-100 text-slate-400"><x-icon :name="$icon" class="h-7 w-7" /></span>
    <h3 class="mt-4 text-base font-bold">{{ $title }}</h3>
    @if ($text) <p class="mt-1 max-w-xs text-sm text-slate-500">{{ $text }}</p> @endif
    {{ $slot }}
</div>
