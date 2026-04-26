import { play as playTts, stop as stopTts } from './tts';

const fetchJson = async (url, options = {}) => {
    const isFormData = options.body instanceof FormData;
    const headers = { Accept: 'application/json', ...(options.headers || {}) };

    if (!isFormData) {
        headers['Content-Type'] = headers['Content-Type'] || 'application/json';
    }

    const response = await fetch(url, { ...options, headers });
    const payload = await response.json().catch(() => ({}));

    if (!response.ok) {
        const message = payload.message || (payload.errors ? Object.values(payload.errors).flat().join(' ') : '') || 'Request failed.';
        throw new Error(message);
    }

    return payload;
};

const replaceToken = (template, token, value) => template.replace(token, String(value));

const setupCreateDeck = () => {
    const modal = document.getElementById('create-deck-modal');
    const form = modal?.querySelector('form');
    const nameInput = document.getElementById('new-deck-name');
    const descriptionInput = document.getElementById('new-deck-description');
    const submitButton = document.getElementById('create-deck-submit-btn');
    const deckSelect = document.querySelector('[data-import-deck-select]');

    const open = () => {
        if (!modal || !nameInput) return;
        nameInput.value = '';
        if (descriptionInput) descriptionInput.value = '';
        modal.showModal();
        nameInput.focus();
    };

    document.querySelectorAll('[data-create-deck-button]').forEach((button) => button.addEventListener('click', open));

    if (deckSelect) {
        let previousValue = deckSelect.value === 'NEW_DECK' ? '' : deckSelect.value;
        deckSelect.addEventListener('change', () => {
            if (deckSelect.value === 'NEW_DECK') {
                deckSelect.value = previousValue;
                open();
            } else {
                previousValue = deckSelect.value;
            }
        });
    }

    form?.addEventListener('submit', async (event) => {
        event.preventDefault();
        if (!nameInput?.value.trim()) return;

        try {
            submitButton.disabled = true;
            const deck = await fetchJson(document.body.dataset.decksApiUrl, {
                method: 'POST',
                body: JSON.stringify({
                    name: nameInput.value.trim(),
                    description: descriptionInput?.value?.trim() || '',
                }),
            });

            modal.close();

            if (deckSelect) {
                const option = document.createElement('option');
                option.value = String(deck.id);
                option.textContent = deck.name;
                deckSelect.insertBefore(option, deckSelect.querySelector('option[value="NEW_DECK"]'));
                deckSelect.value = String(deck.id);
            } else {
                window.location.reload();
            }
        } catch (error) {
            window.alert(error.message);
        } finally {
            submitButton.disabled = false;
        }
    });
};

const setupDashboard = () => {
    document.querySelectorAll('[data-delete-deck-button]').forEach((button) => {
        button.addEventListener('click', async () => {
            const card = button.closest('[data-deck-card]');
            const deckId = Number(card?.dataset.deckId || '');
            const deckName = button.dataset.deckName || 'this deck';
            if (!deckId || !window.confirm(`Delete "${deckName}"? This also removes its notes and cards.`)) return;

            await fetchJson(replaceToken(document.body.dataset.deckUrlTemplate, '__DECK__', deckId), { method: 'DELETE' });
            card?.remove();
        });
    });
};

