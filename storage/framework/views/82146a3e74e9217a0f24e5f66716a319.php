<div class="mx-auto max-w-xl">
    <section class="rounded-3xl bg-slate-900 p-6 text-center text-white">
        <span class="mx-auto grid h-20 w-20 place-items-center rounded-full bg-accent text-2xl font-extrabold"><?php echo e($user->initials()); ?></span>
        <p class="mt-4 text-[11px] font-bold uppercase tracking-[0.18em] text-emerald-300">Akun aktif</p>
        <h1 class="mt-1 text-2xl font-extrabold" data-testid="account-name"><?php echo e($user->name); ?></h1>
        <p class="text-sm text-slate-300"><?php echo e($user->email); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($user->identifier): ?> · <?php echo e($user->identifier); ?><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?></p>
    </section>

    <div class="mt-4 grid grid-cols-2 gap-3">
        <div class="rounded-2xl border border-slate-200 bg-white p-4"><p class="text-xs text-slate-500">Total pesanan</p><strong class="text-2xl font-extrabold" data-testid="account-total-orders"><?php echo e($totalOrders); ?></strong></div>
        <div class="rounded-2xl border border-slate-200 bg-white p-4"><p class="text-xs text-slate-500">Total belanja</p><strong class="text-2xl font-extrabold" data-testid="account-total-spent"><?php echo 'Rp'.number_format((int) ($totalSpent), 0, ',', '.'); ?></strong></div>
    </div>

    <ul class="mt-4 divide-y divide-slate-100 rounded-2xl border border-slate-200 bg-white text-sm">
        <li class="flex items-center gap-3 px-4 py-3"><?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon','data' => ['name' => 'shield','class' => 'h-5 w-5 text-brand']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'shield','class' => 'h-5 w-5 text-brand']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $attributes = $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $component = $__componentOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?><span><strong class="block">Peran akses</strong><small class="text-slate-500"><?php echo e($user->role->label()); ?></small></span></li>
        <li class="flex items-center gap-3 px-4 py-3"><?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon','data' => ['name' => 'store','class' => 'h-5 w-5 text-brand']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'store','class' => 'h-5 w-5 text-brand']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $attributes = $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $component = $__componentOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?><span><strong class="block">Pickup sekolah</strong><small class="text-slate-500">Semua pesanan diambil langsung di tenant</small></span></li>
        <li class="flex items-center gap-3 px-4 py-3"><?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon','data' => ['name' => 'cash','class' => 'h-5 w-5 text-brand']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'cash','class' => 'h-5 w-5 text-brand']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $attributes = $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $component = $__componentOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?><span><strong class="block">Pembayaran</strong><small class="text-slate-500">QRIS, transfer, tunai (mode demo)</small></span></li>
    </ul>

    <form method="POST" action="<?php echo e(route('logout')); ?>" class="mt-6">
        <?php echo csrf_field(); ?>
        <button class="w-full rounded-full border border-slate-300 bg-white py-3 text-sm font-bold text-slate-700 hover:bg-slate-50" data-testid="logout-button">Keluar</button>
    </form>
</div>
<?php /**PATH /Users/mac/Herd/kantinsmk-go/resources/views/livewire/buyer/account.blade.php ENDPATH**/ ?>