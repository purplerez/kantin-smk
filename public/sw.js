// KantinSMK Go — service worker minimal.
// Sengaja TANPA offline caching: Livewire butuh koneksi live ke server.
// Strategi: network-first untuk shell; hanya fallback ke cache jaringan gagal total.
const SHELL_CACHE = 'kantinsmk-shell-v1';

self.addEventListener('install', (event) => {
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) =>
            Promise.all(keys.filter((k) => k !== SHELL_CACHE).map((k) => caches.delete(k)))
        )
    );
    self.clients.claim();
});

self.addEventListener('fetch', (event) => {
    const req = event.request;

    // Hanya tangani GET navigasi dokumen. Semua request Livewire/POST lewat langsung ke jaringan.
    if (req.method !== 'GET' || req.mode !== 'navigate') {
        return;
    }

    event.respondWith(
        fetch(req).catch(() =>
            new Response(
                '<!doctype html><meta charset="utf-8"><title>Offline</title>' +
                '<body style="font-family:sans-serif;padding:2rem;text-align:center">' +
                '<h1>Tidak ada koneksi</h1><p>KantinSMK Go butuh internet aktif. Coba lagi setelah tersambung.</p>' +
                '<button onclick="location.reload()">Muat ulang</button></body>',
                { headers: { 'Content-Type': 'text/html; charset=utf-8' } }
            )
        )
    );
});
