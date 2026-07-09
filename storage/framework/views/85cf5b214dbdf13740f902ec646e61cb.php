<?php $__env->startPush('styles'); ?>
    <link rel="stylesheet" href="<?php echo e(asset('css/deck-detail.css')); ?>?v=20260709">
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<?php if($deck === null): ?>
<div class="dd" data-deck-detail-empty>
    <nav class="dd-breadcrumb" aria-label="Breadcrumb">
        <a href="<?php echo e(route('dashboard')); ?>" class="dd-breadcrumb__link">
            <span class="material-symbols-outlined">home</span>
            <span>Dashboard</span>
        </a>
        <span class="material-symbols-outlined dd-breadcrumb__sep">chevron_right</span>
        <span class="dd-breadcrumb__current">My Decks</span>
    </nav>

    <section class="dd-empty dd-empty--page">
        <div class="dd-empty__icon">
            <span class="material-symbols-outlined">layers_clear</span>
        </div>
        <h1 class="dd-empty__title">No deck found</h1>
        <p class="dd-empty__desc">
            <?php if($allDecks->isEmpty()): ?>
                You do not have any decks yet. Create your first deck or import a TXT file to start studying.
            <?php else: ?>
                This deck is no longer available. Choose another deck from your library.
            <?php endif; ?>
        </p>
        <div class="dd-empty__actions">
            <a href="<?php echo e(route('dashboard')); ?>" class="dd-btn dd-btn--primary">
                <span class="material-symbols-outlined">dashboard</span>
                <span>Back to Dashboard</span>
            </a>
            <a href="<?php echo e(route('imports.index')); ?>" class="dd-btn dd-btn--ghost">
                <span class="material-symbols-outlined">upload_file</span>
                <span>Import TXT</span>
            </a>
        </div>
    </section>
</div>
<?php else: ?>
<?php
    $activeSearch = trim($filters['q'] ?? '');
    $activeStatus = $filters['status'] ?? 'all';
