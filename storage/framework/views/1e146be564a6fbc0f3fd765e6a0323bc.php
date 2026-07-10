<?php ($studyScreen = 'typing'); ?>

<?php $__env->startSection('content'); ?>
    <section class="study-page">
        <div class="study-shell" data-study-app>
            <div class="study-progress study-progress--compact"><div class="progress-block__meta"><span data-study-deck-name><?php echo e($studyDeckName ?? 'Current Deck'); ?></span><strong data-study-progress-compact>0 / 0</strong></div><div class="progress"><div class="progress__bar" data-study-progress-bar style="width: 0%"></div></div></div>
            <div class="study-feedback is-hidden" data-study-feedback></div>
            <article class="study-card study-card--typing" data-study-card>
                <span class="study-side-label">Front side</span>
                <button class="icon-button study-card__tts" type="button" data-study-tts-button disabled><span class="material-symbols-outlined">volume_up</span></button>
                <div class="study-card__content"><h2 data-study-front-text>Loading...</h2></div>
            </article>
            <section class="study-empty-state is-hidden" data-study-empty-state><span class="material-symbols-outlined">check_circle</span><h2 data-study-empty-title>Session complete</h2><p data-study-empty-message>No cards are ready right now. Try again later or switch deck.</p></section>
            <section class="answer-panel"><label for="answer-input" class="answer-panel__label">Your Answer</label><textarea id="answer-input" class="answer-panel__input" rows="5" placeholder="Type your answer here..." data-study-answer-input></textarea><div class="answer-panel__actions"><button class="text-button" type="button" data-study-hint-button disabled><span class="material-symbols-outlined">lightbulb</span><span>Show Hint</span></button><button class="primary-button" type="button" data-study-check-button disabled><span>Check Answer</span><span class="material-symbols-outlined">keyboard_return</span></button></div></section>
        </div>
    </section>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\admin\Documents\mine\vibe-coding\resources\views/screens/study-typing.blade.php ENDPATH**/ ?>