<div>
    <?php if (isset($component)) { $__componentOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.page-header','data' => ['title' => 'Platform Control','eyebrow' => now()->translatedFormat('l, d F Y')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Platform Control','eyebrow' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(now()->translatedFormat('l, d F Y'))]); ?>
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

    <div class="grid grid-cols-2 gap-3 md:grid-cols-3 xl:grid-cols-6">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = [
            ['Pesanan hari ini', $ordersToday, 'orders-today'],
            ['Pendapatan lunas', 'Rp'.number_format($revenueToday, 0, ',', '.'), 'revenue-today'],
            ['Invoice belum bayar', $pendingInvoices, 'pending-invoices'],
            ['Tenant aktif', $activeTenants, 'active-tenants'],
            ['Tenant menunggu', $pendingTenants, 'pending-tenants'],
            ['Pengguna aktif', $totalUsers, 'active-users'],
        ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as [$label, $value, $id]): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="rounded-2xl border border-slate-200 bg-white p-4" data-testid="metric-<?php echo e($id); ?>">
                <p class="text-xs text-slate-500"><?php echo e($label); ?></p>
                <strong class="mt-1 block text-2xl font-extrabold tracking-tight"><?php echo e($value); ?></strong>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($pendingTenants): ?>
        <a href="<?php echo e(route('admin.tenants')); ?>" wire:navigate class="mt-4 flex items-center justify-between rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-semibold text-amber-800" data-testid="pending-tenants-alert"><?php echo e($pendingTenants); ?> tenant menunggu persetujuan <span>→</span></a>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <div class="mt-6 grid gap-6 lg:grid-cols-2">
        <section class="rounded-2xl border border-slate-200 bg-white">
            <header class="border-b border-slate-100 px-4 py-3"><h2 class="text-base font-extrabold">Performa tenant hari ini</h2></header>
            <table class="w-full text-sm">
                <thead class="text-left text-[11px] font-bold uppercase tracking-wider text-slate-400"><tr><th class="px-4 py-2">Tenant</th><th class="px-4 py-2">Order</th><th class="px-4 py-2 text-right">Lunas</th></tr></thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $perTenant; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr data-testid="tenant-perf-row"><td class="px-4 py-2.5 font-semibold"><?php echo e($row->name); ?></td><td class="px-4 py-2.5"><?php echo e($row->orders_count); ?></td><td class="px-4 py-2.5 text-right font-semibold text-brand-dark"><?php echo 'Rp'.number_format((int) ($row->paid_amount), 0, ',', '.'); ?></td></tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr><td colspan="3" class="px-4 py-8 text-center text-slate-500">Belum ada transaksi hari ini.</td></tr>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </tbody>
            </table>
        </section>

        <section class="rounded-2xl border border-slate-200 bg-white">
            <header class="flex items-center justify-between border-b border-slate-100 px-4 py-3">
                <h2 class="text-base font-extrabold">Transaksi terbaru</h2>
                <a href="<?php echo e(route('admin.transactions')); ?>" wire:navigate class="text-xs font-bold text-brand-dark">Lihat semua →</a>
            </header>
            <ul class="divide-y divide-slate-100 text-sm">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $recent; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li class="flex items-center gap-3 px-4 py-2.5" data-testid="recent-order-<?php echo e($order->id); ?>">
                        <div class="min-w-0 flex-1"><strong class="block truncate"><?php echo e($order->tenant->name); ?></strong><span class="text-xs text-slate-500"><?php echo e($order->buyer->name); ?> · <?php echo e($order->code); ?></span></div>
                        <strong><?php echo 'Rp'.number_format((int) ($order->subtotal), 0, ',', '.'); ?></strong>
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
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </ul>
        </section>
    </div>
</div>
<?php /**PATH /Users/mac/Herd/kantinsmk-go/resources/views/livewire/admin/dashboard.blade.php ENDPATH**/ ?>