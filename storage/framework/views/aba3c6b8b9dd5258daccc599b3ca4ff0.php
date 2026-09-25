<div>
    <?php if (isset($component)) { $__componentOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.page-header','data' => ['title' => 'Pembayaran & settlement','eyebrow' => 'Keuangan tenant']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Pembayaran & settlement','eyebrow' => 'Keuangan tenant']); ?>
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

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($pendingTransfers->isNotEmpty()): ?>
        <section class="mb-6 rounded-2xl border border-sky-200 bg-sky-50/60" data-testid="pending-transfers">
            <header class="border-b border-sky-100 px-4 py-3">
                <h2 class="text-base font-extrabold">Transfer menunggu verifikasi <span class="ml-1 rounded-full bg-sky-100 px-2 py-0.5 text-xs text-sky-800" data-testid="pending-transfer-count"><?php echo e($pendingTransfers->count()); ?></span></h2>
                <p class="text-xs text-slate-500">Cek mutasi rekening lalu tandai lunas. Hanya tenant admin / platform admin (backend policy).</p>
            </header>
            <ul class="divide-y divide-sky-100">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $pendingTransfers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $invoiceId => $orders): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php ($inv = $orders->first()->invoice); ?>
                    <li class="flex items-center gap-3 px-4 py-3 text-sm" wire:key="transfer-<?php echo e($invoiceId); ?>" data-testid="pending-transfer-<?php echo e($invoiceId); ?>">
                        <div class="min-w-0 flex-1">
                            <strong class="block font-mono text-xs"><?php echo e($inv->code); ?></strong>
                            <span class="block truncate text-xs text-slate-500"><?php echo e($orders->first()->buyer->name); ?> · VA <?php echo e($inv->payment_reference); ?></span>
                        </div>
                        <strong><?php echo 'Rp'.number_format((int) ($orders->sum('subtotal')), 0, ',', '.'); ?></strong>
                        <button wire:click="confirmTransfer(<?php echo e($invoiceId); ?>)" wire:confirm="Konfirmasi transfer sudah masuk?" class="rounded-full bg-brand px-3 py-1.5 text-xs font-bold text-white hover:bg-brand-dark" data-testid="verify-transfer-<?php echo e($invoiceId); ?>">Verifikasi</button>
                    </li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </ul>
        </section>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <div class="grid gap-6 lg:grid-cols-[1fr_1fr]">
        
        <section class="rounded-2xl border border-slate-200 bg-white">
            <header class="border-b border-slate-100 px-4 py-3">
                <h2 class="text-base font-extrabold">Menunggu verifikasi tunai <span class="ml-1 rounded-full bg-amber-100 px-2 py-0.5 text-xs text-amber-800" data-testid="pending-cash-count"><?php echo e($pendingCash->count()); ?></span></h2>
                <p class="text-xs text-slate-500">Tandai lunas saat pembeli membayar di kasir.</p>
            </header>
            <ul class="divide-y divide-slate-100">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $pendingCash; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <li class="flex items-center gap-3 px-4 py-3 text-sm" data-testid="pending-cash-<?php echo e($order->id); ?>" wire:key="cash-<?php echo e($order->id); ?>">
                        <div class="min-w-0 flex-1">
                            <strong class="block font-mono text-xs"><?php echo e($order->code); ?></strong>
                            <span class="block truncate text-xs text-slate-500"><?php echo e($order->buyer->name); ?> · <?php echo e($order->invoice->payment_method->label()); ?> · <?php echo e($order->created_at->translatedFormat('d M H:i')); ?></span>
                        </div>
                        <strong><?php echo 'Rp'.number_format((int) ($order->subtotal), 0, ',', '.'); ?></strong>
                        <button wire:click="markPaid(<?php echo e($order->id); ?>)" wire:confirm="Konfirmasi pembayaran diterima?" class="rounded-full bg-brand px-3 py-1.5 text-xs font-bold text-white hover:bg-brand-dark" data-testid="verify-payment-<?php echo e($order->id); ?>">Lunas</button>
                        <button wire:click="refund(<?php echo e($order->id); ?>)" wire:confirm="Batalkan pesanan ini?" class="rounded-full px-2 py-1.5 text-xs font-bold text-rose-600 hover:bg-rose-50" data-testid="refund-<?php echo e($order->id); ?>">Batal</button>
                    </li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <li class="px-4 py-10 text-center text-sm text-slate-500">Tidak ada pembayaran tunai yang menunggu.</li>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </ul>
        </section>

        
        <section class="rounded-2xl border border-slate-200 bg-white">
            <header class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-100 px-4 py-3">
                <h2 class="text-base font-extrabold">Settlement harian</h2>
                <div class="flex items-center gap-1 text-xs">
                    <input type="date" wire:model.live="from" class="rounded-lg border border-slate-200 px-2 py-1" data-testid="settlement-from"> –
                    <input type="date" wire:model.live="to" class="rounded-lg border border-slate-200 px-2 py-1" data-testid="settlement-to">
                    <button wire:click="generateSettlement" class="ml-1 rounded-full bg-slate-900 px-3 py-1.5 font-bold text-white" data-testid="generate-settlement-button">Generate</button>
                </div>
            </header>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($settlement->isEmpty()): ?>
                <p class="px-4 py-10 text-center text-sm text-slate-500">Belum ada transaksi lunas pada rentang ini.</p>
            <?php else: ?>
                <ul class="divide-y divide-slate-100">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $settlement; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $day => $rows): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li class="px-4 py-3" data-testid="settlement-day-<?php echo e($day); ?>">
                            <div class="flex items-center justify-between">
                                <strong class="text-sm"><?php echo e(\Carbon\Carbon::parse($day)->translatedFormat('l, d M Y')); ?></strong>
                                <strong class="text-sm text-brand-dark"><?php echo 'Rp'.number_format((int) ($rows->sum('amount')), 0, ',', '.'); ?></strong>
                            </div>
                            <ul class="mt-1 flex flex-wrap gap-2 text-[11px] text-slate-500">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $rows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <li class="rounded-full bg-slate-100 px-2 py-0.5"><?php echo e(\App\Enums\PaymentMethod::from($row->method)->label()); ?>: <?php echo e($row->orders_count); ?> order · <?php echo 'Rp'.number_format((int) ($row->amount), 0, ',', '.'); ?></li>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </ul>
                        </li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </ul>
                <footer class="flex items-center justify-between border-t border-slate-200 px-4 py-3">
                    <span class="text-sm font-semibold">Total periode</span>
                    <strong class="text-lg font-extrabold" data-testid="settlement-total"><?php echo 'Rp'.number_format((int) ($settlement->flatten(1)->sum('amount')), 0, ',', '.'); ?></strong>
                </footer>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </section>
    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($stored->isNotEmpty()): ?>
        <section class="mt-6 rounded-2xl border border-slate-200 bg-white" data-testid="stored-settlements">
            <header class="border-b border-slate-100 px-4 py-3"><h2 class="text-base font-extrabold">Settlement tersimpan</h2><p class="text-xs text-slate-500">Hasil perhitungan harian (tabel tenant_settlements).</p></header>
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500"><tr><th class="px-4 py-2">Tanggal</th><th class="px-4 py-2">Order</th><th class="px-4 py-2 text-right">Bruto</th><th class="px-4 py-2 text-right">Komisi</th><th class="px-4 py-2 text-right">Neto</th></tr></thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $stored; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr wire:key="stored-<?php echo e($s->id); ?>" data-testid="stored-settlement-<?php echo e($s->date->toDateString()); ?>">
                            <td class="px-4 py-2"><?php echo e($s->date->translatedFormat('d M Y')); ?></td>
                            <td class="px-4 py-2"><?php echo e($s->orders_count); ?></td>
                            <td class="px-4 py-2 text-right"><?php echo 'Rp'.number_format((int) ($s->gross), 0, ',', '.'); ?></td>
                            <td class="px-4 py-2 text-right text-rose-600"><?php echo 'Rp'.number_format((int) ($s->commission), 0, ',', '.'); ?></td>
                            <td class="px-4 py-2 text-right font-bold text-brand-dark"><?php echo 'Rp'.number_format((int) ($s->net), 0, ',', '.'); ?></td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </tbody>
            </table>
        </section>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($refunds->isNotEmpty()): ?>
        <section class="mt-6 rounded-2xl border border-slate-200 bg-white">
            <header class="border-b border-slate-100 px-4 py-3"><h2 class="text-base font-extrabold">Refund terakhir</h2></header>
            <ul class="divide-y divide-slate-100 text-sm">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $refunds; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li class="flex items-center justify-between px-4 py-3" data-testid="refund-row-<?php echo e($order->id); ?>">
                        <span><strong class="font-mono text-xs"><?php echo e($order->code); ?></strong> · <?php echo e($order->buyer->name); ?> <em class="text-xs text-slate-500">— <?php echo e($order->cancel_reason); ?></em></span>
                        <span class="font-semibold text-rose-600">-<?php echo 'Rp'.number_format((int) ($order->subtotal), 0, ',', '.'); ?></span>
                    </li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </ul>
        </section>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>
<?php /**PATH /Users/mac/Herd/kantinsmk-go/resources/views/livewire/tenant/payments.blade.php ENDPATH**/ ?>