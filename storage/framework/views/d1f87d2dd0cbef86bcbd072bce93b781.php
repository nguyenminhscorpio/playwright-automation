<?php $__env->startSection('content'); ?>
    <div class="auth-card">

        <div class="auth-card__header">
            <h2 class="auth-card__title">Create your account</h2>
            <p class="auth-card__sub">Start learning smarter — it only takes a minute.</p>
        </div>

        <?php if($errors->any()): ?>
            <div class="auth-alert auth-alert--error">
                <span class="material-symbols-outlined">error</span>
                <?php echo e($errors->first()); ?>

            </div>
        <?php endif; ?>

        <form class="auth-form" method="POST" action="<?php echo e(route('register')); ?>">
            <?php echo csrf_field(); ?>

            <div class="auth-field">
                <label class="auth-field__label" for="name">Full name</label>
                <div class="auth-field__wrap">
                    <span class="auth-field__icon material-symbols-outlined">person</span>
                    <input
                        id="name"
                        class="auth-input <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                        type="text"
                        name="name"
                        value="<?php echo e(old('name')); ?>"
                        placeholder="Your name"
                        required
                        autofocus
                        autocomplete="name"
                    />
                </div>
                <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <span class="auth-field__error"><?php echo e($message); ?></span>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

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
                        autocomplete="email"
                    />
                </div>
                <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <span class="auth-field__error"><?php echo e($message); ?></span>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            <div class="auth-field">
                <label class="auth-field__label" for="password">Password</label>
                <div class="auth-field__wrap">
                    <span class="auth-field__icon material-symbols-outlined">lock</span>
                    <input
                        id="password"
                        class="auth-input <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                        type="password"
                        name="password"
                        placeholder="Min. 8 characters"
                        required
                        autocomplete="new-password"
                    />
                    <button type="button" class="auth-field__toggle" data-pw-toggle="password" aria-label="Show password">
                        <span class="material-symbols-outlined">visibility</span>
                    </button>
                </div>
                <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <span class="auth-field__error"><?php echo e($message); ?></span>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            <div class="auth-field">
                <label class="auth-field__label" for="password_confirmation">Confirm password</label>
                <div class="auth-field__wrap">
                    <span class="auth-field__icon material-symbols-outlined">lock_clock</span>
                    <input
                        id="password_confirmation"
                        class="auth-input"
                        type="password"
                        name="password_confirmation"
                        placeholder="Repeat your password"
                        required
                        autocomplete="new-password"
                    />
                </div>
            </div>

            <button type="submit" class="auth-submit">
                Create Account
                <span class="material-symbols-outlined">arrow_forward</span>
            </button>
        </form>

        <p class="auth-card__footer">
            Already have an account?
            <a href="<?php echo e(route('login')); ?>" class="auth-link">Sign in</a>
        </p>

    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.auth', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\admin\Documents\mine\vibe-coding\resources\views\auth\register.blade.php ENDPATH**/ ?>