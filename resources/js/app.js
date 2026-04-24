const STUDY_MODE_KEY = 'flashmind-study-mode';
const STUDY_REVEAL_KEY = 'flashmind-study-reveal';

const isValidStudyMode = (value) => value === 'flip' || value === 'typing';

const getPreferredStudyMode = (url, body) => {
    const queryMode = url.searchParams.get('mode');

    if (isValidStudyMode(queryMode)) {
        localStorage.setItem(STUDY_MODE_KEY, queryMode);
        return queryMode;
    }

    const storedMode = localStorage.getItem(STUDY_MODE_KEY);

    if (isValidStudyMode(storedMode)) {
        return storedMode;
    }

    const bodyMode = body.dataset.studyMode;
    return isValidStudyMode(bodyMode) ? bodyMode : 'flip';
};

const buildStudyUrl = (baseUrl, mode) => {
    const nextUrl = new URL(baseUrl, window.location.origin);
    nextUrl.searchParams.set('mode', mode);
    return nextUrl.toString();
};

const syncStudyModeUi = (switchElement, mode) => {
    switchElement.querySelectorAll('[data-study-mode-option]').forEach((option) => {
        const isActive = option.dataset.studyModeOption === mode;
        option.classList.toggle('is-active', isActive);
        option.setAttribute('aria-pressed', isActive ? 'true' : 'false');
    });
};

const resolveTargetUrl = (screen, mode, body) => {
    const frontUrl = body.dataset.studyFrontUrl;
    const typingUrl = body.dataset.studyTypingUrl;
    const answerUrl = body.dataset.studyAnswerUrl;

    if (screen === 'typing' && mode === 'flip') {
        return buildStudyUrl(frontUrl, mode);
    }

    if (screen === 'front' && mode === 'typing') {
        return buildStudyUrl(typingUrl, mode);
    }

    if (screen === 'answer') {
        return buildStudyUrl(answerUrl, mode);
    }

    if (screen === 'typing') {
        return buildStudyUrl(typingUrl, mode);
    }

    return buildStudyUrl(frontUrl, mode);
};

const getStudyContext = (body) => {
    const userId = Number(body.dataset.studyUserId || '');
    const deckIdValue = body.dataset.studyDeckId || '';
    const deckId = deckIdValue === '' ? null : Number(deckIdValue);

    return {
        userId: Number.isFinite(userId) && userId > 0 ? userId : null,
        deckId: Number.isFinite(deckId) && deckId > 0 ? deckId : null,
        deckName: body.dataset.studyDeckName || 'Study Session',
        sessionApiUrl: body.dataset.studySessionApiUrl,
        checkAnswerUrlTemplate: body.dataset.studyCheckAnswerUrlTemplate,
        rateUrlTemplate: body.dataset.studyRateUrlTemplate,
        ttsUrlTemplate: body.dataset.studyTtsUrlTemplate,
    };
};

const setText = (selector, value) => {
    const element = document.querySelector(selector);
    if (element) {
        element.textContent = value;
    }
};

const toggleHidden = (selector, hidden) => {
    const element = document.querySelector(selector);
    if (element) {
        element.classList.toggle('is-hidden', hidden);
    }
};

const showFeedback = (message) => {
    const feedback = document.querySelector('[data-study-feedback]');

    if (!feedback) {
        return;
    }

    feedback.textContent = message;
    feedback.classList.remove('is-hidden');
};

const clearFeedback = () => {
    const feedback = document.querySelector('[data-study-feedback]');

    if (!feedback) {
        return;
    }

    feedback.textContent = '';
    feedback.classList.add('is-hidden');
};

const setButtonDisabled = (selector, disabled) => {
    document.querySelectorAll(selector).forEach((button) => {
        button.disabled = disabled;
        button.setAttribute('aria-disabled', disabled ? 'true' : 'false');
    });
};

const buildApiUrl = (baseUrl, params = {}) => {
    const url = new URL(baseUrl, window.location.origin);

    Object.entries(params).forEach(([key, value]) => {
        if (value === null || value === undefined || value === '') {
            return;
        }

        url.searchParams.set(key, value);
    });

    return url.toString();
};

