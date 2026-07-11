<?php $__env->startSection('content'); ?>
    <div class="auth-card">

        <div class="auth-card__header">
            <h2 class="auth-card__title">Welcome back</h2>
            <p class="auth-card__sub">Sign in to continue your learning journey.</p>
        </div>

        <?php if($errors->any()): ?>
            <div class="auth-alert auth-alert--error">
                <span class="material-symbols-outlined">error</span>
                <?php echo e($errors->first()); ?>

            </div>
        <?php endif; ?>

        <form class="auth-form" method="POST" action="<?php echo e(route('login')); ?>">
            <?php echo csrf_field(); ?>

            <div class="auth-field">
                <label class="auth-field__label" for="email">Email address</label>
                <div class="auth-field__wrap">
                    <span class="auth-field__icon material-symbols-outlined">mail</span>
                    <input
                        id="email"
                        class="auth-input <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                        type="email"
                        name="email"
                        value="<?php echo e(old('email')); ?>"
                        placeholder="you@example.com"
                        required
                        autofocus
                        autocomplete="email"
                    />
                </div>
            </div>

            <div class="auth-field">
                <div class="auth-field__row">
                    <label class="auth-field__label" for="password">Password</label>
                </div>
                <div class="auth-field__wrap">
                    <span class="auth-field__icon material-symbols-outlined">lock</span>
                    <input
                        id="password"
                        class="auth-input"
                        type="password"
                        name="password"
                        placeholder="••••••••"
                        required
                        autocomplete="current-password"
                    />
                    <button type="button" class="auth-field__toggle" data-pw-toggle="password" aria-label="Show password">
                        <span class="material-symbols-outlined">visibility</span>
                    </button>
                </div>
            </div>

            <label class="auth-checkbox">
                <input type="checkbox" name="remember" <?php echo e(old('remember') ? 'checked' : ''); ?> />
                <span class="auth-checkbox__box"></span>
                <span class="auth-checkbox__label">Remember me for 30 days</span>
            </label>

            <button type="submit" class="auth-submit">
                Sign In
                <span class="material-symbols-outlined">arrow_forward</span>
            </button>
        </form>

        <p class="auth-card__footer">
            Don't have an account?
            <a href="<?php echo e(route('register')); ?>" class="auth-link">Create one free</a>
        </p>

    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.auth', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\playwright-automation\resources\views/auth/login.blade.php ENDPATH**/ ?>