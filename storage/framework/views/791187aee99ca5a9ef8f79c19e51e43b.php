<?php ($studyScreen = 'answer'); ?>

<?php $__env->startSection('content'); ?>
    <section class="study-page">
        <div class="study-shell" data-study-app>
            <div class="study-progress study-progress--split">
                <div>
                    <span class="eyebrow" data-study-deck-name><?php echo e($studyDeckName ?? 'Study Session'); ?></span>
                    <h1 class="study-title" data-study-progress-title>Current Card</h1>
                </div>
                <div class="progress-group">
                    <span class="progress-pill progress-pill--new" data-study-new-count>0</span>
                    <span class="progress-pill progress-pill--learning" data-study-learning-count>0</span>
                    <span class="progress-pill progress-pill--review" data-study-review-count>0</span>
                </div>
            </div>
            <div class="study-feedback is-hidden" data-study-feedback></div>
            <article class="study-card study-card--answer" data-study-card>
                <div class="study-section"><div class="study-section__head"><span class="study-section__label">Prompt</span><button class="icon-button icon-button--small" type="button" data-study-tts-button="front" disabled><span class="material-symbols-outlined">volume_up</span></button></div><h2 data-study-front-text>Loading...</h2></div>
                <div class="study-section study-section--muted is-hidden" data-study-user-answer-section><span class="study-section__label"><span class="material-symbols-outlined">edit_note</span><span>Your Answer</span></span><p data-study-user-answer></p></div>
                <div class="study-judgement is-hidden" data-study-judgement></div>
                <div class="study-section"><div class="study-section__head"><span class="study-section__label study-section__label--success"><span class="material-symbols-outlined">check_circle</span><span data-study-answer-label>Back Side</span></span><button class="icon-button icon-button--small" type="button" data-study-tts-button="back" disabled><span class="material-symbols-outlined">volume_up</span></button></div><p data-study-back-text>Loading answer...</p></div>
                <div class="tag-row"><span class="chip chip--subtle" data-study-state-tag>State</span><span class="chip chip--subtle" data-study-mode-tag>Mode</span></div>
            </article>
            <section class="rating-panel"><p class="rating-panel__title" data-study-rating-title>How difficult was this to recall?</p><div class="rating-grid"><button class="rating-card rating-card--again" type="button" data-study-rate-button="again"><strong>1</strong><span>Again</span><small data-study-rating-again>&lt; 1 min</small></button><button class="rating-card rating-card--hard" type="button" data-study-rate-button="hard"><strong>2</strong><span>Hard</span><small data-study-rating-hard>~ 5 min</small></button><button class="rating-card rating-card--good" type="button" data-study-rate-button="good"><strong>3</strong><span>Good</span><small data-study-rating-good>~ 10 min</small></button><button class="rating-card rating-card--easy" type="button" data-study-rate-button="easy"><strong>4</strong><span>Easy</span><small data-study-rating-easy>~ 4 days</small></button></div></section>
            <section class="study-empty-state is-hidden" data-study-empty-state><span class="material-symbols-outlined">check_circle</span><h2 data-study-empty-title>Session complete</h2><p data-study-empty-message>No answer payload was found. Start a study card first.</p></section>
        </div>
    </section>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\admin\Documents\mine\vibe-coding\resources\views\screens\study-answer.blade.php ENDPATH**/ ?>