const interpolateCardUrl = (template, cardId) => template.replace('__CARD__', String(cardId));

const fetchJson = async (url, options = {}) => {
    const isFormData = options.body instanceof FormData;
    const headers = {
        Accept: 'application/json',
        ...options.headers,
    };

    if (!isFormData) {
        headers['Content-Type'] = headers['Content-Type'] || 'application/json';
    }

    const response = await fetch(url, {
        headers,
        ...options,
    });

    const payload = await response.json().catch(() => ({}));

    if (!response.ok) {
        const message =
            payload.message ||
            payload.error ||
            (payload.errors ? Object.values(payload.errors).flat().join(' ') : '') ||
            'Request failed.';

        throw new Error(message);
    }

    return payload;
};

const renderImportFeedback = (message, isError = true) => {
    const feedback = document.querySelector('[data-import-feedback]');

    if (!feedback) {
        return;
    }

    feedback.textContent = message || '';
    feedback.classList.toggle('is-hidden', !message);
    feedback.classList.toggle('study-feedback', isError);
    feedback.classList.toggle('import-feedback', !isError);
};

const setImportButtonDisabled = (selector, disabled) => {
    document.querySelectorAll(selector).forEach((button) => {
        button.disabled = disabled;
        button.setAttribute('aria-disabled', disabled ? 'true' : 'false');
    });
};

const renderImportIssues = (selector, items, emptyText) => {
    const list = document.querySelector(selector);

    if (!list) {
        return;
    }

    if (!items.length) {
        list.innerHTML = `<li class="muted-text">${emptyText}</li>`;
        return;
    }

    list.innerHTML = items
        .map((item) => `<li>Row ${item.index}: ${item.message}</li>`)
        .join('');
};

const getImportRowKind = (row) => {
    if (row.status === 'invalid') {
        return 'invalid';
    }

    if ((row.warnings || []).length > 0) {
        return 'warning';
    }

    return 'valid';
};

const renderImportRows = (rows, filter = 'all') => {
    const body = document.querySelector('[data-import-rows-body]');

    if (!body) {
        return;
    }

    const filteredRows = rows.filter((row) => {
        const kind = getImportRowKind(row);

        if (filter === 'all') {
            return true;
        }

        return kind === filter;
    });

    if (!filteredRows.length) {
        body.innerHTML = '<tr><td colspan="5" class="muted-text import-table__empty">No rows match this filter.</td></tr>';
        return;
    }

    body.innerHTML = filteredRows
        .map((row) => {
            const kind = getImportRowKind(row);
            const issueLines = [...(row.errors || []), ...(row.warnings || [])]
                .map((item) => item.message)
                .join('<br>');
            const statusLabel = kind === 'warning' ? 'warning' : row.status;

            return `
                <tr data-row-kind="${kind}">
                    <td>${row.index}</td>
                    <td>${row.data?.front_text || ''}</td>
                    <td>${row.data?.back_text || ''}</td>
                    <td><span class="status-badge status-badge--${kind === 'valid' ? 'green' : kind === 'warning' ? 'amber' : 'red'}">${statusLabel}</span></td>
                    <td class="import-issue-copy">${issueLines || 'No issues'}</td>
                </tr>
            `;
        })
        .join('');
};

