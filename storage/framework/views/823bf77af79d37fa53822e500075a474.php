<div class="grid min-h-screen place-items-center bg-slate-50 px-4 py-10">
    <div class="w-full max-w-md">
        <div class="mb-6 flex items-center justify-center gap-2">
            <span class="grid h-10 w-10 place-items-center rounded-xl bg-brand text-xl font-extrabold text-white">K</span>
            <span class="text-xl font-extrabold tracking-tight">Kantin<span class="text-brand">SMK</span> Go</span>
        </div>

        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($valid): ?>
                <h1 class="text-lg font-extrabold">Atur password akunmu</h1>
                <p class="mt-1 text-sm text-slate-500">Untuk <strong><?php echo e($email); ?></strong>. Setelah ini kamu langsung masuk.</p>

                <form wire:submit="submit" class="mt-5 space-y-4" data-testid="set-password-form">
                    <label class="block text-xs font-bold">Password baru
                        <input type="password" wire:model="password" autocomplete="new-password" class="mt-1.5 w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm outline-none focus:border-brand" data-testid="set-password-input">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><span class="text-xs font-normal text-rose-600"><?php echo e($message); ?></span><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </label>
                    <label class="block text-xs font-bold">Ulangi password
                        <input type="password" wire:model="password_confirmation" autocomplete="new-password" class="mt-1.5 w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm outline-none focus:border-brand" data-testid="set-password-confirm-input">
                    </label>
                    <button class="w-full rounded-full bg-brand py-3 text-sm font-bold text-white hover:bg-brand-dark" data-testid="set-password-submit">Aktifkan & masuk</button>
                </form>
            <?php else: ?>
                <div class="text-center" data-testid="invalid-token">
                    <span class="mx-auto grid h-14 w-14 place-items-center rounded-full bg-rose-100 text-rose-600"><?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon','data' => ['name' => 'x','class' => 'h-7 w-7']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'x','class' => 'h-7 w-7']); ?>
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
                    <h1 class="mt-4 text-lg font-extrabold">Link tidak berlaku</h1>
                    <p class="mt-1 text-sm text-slate-500">Link aktivasi ini salah, sudah dipakai, atau kedaluwarsa. Minta admin membuatkan link baru.</p>
                    <a href="<?php echo e(route('login')); ?>" class="mt-5 inline-block rounded-full border border-slate-300 px-5 py-2.5 text-sm font-bold text-slate-700 hover:bg-slate-50" data-testid="back-to-login">Ke halaman masuk</a>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>
</div>
<?php /**PATH /Users/mac/Herd/kantinsmk-go/resources/views/livewire/auth/set-password.blade.php ENDPATH**/ ?>