?>
<div class="dd" data-deck-detail-app data-deck-id="<?php echo e($deck->id); ?>" data-user-id="<?php echo e($deckDetailUserId ?? ''); ?>" data-total-cards="<?php echo e($cards->total()); ?>">

    
    <nav class="dd-breadcrumb" aria-label="Breadcrumb">
        <a href="<?php echo e(route('dashboard')); ?>" class="dd-breadcrumb__link">
            <span class="material-symbols-outlined">home</span>
            <span>Dashboard</span>
        </a>
        <span class="material-symbols-outlined dd-breadcrumb__sep">chevron_right</span>
        <span class="dd-breadcrumb__current"><?php echo e($deck->name); ?></span>
    </nav>

    
    <header class="dd-hero">
        <div class="dd-hero__info">
            <div class="dd-hero__icon-wrap">
                <span class="material-symbols-outlined">layers</span>
            </div>
            <div class="dd-hero__text">
                <div class="dd-hero__title-row">
                    <h1 class="dd-hero__title"><?php echo e($deck->name); ?></h1>
                    <select class="dd-deck-switcher" data-deck-switcher aria-label="Switch deck">
                        <?php $__currentLoopData = $allDecks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $d): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($d->id); ?>" <?php if($d->id === $deck->id): echo 'selected'; endif; ?>><?php echo e($d->name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <p class="dd-hero__desc"><?php echo e($deck->description ?: 'Manage your flashcards, track progress, and import new content.'); ?></p>
                <div class="dd-hero__meta" aria-label="Deck summary">
                    <span class="dd-meta-pill">
                        <span class="material-symbols-outlined">style</span>
                        <?php echo e($deckStats['total']); ?> cards
                    </span>
                    <span class="dd-meta-pill dd-meta-pill--due">
                        <span class="material-symbols-outlined">notifications_active</span>
                        <?php echo e($deckStats['due']); ?> due now
                    </span>
                </div>
            </div>
        </div>
        <div class="dd-hero__actions">
            <a href="<?php echo e(route('study.typing', ['deck_id' => $deck->id, 'mode' => 'typing'])); ?>" class="dd-btn dd-btn--study">
                <span class="material-symbols-outlined">school</span>
                <span>Study Now</span>
            </a>
            <button class="dd-btn dd-btn--primary" type="button" data-open-card-modal-button>
                <span class="material-symbols-outlined">add_circle</span>
                <span>Add Card</span>
            </button>
            <a href="<?php echo e(route('imports.index', ['deck_id' => $deck->id])); ?>" class="dd-btn dd-btn--ghost">
                <span class="material-symbols-outlined">upload_file</span>
                <span>Import</span>
            </a>
        </div>
    </header>

    
    <section class="dd-stats" aria-label="Deck statistics">
        <div class="dd-stat-card dd-stat-card--total">
            <div class="dd-stat-card__icon dd-stat-card__icon--total">
                <span class="material-symbols-outlined">style</span>
            </div>
            <div class="dd-stat-card__body">
                <span class="dd-stat-card__value"><?php echo e($deckStats['total']); ?></span>
                <span class="dd-stat-card__label">Total Cards</span>
            </div>
        </div>
        <div class="dd-stat-card dd-stat-card--new">
            <div class="dd-stat-card__icon dd-stat-card__icon--new">
                <span class="material-symbols-outlined">fiber_new</span>
            </div>
            <div class="dd-stat-card__body">
                <span class="dd-stat-card__value"><?php echo e($deckStats['new']); ?></span>
                <span class="dd-stat-card__label">New</span>
            </div>
        </div>
        <div class="dd-stat-card dd-stat-card--learning">
            <div class="dd-stat-card__icon dd-stat-card__icon--learning">
                <span class="material-symbols-outlined">neurology</span>
            </div>
            <div class="dd-stat-card__body">
                <span class="dd-stat-card__value"><?php echo e($deckStats['learning']); ?></span>
                <span class="dd-stat-card__label">Learning</span>
            </div>
        </div>
        <div class="dd-stat-card dd-stat-card--review">
            <div class="dd-stat-card__icon dd-stat-card__icon--review">
                <span class="material-symbols-outlined">verified</span>
            </div>
            <div class="dd-stat-card__body">
                <span class="dd-stat-card__value"><?php echo e($deckStats['review']); ?></span>
                <span class="dd-stat-card__label">Review</span>
            </div>
        </div>
        <div class="dd-stat-card dd-stat-card--due">
            <div class="dd-stat-card__icon dd-stat-card__icon--due">
                <span class="material-symbols-outlined">notifications_active</span>
            </div>
            <div class="dd-stat-card__body">
                <span class="dd-stat-card__value"><?php echo e($deckStats['due']); ?></span>
                <span class="dd-stat-card__label">Due Now</span>
            </div>
        </div>
    </section>

    
    <form method="GET" action="<?php echo e(route('decks.show', $deck)); ?>" class="dd-toolbar">
        <div class="dd-toolbar__main">
            <div class="dd-toolbar__label">
                <span class="material-symbols-outlined">tune</span>
                <span>Search & filter</span>
            </div>
            <div class="dd-toolbar__search">
                <span class="material-symbols-outlined">search</span>
                <input type="text" name="q" value="<?php echo e($filters['q'] ?? ''); ?>" placeholder="Search front or back text..." />
                <?php if(!empty($filters['q'])): ?>
                    <a href="<?php echo e(route('decks.show', $deck)); ?>" class="dd-toolbar__clear" aria-label="Clear search">
                        <span class="material-symbols-outlined">close</span>
                    </a>
                <?php endif; ?>
            </div>
            <div class="dd-toolbar__filters">
                <select class="dd-toolbar__select" name="status" aria-label="Filter by status">
                    <option value="all" <?php if(($filters['status'] ?? 'all') === 'all'): echo 'selected'; endif; ?>>All Status</option>
                    <option value="new" <?php if(($filters['status'] ?? '') === 'new'): echo 'selected'; endif; ?>>New</option>
                    <option value="learning" <?php if(($filters['status'] ?? '') === 'learning'): echo 'selected'; endif; ?>>Learning</option>
                    <option value="review" <?php if(($filters['status'] ?? '') === 'review'): echo 'selected'; endif; ?>>Review</option>
                </select>
                <button class="dd-btn dd-btn--filter" type="submit">
                    <span class="material-symbols-outlined">filter_list</span>
                    <span>Filter</span>
                </button>
            </div>
            <?php if($activeSearch !== '' || $activeStatus !== 'all'): ?>
                <div class="dd-active-filters" aria-label="Active filters">
                    <?php if($activeSearch !== ''): ?>
                        <span class="dd-filter-chip">
                            <span class="material-symbols-outlined">search</span>
                            "<?php echo e(\Illuminate\Support\Str::limit($activeSearch, 24)); ?>"
                        </span>
                    <?php endif; ?>
                    <?php if($activeStatus !== 'all'): ?>
                        <span class="dd-filter-chip dd-filter-chip--status">
                            <span class="material-symbols-outlined">filter_alt</span>
                            <?php echo e(ucfirst($activeStatus)); ?>

                        </span>
                    <?php endif; ?>
                    <a href="<?php echo e(route('decks.show', $deck)); ?>" class="dd-filter-chip dd-filter-chip--clear">
                        <span class="material-symbols-outlined">close</span>
                        Clear
                    </a>
                </div>
            <?php endif; ?>
        </div>
        <button class="dd-btn dd-btn--danger is-hidden" type="button" data-action-bulk-delete>
            <span class="material-symbols-outlined">delete_sweep</span>
            <span>Delete Selected</span>
            <span class="dd-btn__count" data-bulk-selected-count>0</span>
        </button>
    </form>

    
    <section class="dd-table-wrap">
        <div class="dd-table-head">
            <div>
                <h2 class="dd-table-head__title">Cards</h2>
                <p class="dd-table-head__meta">
                    <?php echo e($cards->total()); ?> total cards in this deck
                    <?php if($activeSearch !== '' || $activeStatus !== 'all'): ?>
                        · filtered view
                    <?php endif; ?>
                </p>
            </div>
            <span class="dd-table-head__status">
                <span class="material-symbols-outlined">fact_check</span>
                <?php echo e($cards->count()); ?> visible
            </span>
        </div>
        <table class="dd-table">
            <thead>
                <tr>
                    <th class="dd-table__col-check"><input class="dd-check" type="checkbox" aria-label="Select all cards" data-select-all-checkbox></th>
                    <th class="dd-table__col-front">Front</th>
                    <th class="dd-table__col-back">Back</th>
                    <th class="dd-table__col-status">Status</th>
                    <th class="dd-table__col-reviewed">Last Reviewed</th>
                    <th class="dd-table__col-mastery">Mastery</th>
                    <th class="dd-table__col-next">Next Due</th>
                    <th class="dd-table__col-actions"></th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $cards; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $card): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr class="dd-table__row" data-card-row data-card-id="<?php echo e($card->id); ?>">
                        <td class="dd-table__col-check" data-label="Select">
                            <input class="dd-check" type="checkbox" aria-label="Select card" data-row-checkbox value="<?php echo e($card->id); ?>">
                        </td>
                        <td class="dd-table__col-front" data-label="Front">
                            <div class="dd-card-cell dd-card-cell--front">
                                <span class="dd-card-cell__icon material-symbols-outlined" aria-hidden="true">quiz</span>
                                <span class="dd-table__front-text"><?php echo e(\Illuminate\Support\Str::limit($card->note->front_plain_text ?? $card->note->front_text, 72)); ?></span>
                            </div>
                        </td>
                        <td class="dd-table__col-back" data-label="Back">
                            <div class="dd-card-cell">
                                <span class="dd-card-cell__icon dd-card-cell__icon--answer material-symbols-outlined" aria-hidden="true">tips_and_updates</span>
                                <span class="dd-table__back-text"><?php echo e(\Illuminate\Support\Str::limit($card->note->back_plain_text ?? '', 64)); ?></span>
                            </div>
                        </td>
                        <td class="dd-table__col-status" data-label="Status">
                            <?php if($card->state === 'review'): ?>
                                <span class="dd-badge dd-badge--review"><span class="dd-badge__dot"></span>Review</span>
                            <?php elseif($card->state === 'learning' || $card->state === 'relearning'): ?>
                                <span class="dd-badge dd-badge--learning"><span class="dd-badge__dot"></span>Learning</span>
                            <?php else: ?>
                                <span class="dd-badge dd-badge--new"><span class="dd-badge__dot"></span>New</span>
                            <?php endif; ?>
                        </td>
                        <td class="dd-table__col-reviewed" data-label="Last Reviewed">
                            <span class="dd-table__meta">
                                <span class="material-symbols-outlined" aria-hidden="true">history</span>
                                <span><?php echo e($card->last_reviewed_at?->diffForHumans() ?? 'Never'); ?></span>
                            </span>
                        </td>
                        <td class="dd-table__col-mastery" data-label="Mastery">
                            <?php
                                $masteryPercent = $card->state === 'review' ? min(100, (int) round($card->stability * 10)) : ($card->state === 'new' ? 0 : 20);
                            ?>
                            <div class="dd-mastery" title="<?php echo e($masteryPercent); ?>% mastery">
                                <div class="dd-mastery__track">
                                    <div class="dd-mastery__fill dd-mastery__fill--<?php echo e($masteryPercent >= 70 ? 'high' : ($masteryPercent >= 30 ? 'mid' : 'low')); ?>" style="width: <?php echo e($masteryPercent); ?>%"></div>
                                </div>
                                <span class="dd-mastery__label"><?php echo e($masteryPercent); ?>%</span>
                            </div>
                        </td>
                        <td class="dd-table__col-next" data-label="Next Due">
                            <?php if(!$card->due_at): ?>
                                <span class="dd-table__meta">
                                    <span class="material-symbols-outlined" aria-hidden="true">event_busy</span>
                                    <span>-</span>
                                </span>
                            <?php else: ?>
                                <?php
                                    $now = now();
                                    $diffDays = (int) $now->startOfDay()->diffInDays($card->due_at->startOfDay(), false);
                                ?>
                                <?php if($diffDays < 0 || ($diffDays == 0 && $card->due_at->isPast())): ?>
                                    <span class="dd-due dd-due--overdue">Overdue</span>
                                <?php elseif($diffDays == 0): ?>
                                    <?php
                                        $diffMins = $now->diffInMinutes($card->due_at);
                                    ?>
                                    <?php if($diffMins < 60): ?>
                                        <span class="dd-due dd-due--soon"><?php echo e($diffMins); ?>m</span>
                                    <?php else: ?>
                                        <span class="dd-due dd-due--soon"><?php echo e((int)($diffMins/60)); ?>h</span>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="dd-due"><?php echo e($diffDays); ?>d</span>
                                <?php endif; ?>
                            <?php endif; ?>
                        </td>
                        <td class="dd-table__col-actions" data-label="Actions">
                            <div class="dd-table__actions">
                                <button class="dd-icon-btn dd-icon-btn--edit" type="button" data-edit-card-button data-card-front="<?php echo e(e($card->note->front_text ?? $card->note->front_plain_text ?? '')); ?>" data-card-back="<?php echo e(e($card->note->back_text ?? $card->note->back_plain_text ?? '')); ?>" aria-label="Edit card" title="Edit">
                                    <span class="material-symbols-outlined">edit</span>
                                </button>
                                <button class="dd-icon-btn dd-icon-btn--delete" type="button" data-delete-card-button aria-label="Delete card" title="Delete">
                                    <span class="material-symbols-outlined">delete</span>
                                </button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="8">
                            <div class="dd-empty">
                                <div class="dd-empty__icon">
                                    <span class="material-symbols-outlined">note_stack</span>
                                </div>
                                <h3 class="dd-empty__title">No cards yet</h3>
                                <p class="dd-empty__desc">
                                    <?php if(!empty($filters['q']) || ($filters['status'] ?? 'all') !== 'all'): ?>
                                        No cards match your current filters. Try adjusting your search or status filter.
                                    <?php else: ?>
                                        Get started by creating your first card or importing from a TXT file.
                                    <?php endif; ?>
                                </p>
                                <?php if(empty($filters['q']) && ($filters['status'] ?? 'all') === 'all'): ?>
                                    <div class="dd-empty__actions">
                                        <button class="dd-btn dd-btn--primary" type="button" data-open-card-modal-button>
                                            <span class="material-symbols-outlined">add_circle</span>
                                            <span>Create Card</span>
                                        </button>
                                        <a href="<?php echo e(route('imports.index', ['deck_id' => $deck->id])); ?>" class="dd-btn dd-btn--ghost">
                                            <span class="material-symbols-outlined">upload_file</span>
                                            <span>Import TXT</span>
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </section>

    
    <?php if($cards->hasPages()): ?>
        <?php
            $currentPage = $cards->currentPage();
            $lastPage = $cards->lastPage();
            $pageWindow = collect([1, $lastPage, $currentPage - 1, $currentPage, $currentPage + 1])
                ->filter(fn ($page) => $page >= 1 && $page <= $lastPage)
                ->unique()
                ->sort()
                ->values();
            $previousRenderedPage = null;
        ?>
        <footer class="dd-pagination" aria-label="Cards pagination">
            <div class="dd-pagination__info">
                <span class="dd-pagination__info-icon material-symbols-outlined" aria-hidden="true">view_list</span>
                <span class="dd-pagination__info-text">
                    Showing
                    <strong><?php echo e($cards->firstItem()); ?>-<?php echo e($cards->lastItem()); ?></strong>
                    of
                    <strong><?php echo e($cards->total()); ?></strong>
                    cards
                </span>
                <span class="dd-pagination__page-meta">Page <?php echo e($currentPage); ?> of <?php echo e($lastPage); ?></span>
            </div>
            <nav class="dd-pagination__controls" aria-label="Pagination navigation">
                <?php if($currentPage === 1): ?>
                    <span class="dd-page-btn dd-page-btn--edge is-disabled" aria-disabled="true" aria-label="First page">
                        <span class="material-symbols-outlined">first_page</span>
                    </span>
                <?php else: ?>
                    <a class="dd-page-btn dd-page-btn--edge" href="<?php echo e($cards->url(1)); ?>" aria-label="Go to first page">
                        <span class="material-symbols-outlined">first_page</span>
                    </a>
                <?php endif; ?>

                <?php if($cards->onFirstPage()): ?>
                    <span class="dd-page-btn dd-page-btn--arrow is-disabled" aria-disabled="true" aria-label="Previous page">
                        <span class="material-symbols-outlined">chevron_left</span>
                    </span>
                <?php else: ?>
                    <a class="dd-page-btn dd-page-btn--arrow" href="<?php echo e($cards->previousPageUrl()); ?>" rel="prev" aria-label="Previous page">
                        <span class="material-symbols-outlined">chevron_left</span>
                    </a>
                <?php endif; ?>

                <div class="dd-page-list">
                    <?php $__currentLoopData = $pageWindow; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $page): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php if($previousRenderedPage !== null && $page > $previousRenderedPage + 1): ?>
                            <span class="dd-page-btn dd-page-btn--ellipsis" aria-hidden="true">...</span>
                        <?php endif; ?>

                        <?php if($page === $currentPage): ?>
                            <span class="dd-page-btn is-active" aria-current="page"><?php echo e($page); ?></span>
                        <?php else: ?>
                            <a class="dd-page-btn" href="<?php echo e($cards->url($page)); ?>" aria-label="Go to page <?php echo e($page); ?>"><?php echo e($page); ?></a>
                        <?php endif; ?>

                        <?php
                            $previousRenderedPage = $page;
                        ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>

                <?php if($cards->hasMorePages()): ?>
                    <a class="dd-page-btn dd-page-btn--arrow" href="<?php echo e($cards->nextPageUrl()); ?>" rel="next" aria-label="Next page">
                        <span class="material-symbols-outlined">chevron_right</span>
                    </a>
                <?php else: ?>
                    <span class="dd-page-btn dd-page-btn--arrow is-disabled" aria-disabled="true" aria-label="Next page">
                        <span class="material-symbols-outlined">chevron_right</span>
                    </span>
                <?php endif; ?>

                <?php if($currentPage === $lastPage): ?>
                    <span class="dd-page-btn dd-page-btn--edge is-disabled" aria-disabled="true" aria-label="Last page">
                        <span class="material-symbols-outlined">last_page</span>
                    </span>
                <?php else: ?>
                    <a class="dd-page-btn dd-page-btn--edge" href="<?php echo e($cards->url($lastPage)); ?>" aria-label="Go to last page">
                        <span class="material-symbols-outlined">last_page</span>
                    </a>
                <?php endif; ?>
            </nav>
        </footer>
    <?php endif; ?>

    
    <dialog id="card-modal" class="dd-modal dd-modal--card">
        <form method="dialog" class="dd-modal__form" data-card-form>
            <div class="dd-modal__header">
                <div class="dd-modal__header-icon">
                    <span class="material-symbols-outlined">edit_note</span>
                </div>
                <div>
                    <h2 class="dd-modal__title" data-card-modal-title>Create Card</h2>
                    <p class="dd-modal__subtitle">Add front and back content for your flashcard.</p>
                </div>
                <button type="button" class="dd-icon-btn" data-close-card-modal-button aria-label="Close">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            <div class="dd-modal__body">
                <input type="hidden" data-card-id-input />
                <label class="dd-field">
                    <span class="dd-field__label">Front (Question)</span>
                    <textarea class="dd-field__textarea" rows="4" data-card-front-input required placeholder="Enter the question or prompt..."></textarea>
                </label>
                <label class="dd-field">
                    <span class="dd-field__label">Back (Answer)</span>
                    <textarea class="dd-field__textarea" rows="4" data-card-back-input required placeholder="Enter the answer or explanation..."></textarea>
                </label>
                <div class="dd-modal__feedback is-hidden" data-card-form-feedback></div>
            </div>
            <div class="dd-modal__footer">
                <button type="button" class="dd-btn dd-btn--ghost" data-close-card-modal-button>Cancel</button>
                <button type="submit" class="dd-btn dd-btn--primary" data-card-submit-button>
                    <span class="material-symbols-outlined">save</span>
                    <span>Save Card</span>
                </button>
            </div>
        </form>
    </dialog>

    
    <dialog id="delete-card-modal" class="dd-modal dd-modal--compact">
        <form method="dialog" class="dd-modal__form" data-delete-card-form>
            <div class="dd-modal__header">
                <div class="dd-modal__header-icon dd-modal__header-icon--danger">
                    <span class="material-symbols-outlined">warning</span>
                </div>
                <div>
                    <h2 class="dd-modal__title">Delete Card</h2>
                    <p class="dd-modal__subtitle">This action cannot be undone.</p>
                </div>
                <button type="button" class="dd-icon-btn" data-close-delete-modal-button aria-label="Close">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            <div class="dd-modal__body">
                <p class="dd-modal__message" data-delete-modal-message>Are you sure you want to delete this card?</p>
                <input type="hidden" data-delete-card-id-input />
                <div class="dd-modal__feedback is-hidden" data-delete-card-form-feedback></div>
            </div>
            <div class="dd-modal__footer">
                <button type="button" class="dd-btn dd-btn--ghost" data-close-delete-modal-button>Cancel</button>
                <button type="submit" class="dd-btn dd-btn--danger" data-delete-card-submit-button>
                    <span class="material-symbols-outlined">delete</span>
                    <span>Delete</span>
                </button>
            </div>
        </form>
    </dialog>
</div>
<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', ['page' => 'deck-detail'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\admin\Documents\mine\vibe-coding\resources\views/screens/deck-detail.blade.php ENDPATH**/ ?>