const setupImportScreen = (body) => {
    const app = document.querySelector('[data-import-app]');

    if (!app) {
        return;
    }

    const previewUrl = body.dataset.importPreviewUrl;
    const confirmUrl = body.dataset.importConfirmUrl;
    const userId = Number(app.dataset.importUserId || '');
    const deckSelect = app.querySelector('[data-import-deck-select]');
    const fileInput = app.querySelector('[data-import-file-input]');
    const previewButton = app.querySelector('[data-import-preview-button]');
    const confirmButton = app.querySelector('[data-import-confirm-button]');
    const fileMeta = app.querySelector('[data-import-file-meta]');
    const filterButtons = app.querySelectorAll('[data-import-filter]');

    let previewState = {
        importJobId: null,
        rows: [],
        summary: null,
        currentFilter: 'all',
    };

    const syncConfirmState = () => {
        const hasJob = Number.isFinite(previewState.importJobId) && previewState.importJobId > 0;
        const disabled = !hasJob;
        setImportButtonDisabled('[data-import-confirm-button]', disabled);
    };

    const renderPreviewPayload = (payload) => {
        previewState = {
            ...previewState,
            importJobId: Number(payload.import_job_id || 0),
            rows: payload.rows || [],
            summary: payload.summary || null,
        };

        fileMeta.textContent = `${payload.file_name} • ${payload.detected_format} • ${payload.data_lines} data rows`;
        document.querySelector('[data-import-summary-total]').textContent = String(payload.summary?.total || 0);
        document.querySelector('[data-import-summary-valid]').textContent = String(payload.summary?.valid || 0);
        document.querySelector('[data-import-summary-warning]').textContent = String(payload.summary?.warning || 0);
        document.querySelector('[data-import-summary-invalid]').textContent = String(payload.summary?.invalid || 0);

        renderImportRows(previewState.rows, previewState.currentFilter);
        syncConfirmState();
    };

    filterButtons.forEach((button) => {
        button.addEventListener('click', () => {
            previewState.currentFilter = button.dataset.importFilter || 'all';
            filterButtons.forEach((item) => item.classList.remove('is-mode-active'));
            button.classList.add('is-mode-active');
            renderImportRows(previewState.rows, previewState.currentFilter);
        });
    });

    previewButton?.addEventListener('click', async () => {
        const deckId = Number(deckSelect?.value || '');
        const file = fileInput?.files?.[0];

        renderImportFeedback('', false);

        if (!userId || !deckId) {
            renderImportFeedback('A valid user and deck are required before preview.');
            return;
        }

        if (!file) {
            renderImportFeedback('Please choose a TXT file before preview.');
            return;
        }

        setImportButtonDisabled('[data-import-preview-button]', true);
        syncConfirmState();

        try {
            const formData = new FormData();
            formData.append('user_id', String(userId));
            formData.append('deck_id', String(deckId));
            formData.append('file', file);

            const payload = await fetchJson(previewUrl, {
                method: 'POST',
                body: formData,
            });

            renderPreviewPayload(payload);

            if ((payload.summary?.invalid || 0) > 0) {
                renderImportFeedback('Preview completed. Invalid rows will be skipped during confirm.', false);
            } else if ((payload.summary?.warning || 0) > 0) {
                renderImportFeedback('Preview completed with warnings. You can still confirm after reviewing them.', false);
            } else {
                renderImportFeedback('Preview completed successfully. Confirm import is ready.', false);
            }
        } catch (error) {
            renderImportFeedback(error.message);
        } finally {
            setImportButtonDisabled('[data-import-preview-button]', false);
            syncConfirmState();
        }
    });

    confirmButton?.addEventListener('click', async () => {
        if (!previewState.importJobId) {
            renderImportFeedback('Preview must complete before confirm.');
            return;
        }

        setImportButtonDisabled('[data-import-confirm-button]', true);
        setImportButtonDisabled('[data-import-preview-button]', true);

        try {
            const payload = await fetchJson(confirmUrl, {
                method: 'POST',
                body: JSON.stringify({
                    user_id: userId,
                    import_job_id: previewState.importJobId,
                }),
            });

            const summary = payload.summary || {};
            renderImportFeedback(
                `Import complete. Imported ${summary.imported || 0} rows, skipped ${summary.skipped || 0}, invalid ${summary.invalid || 0}.`,
                false
            );
        } catch (error) {
            renderImportFeedback(error.message);
            syncConfirmState();
        } finally {
            setImportButtonDisabled('[data-import-preview-button]', false);
        }
    });
};

