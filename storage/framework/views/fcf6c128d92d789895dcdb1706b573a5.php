<div>
    <?php if (isset($component)) { $__componentOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.page-header','data' => ['title' => 'Settlement platform','eyebrow' => 'Keuangan semua tenant']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Settlement platform','eyebrow' => 'Keuangan semua tenant']); ?>
         <?php $__env->slot('actions', null, []); ?> 
            <div class="flex items-center gap-2">
                <input type="date" wire:model.live="date" class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm" data-testid="settlement-date">
                <button wire:click="generate" class="rounded-full bg-brand px-4 py-2.5 text-xs font-bold text-white hover:bg-brand-dark" data-testid="generate-settlement-button">Generate</button>
            </div>
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

    <div class="mb-5 grid grid-cols-2 gap-3 md:grid-cols-4">
        <div class="rounded-2xl border border-slate-200 bg-white p-4"><p class="text-xs text-slate-500">Order lunas</p><strong class="text-xl font-extrabold" data-testid="totals-orders"><?php echo e($totals['orders']); ?></strong></div>
        <div class="rounded-2xl border border-slate-200 bg-white p-4"><p class="text-xs text-slate-500">Bruto</p><strong class="text-xl font-extrabold"><?php echo 'Rp'.number_format((int) ($totals['gross']), 0, ',', '.'); ?></strong></div>
        <div class="rounded-2xl border border-slate-200 bg-white p-4"><p class="text-xs text-slate-500">Komisi (<?php echo e($commissionPercent); ?>%)</p><strong class="text-xl font-extrabold text-rose-600"><?php echo 'Rp'.number_format((int) ($totals['commission']), 0, ',', '.'); ?></strong></div>
        <div class="rounded-2xl border border-slate-200 bg-white p-4"><p class="text-xs text-slate-500">Neto tenant</p><strong class="text-xl font-extrabold text-brand-dark"><?php echo 'Rp'.number_format((int) ($totals['net']), 0, ',', '.'); ?></strong></div>
    </div>

    <div class="overflow-x-auto rounded-2xl border border-slate-200 bg-white">
        <table class="w-full min-w-[640px] text-sm">
            <thead class="bg-slate-50 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500">
                <tr><th class="px-4 py-3">Tenant</th><th class="px-4 py-3">Order</th><th class="px-4 py-3 text-right">Bruto</th><th class="px-4 py-3 text-right">Komisi</th><th class="px-4 py-3 text-right">Neto</th></tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $settlements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr wire:key="settlement-<?php echo e($s->id); ?>" data-testid="settlement-row-<?php echo e($s->tenant_id); ?>">
                        <td class="px-4 py-3 font-semibold"><?php echo e($s->tenant->name); ?></td>
                        <td class="px-4 py-3"><?php echo e($s->orders_count); ?></td>
                        <td class="px-4 py-3 text-right"><?php echo 'Rp'.number_format((int) ($s->gross), 0, ',', '.'); ?></td>
                        <td class="px-4 py-3 text-right text-rose-600"><?php echo 'Rp'.number_format((int) ($s->commission), 0, ',', '.'); ?></td>
                        <td class="px-4 py-3 text-right font-bold text-brand-dark"><?php echo 'Rp'.number_format((int) ($s->net), 0, ',', '.'); ?></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td colspan="5" class="px-4 py-10 text-center text-sm text-slate-500" data-testid="settlement-empty">Belum ada settlement untuk tanggal ini. Klik <strong>Generate</strong> untuk menghitung.</td></tr>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php /**PATH /Users/mac/Herd/kantinsmk-go/resources/views/livewire/admin/settlements.blade.php ENDPATH**/ ?>