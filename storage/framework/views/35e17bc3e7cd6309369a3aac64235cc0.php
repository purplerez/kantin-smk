<div>
    
    <div class="flex items-center justify-between gap-4">
        <div>
            <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-brand-dark"><?php echo e(now()->translatedFormat('l, d F Y')); ?></p>
            <h1 class="mt-1 text-2xl font-extrabold tracking-tight md:text-3xl">Halo, <?php echo e(explode(' ', auth()->user()->name)[0]); ?> 👋</h1>
            <p class="text-sm text-slate-500">Mau jajan apa hari ini?</p>
        </div>
        <a href="<?php echo e(route('account')); ?>" wire:navigate class="grid h-11 w-11 place-items-center rounded-full bg-accent/15 text-sm font-bold text-accent md:hidden" data-testid="account-avatar"><?php echo e(auth()->user()->initials()); ?></a>
    </div>

    
    <label class="mt-5 flex items-center gap-3 rounded-2xl border border-slate-200 bg-white px-4 py-3 shadow-sm focus-within:border-brand focus-within:ring-4 focus-within:ring-brand/20">
        <?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon','data' => ['name' => 'search','class' => 'h-5 w-5 text-slate-400']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'search','class' => 'h-5 w-5 text-slate-400']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $attributes = $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $component = $__componentOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
        <input type="search" wire:model.live.debounce.300ms="search" placeholder="Cari menu atau tenant..." class="w-full bg-transparent text-sm outline-none" data-testid="catalog-search-input">
    </label>

    
    <div class="no-scrollbar -mx-4 mt-4 flex gap-2 overflow-x-auto px-4">
        <button wire:click="$set('category', '')" class="shrink-0 rounded-full px-4 py-2 text-xs font-bold transition <?php echo e($category === '' ? 'bg-brand text-white' : 'bg-white text-slate-600 ring-1 ring-slate-200'); ?>" data-testid="category-all-button">Semua</button>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <button wire:click="$set('category', '<?php echo e($cat->slug); ?>')" class="shrink-0 rounded-full px-4 py-2 text-xs font-bold transition <?php echo e($category === $cat->slug ? 'bg-brand text-white' : 'bg-white text-slate-600 ring-1 ring-slate-200'); ?>" data-testid="category-<?php echo e($cat->slug); ?>-button"><?php echo e($cat->name); ?></button>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($search === '' && $category === ''): ?>
        <section class="mt-8">
            <div class="mb-3 flex items-end justify-between">
                <div>
                    <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-400">Tenant kantin</p>
                    <h2 class="text-base font-extrabold md:text-lg">Pilih kantin</h2>
                </div>
            </div>
            <div class="no-scrollbar -mx-4 flex gap-3 overflow-x-auto px-4 pb-1">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $tenants; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tenant): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <a href="<?php echo e(route('tenant.menu', $tenant)); ?>" wire:navigate class="group relative w-44 shrink-0 overflow-hidden rounded-2xl bg-slate-900 text-white shadow-sm" data-testid="tenant-card-<?php echo e($tenant->id); ?>">
                        <img src="<?php echo e($tenant->image_url); ?>" alt="<?php echo e($tenant->name); ?>" class="h-28 w-full object-cover opacity-80 transition group-hover:scale-105 group-hover:opacity-90">
                        <div class="absolute inset-0 bg-gradient-to-t from-slate-900 via-slate-900/30 to-transparent"></div>
                        <div class="absolute inset-x-3 bottom-3">
                            <div class="text-sm font-bold leading-tight"><?php echo e($tenant->name); ?></div>
                            <div class="mt-0.5 flex items-center gap-1 text-[11px] text-slate-300">
                                <span class="h-1.5 w-1.5 rounded-full <?php echo e($tenant->is_open ? 'bg-emerald-400' : 'bg-rose-400'); ?>"></span>
                                <?php echo e($tenant->is_open ? 'Buka' : 'Tutup'); ?> · <?php echo e($tenant->products_count); ?> menu
                            </div>
                        </div>
                    </a>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </section>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <section class="mt-8">
        <div class="mb-3 flex items-end justify-between">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-400">Pilihan untukmu</p>
                <h2 class="text-base font-extrabold md:text-lg">Menu kantin</h2>
            </div>
            <span class="text-xs font-semibold text-slate-500" data-testid="result-count"><?php echo e($products->count()); ?> menu</span>
        </div>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($products->isEmpty()): ?>
            <?php if (isset($component)) { $__componentOriginal074a021b9d42f490272b5eefda63257c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal074a021b9d42f490272b5eefda63257c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.empty-state','data' => ['icon' => 'search','title' => 'Menu tidak ditemukan','text' => 'Coba kata kunci lain atau pilih kategori berbeda.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'search','title' => 'Menu tidak ditemukan','text' => 'Coba kata kunci lain atau pilih kategori berbeda.']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal074a021b9d42f490272b5eefda63257c)): ?>
<?php $attributes = $__attributesOriginal074a021b9d42f490272b5eefda63257c; ?>
<?php unset($__attributesOriginal074a021b9d42f490272b5eefda63257c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal074a021b9d42f490272b5eefda63257c)): ?>
<?php $component = $__componentOriginal074a021b9d42f490272b5eefda63257c; ?>
<?php unset($__componentOriginal074a021b9d42f490272b5eefda63257c); ?>
<?php endif; ?>
        <?php else: ?>
            <div class="grid grid-cols-2 gap-3 md:grid-cols-3 md:gap-5 lg:grid-cols-4">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php if (isset($component)) { $__componentOriginal3fd2897c1d6a149cdb97b41db9ff827a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3fd2897c1d6a149cdb97b41db9ff827a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.product-card','data' => ['product' => $product,'key' => $product->id]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('product-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['product' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($product),'key' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($product->id)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal3fd2897c1d6a149cdb97b41db9ff827a)): ?>
<?php $attributes = $__attributesOriginal3fd2897c1d6a149cdb97b41db9ff827a; ?>
<?php unset($__attributesOriginal3fd2897c1d6a149cdb97b41db9ff827a); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal3fd2897c1d6a149cdb97b41db9ff827a)): ?>
<?php $component = $__componentOriginal3fd2897c1d6a149cdb97b41db9ff827a; ?>
<?php unset($__componentOriginal3fd2897c1d6a149cdb97b41db9ff827a); ?>
<?php endif; ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </section>
</div>
<?php /**PATH /Users/mac/Herd/kantinsmk-go/resources/views/livewire/buyer/catalog.blade.php ENDPATH**/ ?>