<span>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($count > 0): ?>
        <span class="<?php echo e($position === 'absolute' ? 'absolute right-1/2 top-1 -mr-5' : ''); ?> grid min-w-[20px] place-items-center rounded-full bg-accent px-1.5 py-0.5 text-[10px] font-bold text-white" data-testid="cart-badge"><?php echo e($count); ?></span>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</span>
<?php /**PATH /Users/mac/Herd/kantinsmk-go/resources/views/livewire/buyer/cart-badge.blade.php ENDPATH**/ ?>