<span>
    @if ($count > 0)
        <span class="{{ $position === 'absolute' ? 'absolute right-1/2 top-1 -mr-5' : '' }} grid min-w-[20px] place-items-center rounded-full bg-accent px-1.5 py-0.5 text-[10px] font-bold text-white" data-testid="cart-badge">{{ $count }}</span>
    @endif
</span>
