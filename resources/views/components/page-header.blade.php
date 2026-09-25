@props(['title', 'eyebrow' => null, 'back' => null])
<div class="mb-6 flex items-start gap-3">
    @if ($back)
        <a href="{{ $back }}" wire:navigate class="mt-0.5 grid h-10 w-10 shrink-0 place-items-center rounded-full border border-slate-200 bg-white text-slate-600 hover:bg-slate-50" data-testid="back-button">
            <x-icon name="back" class="h-5 w-5" />
        </a>
    @endif
    <div class="min-w-0 flex-1">
        @if ($eyebrow) <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-brand-dark">{{ $eyebrow }}</p> @endif
        <h1 class="text-2xl font-extrabold tracking-tight md:text-3xl">{{ $title }}</h1>
    </div>
    @isset($actions) <div class="shrink-0">{{ $actions }}</div> @endisset
</div>
