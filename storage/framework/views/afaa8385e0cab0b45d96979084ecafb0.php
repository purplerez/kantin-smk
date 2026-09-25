<div>
    <?php if (isset($component)) { $__componentOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.page-header','data' => ['title' => 'Selamat datang, '.explode(' ', auth()->user()->name)[0],'eyebrow' => $tenant->name]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('Selamat datang, '.explode(' ', auth()->user()->name)[0]),'eyebrow' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($tenant->name)]); ?>
         <?php $__env->slot('actions', null, []); ?> 
            <button wire:click="toggleOpen" class="flex items-center gap-2 rounded-full px-4 py-2 text-xs font-bold ring-1 transition <?php echo e($tenant->is_open ? 'bg-brand-light text-brand-dark ring-brand/30' : 'bg-rose-50 text-rose-700 ring-rose-200'); ?>" data-testid="toggle-open-button">
                <span class="h-2 w-2 rounded-full <?php echo e($tenant->is_open ? 'bg-brand' : 'bg-rose-500'); ?>"></span>
                <?php echo e($tenant->is_open ? 'Tenant Buka' : 'Tenant Tutup'); ?>

            </button>
         <?php $__env->endSlot(); ?>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalf8d4ea307ab1e58d4e472a43c8548d8e)): ?>
<?php $attributes = $__attributesOriginalf8d4ea307ab1e58d4e472a43c8548d8e; ?>
<?php unset($__attributesOriginalf8d4ea307ab1e58d4e472a43c8548d8e); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalf8d4ea307ab1e58d4e472a43c8548d8e)): ?>
<?php $component = $__componentOriginalf8d4ea307ab1e58d4e472a43c8548d8e; ?>
<?php unset($__componentOriginalf8d4ea307ab1e58d4e472a43c8548d8e); ?>
<?php endif; ?>

    <div class="grid grid-cols-2 gap-3 md:grid-cols-4">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = [
            ['Pesanan hari ini', $ordersToday, 'orders-today'],
            ['Pendapatan (lunas)', 'Rp'.number_format($revenueToday, 0, ',', '.'), 'revenue-today'],
            ['Pesanan aktif', $activeOrders, 'active-orders'],
            ['Menunggu bayar tunai', $pendingPayments, 'pending-payments'],
        ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as [$label, $value, $id]): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="rounded-2xl border border-slate-200 bg-white p-4" data-testid="metric-<?php echo e($id); ?>">
                <p class="text-xs text-slate-500"><?php echo e($label); ?></p>
                <strong class="mt-1 block text-2xl font-extrabold tracking-tight"><?php echo e($value); ?></strong>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-[1fr_360px]">
        <section class="rounded-2xl border border-slate-200 bg-white">
            <header class="flex items-center justify-between border-b border-slate-100 px-4 py-3">
                <h2 class="text-base font-extrabold">Pesanan terbaru</h2>
                <a href="<?php echo e(route('tenant.orders')); ?>" wire:navigate class="text-xs font-bold text-brand-dark" data-testid="see-all-orders-link">Lihat semua →</a>
            </header>
            <ul class="divide-y divide-slate-100">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $recent; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <li class="flex items-center gap-3 px-4 py-3 text-sm" data-testid="recent-order-<?php echo e($order->id); ?>">
                        <div class="min-w-0 flex-1">
                            <strong class="block truncate"><?php echo e($order->buyer->name); ?> <span class="font-mono text-xs text-slate-400"><?php echo e($order->code); ?></span></strong>
                            <span class="block truncate text-xs text-slate-500"><?php echo e($order->items->map(fn ($i) => "{$i->qty}× {$i->product_name}")->implode(', ')); ?></span>
                        </div>
                        <strong class="text-sm"><?php echo 'Rp'.number_format((int) ($order->subtotal), 0, ',', '.'); ?></strong>
                        <?php if (isset($component)) { $__componentOriginal8c81617a70e11bcf247c4db924ab1b62 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8c81617a70e11bcf247c4db924ab1b62 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.status-badge','data' => ['status' => $order->status]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($order->status)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8c81617a70e11bcf247c4db924ab1b62)): ?>
<?php $attributes = $__attributesOriginal8c81617a70e11bcf247c4db924ab1b62; ?>
<?php unset($__attributesOriginal8c81617a70e11bcf247c4db924ab1b62); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8c81617a70e11bcf247c4db924ab1b62)): ?>
<?php $component = $__componentOriginal8c81617a70e11bcf247c4db924ab1b62; ?>
<?php unset($__componentOriginal8c81617a70e11bcf247c4db924ab1b62); ?>
<?php endif; ?>
                    </li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <li class="px-4 py-10 text-center text-sm text-slate-500">Belum ada pesanan.</li>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </ul>
        </section>

        <div class="space-y-4">
            <section class="rounded-2xl bg-slate-900 p-5 text-white">
                <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-emerald-300">Rating tenant</p>
                <div class="mt-2 flex items-end gap-2">
                    <strong class="text-4xl font-extrabold" data-testid="tenant-rating"><?php echo e($rating ?? '–'); ?></strong>
                    <span class="mb-1 text-sm text-slate-300">/ 5 dari <?php echo e($tenant->reviews()->count()); ?> ulasan</span>
                </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($unavailable): ?> <p class="mt-4 rounded-xl bg-white/10 px-3 py-2 text-xs"><?php echo e($unavailable); ?> menu ditandai habis hari ini. <a href="<?php echo e(route('tenant.products')); ?>" wire:navigate class="font-bold underline">Kelola</a></p> <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </section>

            <section class="rounded-2xl border border-slate-200 bg-white p-4">
                <h2 class="text-sm font-extrabold">Menu terlaris</h2>
                <ol class="mt-3 space-y-2 text-sm">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $topProducts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li class="flex items-center gap-3"><span class="grid h-6 w-6 place-items-center rounded-full bg-brand-light text-[11px] font-bold text-brand-dark"><?php echo e($i + 1); ?></span><span class="flex-1 truncate"><?php echo e($p->name); ?></span><span class="text-xs font-semibold text-slate-500"><?php echo e((int) $p->sold); ?> terjual</span></li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </ol>
            </section>
        </div>
    </div>
</div>
<?php /**PATH /Users/mac/Herd/kantinsmk-go/resources/views/livewire/tenant/dashboard.blade.php ENDPATH**/ ?>