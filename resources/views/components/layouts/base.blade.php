<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#00AA13">
    <link rel="manifest" href="/manifest.json">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="KantinSMK Go">
    <link rel="apple-touch-icon" href="/icons/icon-192.png">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? config('app.name') }}</title>
    <link rel="icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'%3E%3Crect width='32' height='32' rx='8' fill='%2300AA13'/%3E%3Ctext x='16' y='23' font-family='sans-serif' font-weight='800' font-size='20' fill='white' text-anchor='middle'%3EK%3C/text%3E%3C/svg%3E">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    {{-- [JS #1] Tailwind Play CDN: hanya untuk styling tanpa build step. Bisa diganti CSS hasil kompilasi (npm run build) di produksi. --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: { brand: { DEFAULT: '#00AA13', dark: '#008A0F', light: '#E6F7E8' }, accent: '#FF8C00' },
                    fontFamily: { sans: ['Plus Jakarta Sans', 'ui-sans-serif', 'system-ui', 'sans-serif'] },
                },
            },
        };
    </script>
    <style>
        [x-cloak] { display: none !important; }
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
        .safe-bottom { padding-bottom: env(safe-area-inset-bottom); }
    </style>
    @livewireStyles
</head>
<body class="h-full bg-slate-50 text-slate-900 antialiased">
    {{ $slot }}

    {{-- Toast ringan (Alpine, bawaan Livewire) --}}
    <div x-data="{ show: false, message: '', tone: 'ok' }"
         x-on:toast.window="message = $event.detail.message; tone = $event.detail.tone ?? 'ok'; show = true; clearTimeout(window.__t); window.__t = setTimeout(() => show = false, 2600)"
         x-show="show" x-cloak x-transition
         class="fixed left-1/2 top-4 z-50 -translate-x-1/2 rounded-full px-5 py-2.5 text-sm font-semibold text-white shadow-lg"
         :class="tone === 'ok' ? 'bg-slate-900' : 'bg-rose-600'"
         data-testid="toast">
        <span x-text="message"></span>
    </div>

    <x-pickup-alert />

    {{-- [JS #5] Laravel Echo + Pusher protocol client (CDN) untuk menerima push dari Laravel Reverb. Hanya dimuat bila BROADCAST_CONNECTION=reverb. --}}
    @if (\App\Services\Realtime::enabled())
        @php($rv = config('broadcasting.connections.reverb.options'))
        <script src="https://cdn.jsdelivr.net/npm/pusher-js@8.4.0/dist/web/pusher.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.16.1/dist/echo.iife.js"></script>
        <script>
            window.Pusher = Pusher;
            window.Echo = new Echo({
                broadcaster: 'reverb',
                key: @json(config('broadcasting.connections.reverb.key')),
                wsHost: @json($rv['host']),
                wsPort: @json((int) $rv['port']),
                wssPort: @json((int) $rv['port']),
                forceTLS: @json(($rv['scheme'] ?? 'http') === 'https'),
                enabledTransports: ['ws', 'wss'],
                authEndpoint: '/broadcasting/auth',
                csrfToken: document.querySelector('meta[name="csrf-token"]').content,
            });
        </script>
    @endif

    {{-- PWA: service worker minimal (network-first shell, tanpa offline — Livewire butuh koneksi live). --}}
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => navigator.serviceWorker.register('/sw.js').catch(() => {}));
        }
    </script>

    @livewireScripts
</body>
</html>
