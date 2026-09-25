<?php if (isset($component)) { $__componentOriginald4c772c02301431d3253f64117700596 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald4c772c02301431d3253f64117700596 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layouts.base','data' => ['title' => 'Masuk — KantinSMK Go']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts.base'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Masuk — KantinSMK Go']); ?>
    <main class="grid min-h-screen lg:grid-cols-[1.1fr_0.9fr]">
        <section class="relative hidden overflow-hidden bg-slate-900 p-14 text-white lg:block">
            <div class="flex items-center gap-2">
                <span class="grid h-10 w-10 place-items-center rounded-xl bg-brand text-xl font-extrabold">K</span>
                <span class="text-xl font-extrabold">Kantin<span class="text-brand">SMK</span> Go</span>
            </div>
            <p class="mt-14 text-xs font-bold uppercase tracking-[0.2em] text-brand">Marketplace kantin sekolah</p>
            <h1 class="mt-4 max-w-md text-5xl font-extrabold leading-[1.05] tracking-tight xl:text-6xl">Jajan enak,<br><span class="text-emerald-300">tanpa antre.</span></h1>
            <p class="mt-6 max-w-sm text-slate-300">Pesan dari kantin favoritmu, bayar dengan cara yang kamu suka, ambil langsung saat sudah siap.</p>
            <img src="https://images.unsplash.com/photo-1547592180-85f173990554?auto=format&fit=crop&w=1000&q=80" alt="Makanan kantin"
                 class="absolute -bottom-16 -right-16 w-[62%] rotate-[-6deg] rounded-3xl border-8 border-slate-800 object-cover shadow-2xl">
        </section>

        <section class="flex items-center justify-center px-6 py-12">
            <form method="POST" action="<?php echo e(route('login.store')); ?>" class="w-full max-w-sm" data-testid="login-form">
                <?php echo csrf_field(); ?>
                <div class="mb-8 flex items-center gap-2 lg:hidden">
                    <span class="grid h-10 w-10 place-items-center rounded-xl bg-brand text-xl font-extrabold text-white">K</span>
                    <span class="text-xl font-extrabold">Kantin<span class="text-brand">SMK</span> Go</span>
                </div>
                <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-brand-dark">Selamat datang kembali</p>
                <h2 class="mt-1 text-2xl font-extrabold tracking-tight">Masuk ke akunmu</h2>
                <p class="mt-1 text-sm text-slate-500">Akun disediakan oleh admin sekolah. Tidak ada pendaftaran mandiri.</p>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($errors->any()): ?>
                    <div class="mt-5 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-700" data-testid="login-error"><?php echo e($errors->first()); ?></div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                <label class="mt-6 block text-xs font-bold text-slate-700">Email
                    <input type="email" name="email" value="<?php echo e(old('email')); ?>" required autofocus autocomplete="username"
                           class="mt-1.5 w-full rounded-xl border border-slate-300 px-4 py-3 text-sm outline-none ring-brand/30 transition focus:border-brand focus:ring-4"
                           data-testid="login-email-input">
                </label>
                <label class="mt-4 block text-xs font-bold text-slate-700">Password
                    <input type="password" name="password" required autocomplete="current-password"
                           class="mt-1.5 w-full rounded-xl border border-slate-300 px-4 py-3 text-sm outline-none ring-brand/30 transition focus:border-brand focus:ring-4"
                           data-testid="login-password-input">
                </label>
                <label class="mt-4 flex items-center gap-2 text-sm text-slate-600">
                    <input type="checkbox" name="remember" class="h-4 w-4 rounded border-slate-300 text-brand" data-testid="login-remember-checkbox"> Ingat saya
                </label>
                <button class="mt-6 w-full rounded-full bg-brand py-3.5 text-sm font-bold text-white shadow-lg shadow-brand/30 transition hover:bg-brand-dark active:scale-[0.99]" data-testid="login-submit-button">Masuk</button>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(app()->environment('local')): ?>
                    <div class="mt-8 rounded-2xl border border-slate-200 bg-slate-50 p-4 text-xs">
                        <p class="font-bold">Akun demo (seeder)</p>
                        <ul class="mt-2 space-y-1 text-slate-600">
                            <li>Pembeli: <code>siswa@smkgo.id</code> / <code>buyer123</code></li>
                            <li>Tenant Staf: <code>staff@bu-rina.id</code> / <code>staff123</code></li>
                            <li>Tenant Admin: <code>owner@bu-rina.id</code> / <code>owner123</code></li>
                            <li>Platform Admin: <code>admin@smkgo.id</code> / <code>admin123</code></li>
                        </ul>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </form>
        </section>
    </main>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald4c772c02301431d3253f64117700596)): ?>
<?php $attributes = $__attributesOriginald4c772c02301431d3253f64117700596; ?>
<?php unset($__attributesOriginald4c772c02301431d3253f64117700596); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald4c772c02301431d3253f64117700596)): ?>
<?php $component = $__componentOriginald4c772c02301431d3253f64117700596; ?>
<?php unset($__componentOriginald4c772c02301431d3253f64117700596); ?>
<?php endif; ?>
<?php /**PATH /Users/mac/Herd/kantinsmk-go/resources/views/auth/login.blade.php ENDPATH**/ ?>