const deriveProgress = (progress) => {
    const total = Number(progress?.total || 0);
    const remaining = Number(progress?.remaining || 0);
    const completed = Math.max(total - remaining, 0);
    const currentIndex = total > 0 && remaining > 0 ? completed + 1 : completed;
    const percent = total > 0 ? Math.round((completed / total) * 100) : 0;

    return { total, remaining, completed, currentIndex, percent };
};

const renderEmptyState = (title, message) => {
    toggleHidden('[data-study-card]', true);
    toggleHidden('.answer-panel', true);
    toggleHidden('.rating-panel', true);
    toggleHidden('[data-study-empty-state]', false);
    setText('[data-study-empty-title]', title);
    setText('[data-study-empty-message]', message);
};

const renderProgress = (session) => {
    const progress = deriveProgress(session.progress);
    const deckName = session.current_card?.deck_name || document.body.dataset.studyDeckName || 'Study Session';

    setText('[data-study-deck-name]', deckName);
    setText(
        '[data-study-progress-title]',
        progress.total > 0 ? `Card ${progress.currentIndex} of ${progress.total}` : 'Session Progress'
    );
    setText('[data-study-progress-pill]', `${progress.completed} / ${progress.total} Reviewed`);
    setText('[data-study-progress-compact]', `${progress.completed} / ${progress.total}`);
    setText('[data-study-progress-percent]', `${progress.percent}%`);

    document.querySelectorAll('[data-study-progress-bar]').forEach((bar) => {
        bar.style.width = `${progress.percent}%`;
    });
};

const stateLabelMap = {
    new: 'New Card',
    learning: 'Learning',
    review: 'Review',
    relearning: 'Relearning',
};

const renderCard = (session) => {
    const card = session.current_card;

    renderProgress(session);

    if (!card) {
        renderEmptyState('Session complete', 'No cards are ready right now. Try again later or switch deck.');
        return;
    }

    toggleHidden('[data-study-card]', false);
    toggleHidden('.answer-panel', false);
    toggleHidden('.rating-panel', false);
    toggleHidden('[data-study-empty-state]', true);

    setText('[data-study-front-text]', card.front_text || card.front_plain_text || 'Untitled card');
    setText('[data-study-front-plain-text]', card.front_plain_text || ' ');
    setText('[data-study-back-text]', card.back_text || card.back_plain_text || 'No answer available.');
    setText('[data-study-state-label]', stateLabelMap[card.state] || 'Study Card');
    setText('[data-study-state-tag]', stateLabelMap[card.state] || card.state || 'Card');
    setText('[data-study-mode-tag]', `Mode: ${session.mode}`);
    setButtonDisabled('[data-study-reveal-button]', false);
    setButtonDisabled('[data-study-check-button]', false);
    setButtonDisabled('[data-study-tts-button]', false);
    setButtonDisabled('[data-study-hint-button]', !(card.back_plain_text || card.back_text));
};

const saveRevealPayload = (payload) => {
    sessionStorage.setItem(STUDY_REVEAL_KEY, JSON.stringify(payload));
};

const loadRevealPayload = () => {
    const raw = sessionStorage.getItem(STUDY_REVEAL_KEY);

    if (!raw) {
        return null;
    }

    try {
        return JSON.parse(raw);
    } catch {
        sessionStorage.removeItem(STUDY_REVEAL_KEY);
        return null;
    }
};

const clearRevealPayload = () => sessionStorage.removeItem(STUDY_REVEAL_KEY);

const buildSessionRequestUrl = (context, mode) =>
    buildApiUrl(context.sessionApiUrl, {
        user_id: context.userId,
        deck_id: context.deckId,
        mode,
    });

const formatDueHint = (rating, state) => {
    if (state === 'review' && rating === 'easy') return '~ 4 days';
    if (state === 'review' && rating === 'good') return '~ 2 days';
    if (state === 'review' && rating === 'hard') return '~ 1 day';
    if (rating === 'easy') return '~ 4 days';
    if (rating === 'good') return '~ 10 min';
    if (rating === 'hard') return '~ 5 min';
    return '< 1 min';
};

