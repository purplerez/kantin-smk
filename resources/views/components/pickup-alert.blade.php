{{--
  [JS #4b] Pengingat pickup & pesanan baru (Alpine + Web Audio API).
  Bunyi, getar, dan banner yang muncul lalu hilang hanya bisa dibuat di sisi klien.
  Dipicu oleh browser event `order-ready` (pembeli) dan `new-order` (tenant) yang dikirim komponen Livewire.
--}}
<script>
    window.kantinMuted = () => localStorage.getItem('kantin_mute') === '1';
    window.kantinChime = function (notes) {
        if (window.kantinMuted()) return;
        try {
            const ctx = window.__kantinAudio ??= new (window.AudioContext || window.webkitAudioContext)();
            if (ctx.state === 'suspended') ctx.resume();
            let t = ctx.currentTime;
            notes.forEach(([freq, dur]) => {
                const osc = ctx.createOscillator(), gain = ctx.createGain();
                osc.type = 'sine'; osc.frequency.value = freq;
                gain.gain.setValueAtTime(0.0001, t);
                gain.gain.exponentialRampToValueAtTime(0.35, t + 0.02);
                gain.gain.exponentialRampToValueAtTime(0.0001, t + dur);
                osc.connect(gain).connect(ctx.destination);
                osc.start(t); osc.stop(t + dur);
                t += dur;
            });
            if (navigator.vibrate) navigator.vibrate([120, 60, 120]);
        } catch (e) { /* browser tanpa audio */ }
    };
    // Web Audio hanya boleh mulai setelah interaksi pengguna: buat konteks pada klik pertama.
    document.addEventListener('click', () => { try { window.__kantinAudio ??= new (window.AudioContext || window.webkitAudioContext)(); } catch (e) {} }, { once: true });
</script>

<div x-data="{ items: [] }"
     x-on:order-ready.window="items.unshift({ id: Date.now(), kind: 'ready', title: 'Pesanan siap diambil!', text: $event.detail.tenant + ' · ' + $event.detail.code, url: $event.detail.url }); window.kantinChime([[880, .16], [1175, .16], [1568, .38]]); setTimeout(() => items.pop(), 15000)"
     x-on:new-order.window="items.unshift({ id: Date.now(), kind: 'new', title: $event.detail.count + ' pesanan baru masuk', text: $event.detail.text ?? 'Segera siapkan pesanan.', url: null }); window.kantinChime([[660, .12], [660, .12], [990, .3]]); setTimeout(() => items.pop(), 8000)"
     class="pointer-events-none fixed inset-x-0 bottom-20 z-50 flex flex-col items-center gap-2 px-4 md:bottom-6"
     data-testid="pickup-alert-stack">
    <template x-for="item in items" :key="item.id">
        <div x-transition.duration.300ms
             class="pointer-events-auto flex w-full max-w-md items-center gap-3 rounded-2xl p-3 text-white shadow-2xl ring-1 ring-white/20"
             :class="item.kind === 'ready' ? 'bg-brand' : 'bg-slate-900'"
             data-testid="pickup-alert">
            <span class="relative grid h-11 w-11 shrink-0 place-items-center rounded-full bg-white/20">
                <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-white/30"></span>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="relative h-6 w-6"><path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9M10.3 21a1.94 1.94 0 0 0 3.4 0"/></svg>
            </span>
            <span class="min-w-0 flex-1">
                <strong class="block text-sm" x-text="item.title"></strong>
                <small class="block truncate text-xs opacity-90" x-text="item.text"></small>
            </span>
            <a x-show="item.url" :href="item.url" wire:navigate class="rounded-full bg-white px-3 py-1.5 text-xs font-bold text-brand-dark" data-testid="pickup-alert-open">Lihat</a>
            <button type="button" x-on:click="items = items.filter(i => i.id !== item.id)" class="grid h-8 w-8 place-items-center rounded-full hover:bg-white/20" aria-label="Tutup" data-testid="pickup-alert-close">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" class="h-4 w-4"><path d="M6 6l12 12M18 6 6 18"/></svg>
            </button>
        </div>
    </template>
</div>
