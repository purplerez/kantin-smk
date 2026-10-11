<?php ($method = $invoice->payment_method); ?>
<div>
    <?php if (isset($component)) { $__componentOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.page-header','data' => ['title' => 'Pembayaran','eyebrow' => $invoice->code,'back' => route('orders')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Pembayaran','eyebrow' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($invoice->code),'back' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('orders'))]); ?>
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
            
            <section class="rounded-3xl border border-slate-200 bg-white p-5 text-center" data-testid="payment-panel">
                <p class="text-xs text-slate-500">Total tagihan</p>
                <strong class="mt-1 block text-3xl font-extrabold tracking-tight" data-testid="invoice-total"><?php echo 'Rp'.number_format((int) ($invoice->total), 0, ',', '.'); ?></strong>
                <span class="mt-3 inline-flex items-center gap-1 rounded-full px-3 py-1 text-xs font-bold <?php echo e($invoice->isPaid() ? 'bg-brand-light text-brand-dark' : 'bg-amber-100 text-amber-800'); ?>" data-testid="invoice-payment-status">
                    <?php echo e($method->label()); ?> · <?php echo e($invoice->payment_status->label()); ?>

                </span>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($invoice->isPaid()): ?>
                    <div class="mt-6 flex flex-col items-center">
                        <span class="grid h-16 w-16 place-items-center rounded-full bg-brand text-white"><?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon','data' => ['name' => 'check','class' => 'h-8 w-8']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'check','class' => 'h-8 w-8']); ?>
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
                        <p class="mt-3 text-sm font-semibold">Pembayaran diterima <?php echo e($invoice->paid_at?->translatedFormat('d M Y H:i')); ?></p>
                        <p class="text-xs text-slate-500">Pantau status pesananmu di bawah.</p>
                    </div>
                <?php elseif($method === \App\Enums\PaymentMethod::Qris): ?>
                    <div class="mx-auto mt-6 w-56 rounded-2xl border-4 border-slate-900 bg-white p-3" data-testid="qris-code">
                        <svg viewBox="0 0 21 21" class="h-full w-full" shape-rendering="crispEdges">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $this->qrCells(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as [$x, $y]): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><rect x="<?php echo e($x); ?>" y="<?php echo e($y); ?>" width="1" height="1" fill="#0f172a" /><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </svg>
                    </div>
                    <p class="mt-3 text-xs text-slate-500">Scan dengan aplikasi e-wallet / m-banking (simulasi).</p>
                <?php elseif($method === \App\Enums\PaymentMethod::Transfer): ?>
                    <div class="mx-auto mt-6 max-w-xs rounded-2xl bg-slate-50 p-4 text-left" data-testid="va-panel">
                        <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Nomor Virtual Account</p>
                        <p class="mt-1 font-mono text-2xl font-bold tracking-wider" data-testid="va-number"><?php echo e($invoice->payment_reference); ?></p>
                        <p class="mt-2 text-xs text-slate-500">Bank BCA / Mandiri · a.n. KantinSMK Go</p>
                    </div>
                <?php else: ?>
                    <div class="mx-auto mt-6 max-w-xs rounded-2xl bg-slate-50 p-4 text-sm text-slate-600" data-testid="cash-panel">
                        Tunjukkan kode pesanan ke kasir tenant dan bayar <strong><?php echo 'Rp'.number_format((int) ($invoice->total), 0, ',', '.'); ?></strong> saat mengambil pesanan. Tenant akan menandai lunas.
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! $invoice->isPaid() && $method === \App\Enums\PaymentMethod::Qris): ?>
                    <button wire:click="simulatePayment" wire:loading.attr="disabled" class="mt-6 w-full rounded-full bg-brand py-3.5 text-sm font-bold text-white shadow-lg shadow-brand/30 hover:bg-brand-dark disabled:opacity-60 md:w-auto md:px-10" data-testid="simulate-payment-button">
                        Saya sudah bayar (simulasi QRIS)
                    </button>
                <?php elseif(! $invoice->isPaid() && $method === \App\Enums\PaymentMethod::Transfer): ?>
                    <p class="mt-6 inline-flex items-center gap-2 rounded-full bg-amber-100 px-4 py-2 text-xs font-semibold text-amber-800" data-testid="awaiting-verification">
                        Menunggu verifikasi pembayaran oleh tenant / admin.
                    </p>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </section>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($invoice->note): ?>
                <p class="rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm"><span class="text-slate-500">Catatan:</span> <?php echo e($invoice->note); ?></p>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>

        
        <aside class="space-y-3">
            <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-400"><?php echo e($invoice->orders->count()); ?> order dalam invoice ini</p>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $invoice->orders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <a href="<?php echo e(route('orders.show', $order)); ?>" wire:navigate class="block rounded-2xl border border-slate-200 bg-white p-4 transition hover:border-brand" data-testid="invoice-order-<?php echo e($order->id); ?>" wire:key="inv-order-<?php echo e($order->id); ?>">
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <strong class="block text-sm"><?php echo e($order->tenant->name); ?></strong>
                            <span class="text-xs text-slate-500"><?php echo e($order->code); ?></span>
                        </div>
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
                    </div>
                    <ul class="mt-2 text-xs text-slate-600">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $order->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li><?php echo e($item->qty); ?>× <?php echo e($item->product_name); ?></li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </ul>
                    <div class="mt-2 text-right text-sm font-bold"><?php echo 'Rp'.number_format((int) ($order->subtotal), 0, ',', '.'); ?></div>
                </a>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </aside>
    </div>
</div>
<?php /**PATH /Users/mac/Herd/kantinsmk-go/resources/views/livewire/buyer/invoice-show.blade.php ENDPATH**/ ?>