const setupStudyFront = async (body, context) => {
    clearFeedback();

    if (!context.userId) {
        renderEmptyState('Missing study context', 'No study user was resolved for this screen.');
        return;
    }

    try {
        const session = await fetchJson(buildSessionRequestUrl(context, 'flip'));
        renderCard(session);

        const revealButton = document.querySelector('[data-study-reveal-button]');
        const ttsButton = document.querySelector('[data-study-tts-button]');

        revealButton?.addEventListener('click', () => {
            if (!session.current_card) {
                return;
            }

            saveRevealPayload({
                mode: 'flip',
                session,
                card: session.current_card,
                user_answer: null,
                judged_result: null,
            });

            window.location.href = resolveTargetUrl('answer', 'flip', body);
        });

        ttsButton?.addEventListener('click', async () => {
            if (!session.current_card) {
                return;
            }

            try {
                await fetchJson(interpolateCardUrl(context.ttsUrlTemplate, session.current_card.id), {
                    method: 'POST',
                    body: JSON.stringify({ mode: 'flip' }),
                });
                showFeedback('TTS backend is scaffolded only in this phase.');
            } catch (error) {
                showFeedback(error.message);
            }
        });
    } catch (error) {
        renderEmptyState('Unable to load session', 'Study session data could not be loaded.');
        showFeedback(error.message);
    }
};

const setupStudyTyping = async (body, context) => {
    clearFeedback();

    if (!context.userId) {
        renderEmptyState('Missing study context', 'No study user was resolved for this screen.');
        return;
    }

    try {
        const session = await fetchJson(buildSessionRequestUrl(context, 'typing'));
        renderCard(session);

        const answerInput = document.querySelector('[data-study-answer-input]');
        const hintButton = document.querySelector('[data-study-hint-button]');
        const checkButton = document.querySelector('[data-study-check-button]');

        hintButton?.addEventListener('click', () => {
            if (!session.current_card) {
                return;
            }

            const hint = session.current_card.back_plain_text || session.current_card.back_text || 'No hint available.';
            showFeedback(`Hint: ${hint}`);
        });

        checkButton?.addEventListener('click', async () => {
            if (!session.current_card) {
                return;
            }

            const userAnswer = answerInput?.value?.trim() || '';

            if (!userAnswer) {
                showFeedback('Please enter your answer before checking.');
                answerInput?.focus();
                return;
            }

            setButtonDisabled('[data-study-check-button]', true);
            clearFeedback();

            try {
                const result = await fetchJson(interpolateCardUrl(context.checkAnswerUrlTemplate, session.current_card.id), {
                    method: 'POST',
                    body: JSON.stringify({
                        mode: 'typing',
                        user_answer: userAnswer,
                    }),
                });

                saveRevealPayload({
                    mode: 'typing',
                    session,
                    card: session.current_card,
                    user_answer: userAnswer,
                    judged_result: result.result,
                    check_result: result,
                });

                window.location.href = resolveTargetUrl('answer', 'typing', body);
            } catch (error) {
                showFeedback(error.message);
                setButtonDisabled('[data-study-check-button]', false);
            }
        });
    } catch (error) {
        renderEmptyState('Unable to load session', 'Study session data could not be loaded.');
        showFeedback(error.message);
    }
};

const renderJudgement = (payload) => {
    const judgement = document.querySelector('[data-study-judgement]');

    if (!judgement) {
        return;
    }

    const result = payload.judged_result;

    if (!result) {
        judgement.classList.add('is-hidden');
        judgement.removeAttribute('data-result');
        judgement.textContent = '';
        return;
    }

    const copy = {
        correct: 'Correct match. Your typed answer matched the expected answer.',
        close_match: 'Close match. Your answer was similar, but not exact.',
        incorrect: 'Incorrect. Review the correct answer before rating.',
    };

    judgement.dataset.result = result;
    judgement.textContent = copy[result] || result;
    judgement.classList.remove('is-hidden');
};

