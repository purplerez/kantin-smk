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
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title><?php echo e($title ?? config('app.name')); ?></title>
    <link rel="icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'%3E%3Crect width='32' height='32' rx='8' fill='%2300AA13'/%3E%3Ctext x='16' y='23' font-family='sans-serif' font-weight='800' font-size='20' fill='white' text-anchor='middle'%3EK%3C/text%3E%3C/svg%3E">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
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
    <?php echo \Livewire\Mechanisms\FrontendAssets\FrontendAssets::styles(); ?>

</head>
<body class="h-full bg-slate-50 text-slate-900 antialiased">
    <?php echo e($slot); ?>


    
    <div x-data="{ show: false, message: '', tone: 'ok' }"
         x-on:toast.window="message = $event.detail.message; tone = $event.detail.tone ?? 'ok'; show = true; clearTimeout(window.__t); window.__t = setTimeout(() => show = false, 2600)"
         x-show="show" x-cloak x-transition
         class="fixed left-1/2 top-4 z-50 -translate-x-1/2 rounded-full px-5 py-2.5 text-sm font-semibold text-white shadow-lg"
         :class="tone === 'ok' ? 'bg-slate-900' : 'bg-rose-600'"
         data-testid="toast">
        <span x-text="message"></span>
    </div>

    <?php if (isset($component)) { $__componentOriginale39cef3e18f2351d7ab18804fae3c69e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale39cef3e18f2351d7ab18804fae3c69e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.pickup-alert','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('pickup-alert'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginale39cef3e18f2351d7ab18804fae3c69e)): ?>
<?php $attributes = $__attributesOriginale39cef3e18f2351d7ab18804fae3c69e; ?>
<?php unset($__attributesOriginale39cef3e18f2351d7ab18804fae3c69e); ?>
<?php endif; ?>
<?php if (isset($__componentOriginale39cef3e18f2351d7ab18804fae3c69e)): ?>
<?php $component = $__componentOriginale39cef3e18f2351d7ab18804fae3c69e; ?>
<?php unset($__componentOriginale39cef3e18f2351d7ab18804fae3c69e); ?>
<?php endif; ?>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(\App\Services\Realtime::enabled()): ?>
        <?php ($rv = config('broadcasting.connections.reverb.options')); ?>
        <script src="https://cdn.jsdelivr.net/npm/pusher-js@8.4.0/dist/web/pusher.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.16.1/dist/echo.iife.js"></script>
        <script>
            window.Pusher = Pusher;
            window.Echo = new Echo({
                broadcaster: 'reverb',
                key: <?php echo json_encode(config('broadcasting.connections.reverb.key'), 15, 512) ?>,
                wsHost: <?php echo json_encode($rv['host'], 15, 512) ?>,
                wsPort: <?php echo json_encode((int) $rv['port'], 15, 512) ?>,
                wssPort: <?php echo json_encode((int) $rv['port'], 15, 512) ?>,
                forceTLS: <?php echo json_encode(($rv['scheme'] ?? 'http') === 'https', 15, 512) ?>,
                enabledTransports: ['ws', 'wss'],
                authEndpoint: '/broadcasting/auth',
                csrfToken: document.querySelector('meta[name="csrf-token"]').content,
            });
        </script>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => navigator.serviceWorker.register('/sw.js').catch(() => {}));
        }
    </script>

    <?php echo \Livewire\Mechanisms\FrontendAssets\FrontendAssets::scripts(); ?>

</body>
</html>
<?php /**PATH /Users/mac/Herd/kantinsmk-go/resources/views/components/layouts/base.blade.php ENDPATH**/ ?>