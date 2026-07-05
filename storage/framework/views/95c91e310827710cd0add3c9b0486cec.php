<?php $__env->startSection('content'); ?>
    <?php
        $initials = collect(explode(' ', trim($user->name)))
            ->map(fn($w) => strtoupper(substr($w, 0, 1)))
            ->take(2)
            ->implode('');
    ?>

    <section class="page-section">

        <div class="profile-header">
            <div class="profile-avatar"><?php echo e($initials); ?></div>
            <div>
                <h1 class="profile-header__name"><?php echo e($user->name); ?></h1>
                <p class="profile-header__email"><?php echo e($user->email); ?></p>
                <p class="profile-header__since">Member since <?php echo e($user->created_at->format('F Y')); ?></p>
            </div>
        </div>

        <div class="profile-grid">

            
            <section class="profile-card">
                <div class="profile-card__head">
                    <div class="profile-card__head-icon">
                        <span class="material-symbols-outlined">person</span>
                    </div>
                    <div>
                        <h2 class="profile-card__title">Account Information</h2>
                        <p class="profile-card__desc">Update your name and email address.</p>
                    </div>
                </div>

                <?php if(session('profile_success')): ?>
                    <div class="profile-alert profile-alert--ok">
                        <span class="material-symbols-outlined">check_circle</span>
                        <?php echo e(session('profile_success')); ?>

                    </div>
                <?php endif; ?>

                <form method="POST" action="<?php echo e(route('profile.update')); ?>" class="profile-form">
                    <?php echo csrf_field(); ?>
                    <?php echo method_field('PUT'); ?>

                    <div class="profile-field">
                        <label class="profile-field__label" for="name">Full name</label>
                        <div class="profile-field__wrap">
                            <span class="profile-field__icon material-symbols-outlined">badge</span>
                            <input
                                id="name"
                                class="profile-input <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                type="text"
                                name="name"
                                value="<?php echo e(old('name', $user->name)); ?>"
                                required
                                autocomplete="name"
                            />
                        </div>
                        <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <span class="profile-field__error"><?php echo e($message); ?></span>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <div class="profile-field">
                        <label class="profile-field__label" for="email">Email address</label>
                        <div class="profile-field__wrap">
                            <span class="profile-field__icon material-symbols-outlined">mail</span>
                            <input
                                id="email"
                                class="profile-input <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                type="email"
                                name="email"
                                value="<?php echo e(old('email', $user->email)); ?>"
                                required
                                autocomplete="email"
                            />
                        </div>
                        <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <span class="profile-field__error"><?php echo e($message); ?></span>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <div class="profile-form__actions">
                        <button type="submit" class="profile-btn profile-btn--primary">
                            <span class="material-symbols-outlined">save</span>Save Changes
                        </button>
                    </div>
                </form>
            </section>

            
            <section class="profile-card">
                <div class="profile-card__head">
                    <div class="profile-card__head-icon profile-card__head-icon--warning">
                        <span class="material-symbols-outlined">lock</span>
                    </div>
                    <div>
                        <h2 class="profile-card__title">Change Password</h2>
                        <p class="profile-card__desc">Use a strong password of at least 8 characters.</p>
                    </div>
                </div>

                <?php if(session('password_success')): ?>
                    <div class="profile-alert profile-alert--ok">
                        <span class="material-symbols-outlined">check_circle</span>
                        <?php echo e(session('password_success')); ?>

                    </div>
                <?php endif; ?>

                <form method="POST" action="<?php echo e(route('profile.password')); ?>" class="profile-form">
                    <?php echo csrf_field(); ?>
                    <?php echo method_field('PUT'); ?>

                    <div class="profile-field">
                        <label class="profile-field__label" for="current_password">Current password</label>
                        <div class="profile-field__wrap">
                            <span class="profile-field__icon material-symbols-outlined">lock_open</span>
                            <input
                                id="current_password"
                                class="profile-input <?php $__errorArgs = ['current_password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                type="password"
                                name="current_password"
                                placeholder="Your current password"
                                autocomplete="current-password"
                            />
                            <button type="button" class="profile-field__toggle" data-pw-toggle="current_password">
                                <span class="material-symbols-outlined">visibility</span>
                            </button>
                        </div>
                        <?php $__errorArgs = ['current_password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <span class="profile-field__error"><?php echo e($message); ?></span>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <div class="profile-field">
                        <label class="profile-field__label" for="new_password">New password</label>
                        <div class="profile-field__wrap">
                            <span class="profile-field__icon material-symbols-outlined">lock</span>
                            <input
                                id="new_password"
                                class="profile-input <?php $__errorArgs = ['password'];
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
                                autocomplete="new-password"
                            />
                            <button type="button" class="profile-field__toggle" data-pw-toggle="new_password">
                                <span class="material-symbols-outlined">visibility</span>
                            </button>
                        </div>
                        <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <span class="profile-field__error"><?php echo e($message); ?></span>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <div class="profile-field">
                        <label class="profile-field__label" for="password_confirmation">Confirm new password</label>
                        <div class="profile-field__wrap">
                            <span class="profile-field__icon material-symbols-outlined">lock_clock</span>
                            <input
                                id="password_confirmation"
                                class="profile-input"
                                type="password"
                                name="password_confirmation"
                                placeholder="Repeat new password"
                                autocomplete="new-password"
                            />
                        </div>
                    </div>

                    <div class="profile-form__actions">
                        <button type="submit" class="profile-btn profile-btn--primary">
                            <span class="material-symbols-outlined">key</span>Update Password
                        </button>
                    </div>
                </form>
            </section>

            
            <section class="profile-card profile-card--danger">
                <div class="profile-card__head">
                    <div class="profile-card__head-icon profile-card__head-icon--danger">
                        <span class="material-symbols-outlined">logout</span>
                    </div>
                    <div>
                        <h2 class="profile-card__title">Sign Out</h2>
                        <p class="profile-card__desc">End your current session on this device.</p>
                    </div>
                </div>
                <form method="POST" action="<?php echo e(route('logout')); ?>">
                    <?php echo csrf_field(); ?>
                    <button type="submit" class="profile-btn profile-btn--danger">
                        <span class="material-symbols-outlined">logout</span>Sign Out
                    </button>
                </form>
            </section>

        </div>
    </section>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\admin\Documents\mine\vibe-coding\resources\views\screens\profile.blade.php ENDPATH**/ ?>