const setupStudyAnswer = async (body, context) => {
    clearFeedback();

    const payload = loadRevealPayload();

    if (!payload?.card) {
        renderEmptyState('No revealed card', 'Start from Flip or Typing mode before opening the answer screen.');
        return;
    }

    const session = payload.session || {
        progress: { total: 1, remaining: 0, completed: 0, has_cards: true, ended: false },
        mode: payload.mode,
    };

    renderCard({
        ...session,
        mode: payload.mode,
        current_card: payload.card,
    });

    setText('[data-study-answer-label]', payload.mode === 'typing' ? 'Correct Answer' : 'Back Side');
    setText(
        '[data-study-rating-title]',
        payload.mode === 'typing'
            ? 'How well did you know this after checking?'
            : 'How difficult was this to recall?'
    );
    setText('[data-study-user-answer]', payload.user_answer || '');
    toggleHidden('[data-study-user-answer-section]', payload.mode !== 'typing' || !payload.user_answer);
    renderJudgement(payload);

    ['again', 'hard', 'good', 'easy'].forEach((rating) => {
        setText(`[data-study-rating-${rating}]`, formatDueHint(rating, payload.card.state));
    });

    document.querySelectorAll('[data-study-rate-button]').forEach((button) => {
        button.addEventListener('click', async () => {
            const rating = button.dataset.studyRateButton;

            if (!rating) {
                return;
            }

            setButtonDisabled('[data-study-rate-button]', true);
            clearFeedback();

            try {
                const result = await fetchJson(interpolateCardUrl(context.rateUrlTemplate, payload.card.id), {
                    method: 'POST',
                    body: JSON.stringify({
                        mode: payload.mode,
                        rating,
                        typed_answer: payload.user_answer,
                        judged_result: payload.judged_result,
                    }),
                });

                clearRevealPayload();

                const targetMode = payload.mode === 'typing' ? 'typing' : 'flip';
                const targetScreen = targetMode === 'typing' ? 'typing' : 'front';
                const nextUrl = new URL(resolveTargetUrl(targetScreen, targetMode, body), window.location.origin);

                if (context.deckId) {
                    nextUrl.searchParams.set('deck_id', String(context.deckId));
                }

                if (result.next_card_id === null) {
                    showFeedback('Session complete. Redirecting to refreshed study session...');
                }

                window.location.href = nextUrl.toString();
            } catch (error) {
                showFeedback(error.message);
                setButtonDisabled('[data-study-rate-button]', false);
            }
        });
    });
};

document.addEventListener('DOMContentLoaded', () => {
    const body = document.body;
    const switchElement = document.querySelector('[data-study-mode-switch]');

    if (switchElement) {
        const currentUrl = new URL(window.location.href);
        const currentScreen = body.dataset.studyScreen || 'front';
        const preferredMode = getPreferredStudyMode(currentUrl, body);

        syncStudyModeUi(switchElement, preferredMode);

        if (!isValidStudyMode(currentUrl.searchParams.get('mode'))) {
            currentUrl.searchParams.set('mode', preferredMode);
            window.history.replaceState({}, '', currentUrl);
        }

        if (
            (currentScreen === 'typing' && preferredMode === 'flip') ||
            (currentScreen === 'front' && preferredMode === 'typing')
        ) {
            window.location.replace(resolveTargetUrl(currentScreen, preferredMode, body));
            return;
        }

        switchElement.querySelectorAll('[data-study-mode-option]').forEach((option) => {
            option.addEventListener('click', () => {
                const nextMode = option.dataset.studyModeOption;

                if (!isValidStudyMode(nextMode)) {
                    return;
                }

                localStorage.setItem(STUDY_MODE_KEY, nextMode);
            });
        });
    }

    setupImportScreen(body);

    const studyApp = document.querySelector('[data-study-app]');

    if (!studyApp) {
        return;
    }

    const context = getStudyContext(body);
    const currentScreen = body.dataset.studyScreen || 'front';

    if (currentScreen === 'front') {
        setupStudyFront(body, context);
        return;
    }

    if (currentScreen === 'typing') {
        setupStudyTyping(body, context);
        return;
    }

    if (currentScreen === 'answer') {
        setupStudyAnswer(body, context);
    }
});
