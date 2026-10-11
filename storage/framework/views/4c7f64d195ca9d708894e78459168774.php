<div>
    <?php if (isset($component)) { $__componentOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.page-header','data' => ['title' => 'Konfirmasi pesanan','eyebrow' => 'Langkah terakhir','back' => route('cart')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Konfirmasi pesanan','eyebrow' => 'Langkah terakhir','back' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('cart'))]); ?>
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

    <form wire:submit="placeOrder" class="grid gap-6 lg:grid-cols-[1fr_380px]" data-testid="checkout-form">
        <div class="space-y-5">
            <div class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-xs text-amber-800" data-testid="demo-alert">
                <strong>DEMO PAYMENT</strong> — Gateway pembayaran belum terhubung. Pilihan di bawah hanya simulasi untuk alur pesanan.
            </div>

            <section class="rounded-2xl border border-slate-200 bg-white p-4">
                <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-400">Ambil di</p>
                <div class="mt-2 flex items-center gap-3">
                    <span class="grid h-10 w-10 place-items-center rounded-xl bg-brand-light text-brand"><?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon','data' => ['name' => 'store','class' => 'h-5 w-5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'store','class' => 'h-5 w-5']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $attributes = $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $component = $__componentOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?></span>
                    <div>
                        <strong class="block text-sm"><?php echo e($groups->map(fn ($l) => $l->first()['product']->tenant->name)->implode(' & ')); ?></strong>
                        <span class="text-xs text-slate-500">Area kantin sekolah · Pickup langsung</span>
                    </div>
                </div>
            </section>

            <section class="rounded-2xl border border-slate-200 bg-white p-4">
                <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-400">Metode pembayaran</p>
                <div class="mt-3 space-y-2">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $methods; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $method): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <label class="flex cursor-pointer items-center gap-3 rounded-xl border p-3 transition <?php echo e($paymentMethod === $method->value ? 'border-brand bg-brand-light/60 ring-2 ring-brand/30' : 'border-slate-200 hover:border-slate-300'); ?>" data-testid="payment-<?php echo e($method->value); ?>-option">
                            <input type="radio" wire:model.live="paymentMethod" value="<?php echo e($method->value); ?>" class="h-4 w-4 accent-brand">
                            <span class="grid h-9 w-9 place-items-center rounded-lg bg-slate-100 text-slate-700"><?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon','data' => ['name' => $method === \App\Enums\PaymentMethod::Qris ? 'qr' : ($method === \App\Enums\PaymentMethod::Cash ? 'cash' : 'list'),'class' => 'h-5 w-5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($method === \App\Enums\PaymentMethod::Qris ? 'qr' : ($method === \App\Enums\PaymentMethod::Cash ? 'cash' : 'list')),'class' => 'h-5 w-5']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $attributes = $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $component = $__componentOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?></span>
                            <span class="flex-1">
                                <strong class="block text-sm"><?php echo e($method->label()); ?></strong>
                                <small class="text-xs text-slate-500"><?php echo e($method->description()); ?></small>
                            </span>
                        </label>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['paymentMethod'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="mt-2 text-xs text-rose-600"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </section>

            <section class="rounded-2xl border border-slate-200 bg-white p-4">
                <label class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-400">Catatan untuk tenant (opsional)
                    <textarea wire:model="note" rows="2" maxlength="200" placeholder="Contoh: ambil jam istirahat kedua" class="mt-2 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm normal-case tracking-normal outline-none focus:border-brand" data-testid="checkout-note-input"></textarea>
                </label>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['note'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="mt-1 text-xs text-rose-600"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </section>
        </div>

        <aside class="space-y-4 lg:sticky lg:top-24 lg:self-start">
            <section class="rounded-2xl border border-slate-200 bg-white p-4">
                <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-400">Ringkasan</p>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $groups; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tenantId => $lines): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="mt-3 border-t border-slate-100 pt-3 first:border-0" wire:key="sum-<?php echo e($tenantId); ?>">
                        <strong class="text-xs text-brand-dark"><?php echo e($lines->first()['product']->tenant->name); ?></strong>
                        <ul class="mt-1 space-y-1 text-sm">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $lines; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $line): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <li class="flex justify-between gap-3"><span class="text-slate-600"><?php echo e($line['qty']); ?>× <?php echo e($line['product']->name); ?></span><span><?php echo 'Rp'.number_format((int) ($line['line_total']), 0, ',', '.'); ?></span></li>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </ul>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <div class="mt-4 flex items-center justify-between border-t border-slate-200 pt-3">
                    <span class="text-sm font-semibold">Total bayar</span>
                    <strong class="text-xl font-extrabold" data-testid="checkout-total"><?php echo 'Rp'.number_format((int) ($total), 0, ',', '.'); ?></strong>
                </div>
            </section>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['cart'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="rounded-xl bg-rose-50 px-4 py-3 text-xs font-semibold text-rose-700" data-testid="checkout-error"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <button type="submit" wire:loading.attr="disabled" class="w-full rounded-full bg-brand py-3.5 text-sm font-bold text-white shadow-lg shadow-brand/30 transition hover:bg-brand-dark disabled:opacity-60" data-testid="confirm-order-button">
                <span wire:loading.remove wire:target="placeOrder">Buat pesanan</span>
                <span wire:loading wire:target="placeOrder">Memproses...</span>
            </button>
        </aside>
    </form>
</div>
<?php /**PATH /Users/mac/Herd/kantinsmk-go/resources/views/livewire/buyer/checkout.blade.php ENDPATH**/ ?>