const setupDeckDetail = () => {
    const app = document.querySelector('[data-deck-detail-app]');
    if (!app) return;

    const modal = document.getElementById('card-modal');
    const form = app.querySelector('[data-card-form]');
    const cardIdInput = app.querySelector('[data-card-id-input]');
    const frontInput = app.querySelector('[data-card-front-input]');
    const backInput = app.querySelector('[data-card-back-input]');
    const title = app.querySelector('[data-card-modal-title]');
    const feedback = app.querySelector('[data-card-form-feedback]');
    const deckId = Number(app.dataset.deckId || '');

    const deckSwitcher = app.querySelector('[data-deck-switcher]');
    deckSwitcher?.addEventListener('change', () => {
        const selectedId = deckSwitcher.value;
        if (selectedId && Number(selectedId) !== deckId) {
            window.location.href = `/decks/${selectedId}`;
        }
    });

    const close = () => modal?.close();
    app.querySelectorAll('[data-close-card-modal-button]').forEach((button) => button.addEventListener('click', close));
    app.querySelector('[data-open-card-modal-button]')?.addEventListener('click', () => {
        cardIdInput.value = '';
        frontInput.value = '';
        backInput.value = '';
        title.textContent = 'Create Card';
        feedback.classList.add('is-hidden');
        modal.showModal();
    });

    app.querySelectorAll('[data-edit-card-button]').forEach((button) => {
        button.addEventListener('click', () => {
            const row = button.closest('[data-card-row]');
            cardIdInput.value = row?.dataset.cardId || '';
            frontInput.value = button.dataset.cardFront || '';
            backInput.value = button.dataset.cardBack || '';
            title.textContent = 'Edit Card';
            feedback.classList.add('is-hidden');
            modal.showModal();
        });
    });

    const deleteModal = document.getElementById('delete-card-modal');
    const deleteForm = app.querySelector('[data-delete-card-form]');
    const deleteCardIdInput = app.querySelector('[data-delete-card-id-input]');
    const deleteFeedback = app.querySelector('[data-delete-card-form-feedback]');
    const deleteSubmitBtn = app.querySelector('[data-delete-card-submit-button]');
    const deleteModalMessage = app.querySelector('[data-delete-modal-message]');

    const closeDelete = () => deleteModal?.close();
    app.querySelectorAll('[data-close-delete-modal-button]').forEach((button) => button.addEventListener('click', closeDelete));

    const totalCards = Number(app.dataset.totalCards || '0');
    let isAllSelected = false;
    let excludedIds = new Set();

    const openDeleteModal = (ids) => {
        if (!ids || ids.length === 0) return;
        deleteCardIdInput.value = ids.join(',');
        
        if (ids[0] === 'ALL') {
            const excludedCount = ids.length - 1;
            const toDeleteCount = totalCards - excludedCount;
            deleteModalMessage.textContent = `Are you sure you want to delete ${toDeleteCount} selected cards? This action cannot be undone.`;
        } else {
            deleteModalMessage.textContent = ids.length === 1 
                ? 'Are you sure you want to delete this card? This action cannot be undone.'
                : `Are you sure you want to delete ${ids.length} selected cards? This action cannot be undone.`;
        }
        
        deleteFeedback.classList.add('is-hidden');
        deleteModal.showModal();
    };

    app.querySelectorAll('[data-delete-card-button]').forEach((button) => {
        button.addEventListener('click', () => {
            const row = button.closest('[data-card-row]');
            const cardId = row?.dataset.cardId;
            if (cardId) openDeleteModal([cardId]);
        });
    });

    const selectAllCheckbox = app.querySelector('[data-select-all-checkbox]');
    const rowCheckboxes = Array.from(app.querySelectorAll('[data-row-checkbox]'));
    const bulkDeleteBtn = app.querySelector('[data-action-bulk-delete]');

    const updateBulkActions = () => {
        if (!bulkDeleteBtn || rowCheckboxes.length === 0) return;
        const checkedCount = rowCheckboxes.filter(cb => cb.checked).length;
        const actualSelectedCount = isAllSelected ? (totalCards - excludedIds.size) : checkedCount;

        bulkDeleteBtn.classList.toggle('is-hidden', actualSelectedCount === 0);
        
        if (selectAllCheckbox) {
            if (isAllSelected) {
                selectAllCheckbox.checked = true;
                selectAllCheckbox.indeterminate = excludedIds.size > 0;
            } else {
                selectAllCheckbox.checked = checkedCount === rowCheckboxes.length;
                selectAllCheckbox.indeterminate = checkedCount > 0 && checkedCount < rowCheckboxes.length;
            }
        }
    };

    selectAllCheckbox?.addEventListener('change', (e) => {
        isAllSelected = e.target.checked;
        excludedIds.clear();
        rowCheckboxes.forEach(cb => cb.checked = isAllSelected);
        updateBulkActions();
    });

    rowCheckboxes.forEach(cb => cb.addEventListener('change', (e) => {
        if (isAllSelected) {
            const id = Number(e.target.value);
            if (!e.target.checked) excludedIds.add(id);
            else excludedIds.delete(id);
        }
        updateBulkActions();
    }));

    bulkDeleteBtn?.addEventListener('click', () => {
        if (isAllSelected) {
            openDeleteModal(['ALL', ...Array.from(excludedIds)]);
        } else {
            const selectedIds = rowCheckboxes.filter(cb => cb.checked).map(cb => Number(cb.value));
            openDeleteModal(selectedIds);
        }
    });

    deleteForm?.addEventListener('submit', async (event) => {
        event.preventDefault();
        const ids = deleteCardIdInput.value.split(',').filter(Boolean);
        if (ids.length === 0) return;
        
        try {
            deleteSubmitBtn.disabled = true;
            deleteFeedback.classList.add('is-hidden');
            
            const payload = { deck_id: deckId };
            if (ids[0] === 'ALL') {
                payload.all = true;
                if (ids.length > 1) {
                    payload.exclude_ids = ids.slice(1).map(Number);
                }
            } else {
                payload.ids = ids.map(Number);
            }

            await fetchJson('/api/cards/bulk', { 
                method: 'DELETE',
                body: JSON.stringify(payload)
            });
            window.location.reload();
        } catch (error) {
            deleteFeedback.textContent = error.message;
            deleteFeedback.classList.remove('is-hidden');
        } finally {
            deleteSubmitBtn.disabled = false;
        }
    });

    form?.addEventListener('submit', async (event) => {
        event.preventDefault();
        const cardId = Number(cardIdInput.value || '');
        try {
            await fetchJson(cardId ? replaceToken(document.body.dataset.cardUrlTemplate, '__CARD__', cardId) : document.body.dataset.cardsApiUrl, {
                method: cardId ? 'PUT' : 'POST',
                body: JSON.stringify(cardId ? {
                    front_text: frontInput.value.trim(),
                    back_text: backInput.value.trim(),
                } : {
                    deck_id: deckId,
                    front_text: frontInput.value.trim(),
                    back_text: backInput.value.trim(),
                }),
            });
            window.location.reload();
        } catch (error) {
            feedback.textContent = error.message;
            feedback.classList.remove('is-hidden');
        }
    });
};

