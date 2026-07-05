<?php $__env->startSection('content'); ?>
    <section class="page-section" data-dashboard-app data-dashboard-user-id="<?php echo e($dashboardUserId ?? ''); ?>">
        <div class="hero" data-reveal>
            <div>
                <h1 class="hero__title" style="font-weight: 800; letter-spacing: -0.04em;">
                    Welcome back,<br>
                    <span style="color: var(--primary);"><?php echo e($dashboardUserName); ?></span>
                </h1>
                <p class="hero__subtitle" style="font-size: 1.25rem; max-width: 32rem; margin-top: 1rem;">
                    Your learning momentum is strong. Track your streak and tackle the decks that need you today.
                </p>
            </div>
        </div>

        <div class="stats-grid">
            <article class="stat-card stagger-1" data-reveal style="border-top: 3px solid var(--warning);">
                <div class="stat-card__header">
                    <div class="stat-card__icon stat-card__icon--gold"><span class="material-symbols-outlined">local_fire_department</span></div>
                    <span class="stat-card__label">Current Streak</span>
                </div>
                <div class="stat-card__value"><?php echo e($dashboardStats['daily_streak']); ?> <small>days</small></div>
                <p class="stat-card__hint">Keep it up! One more session to hit tomorrow.</p>
            </article>

            <article class="stat-card stagger-2" data-reveal style="border-top: 3px solid var(--primary);">
                <div class="stat-card__header">
                    <div class="stat-card__icon"><span class="material-symbols-outlined">analytics</span></div>
                    <span class="stat-card__label">Due Today</span>
                </div>
                <div class="stat-card__value"><?php echo e($dashboardStats['totals']['due_count']); ?> <small>cards</small></div>
                <p class="stat-card__hint">Ready for review in your active collections.</p>
            </article>

            <article class="stat-card stagger-3" data-reveal>
                <div class="stat-card__header">
                    <div class="stat-card__icon"><span class="material-symbols-outlined">inventory_2</span></div>
                    <span class="stat-card__label">Total Decks</span>
                </div>
                <div class="stat-card__value"><?php echo e($dashboardStats['totals']['deck_count']); ?> <small>active</small></div>
                <p class="stat-card__hint"><?php echo e($dashboardStats['totals']['card_count']); ?> cards across all systems.</p>
            </article>
        </div>

        <article class="stat-card stat-card--wide stagger-4" data-reveal>
            <div class="stat-card__header">
                <div class="stat-card__icon" style="background: var(--secondary-soft); color: var(--secondary);"><span class="material-symbols-outlined">rocket_launch</span></div>
                <span class="stat-card__label">Monthly Progress</span>
            </div>
            <div class="milestone">
                <div style="flex: 1;">
                    <div class="stat-card__value" style="font-size: 2.5rem;"><?php echo e($dashboardStats['monthly_learned']); ?> <small>/ <?php echo e($dashboardStats['monthly_goal']); ?> learned</small></div>
                    <p class="muted-text">You're on track to reach your monthly goal. Keep pushing!</p>
                </div>
                <div class="progress-block" style="width: 24rem;">
                    <div class="progress-block__meta">
                        <span>Milestone completion</span>
                        <strong style="color: var(--secondary);">
                            <?php ($progressPercent = $dashboardStats['monthly_goal'] > 0 ? min(100, (int) round(($dashboardStats['monthly_learned'] / $dashboardStats['monthly_goal']) * 100)) : 0); ?>
                            <?php echo e($progressPercent); ?>%
                        </strong>
                    </div>
                <div class="progress" style="height: 12px; background: var(--surface-mid);"><div class="progress__bar" style="width: <?php echo e($progressPercent); ?>%;"></div></div>
                </div>
            </div>
        </article>

        <section class="section-header" data-reveal>
            <div>
                <h2 class="section-title">Active Collections</h2>
                <p class="section-subtitle">Manage your study systems or start a new session.</p>
            </div>
            <a href="<?php echo e(route('imports.index')); ?>" class="primary-button primary-button--pill" style="padding: 0.6rem 1.25rem; font-size: 0.9rem;">
                <span class="material-symbols-outlined">upload_file</span>
                Import Data
            </a>
        </section>

        <div class="deck-grid">
            <?php $__empty_1 = true; $__currentLoopData = $dashboardDecks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $deck): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <article class="deck-card stagger-<?php echo e(($index % 4) + 1); ?>" data-reveal data-deck-card data-deck-id="<?php echo e($deck['id']); ?>">
                    <div class="deck-card__top">
                        <div class="deck-card__icon" style="width: 2.5rem; height: 2.5rem; border-radius: var(--radius-sm);">
                            <span class="material-symbols-outlined">style</span>
                        </div>
                        <div class="deck-card__top-actions">
                            <span class="status-badge <?php echo e($deck['due_count'] > 0 ? 'status-badge--amber' : 'status-badge--green'); ?>" style="font-size: 0.7rem;">
                                <?php echo e($deck['due_count'] > 0 ? $deck['due_count'] . ' pending' : 'On track'); ?>

                            </span>
                            <button class="icon-button icon-button--small" type="button" data-delete-deck-button data-deck-name="<?php echo e($deck['name']); ?>" aria-label="Delete deck"><span class="material-symbols-outlined">delete</span></button>
                        </div>
                    </div>
                    <div style="margin-block: 0.5rem 1rem;">
                        <h3 class="deck-card__title" style="font-size: 1.25rem;"><?php echo e($deck['name']); ?></h3>
                        <p class="deck-card__desc" style="font-size: 0.9rem; line-height: 1.5; height: 3rem; overflow: hidden; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;">
                            <?php echo e($deck['description'] ?: 'No description provided.'); ?>

                        </p>
                    </div>
                    <div class="progress-block" style="width: 100%; margin-bottom: 1.5rem;">
                        <div class="progress-block__meta" style="font-size: 0.8rem;">
                            <span>Learned / Total</span>
                            <strong><?php echo e($deck['learned_count']); ?> / <?php echo e($deck['total_count']); ?></strong>
                        </div>
                        <div class="progress" style="height: 6px; background: var(--surface-low);"><div class="progress__bar" style="width: <?php echo e($deck['mastery_percent']); ?>%"></div></div>
                    </div>
                    <div class="deck-card__actions" style="margin-top: auto; display: grid; grid-template-columns: 1fr 1.2fr; gap: 0.5rem;">
                        <a href="<?php echo e(route('decks.show', ['deck' => $deck['id']])); ?>" class="secondary-button" style="padding: 0.75rem 0.5rem; font-size: 0.85rem;">View Deck</a>
                        <a href="<?php echo e(route('study.front', ['deck_id' => $deck['id']])); ?>" class="primary-button" style="padding: 0.75rem 0.5rem; font-size: 0.85rem;">
                            Review (<?php echo e($deck['due_count']); ?>)
                        </a>
                    </div>
                </article>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="stat-card" style="grid-column: span 3; text-align: center; padding: 4rem 2rem;" data-reveal>
                    <span class="material-symbols-outlined" style="font-size: 4rem; color: var(--muted); margin-bottom: 1.5rem;">folder_open</span>
                    <h3 class="deck-card__title">No collections found</h3>
                    <p class="deck-card__desc" style="margin-bottom: 2rem;">Start your journey by creating a deck or importing cards.</p>
                    <button class="primary-button primary-button--pill" type="button" data-create-deck-button>Create First Deck</button>
                </div>
            <?php endif; ?>
        </div>

        
        <dialog id="delete-deck-modal" class="custom-modal">
            <form method="dialog" class="custom-modal__form" data-delete-deck-form>
                <div class="custom-modal__header">
                    <h2>Delete Deck</h2>
                    <button type="button" class="icon-button" data-close-delete-deck-modal-button>
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>
                <div class="custom-modal__body">
                    <p data-delete-deck-modal-message>Are you sure you want to delete this deck? This also removes its notes and cards.</p>
                    <input type="hidden" data-delete-deck-id-input />
                    <div class="study-feedback is-hidden" data-delete-deck-form-feedback></div>
                </div>
                <div class="custom-modal__footer">
                    <button type="button" class="secondary-button" data-close-delete-deck-modal-button>Cancel</button>
                    <button type="submit" class="primary-button" style="background: var(--danger);" data-delete-deck-submit-button>Delete</button>
                </div>
            </form>
        </dialog>

        <section class="section-header" data-reveal style="margin-top: 2rem;">
            <div>
                <h2 class="section-title">Activity Feed</h2>
                <p class="section-subtitle">Recent system updates and imports.</p>
            </div>
        </section>

        <div class="import-table-wrap" data-reveal>
            <table class="import-table">
                <thead>
                    <tr><th>Job ID</th><th>Source File</th><th>Target Deck</th><th>Status</th><th>Imported</th><th>Failed</th><th>Completed</th></tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $recentImports; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $job): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td style="font-family: monospace; color: var(--primary);">#<?php echo e($job['id']); ?></td>
                            <td><?php echo e($job['file_name']); ?></td>
                            <td><?php echo e($job['deck_name']); ?></td>
                            <td><span class="status-badge <?php echo e($job['status'] === 'imported' ? 'status-badge--green' : 'status-badge--amber'); ?>"><?php echo e($job['status']); ?></span></td>
                            <td><strong style="color: var(--secondary);"><?php echo e($job['success_rows']); ?></strong></td>
                            <td><strong style="color: var(--danger);"><?php echo e($job['failed_rows']); ?></strong></td>
                            <td class="muted-text" style="font-size: 0.85rem;"><?php echo e($job['finished_at']); ?></td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr><td colspan="7" class="muted-text import-table__empty" style="padding: 3rem;">Activity feed is currently empty. Run an import to see history here.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\playwright-automation\resources\views/screens/dashboard.blade.php ENDPATH**/ ?>