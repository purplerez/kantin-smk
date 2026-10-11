<?php ($isCancelled = $order->status === \App\Enums\OrderStatus::Cancelled); ?>

<div <?php if (! ($order->status->isFinal())): ?> wire:poll.<?php echo e($pollSeconds); ?>s="tick" <?php endif; ?> data-testid="order-detail-page">
    <?php if (isset($component)) { $__componentOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.page-header','data' => ['title' => $order->tenant->name,'eyebrow' => $order->code,'back' => route('orders')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($order->tenant->name),'eyebrow' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($order->code),'back' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('orders'))]); ?>
         <?php $__env->slot('actions', null, []); ?> <?php if (isset($component)) { $__componentOriginal8c81617a70e11bcf247c4db924ab1b62 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8c81617a70e11bcf247c4db924ab1b62 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.status-badge','data' => ['status' => $order->status,'class' => 'text-xs','dataTestid' => 'order-status']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($order->status),'class' => 'text-xs','data-testid' => 'order-status']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8c81617a70e11bcf247c4db924ab1b62)): ?>
<?php $attributes = $__attributesOriginal8c81617a70e11bcf247c4db924ab1b62; ?>
<?php unset($__attributesOriginal8c81617a70e11bcf247c4db924ab1b62); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8c81617a70e11bcf247c4db924ab1b62)): ?>
<?php $component = $__componentOriginal8c81617a70e11bcf247c4db924ab1b62; ?>
<?php unset($__componentOriginal8c81617a70e11bcf247c4db924ab1b62); ?>
<?php endif; ?> <?php $__env->endSlot(); ?>
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

    <div class="grid gap-6 lg:grid-cols-[1fr_380px]">
        <div class="space-y-5">
            
            <section class="rounded-2xl border border-slate-200 bg-white p-5" data-testid="order-timeline">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isCancelled): ?>
                    <div class="flex items-center gap-3 text-rose-700">
                        <span class="grid h-10 w-10 place-items-center rounded-full bg-rose-100"><?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
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
<?php endif; ?></span>
                        <div><strong class="block text-sm">Pesanan dibatalkan</strong><span class="text-xs"><?php echo e($order->cancel_reason); ?> · <?php echo e($order->cancelled_at?->translatedFormat('d M H:i')); ?></span></div>
                    </div>
                <?php else: ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($order->status === \App\Enums\OrderStatus::Pending): ?>
                        <a href="<?php echo e(route('invoice.show', $order->invoice)); ?>" wire:navigate class="mb-5 flex items-center justify-between rounded-xl bg-amber-50 px-4 py-3 text-sm font-bold text-amber-800" data-testid="pay-now-link">Menunggu pembayaran — bayar sekarang <span>→</span></a>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <ol class="grid grid-cols-4 gap-1">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $timeline; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $step): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php ($done = $currentIndex !== false && $i <= $currentIndex); ?>
                            <li class="flex flex-col items-center text-center" data-testid="timeline-step-<?php echo e($step->value); ?>">
                                <div class="flex w-full items-center">
                                    <span class="h-0.5 flex-1 <?php echo e($i === 0 ? 'bg-transparent' : ($done ? 'bg-brand' : 'bg-slate-200')); ?>"></span>
                                    <span class="grid h-8 w-8 shrink-0 place-items-center rounded-full text-xs font-bold <?php echo e($done ? 'bg-brand text-white' : 'bg-slate-100 text-slate-400'); ?> <?php echo e($i === $currentIndex ? 'ring-4 ring-brand/25' : ''); ?>">
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($done): ?> <?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon','data' => ['name' => 'check','class' => 'h-4 w-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'check','class' => 'h-4 w-4']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $attributes = $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $component = $__componentOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?> <?php else: ?> <?php echo e($i + 1); ?> <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </span>
                                    <span class="h-0.5 flex-1 <?php echo e($loop->last ? 'bg-transparent' : ($currentIndex !== false && $i < $currentIndex ? 'bg-brand' : 'bg-slate-200')); ?>"></span>
                                </div>
                                <small class="mt-2 text-[10px] font-semibold leading-tight <?php echo e($done ? 'text-slate-900' : 'text-slate-400'); ?>"><?php echo e($step->label()); ?></small>
                            </li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </ol>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($order->status === \App\Enums\OrderStatus::Ready): ?>
                        <p class="mt-5 rounded-xl bg-brand-light px-4 py-3 text-center text-sm font-bold text-brand-dark">Pesanan siap! Tunjukkan kode <span class="font-mono"><?php echo e($order->code); ?></span> ke kasir <?php echo e($order->tenant->name); ?>.</p>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </section>

            
            <section class="rounded-2xl border border-slate-200 bg-white">
                <header class="border-b border-slate-100 px-4 py-3 text-[11px] font-bold uppercase tracking-[0.18em] text-slate-400">Rincian pesanan</header>
                <ul class="divide-y divide-slate-100">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $order->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li class="flex items-start justify-between gap-3 px-4 py-3 text-sm" data-testid="order-item-<?php echo e($item->id); ?>">
                            <div>
                                <strong><?php echo e($item->qty); ?>× <?php echo e($item->product_name); ?></strong>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($item->note): ?> <p class="text-xs text-slate-500">Catatan: <?php echo e($item->note); ?></p> <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                            <span><?php echo 'Rp'.number_format((int) ($item->line_total), 0, ',', '.'); ?></span>
                        </li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </ul>
                <footer class="flex items-center justify-between border-t border-slate-200 px-4 py-3">
                    <span class="text-sm font-semibold">Subtotal</span>
                    <strong class="text-lg font-extrabold" data-testid="order-subtotal"><?php echo 'Rp'.number_format((int) ($order->subtotal), 0, ',', '.'); ?></strong>
                </footer>
            </section>

            
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($order->review): ?>
                <section class="rounded-2xl border border-slate-200 bg-white p-4" data-testid="order-review">
                    <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-400">Ulasanmu</p>
                    <div class="mt-2 flex text-amber-400"><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php for($i = 1; $i <= 5; $i++): ?><?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon','data' => ['name' => 'star','class' => 'h-5 w-5 '.e($i <= $order->review->rating ? 'fill-amber-400' : 'text-slate-300').'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'star','class' => 'h-5 w-5 '.e($i <= $order->review->rating ? 'fill-amber-400' : 'text-slate-300').'']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $attributes = $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $component = $__componentOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?><?php endfor; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?></div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($order->review->comment): ?> <p class="mt-2 text-sm text-slate-600"><?php echo e($order->review->comment); ?></p> <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </section>
            <?php elseif($order->canBeReviewed()): ?>
                <form wire:submit="submitReview" class="rounded-2xl border border-slate-200 bg-white p-4" data-testid="review-form">
                    <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-400">Beri ulasan untuk <?php echo e($order->tenant->name); ?></p>
                    <div class="mt-3 flex gap-1">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php for($i = 1; $i <= 5; $i++): ?>
                            <button type="button" wire:click="$set('rating', <?php echo e($i); ?>)" class="text-amber-400 transition hover:scale-110" data-testid="rating-<?php echo e($i); ?>-button" aria-label="<?php echo e($i); ?> bintang">
                                <?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon','data' => ['name' => 'star','class' => 'h-8 w-8 '.e($i <= $rating ? 'fill-amber-400' : 'text-slate-300').'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'star','class' => 'h-8 w-8 '.e($i <= $rating ? 'fill-amber-400' : 'text-slate-300').'']); ?>
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
                            </button>
                        <?php endfor; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                    <textarea wire:model="comment" rows="2" maxlength="300" placeholder="Ceritakan pengalamanmu (opsional)" class="mt-3 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm outline-none focus:border-brand" data-testid="review-comment-input"></textarea>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['comment'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="mt-1 text-xs text-rose-600"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <button class="mt-3 rounded-full bg-brand px-6 py-2.5 text-sm font-bold text-white hover:bg-brand-dark" data-testid="submit-review-button">Kirim ulasan</button>
                </form>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>

        <aside class="space-y-3 lg:sticky lg:top-24 lg:self-start">
            <section class="rounded-2xl border border-slate-200 bg-white p-4 text-sm">
                <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-400">Pembayaran</p>
                <dl class="mt-2 space-y-1.5">
                    <div class="flex justify-between"><dt class="text-slate-500">Metode</dt><dd class="font-semibold"><?php echo e($order->invoice->payment_method->label()); ?></dd></div>
                    <div class="flex justify-between"><dt class="text-slate-500">Status</dt><dd class="font-semibold <?php echo e($order->isPaid() ? 'text-brand-dark' : 'text-amber-700'); ?>" data-testid="order-payment-status"><?php echo e($order->payment_status->label()); ?></dd></div>
                    <div class="flex justify-between"><dt class="text-slate-500">Invoice</dt><dd><a href="<?php echo e(route('invoice.show', $order->invoice)); ?>" wire:navigate class="font-semibold text-brand-dark underline-offset-2 hover:underline" data-testid="invoice-link"><?php echo e($order->invoice->code); ?></a></dd></div>
                    <div class="flex justify-between"><dt class="text-slate-500">Dipesan</dt><dd class="font-semibold"><?php echo e($order->created_at->translatedFormat('d M Y, H:i')); ?></dd></div>
                </dl>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($order->note): ?> <p class="mt-3 rounded-xl bg-slate-50 px-3 py-2 text-xs"><span class="text-slate-500">Catatan:</span> <?php echo e($order->note); ?></p> <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </section>

            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('cancel', $order)): ?>
                <button wire:click="cancel" wire:confirm="Batalkan pesanan ini?" class="w-full rounded-full border border-rose-200 py-3 text-sm font-bold text-rose-600 hover:bg-rose-50" data-testid="cancel-order-button">Batalkan pesanan</button>
            <?php endif; ?>
        </aside>
    </div>
</div>
<?php /**PATH /Users/mac/Herd/kantinsmk-go/resources/views/livewire/buyer/order-detail.blade.php ENDPATH**/ ?>