const setupImport = () => {
    const app = document.querySelector('[data-import-app]');
    if (!app) return;

    const previewButton = app.querySelector('[data-import-preview-button]');
    const confirmButton = app.querySelector('[data-import-confirm-button]');
    const deckSelect = app.querySelector('[data-import-deck-select]');
    const fileInput = app.querySelector('[data-import-file-input]');
    const feedback = app.querySelector('[data-import-feedback]');
    const rowsBody = app.querySelector('[data-import-rows-body]');
    const fileMeta = app.querySelector('[data-import-file-meta]');
    let importJobId = null;
    let confirmed = false;
    let rows = [];
    let filter = 'all';

    const renderRows = () => {
        const visible = rows.filter((row) => {
            const kind = row.status === 'invalid' ? 'invalid' : (row.warnings || []).length ? 'warning' : 'valid';
            return filter === 'all' ? true : kind === filter;
        });

        rowsBody.innerHTML = visible.length ? visible.map((row) => {
            const kind = row.status === 'invalid' ? 'invalid' : (row.warnings || []).length ? 'warning' : 'valid';
            const issueLines = [...(row.errors || []), ...(row.warnings || [])].map((item) => item.message).join('<br>') || 'No issues';
            return `<tr data-row-kind="${kind}"><td>${row.index}</td><td>${row.data?.front_text || ''}</td><td>${row.data?.back_text || ''}</td><td><span class="status-badge status-badge--${kind === 'valid' ? 'green' : kind === 'warning' ? 'amber' : 'red'}">${kind}</span></td><td class="import-issue-copy">${issueLines}</td></tr>`;
        }).join('') : '<tr><td colspan="5" class="muted-text import-table__empty">No rows match this filter.</td></tr>';
    };

    app.querySelectorAll('[data-import-filter]').forEach((button) => button.addEventListener('click', () => {
        filter = button.dataset.importFilter || 'all';
        app.querySelectorAll('[data-import-filter]').forEach((item) => item.classList.remove('is-mode-active'));
        button.classList.add('is-mode-active');
        renderRows();
    }));

    previewButton?.addEventListener('click', async () => {
        const file = fileInput?.files?.[0];
        const deckId = Number(deckSelect?.value || '');
        if (!file || !deckId) return;

        const formData = new FormData();
        formData.append('user_id', String(app.dataset.importUserId || ''));
        formData.append('deck_id', String(deckId));
        formData.append('file', file);

        const payload = await fetchJson(document.body.dataset.importPreviewUrl, { method: 'POST', body: formData });
        importJobId = payload.import_job_id;
        confirmed = false;
        rows = payload.rows || [];
        fileMeta.textContent = `${payload.file_name} - ${payload.detected_format} - ${payload.data_lines} data rows`;
        document.querySelector('[data-import-summary-total]').textContent = String(payload.summary?.total || 0);
        document.querySelector('[data-import-summary-valid]').textContent = String(payload.summary?.valid || 0);
        document.querySelector('[data-import-summary-warning]').textContent = String(payload.summary?.warning || 0);
        document.querySelector('[data-import-summary-invalid]').textContent = String(payload.summary?.invalid || 0);
        confirmButton.disabled = false;
        feedback.textContent = 'Preview completed. Review rows, then confirm import.';
        feedback.classList.remove('is-hidden');
        renderRows();
    });

    confirmButton?.addEventListener('click', async () => {
        if (!importJobId || confirmed) return;
        const payload = await fetchJson(document.body.dataset.importConfirmUrl, {
            method: 'POST',
            body: JSON.stringify({ user_id: Number(app.dataset.importUserId || ''), import_job_id: importJobId }),
        });
        confirmed = true;
        confirmButton.disabled = true;
        feedback.textContent = `Import complete. Imported ${payload.summary?.imported || 0} rows, skipped ${payload.summary?.skipped || 0}, invalid ${payload.summary?.invalid || 0}.`;
        feedback.classList.remove('is-hidden');
    });
};

