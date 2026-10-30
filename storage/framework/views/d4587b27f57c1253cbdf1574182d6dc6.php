<div>
    <?php if (isset($component)) { $__componentOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.page-header','data' => ['title' => 'Jam Operasional','eyebrow' => 'Pengaturan Toko']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Jam Operasional','eyebrow' => 'Pengaturan Toko']); ?>
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

    <div class="mx-auto max-w-2xl">
        
        <div class="mb-6 rounded-2xl border border-violet-200 bg-violet-50 p-4 text-sm text-violet-800">
            <strong>Cara kerja Pre-Order:</strong> Pesanan yang masuk di luar jam operasional akan berstatus
            <span class="inline-flex items-center rounded-full bg-violet-100 px-2 py-0.5 text-xs font-bold text-violet-800">Pre-Order</span>
            dan akan dikonfirmasi otomatis ketika toko mulai buka.
        </div>

        <form wire:submit="save" class="rounded-3xl border border-slate-200 bg-white shadow-sm">
            <header class="border-b border-slate-100 px-6 py-4">
                <h2 class="text-base font-bold">Jadwal Buka per Hari</h2>
                <p class="mt-0.5 text-xs text-slate-500">Hari yang tidak dicentang "Buka" dianggap libur — tidak menerima pesanan.</p>
            </header>

            <ul class="divide-y divide-slate-100">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $dayNames; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $d => $dayName): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li class="flex flex-wrap items-center gap-4 px-6 py-4" wire:key="day-<?php echo e($d); ?>">
                        
                        <span class="w-16 text-sm font-bold text-slate-700"><?php echo e($dayName); ?></span>

                        
                        <label class="flex items-center gap-2 text-sm">
                            <input type="checkbox" wire:model.live="hours.<?php echo e($d); ?>.is_open"
                                   class="h-4 w-4 rounded border-slate-300 accent-brand"
                                   data-testid="hours-open-<?php echo e($d); ?>">
                            <span class="font-semibold <?php echo e($hours[$d]['is_open'] ? 'text-brand-dark' : 'text-slate-400'); ?>">
                                <?php echo e($hours[$d]['is_open'] ? 'Buka' : 'Tutup'); ?>

                            </span>
                        </label>

                        
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($hours[$d]['is_open']): ?>
                            <div class="flex items-center gap-2 text-sm">
                                <input type="time" wire:model="hours.<?php echo e($d); ?>.open_at"
                                       class="rounded-lg border border-slate-200 px-3 py-1.5 text-sm outline-none focus:border-brand"
                                       data-testid="hours-open-at-<?php echo e($d); ?>">
                                <span class="text-slate-400">—</span>
                                <input type="time" wire:model="hours.<?php echo e($d); ?>.close_at"
                                       class="rounded-lg border border-slate-200 px-3 py-1.5 text-sm outline-none focus:border-brand"
                                       data-testid="hours-close-at-<?php echo e($d); ?>">
                            </div>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ["hours.{$d}.close_at"];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <span class="w-full text-xs text-rose-600"><?php echo e($message); ?></span>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php else: ?>
                            <span class="text-xs text-slate-400 italic">Libur seharian</span>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </ul>

            <footer class="flex items-center justify-end gap-3 border-t border-slate-100 px-6 py-4">
                <button type="submit"
                        class="rounded-full bg-brand px-8 py-2.5 text-sm font-bold text-white shadow-md shadow-brand/20 hover:bg-brand-dark"
                        data-testid="save-hours-button">
                    Simpan Jadwal
                </button>
            </footer>
        </form>

        
        <?php
            $tenant = auth()->user()->tenant;
            $tenant->setRelation('hours', collect(array_map(
                fn ($d, $row) => (object) array_merge($row, ['day_of_week' => $d]),
                array_keys($hours), $hours
            )));
        ?>
    </div>
</div>

<?php /**PATH /Users/mac/Herd/kantinsmk-go/resources/views/livewire/tenant/operating-hours.blade.php ENDPATH**/ ?>