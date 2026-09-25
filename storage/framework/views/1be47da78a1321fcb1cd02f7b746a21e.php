<div>
    <?php if (isset($component)) { $__componentOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.page-header','data' => ['title' => 'Kelola tenant','eyebrow' => 'Persetujuan & status']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Kelola tenant','eyebrow' => 'Persetujuan & status']); ?>
         <?php $__env->slot('actions', null, []); ?> 
            <button wire:click="create" class="flex items-center gap-1.5 rounded-full bg-brand px-4 py-2.5 text-xs font-bold text-white hover:bg-brand-dark" data-testid="add-tenant-button"><?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon','data' => ['name' => 'plus','class' => 'h-4 w-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'plus','class' => 'h-4 w-4']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $attributes = $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $component = $__componentOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?> Tenant baru</button>
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

    <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-3">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $tenants; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tenant): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <article class="overflow-hidden rounded-2xl border border-slate-200 bg-white" data-testid="tenant-row-<?php echo e($tenant->id); ?>" wire:key="tenant-<?php echo e($tenant->id); ?>">
                <div class="relative h-28 bg-slate-200">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($tenant->image_url): ?> <img src="<?php echo e($tenant->image_url); ?>" alt="" class="h-full w-full object-cover"> <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <span class="absolute left-3 top-3 rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider <?php echo e(['pending' => 'bg-amber-400 text-amber-950', 'active' => 'bg-brand text-white', 'suspended' => 'bg-rose-500 text-white'][$tenant->status]); ?>" data-testid="tenant-status-<?php echo e($tenant->id); ?>"><?php echo e($tenant->status); ?></span>
                </div>
                <div class="p-4">
                    <h3 class="text-base font-extrabold"><?php echo e($tenant->name); ?></h3>
                    <p class="mt-0.5 line-clamp-2 text-xs text-slate-500"><?php echo e($tenant->description); ?></p>
                    <div class="mt-3 flex gap-3 text-xs text-slate-600">
                        <span><b><?php echo e($tenant->products_count); ?></b> menu</span><span><b><?php echo e($tenant->staff_count); ?></b> staf</span><span><b><?php echo e($tenant->orders_count); ?></b> order</span>
                        <span class="ml-auto <?php echo e($tenant->is_open ? 'text-brand-dark' : 'text-slate-400'); ?>"><?php echo e($tenant->is_open ? 'Buka' : 'Tutup'); ?></span>
                    </div>
                    <div class="mt-4 flex flex-wrap gap-2">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($tenant->status !== 'active'): ?>
                            <button wire:click="setStatus(<?php echo e($tenant->id); ?>, 'active')" class="rounded-full bg-brand px-3 py-1.5 text-xs font-bold text-white hover:bg-brand-dark" data-testid="approve-tenant-<?php echo e($tenant->id); ?>"><?php echo e($tenant->status === 'pending' ? 'Setujui' : 'Aktifkan'); ?></button>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($tenant->status === 'active'): ?>
                            <button wire:click="setStatus(<?php echo e($tenant->id); ?>, 'suspended')" wire:confirm="Tangguhkan tenant <?php echo e($tenant->name); ?>? Staf tidak bisa masuk panel." class="rounded-full border border-rose-200 px-3 py-1.5 text-xs font-bold text-rose-600 hover:bg-rose-50" data-testid="suspend-tenant-<?php echo e($tenant->id); ?>">Tangguhkan</button>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <button wire:click="edit(<?php echo e($tenant->id); ?>)" class="rounded-full px-3 py-1.5 text-xs font-bold text-slate-700 hover:bg-slate-100" data-testid="edit-tenant-<?php echo e($tenant->id); ?>">Edit</button>
                    </div>
                </div>
            </article>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showForm): ?>
        <div class="fixed inset-0 z-40 flex items-end justify-center bg-slate-900/50 md:items-center md:p-6" data-testid="tenant-form-modal">
            <form wire:submit="save" class="max-h-[92vh] w-full max-w-lg overflow-y-auto rounded-t-3xl bg-white p-6 md:rounded-3xl">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-extrabold"><?php echo e($editingId ? 'Edit tenant' : 'Tenant baru'); ?></h2>
                    <button type="button" wire:click="$set('showForm', false)" class="grid h-9 w-9 place-items-center rounded-full hover:bg-slate-100" data-testid="close-form-button"><?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon','data' => ['name' => 'x','class' => 'h-5 w-5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'x','class' => 'h-5 w-5']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $attributes = $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $component = $__componentOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?></button>
                </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = [['name', 'Nama tenant', 'text'], ['image_url', 'URL gambar', 'url'], ['bank_name', 'Bank', 'text'], ['bank_account', 'No. rekening', 'text'], ['bank_holder', 'Atas nama', 'text']]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as [$field, $label, $type]): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <label class="mt-4 block text-xs font-bold"><?php echo e($label); ?>

                        <input type="<?php echo e($type); ?>" wire:model="<?php echo e($field); ?>" class="mt-1.5 w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm outline-none focus:border-brand" data-testid="tenant-<?php echo e(str_replace('_', '-', $field)); ?>-input">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = [$field];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-xs font-normal text-rose-600"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </label>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <label class="mt-4 block text-xs font-bold">Deskripsi
                    <textarea wire:model="description" rows="2" class="mt-1.5 w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm outline-none focus:border-brand" data-testid="tenant-description-input"></textarea>
                </label>
                <p class="mt-3 text-xs text-slate-500">Tenant baru berstatus <b>pending</b> sampai disetujui. Tambahkan akun Tenant Admin di menu Pengguna.</p>
                <button class="mt-6 w-full rounded-full bg-brand py-3 text-sm font-bold text-white hover:bg-brand-dark" data-testid="save-tenant-button">Simpan</button>
            </form>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>
<?php /**PATH /Users/mac/Herd/kantinsmk-go/resources/views/livewire/admin/tenants.blade.php ENDPATH**/ ?>