const setupStudy = async () => {
    const app = document.querySelector('[data-study-app]');
    if (!app) return;

    const body = document.body;
    const sessionUrl = new URL(body.dataset.studySessionApiUrl, window.location.origin);
    sessionUrl.searchParams.set('user_id', body.dataset.studyUserId || '');
    if (body.dataset.studyDeckId) sessionUrl.searchParams.set('deck_id', body.dataset.studyDeckId);
    sessionUrl.searchParams.set('mode', body.dataset.studyMode || 'flip');

    const setText = (selector, value) => {
        const el = document.querySelector(selector);
        if (el) el.textContent = value;
    };

    const session = await fetchJson(sessionUrl.toString()).catch(() => null);

    if (session?.progress) {
        setText('[data-study-new-count]', session.progress.new);
        setText('[data-study-learning-count]', session.progress.learning);
        setText('[data-study-review-count]', session.progress.review);

        const compact = document.querySelector('[data-study-progress-compact]');
        if (compact) {
            compact.textContent = `${session.progress.completed} / ${session.progress.total}`;
        }

        const progressBar = document.querySelector('[data-study-progress-bar]');
        if (progressBar) {
            const percent = session.progress.total > 0 ? (session.progress.completed / session.progress.total) * 100 : 100;
            progressBar.style.width = `${percent}%`;
        }
    }

    if (!session?.current_card) {
        const hide = (sel) => document.querySelectorAll(sel).forEach(el => {
            el.classList.add('is-hidden');
            el.style.display = 'none';
        });
        hide('[data-study-card]');
        hide('.rating-panel');
        hide('.answer-panel');
        hide('.study-actions');
        
        const emptyState = document.querySelector('[data-study-empty-state]');
        if (emptyState) {
            emptyState.classList.remove('is-hidden');
            emptyState.style.display = 'block';
        }
        return;
    }

    const card = session.current_card;
    setText('[data-study-front-text]', card.front_text || card.front_plain_text || 'Untitled card');
    setText('[data-study-back-text]', card.back_text || card.back_plain_text || 'No answer available.');
    setText('[data-study-state-label]', card.state || 'Card');
    setText('[data-study-state-tag]', card.state || 'Card');
    setText('[data-study-mode-tag]', `Mode: ${session.mode}`);

    document.querySelectorAll('[data-study-tts-button]').forEach((button) => {
        button.disabled = false;
        button.addEventListener('click', async () => {
            const side = button.dataset.studyTtsButton === 'back' ? (card.back_plain_text || card.back_text) : (card.front_plain_text || card.front_text);
            const played = await playTts(side);
            if (!played) window.alert('Text-to-speech is not available in this browser.');
        });
    });

    const revealButton = document.querySelector('[data-study-reveal-button]');
    if (revealButton) {
        revealButton.disabled = false;
        revealButton.addEventListener('click', () => {
            sessionStorage.setItem('flashmind-study-reveal', JSON.stringify({ session, card, mode: session.mode }));
            window.location.href = body.dataset.studyAnswerUrl + '?mode=' + (body.dataset.studyMode || 'flip') + (body.dataset.studyDeckId ? '&deck_id=' + body.dataset.studyDeckId : '');
        });
    }

    const checkButton = document.querySelector('[data-study-check-button]');
    if (checkButton) {
        checkButton.disabled = false;
        checkButton.addEventListener('click', async () => {
            const userAnswer = document.querySelector('[data-study-answer-input]')?.value?.trim() || '';
            if (!userAnswer) return;
            const result = await fetchJson(replaceToken(body.dataset.studyCheckAnswerUrlTemplate, '__CARD__', card.id), {
                method: 'POST',
                body: JSON.stringify({ mode: 'typing', user_answer: userAnswer }),
            });
            sessionStorage.setItem('flashmind-study-reveal', JSON.stringify({ session, card, mode: 'typing', user_answer: userAnswer, judged_result: result.result }));
            window.location.href = body.dataset.studyAnswerUrl + '?mode=typing' + (body.dataset.studyDeckId ? '&deck_id=' + body.dataset.studyDeckId : '');
        });
    }

    if (body.dataset.studyScreen === 'answer') {
        const stored = JSON.parse(sessionStorage.getItem('flashmind-study-reveal') || '{}');
        if (stored.card) {
            setText('[data-study-front-text]', stored.card.front_text || stored.card.front_plain_text || 'Untitled card');
            setText('[data-study-back-text]', stored.card.back_text || stored.card.back_plain_text || 'No answer available.');
            setText('[data-study-user-answer]', stored.user_answer || '');
            document.querySelector('[data-study-user-answer-section]')?.classList.toggle('is-hidden', !stored.user_answer);

            if (stored.session && stored.session.progress) {
                setText('[data-study-new-count]', stored.session.progress.new);
                setText('[data-study-learning-count]', stored.session.progress.learning);
                setText('[data-study-review-count]', stored.session.progress.review);
            }

            // Auto-play TTS for the back side
            const backText = stored.card.back_plain_text || stored.card.back_text;
            if (backText) {
                setTimeout(() => {
                    playTts(backText).catch(err => console.warn('Auto-play TTS failed:', err));
                }, 300);
            }
        }

        document.querySelectorAll('[data-study-rate-button]').forEach((button) => button.addEventListener('click', async () => {
            const rating = button.dataset.studyRateButton;
            const storedPayload = JSON.parse(sessionStorage.getItem('flashmind-study-reveal') || '{}');
            if (!storedPayload.card || !rating) return;
            await fetchJson(replaceToken(body.dataset.studyRateUrlTemplate, '__CARD__', storedPayload.card.id), {
                method: 'POST',
                body: JSON.stringify({ mode: storedPayload.mode || 'flip', rating, typed_answer: storedPayload.user_answer || null, judged_result: storedPayload.judged_result || null }),
            });
            stopTts();
            sessionStorage.removeItem('flashmind-study-reveal');
            window.location.href = (storedPayload.mode === 'typing' ? body.dataset.studyTypingUrl : body.dataset.studyFrontUrl) + '?mode=' + (storedPayload.mode || 'flip') + (body.dataset.studyDeckId ? '&deck_id=' + body.dataset.studyDeckId : '');
        }));
    }
};

document.addEventListener('DOMContentLoaded', async () => {
    setupCreateDeck();
    setupDashboard();
    setupDeckDetail();
    setupImport();
    await setupStudy();
});
