<?php $__env->startSection('content'); ?>
    <section class="page-section" data-import-app data-import-user-id="<?php echo e($importUserId ?? ''); ?>" data-import-selected-deck-id="<?php echo e($importSelectedDeckId ?? ''); ?>">

        
        <div class="hero" data-reveal>
            <div>
                <p class="import-step-tag">
                    <span class="material-symbols-outlined" style="font-size: 1rem;">upload_file</span>
                    Data Ingestion
                </p>
                <h1 class="hero__title" style="font-weight: 800; letter-spacing: -0.04em;">
                    Import <span style="color: var(--primary);">Your Cards</span>
                </h1>
                <p class="hero__subtitle" style="max-width: 36rem; margin-top: 0.75rem;">
                    Upload an Anki-style TXT file, review the parsed rows, and confirm only when the preview looks right.
                </p>
            </div>

            
            <div class="import-steps">
                <div class="import-step import-step--active">
                    <span class="import-step__num">1</span>
                    <span class="import-step__label">Configure</span>
                </div>
                <div class="import-step__divider"></div>
                <div class="import-step">
                    <span class="import-step__num">2</span>
                    <span class="import-step__label">Preview</span>
                </div>
                <div class="import-step__divider"></div>
                <div class="import-step">
                    <span class="import-step__num">3</span>
                    <span class="import-step__label">Confirm</span>
                </div>
            </div>
        </div>

        
        <div class="import-command-layout" data-reveal>

            
            <aside class="import-control-panel">
                <div class="import-panel-header">
                    <div class="import-panel-icon">
                        <span class="material-symbols-outlined">tune</span>
                    </div>
                    <div>
                        <h2 class="import-panel-title">Control Panel</h2>
                        <p class="import-panel-subtitle">Select target deck and upload your file</p>
                    </div>
                </div>

                
                <label class="import-field">
                    <span class="import-field__label">
                        <span class="material-symbols-outlined" style="font-size: 1rem; vertical-align: middle;">layers</span>
                        Target Deck
                    </span>
                    <select class="import-select" data-import-deck-select>
                        <?php $__empty_1 = true; $__currentLoopData = $importDecks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $deck): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <option value="<?php echo e($deck->id); ?>" <?php if(($importSelectedDeckId ?? null) === $deck->id): echo 'selected'; endif; ?>><?php echo e($deck->name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <option value="" disabled selected>No deck available</option>
                        <?php endif; ?>
                        <option value="NEW_DECK" style="font-weight: bold; color: var(--primary);">+ Create New Deck...</option>
                    </select>
                </label>

                
                <label class="import-dropzone" id="import-dropzone" for="import-file-hidden">
                    <input type="file" id="import-file-hidden" accept=".txt,text/plain" data-import-file-input style="display: none;" />
                    <div class="import-dropzone__inner">
                        <div class="import-dropzone__icon">
                            <span class="material-symbols-outlined">cloud_upload</span>
                        </div>
                        <p class="import-dropzone__title">Drop your TXT file here</p>
                        <p class="import-dropzone__hint">or click to browse — .txt format only</p>
                        <p class="import-dropzone__selected is-hidden" data-dropzone-filename>No file selected</p>
                    </div>
                </label>

                
                <div class="import-format-guide">
                    <p class="import-format-guide__title">
                        <span class="material-symbols-outlined" style="font-size: 1rem;">info</span>
                        Expected Format
                    </p>
                    <code class="import-format-guide__code">front_text<span style="color: var(--warning);">&#9;</span>back_text</code>
                    <p class="import-format-guide__desc">Each line = one card. Separator can be tab or pipe (<code>|</code>).</p>
                </div>

                
                <div class="import-control-actions">
                    <button class="primary-button" type="button" data-import-preview-button style="flex: 1;">
                        <span class="material-symbols-outlined">visibility</span>
                        <span>Preview</span>
                    </button>
                    <button class="primary-button" type="button" data-import-confirm-button disabled style="flex: 1; background: var(--secondary); box-shadow: 0 4px 14px rgba(21, 128, 61, 0.2);">
                        <span class="material-symbols-outlined">task_alt</span>
                        <span>Confirm Import</span>
                    </button>
                </div>

                
                <div class="import-feedback is-hidden" data-import-feedback></div>
            </aside>

            
            <div class="import-live-panel">
                <div class="import-panel-header">
                    <div class="import-panel-icon" style="background: var(--secondary-soft); color: var(--secondary);">
                        <span class="material-symbols-outlined">bar_chart</span>
                    </div>
                    <div>
                        <h2 class="import-panel-title">Live Summary</h2>
                        <p class="import-panel-subtitle" data-import-file-meta>Waiting for file preview...</p>
                    </div>
                </div>

                <div class="import-metrics">
                    <div class="import-metric">
                        <span class="import-metric__value" data-import-summary-total>—</span>
                        <span class="import-metric__label">Total Rows</span>
                    </div>
                    <div class="import-metric import-metric--valid">
                        <span class="import-metric__value" data-import-summary-valid>—</span>
                        <span class="import-metric__label">Valid</span>
                    </div>
                    <div class="import-metric import-metric--warning">
                        <span class="import-metric__value" data-import-summary-warning>—</span>
                        <span class="import-metric__label">Warnings</span>
                    </div>
                    <div class="import-metric import-metric--invalid">
                        <span class="import-metric__value" data-import-summary-invalid>—</span>
                        <span class="import-metric__label">Invalid</span>
                    </div>
                </div>
            </div>
        </div>

        
        <section class="import-preview-section" data-reveal>
            <div class="section-header" style="margin-bottom: 1.25rem;">
                <div>
                    <h2 class="section-title">Row Preview</h2>
                    <p class="section-subtitle">Rows grouped by valid, warning, and error states for fast scanning.</p>
                </div>
                <div class="import-tabs">
                    <button class="import-tab import-tab--active" type="button" data-import-filter="all">All</button>
                    <button class="import-tab import-tab--valid" type="button" data-import-filter="valid">
                        <span class="material-symbols-outlined" style="font-size: 1rem;">check_circle</span>
                        Valid
                    </button>
                    <button class="import-tab import-tab--warning" type="button" data-import-filter="warning">
                        <span class="material-symbols-outlined" style="font-size: 1rem;">warning</span>
                        Warnings
                    </button>
                    <button class="import-tab import-tab--invalid" type="button" data-import-filter="invalid">
                        <span class="material-symbols-outlined" style="font-size: 1rem;">error</span>
                        Errors
                    </button>
                </div>
            </div>

            <div class="import-table-wrap">
                <table class="import-table">
                    <thead>
                        <tr>
                            <th style="width: 3rem;">#</th>
                            <th>Front Side</th>
                            <th>Back Side</th>
                            <th style="width: 8rem;">Status</th>
                            <th>Notes</th>
                        </tr>
                    </thead>
                    <tbody data-import-rows-body>
                        <tr>
                            <td colspan="5" class="muted-text import-table__empty" style="padding: 4rem; text-align: center;">
                                <span class="material-symbols-outlined" style="font-size: 2.5rem; display: block; margin-bottom: 0.75rem; opacity: 0.4;">table_view</span>
                                Run Preview to see parsed rows here.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>

    </section>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\playwright-automation\resources\views/screens/imports.blade.php ENDPATH**/ ?>