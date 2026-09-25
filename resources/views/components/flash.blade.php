@if (session('success') || session('error'))
    <div class="mx-auto w-full max-w-6xl px-4 pt-4 md:px-8">
        @if (session('success'))
            <div class="flex items-center gap-2 rounded-xl border border-brand/30 bg-brand-light px-4 py-3 text-sm font-semibold text-brand-dark" data-testid="flash-success">
                <x-icon name="check" class="h-4 w-4" /> {{ session('success') }}
            </div>
        @endif
        @if (session('error'))
            <div class="flex items-center gap-2 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-700" data-testid="flash-error">
                <x-icon name="x" class="h-4 w-4" /> {{ session('error') }}
            </div>
        @endif
    </div>
@endif
