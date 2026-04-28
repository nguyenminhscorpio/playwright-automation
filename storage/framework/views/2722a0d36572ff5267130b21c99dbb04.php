<?php $__env->startSection('content'); ?>
    <section class="page-section">
        <div class="hero">
            <div>
                <h1 class="hero__title">Import TXT into your deck</h1>
                <p class="hero__subtitle">Upload an Anki-style TXT file, review parsed rows, then confirm only when the preview looks safe.</p>
            </div>
        </div>

        <div class="import-shell" data-import-app data-import-user-id="<?php echo e($importUserId ?? ''); ?>" data-import-selected-deck-id="<?php echo e($importSelectedDeckId ?? ''); ?>">
            <section class="import-card">
                <div class="section-header"><div><h2 class="section-title">Preview & Confirm</h2><p class="section-subtitle">Preview first. Invalid rows will be skipped automatically during confirm.</p></div></div>
                <div class="import-form">
                    <label class="import-field">
                        <span class="import-field__label">Target deck</span>
                        <select class="import-select" data-import-deck-select>
                            <?php $__empty_1 = true; $__currentLoopData = $importDecks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $deck): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <option value="<?php echo e($deck->id); ?>" <?php if(($importSelectedDeckId ?? null) === $deck->id): echo 'selected'; endif; ?>><?php echo e($deck->name); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <option value="" disabled selected>No deck available</option>
                            <?php endif; ?>
                            <option value="NEW_DECK" style="font-weight: bold; color: var(--primary);">+ Create New Deck...</option>
                        </select>
                    </label>
                    <label class="import-field"><span class="import-field__label">TXT file</span><input class="import-file-input" type="file" accept=".txt,text/plain" data-import-file-input /></label>
                    <div class="import-actions">
                        <button class="primary-button" type="button" data-import-preview-button><span class="material-symbols-outlined">preview</span><span>Preview Import</span></button>
                        <button class="primary-button primary-button--success" type="button" data-import-confirm-button disabled><span class="material-symbols-outlined">task_alt</span><span>Confirm Import</span></button>
                    </div>
                </div>
                <div class="import-feedback is-hidden" data-import-feedback></div>
            </section>

            <section class="import-grid">
                <article class="import-card">
                    <div class="section-header"><div><h2 class="section-title">Summary</h2><p class="section-subtitle" data-import-file-meta>No file previewed yet.</p></div></div>
                    <div class="import-summary-grid">
                        <div class="import-summary-box"><span>Total</span><strong data-import-summary-total>0</strong></div>
                        <div class="import-summary-box import-summary-box--valid"><span>Valid</span><strong data-import-summary-valid>0</strong></div>
                        <div class="import-summary-box import-summary-box--warning"><span>Warnings</span><strong data-import-summary-warning>0</strong></div>
                        <div class="import-summary-box import-summary-box--invalid"><span>Invalid</span><strong data-import-summary-invalid>0</strong></div>
                    </div>
                </article>
            </section>

            <section class="import-card">
                <div class="section-header"><div><h2 class="section-title">Row Preview</h2><p class="section-subtitle">Rows are grouped by valid, warning, and error states so you can scan quickly.</p></div></div>
                <div class="import-tabs">
                    <button class="secondary-button is-mode-active" type="button" data-import-filter="all">All</button>
                    <button class="secondary-button" type="button" data-import-filter="valid">Valid</button>
                    <button class="secondary-button" type="button" data-import-filter="warning">Warnings</button>
                    <button class="secondary-button" type="button" data-import-filter="invalid">Errors</button>
                </div>
                <div class="import-table-wrap">
                    <table class="import-table">
                        <thead><tr><th>#</th><th>Front</th><th>Back</th><th>Status</th><th>Issues</th></tr></thead>
                        <tbody data-import-rows-body><tr><td colspan="5" class="muted-text import-table__empty">Run preview to see parsed rows.</td></tr></tbody>
                    </table>
                </div>
            </section>
        </div>
    </section>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\vibe-coding\resources\views/screens/imports.blade.php ENDPATH**/ ?>