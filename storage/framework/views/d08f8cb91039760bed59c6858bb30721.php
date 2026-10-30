<div x-data="{ tab: 'products' }">
    <?php if (isset($component)) { $__componentOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.page-header','data' => ['title' => 'Kasir Counter','eyebrow' => ''.e(auth()->user()->tenant?->name).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Kasir Counter','eyebrow' => ''.e(auth()->user()->tenant?->name).'']); ?>
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

    <div class="grid gap-6 lg:grid-cols-[1fr_360px]">

        
        
        
        <section class="space-y-4">
            
            <div class="rounded-3xl border border-slate-200 bg-white shadow-sm">
                <header class="border-b border-slate-100 px-5 py-3.5">
                    <h2 class="text-sm font-bold text-slate-700">Pilih Menu</h2>
                    <p class="text-xs text-slate-400 mt-0.5">Hanya menu yang tersedia hari ini ditampilkan.</p>
                </header>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($products->isEmpty()): ?>
                    <div class="px-5 py-10 text-center text-sm text-slate-400">
                        Tidak ada menu tersedia hari ini.
                    </div>
                <?php else: ?>
                    <ul class="grid grid-cols-2 gap-3 p-4 sm:grid-cols-3">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php ($inCart = $cart[(string) $product->id] ?? 0); ?>
                            <li class="flex flex-col overflow-hidden rounded-2xl border border-slate-100 bg-slate-50 transition hover:border-brand/40"
                                wire:key="prod-<?php echo e($product->id); ?>" data-testid="product-<?php echo e($product->id); ?>">
                                
                                <div class="flex-1 px-3 pt-3 pb-2">
                                    <p class="text-sm font-semibold leading-tight text-slate-800 line-clamp-2"><?php echo e($product->name); ?></p>
                                    <p class="mt-1 text-xs font-bold text-brand-dark"><?php echo 'Rp'.number_format((int) ($product->price), 0, ',', '.'); ?></p>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($product->category): ?>
                                        <span class="mt-1 inline-block rounded-full bg-slate-200 px-2 py-0.5 text-[10px] text-slate-500"><?php echo e($product->category->name); ?></span>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>

                                
                                <div class="border-t border-slate-100 px-3 py-2">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($inCart === 0): ?>
                                        <button wire:click="addToCart(<?php echo e($product->id); ?>)"
                                                class="w-full rounded-xl bg-brand py-1.5 text-xs font-bold text-white hover:bg-brand-dark transition"
                                                data-testid="add-to-cart-<?php echo e($product->id); ?>">
                                            + Tambah
                                        </button>
                                    <?php else: ?>
                                        <div class="flex items-center justify-between gap-2">
                                            <button wire:click="decrementCart(<?php echo e($product->id); ?>)"
                                                    class="grid h-7 w-7 place-items-center rounded-full bg-slate-200 text-sm font-bold hover:bg-rose-100 hover:text-rose-600 transition"
                                                    data-testid="decrement-<?php echo e($product->id); ?>">−</button>
                                            <span class="text-sm font-bold text-brand-dark" data-testid="qty-<?php echo e($product->id); ?>"><?php echo e($inCart); ?></span>
                                            <button wire:click="addToCart(<?php echo e($product->id); ?>)"
                                                    class="grid h-7 w-7 place-items-center rounded-full bg-brand text-sm font-bold text-white hover:bg-brand-dark transition"
                                                    data-testid="increment-<?php echo e($product->id); ?>">+</button>
                                        </div>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                            </li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </ul>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

            
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($recentOrders->isNotEmpty()): ?>
                <div class="rounded-3xl border border-slate-200 bg-white shadow-sm">
                    <header class="border-b border-slate-100 px-5 py-3.5">
                        <h2 class="text-sm font-bold text-slate-700">Transaksi Terakhir</h2>
                    </header>
                    <ul class="divide-y divide-slate-100">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $recentOrders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $recent): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li class="flex items-center justify-between gap-3 px-5 py-3 text-sm" wire:key="recent-<?php echo e($recent->id); ?>" data-testid="recent-order-<?php echo e($recent->id); ?>">
                                <div class="min-w-0">
                                    <p class="font-semibold truncate"><?php echo e($recent->buyer->name); ?></p>
                                    <p class="text-xs text-slate-400">
                                        <?php echo e($recent->items->pluck('product_name')->implode(', ')); ?>

                                        · <?php echo e($recent->completed_at?->setTimezone('Asia/Jakarta')->format('H:i')); ?>

                                    </p>
                                </div>
                                <span class="shrink-0 font-bold text-brand-dark"><?php echo 'Rp'.number_format((int) ($recent->subtotal), 0, ',', '.'); ?></span>
                            </li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </ul>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </section>

        
        
        
        <aside class="space-y-4 lg:sticky lg:top-24 lg:self-start">

            
            <div class="rounded-3xl border border-slate-200 bg-white shadow-sm">
                <header class="flex items-center justify-between border-b border-slate-100 px-5 py-3.5">
                    <h2 class="text-sm font-bold text-slate-700">Keranjang</h2>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! empty($cart)): ?>
                        <button wire:click="clearCart" class="text-xs font-semibold text-rose-500 hover:underline"
                                data-testid="clear-cart-button">Kosongkan</button>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </header>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(empty($cartLines)): ?>
                    <div class="px-5 py-8 text-center text-sm text-slate-400" data-testid="empty-cart-message">
                        Belum ada menu dipilih.
                    </div>
                <?php else: ?>
                    <ul class="divide-y divide-slate-100">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $cartLines; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $line): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li class="flex items-center justify-between gap-3 px-5 py-2.5 text-sm"
                                wire:key="cart-line-<?php echo e($line['product']->id); ?>"
                                data-testid="cart-item-<?php echo e($line['product']->id); ?>">
                                <div class="min-w-0">
                                    <p class="font-semibold truncate"><?php echo e($line['product']->name); ?></p>
                                    <p class="text-xs text-slate-400"><?php echo e($line['qty']); ?> × <?php echo 'Rp'.number_format((int) ($line['product']->price), 0, ',', '.'); ?></p>
                                </div>
                                <div class="flex items-center gap-2 shrink-0">
                                    <span class="font-bold"><?php echo 'Rp'.number_format((int) ($line['lineTotal']), 0, ',', '.'); ?></span>
                                    <button wire:click="removeFromCart(<?php echo e($line['product']->id); ?>)"
                                            class="grid h-6 w-6 place-items-center rounded-full text-slate-400 hover:bg-rose-100 hover:text-rose-600 transition text-xs"
                                            data-testid="remove-<?php echo e($line['product']->id); ?>">✕</button>
                                </div>
                            </li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </ul>
                    <div class="flex items-center justify-between border-t border-slate-200 px-5 py-3">
                        <span class="text-sm font-semibold text-slate-600">Total</span>
                        <strong class="text-xl font-extrabold text-brand-dark" data-testid="cart-total"><?php echo 'Rp'.number_format((int) ($cartTotal), 0, ',', '.'); ?></strong>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['cart'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <p class="px-5 pb-3 text-xs text-rose-600"><?php echo e($message); ?></p>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

            
            <div class="rounded-3xl border border-slate-200 bg-white shadow-sm">
                <header class="border-b border-slate-100 px-5 py-3.5">
                    <h2 class="text-sm font-bold text-slate-700">Pembeli <span class="text-slate-400 font-normal">(opsional)</span></h2>
                </header>
                <div class="p-4 space-y-2">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($selectedBuyer): ?>
                        <div class="flex items-center justify-between rounded-xl bg-brand-light/50 px-4 py-2.5" data-testid="selected-buyer">
                            <div>
                                <p class="text-sm font-bold text-brand-dark"><?php echo e($selectedBuyer->name); ?></p>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($selectedBuyer->identifier): ?>
                                    <p class="text-xs text-slate-500"><?php echo e($selectedBuyer->identifier); ?></p>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                            <button wire:click="clearBuyer" class="text-xs text-slate-500 hover:text-rose-600" data-testid="clear-buyer-button">✕</button>
                        </div>
                    <?php else: ?>
                        <p class="text-xs text-slate-400 italic">Tidak ada pembeli dipilih — akan dicatat sebagai <strong>Pelanggan Umum</strong>.</p>
                        <button wire:click="selectAnonymous" class="hidden" data-testid="select-anonymous-button">anonim</button>

                        <div class="relative">
                            <input type="text" wire:model.live.debounce.300ms="buyerSearch"
                                   placeholder="Cari nama / NIS / email..."
                                   class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm outline-none focus:border-brand"
                                   data-testid="buyer-search-input">
                        </div>

                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($buyerResults->isNotEmpty()): ?>
                            <ul class="max-h-40 overflow-y-auto rounded-xl border border-slate-200 bg-white shadow-sm divide-y divide-slate-100">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $buyerResults; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $br): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <li>
                                        <button wire:click="selectBuyer(<?php echo e($br->id); ?>)"
                                                class="w-full px-4 py-2.5 text-left text-sm hover:bg-slate-50 transition"
                                                data-testid="buyer-result-<?php echo e($br->id); ?>">
                                            <span class="font-semibold"><?php echo e($br->name); ?></span>
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($br->identifier): ?> <span class="text-xs text-slate-400">· <?php echo e($br->identifier); ?></span> <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </button>
                                    </li>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </ul>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>

            
            <div class="rounded-3xl border border-slate-200 bg-white shadow-sm p-4 space-y-3">
                <h2 class="text-sm font-bold text-slate-700">Metode Bayar</h2>
                <div class="grid grid-cols-2 gap-2">
                    <label class="flex cursor-pointer items-center gap-2 rounded-xl border px-4 py-3 text-sm font-semibold transition
                        <?php echo e($paymentMethod === 'cash' ? 'border-brand bg-brand-light/40 text-brand-dark' : 'border-slate-200 text-slate-600 hover:border-slate-300'); ?>"
                        data-testid="payment-cash">
                        <input type="radio" wire:model.live="paymentMethod" value="cash" class="sr-only">
                        💵 Tunai
                    </label>
                    <label class="flex cursor-pointer items-center gap-2 rounded-xl border px-4 py-3 text-sm font-semibold transition
                        <?php echo e($paymentMethod === 'qris' ? 'border-brand bg-brand-light/40 text-brand-dark' : 'border-slate-200 text-slate-600 hover:border-slate-300'); ?>"
                        data-testid="payment-qris">
                        <input type="radio" wire:model.live="paymentMethod" value="qris" class="sr-only">
                        📱 QRIS
                    </label>
                </div>

                <textarea wire:model="note" rows="2" maxlength="200" placeholder="Catatan (opsional)"
                          class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm outline-none focus:border-brand resize-none"
                          data-testid="counter-note-input"></textarea>

                <button wire:click="save" wire:loading.attr="disabled"
                        <?php if(empty($cart)): ?> disabled <?php endif; ?>
                        class="w-full rounded-full py-4 text-base font-extrabold shadow-lg transition
                        <?php echo e(empty($cart)
                            ? 'bg-slate-100 text-slate-400 cursor-not-allowed'
                            : 'bg-brand text-white shadow-brand/25 hover:bg-brand-dark'); ?>"
                        data-testid="counter-pay-button">
                    <span wire:loading.remove>
                        <?php echo e(empty($cart) ? 'Pilih menu dahulu' : 'Bayar ' . (empty($cartTotal) ? '' : number_format($cartTotal, 0, ',', '.'))); ?>

                    </span>
                    <span wire:loading>Menyimpan…</span>
                </button>
            </div>

        </aside>
    </div>
</div>

<?php /**PATH /Users/mac/Herd/kantinsmk-go/resources/views/livewire/tenant/counter-cashier.blade.php ENDPATH**/ ?>