
<button type="button"
        x-data="{ muted: localStorage.getItem('kantin_mute') === '1' }"
        x-on:click="muted = !muted; localStorage.setItem('kantin_mute', muted ? '1' : '0'); if (!muted) window.kantinChime([[880, .12]])"
        :title="muted ? 'Bunyi notifikasi mati' : 'Bunyi notifikasi hidup'"
        <?php echo e($attributes->merge(['class' => 'grid h-9 w-9 place-items-center rounded-full text-slate-500 hover:bg-slate-100', 'data-testid' => 'sound-toggle'])); ?>>
    <svg x-show="!muted" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5"><path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9M10.3 21a1.94 1.94 0 0 0 3.4 0"/></svg>
    <svg x-show="muted" x-cloak viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5"><path d="M13.7 21a2 2 0 0 1-3.4 0M18.6 13A17.9 17.9 0 0 1 18 8M6.3 6.3A6 6 0 0 0 6 8c0 7-3 9-3 9h14M18 8a6 6 0 0 0-9.3-5M2 2l20 20"/></svg>
</button>
<?php /**PATH /Users/mac/Herd/kantinsmk-go/resources/views/components/sound-toggle.blade.php ENDPATH**/ ?>