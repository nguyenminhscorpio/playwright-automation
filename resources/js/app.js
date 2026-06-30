import { play as playTts, stop as stopTts } from "./tts";

const fetchJson = async (url, options = {}) => {
    const isFormData = options.body instanceof FormData;
    const headers = { Accept: "application/json", ...(options.headers || {}) };

    if (!isFormData) {
        headers["Content-Type"] = headers["Content-Type"] || "application/json";
    }

    const response = await fetch(url, { ...options, headers });
    const payload = await response.json().catch(() => ({}));

    if (!response.ok) {
        const message =
            payload.message ||
            (payload.errors
                ? Object.values(payload.errors).flat().join(" ")
                : "") ||
            "Request failed.";
        throw new Error(message);
    }

    return payload;
};

const replaceToken = (template, token, value) =>
    template.replace(token, String(value));

const setupCreateDeck = () => {
    const modal = document.getElementById("create-deck-modal");
    const form = modal?.querySelector("form");
    const nameInput = document.getElementById("new-deck-name");
    const descriptionInput = document.getElementById("new-deck-description");
    const submitButton = document.getElementById("create-deck-submit-btn");
    const deckSelect = document.querySelector("[data-import-deck-select]");

    const open = () => {
        if (!modal || !nameInput) return;
        nameInput.value = "";
        if (descriptionInput) descriptionInput.value = "";
        modal.showModal();
        nameInput.focus();
    };

    document
        .querySelectorAll("[data-create-deck-button]")
        .forEach((button) => button.addEventListener("click", open));

    if (deckSelect) {
        let previousValue =
            deckSelect.value === "NEW_DECK" ? "" : deckSelect.value;
        deckSelect.addEventListener("change", () => {
            if (deckSelect.value === "NEW_DECK") {
                deckSelect.value = previousValue;
                open();
            } else {
                previousValue = deckSelect.value;
            }
        });
    }

    form?.addEventListener("submit", async (event) => {
        event.preventDefault();
        if (!nameInput?.value.trim()) return;

        try {
            submitButton.disabled = true;
            const userId = document.body.dataset.authUserId || "";
            const deck = await fetchJson(document.body.dataset.decksApiUrl, {
                method: "POST",
                body: JSON.stringify({
                    user_id: userId ? Number(userId) : undefined,
                    name: nameInput.value.trim(),
                    description: descriptionInput?.value?.trim() || "",
                }),
            });

            modal.close();

            if (deckSelect) {
                const option = document.createElement("option");
                option.value = String(deck.id);
                option.textContent = deck.name;
                deckSelect.insertBefore(
                    option,
                    deckSelect.querySelector('option[value="NEW_DECK"]'),
                );
                deckSelect.value = String(deck.id);
                // notify custom dropdown to rebuild
                deckSelect.dispatchEvent(
                    new Event("change", { bubbles: true }),
                );
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
    const deleteModal = document.getElementById("delete-deck-modal");
    const deleteForm = document.querySelector("[data-delete-deck-form]");
    const deleteDeckIdInput = document.querySelector(
        "[data-delete-deck-id-input]",
    );
    const deleteFeedback = document.querySelector(
        "[data-delete-deck-form-feedback]",
    );
    const deleteSubmitBtn = document.querySelector(
        "[data-delete-deck-submit-button]",
    );
    const deleteModalMessage = document.querySelector(
        "[data-delete-deck-modal-message]",
    );
    let activeDeckCard = null;

    const closeDeleteModal = () => deleteModal?.close();
    document
        .querySelectorAll("[data-close-delete-deck-modal-button]")
        .forEach((button) =>
            button.addEventListener("click", closeDeleteModal),
        );

    document.querySelectorAll("[data-delete-deck-button]").forEach((button) => {
        button.addEventListener("click", () => {
            const card = button.closest("[data-deck-card]");
            const deckId = Number(card?.dataset.deckId || "");
            const deckName = button.dataset.deckName || "this deck";
            if (
                !deckId ||
                !deleteModal ||
                !deleteDeckIdInput ||
                !deleteModalMessage
            )
                return;

            activeDeckCard = card;
            deleteDeckIdInput.value = String(deckId);
            deleteModalMessage.textContent = `Are you sure you want to delete "${deckName}"? This also removes its notes and cards.`;
            deleteFeedback?.classList.add("is-hidden");
            if (deleteFeedback) deleteFeedback.textContent = "";
            deleteModal.showModal();
        });
    });

    deleteForm?.addEventListener("submit", async (event) => {
        event.preventDefault();
        const deckId = Number(deleteDeckIdInput?.value || "");
        if (!deckId) return;

        try {
            deleteSubmitBtn.disabled = true;
            deleteFeedback?.classList.add("is-hidden");
            if (deleteFeedback) deleteFeedback.textContent = "";

            await fetchJson(
                replaceToken(
                    document.body.dataset.deckUrlTemplate,
                    "__DECK__",
                    deckId,
                ),
                { method: "DELETE" },
            );
            activeDeckCard?.remove();
            closeDeleteModal();
        } catch (error) {
            if (deleteFeedback) {
                deleteFeedback.textContent = error.message;
                deleteFeedback.classList.remove("is-hidden");
            }
        } finally {
            deleteSubmitBtn.disabled = false;
        }
    });
};

const setupDeckDetail = () => {
    const app = document.querySelector("[data-deck-detail-app]");
    if (!app) return;

    const modal = document.getElementById("card-modal");
    const form = app.querySelector("[data-card-form]");
    const cardIdInput = app.querySelector("[data-card-id-input]");
    const frontInput = app.querySelector("[data-card-front-input]");
    const backInput = app.querySelector("[data-card-back-input]");
    const title = app.querySelector("[data-card-modal-title]");
    const feedback = app.querySelector("[data-card-form-feedback]");
    const deckId = Number(app.dataset.deckId || "");

    const deckSwitcher = app.querySelector("[data-deck-switcher]");
    deckSwitcher?.addEventListener("change", () => {
        const selectedId = deckSwitcher.value;
        if (selectedId && Number(selectedId) !== deckId) {
            window.location.href = `/decks/${selectedId}`;
        }
    });

    const close = () => modal?.close();
    app.querySelectorAll("[data-close-card-modal-button]").forEach((button) =>
        button.addEventListener("click", close),
    );
    app.querySelector("[data-open-card-modal-button]")?.addEventListener(
        "click",
        () => {
            cardIdInput.value = "";
            frontInput.value = "";
            backInput.value = "";
            title.textContent = "Create Card";
            feedback.classList.add("is-hidden");
            modal.showModal();
        },
    );

    app.querySelectorAll("[data-edit-card-button]").forEach((button) => {
        button.addEventListener("click", () => {
            const row = button.closest("[data-card-row]");
            cardIdInput.value = row?.dataset.cardId || "";
            frontInput.value = button.dataset.cardFront || "";
            backInput.value = button.dataset.cardBack || "";
            title.textContent = "Edit Card";
            feedback.classList.add("is-hidden");
            modal.showModal();
        });
    });

    const deleteModal = document.getElementById("delete-card-modal");
    const deleteForm = app.querySelector("[data-delete-card-form]");
    const deleteCardIdInput = app.querySelector("[data-delete-card-id-input]");
    const deleteFeedback = app.querySelector(
        "[data-delete-card-form-feedback]",
    );
    const deleteSubmitBtn = app.querySelector(
        "[data-delete-card-submit-button]",
    );
    const deleteModalMessage = app.querySelector("[data-delete-modal-message]");

    const closeDelete = () => deleteModal?.close();
    app.querySelectorAll("[data-close-delete-modal-button]").forEach((button) =>
        button.addEventListener("click", closeDelete),
    );

    const totalCards = Number(app.dataset.totalCards || "0");
    let isAllSelected = false;
    let excludedIds = new Set();

    const openDeleteModal = (ids) => {
        if (!ids || ids.length === 0) return;
        deleteCardIdInput.value = ids.join(",");

        if (ids[0] === "ALL") {
            const excludedCount = ids.length - 1;
            const toDeleteCount = totalCards - excludedCount;
            deleteModalMessage.textContent = `Are you sure you want to delete ${toDeleteCount} selected cards? This action cannot be undone.`;
        } else {
            deleteModalMessage.textContent =
                ids.length === 1
                    ? "Are you sure you want to delete this card? This action cannot be undone."
                    : `Are you sure you want to delete ${ids.length} selected cards? This action cannot be undone.`;
        }

        deleteFeedback.classList.add("is-hidden");
        deleteModal.showModal();
    };

    app.querySelectorAll("[data-delete-card-button]").forEach((button) => {
        button.addEventListener("click", () => {
            const row = button.closest("[data-card-row]");
            const cardId = row?.dataset.cardId;
            if (cardId) openDeleteModal([cardId]);
        });
    });

    const selectAllCheckbox = app.querySelector("[data-select-all-checkbox]");
    const rowCheckboxes = Array.from(
        app.querySelectorAll("[data-row-checkbox]"),
    );
    const bulkDeleteBtn = app.querySelector("[data-action-bulk-delete]");

    const updateBulkActions = () => {
        if (!bulkDeleteBtn || rowCheckboxes.length === 0) return;
        const checkedCount = rowCheckboxes.filter((cb) => cb.checked).length;
        const actualSelectedCount = isAllSelected
            ? totalCards - excludedIds.size
            : checkedCount;

        bulkDeleteBtn.classList.toggle("is-hidden", actualSelectedCount === 0);

        if (selectAllCheckbox) {
            if (isAllSelected) {
                selectAllCheckbox.checked = true;
                selectAllCheckbox.indeterminate = excludedIds.size > 0;
            } else {
                selectAllCheckbox.checked =
                    checkedCount === rowCheckboxes.length;
                selectAllCheckbox.indeterminate =
                    checkedCount > 0 && checkedCount < rowCheckboxes.length;
            }
        }
    };

    selectAllCheckbox?.addEventListener("change", (e) => {
        isAllSelected = e.target.checked;
        excludedIds.clear();
        rowCheckboxes.forEach((cb) => (cb.checked = isAllSelected));
        updateBulkActions();
    });

    rowCheckboxes.forEach((cb) =>
        cb.addEventListener("change", (e) => {
            if (isAllSelected) {
                const id = Number(e.target.value);
                if (!e.target.checked) excludedIds.add(id);
                else excludedIds.delete(id);
            }
            updateBulkActions();
        }),
    );

    bulkDeleteBtn?.addEventListener("click", () => {
        if (isAllSelected) {
            openDeleteModal(["ALL", ...Array.from(excludedIds)]);
        } else {
            const selectedIds = rowCheckboxes
                .filter((cb) => cb.checked)
                .map((cb) => Number(cb.value));
            openDeleteModal(selectedIds);
        }
    });

    deleteForm?.addEventListener("submit", async (event) => {
        event.preventDefault();
        const ids = deleteCardIdInput.value.split(",").filter(Boolean);
        if (ids.length === 0) return;

        try {
            deleteSubmitBtn.disabled = true;
            deleteFeedback.classList.add("is-hidden");

            const payload = { deck_id: deckId };
            if (ids[0] === "ALL") {
                payload.all = true;
                if (ids.length > 1) {
                    payload.exclude_ids = ids.slice(1).map(Number);
                }
            } else {
                payload.ids = ids.map(Number);
            }

            await fetchJson("/api/cards/bulk", {
                method: "DELETE",
                body: JSON.stringify(payload),
            });
            window.location.reload();
        } catch (error) {
            deleteFeedback.textContent = error.message;
            deleteFeedback.classList.remove("is-hidden");
        } finally {
            deleteSubmitBtn.disabled = false;
        }
    });

    form?.addEventListener("submit", async (event) => {
        event.preventDefault();
        const cardId = Number(cardIdInput.value || "");
        try {
            await fetchJson(
                cardId
                    ? replaceToken(
                          document.body.dataset.cardUrlTemplate,
                          "__CARD__",
                          cardId,
                      )
                    : document.body.dataset.cardsApiUrl,
                {
                    method: cardId ? "PUT" : "POST",
                    body: JSON.stringify(
                        cardId
                            ? {
                                  front_text: frontInput.value.trim(),
                                  back_text: backInput.value.trim(),
                              }
                            : {
                                  deck_id: deckId,
                                  front_text: frontInput.value.trim(),
                                  back_text: backInput.value.trim(),
                              },
                    ),
                },
            );
            window.location.reload();
        } catch (error) {
            feedback.textContent = error.message;
            feedback.classList.remove("is-hidden");
        }
    });
};

/**
 * Replaces a hidden native <select data-import-deck-select> with a fully
 * styled custom dropdown. The native select is kept hidden for JS compatibility.
 */
const setupDeckSelect = (nativeSelect) => {
    if (!nativeSelect) return;

    const wrap = nativeSelect.closest("[data-deck-select-wrap]");
    if (!wrap) return;

    // ── Build trigger button ────────────────────────────────
    const trigger = document.createElement("button");
    trigger.type = "button";
    trigger.className = "deck-select-trigger";
    trigger.setAttribute("aria-haspopup", "listbox");
    trigger.setAttribute("aria-expanded", "false");

    const iconEl = document.createElement("span");
    iconEl.className = "material-symbols-outlined deck-select-trigger__icon";
    iconEl.textContent = "layers";

    const labelEl = document.createElement("span");
    labelEl.className = "deck-select-trigger__label";

    const chevronEl = document.createElement("span");
    chevronEl.className =
        "material-symbols-outlined deck-select-trigger__chevron";
    chevronEl.textContent = "expand_more";

    trigger.append(iconEl, labelEl, chevronEl);

    // ── Build panel ─────────────────────────────────────────
    const panel = document.createElement("div");
    panel.className = "deck-select-panel is-hidden";
    panel.setAttribute("role", "listbox");

    wrap.append(trigger, panel);

    // ── Helpers ─────────────────────────────────────────────
    const isOpen = () => !panel.classList.contains("is-hidden");

    const openPanel = () => {
        buildOptions();
        panel.classList.remove("is-hidden");
        trigger.classList.add("is-open");
        trigger.setAttribute("aria-expanded", "true");
    };

    const closePanel = () => {
        panel.classList.add("is-hidden");
        trigger.classList.remove("is-open");
        trigger.setAttribute("aria-expanded", "false");
    };

    const buildOptions = () => {
        panel.innerHTML = "";
        const opts = Array.from(nativeSelect.options);
        const current = nativeSelect.value;

        // Update trigger label
        const selected = opts.find((o) => o.value === current && !o.disabled);
        labelEl.textContent = selected ? selected.text : "Select a deck…";
        labelEl.className = selected
            ? "deck-select-trigger__label"
            : "deck-select-trigger__label deck-select-trigger__label--empty";

        opts.forEach((opt) => {
            const el = document.createElement("div");

            // "No deck available" placeholder
            if (opt.disabled) {
                el.className = "deck-select-option deck-select-option--empty";
                el.setAttribute("role", "option");
                el.setAttribute("aria-disabled", "true");
                el.textContent = opt.text;
                panel.append(el);
                return;
            }

            // "+ Create New Deck" special row
            if (opt.value === "NEW_DECK") {
                el.className = "deck-select-option deck-select-option--new";
                el.setAttribute("role", "option");
                el.innerHTML =
                    '<span class="material-symbols-outlined">add</span>' +
                    "<span>Create New Deck…</span>";
                el.addEventListener("click", () => {
                    closePanel();
                    // Temporarily set NEW_DECK so setupCreateDeck fires its logic
                    nativeSelect.value = "NEW_DECK";
                    nativeSelect.dispatchEvent(
                        new Event("change", { bubbles: true }),
                    );
                });
                panel.append(el);
                return;
            }

            // Normal deck option
            el.className =
                "deck-select-option" +
                (opt.value === current ? " is-selected" : "");
            el.setAttribute("role", "option");
            el.setAttribute("aria-selected", String(opt.value === current));
            el.innerHTML =
                '<span class="material-symbols-outlined deck-select-option__check">check</span>' +
                `<span class="deck-select-option__text">${opt.text}</span>`;

            el.addEventListener("click", () => {
                nativeSelect.value = opt.value;
                nativeSelect.dispatchEvent(
                    new Event("change", { bubbles: true }),
                );
                closePanel();
                // Rebuild trigger label immediately
                labelEl.textContent = opt.text;
                labelEl.className = "deck-select-trigger__label";
            });

            panel.append(el);
        });
    };

    // ── Events ──────────────────────────────────────────────
    trigger.addEventListener("click", (e) => {
        e.stopPropagation();
        isOpen() ? closePanel() : openPanel();
    });

    // Close on outside click
    document.addEventListener("click", (e) => {
        if (!wrap.contains(e.target)) closePanel();
    });

    // Keyboard: Escape closes
    wrap.addEventListener("keydown", (e) => {
        if (e.key === "Escape") closePanel();
    });

    // Rebuild when native select is updated externally (e.g., new deck created)
    nativeSelect.addEventListener("change", () => {
        if (!isOpen()) {
            // Just refresh the trigger label
            const opts = Array.from(nativeSelect.options);
            const sel = opts.find(
                (o) => o.value === nativeSelect.value && !o.disabled,
            );
            if (sel) {
                labelEl.textContent = sel.text;
                labelEl.className = "deck-select-trigger__label";
            }
        }
    });

    // Initial render of trigger label
    buildOptions();
    // Collapse panel (buildOptions opens it conceptually, but panel is still hidden)
};

const setupImport = () => {
    const app = document.querySelector("[data-import-app]");
    if (!app) return;

    const previewButton = app.querySelector("[data-import-preview-button]");
    const confirmButton = app.querySelector("[data-import-confirm-button]");
    const deckSelect = app.querySelector("[data-import-deck-select]");
    const fileInput = app.querySelector("[data-import-file-input]");

    // Boot the custom deck dropdown
    setupDeckSelect(deckSelect);

    const feedback = app.querySelector("[data-import-feedback]");
    const rowsBody = app.querySelector("[data-import-rows-body]");
    const fileMeta = app.querySelector("[data-import-file-meta]");
    const dropzone = app.querySelector("[data-import-dropzone]");
    const dropzoneIdle = app.querySelector("[data-import-dropzone-idle]");
    const dropzoneReady = app.querySelector("[data-import-dropzone-ready]");
    const filenameEl = app.querySelector("[data-import-filename]");
    const clearBtn = app.querySelector("[data-import-dropzone-clear]");
    const progressEl = app.querySelector("[data-import-progress]");
    const progressLabel = app.querySelector("[data-import-progress-label]");
    const progressPct = app.querySelector("[data-import-progress-pct]");
    const progressBar = app.querySelector("[data-import-progress-bar]");

    let importJobId = null;
    let confirmed = false;
    let rows = [];
    let filter = "all";
    let stepTimer = null;

    // ── Step indicator ───────────────────────────────────────
    const setStep = (step) => {
        document.querySelectorAll("[data-import-step]").forEach((el) => {
            const n = Number(el.dataset.importStep);
            el.classList.toggle("is-active", n === step);
            el.classList.toggle("is-done", n < step);
        });
    };

    // ── Feedback helper ──────────────────────────────────────
    const showFeedback = (message, type = "info") => {
        feedback.textContent = message;
        feedback.className = `import-feedback import-feedback--${type}`;
        feedback.classList.remove("is-hidden");
    };

    // ── Progress helpers ─────────────────────────────────────
    const setProgress = (label, pct) => {
        if (progressLabel) progressLabel.textContent = label;
        if (progressPct) progressPct.textContent = `${pct}%`;
        if (progressBar) progressBar.style.width = `${pct}%`;
    };

    const showProgress = (label, pct = 0) => {
        progressEl?.classList.remove("is-hidden");
        feedback.classList.add("is-hidden");
        setProgress(label, pct);
    };

    const hideProgress = () => {
        progressEl?.classList.add("is-hidden");
        if (progressBar) progressBar.style.width = "0%";
    };

    /**
     * Cycles through an array of { label, pct } steps at the given interval,
     * stopping at the last step until stopSteps() is called.
     */
    const startSteps = (steps, intervalMs = 700) => {
        clearTimeout(stepTimer);
        let i = 0;
        const tick = () => {
            if (i < steps.length) {
                setProgress(steps[i].label, steps[i].pct);
                i++;
                if (i < steps.length) stepTimer = setTimeout(tick, intervalMs);
            }
        };
        tick();
    };

    const stopSteps = () => clearTimeout(stepTimer);

    /**
     * Completes the progress bar to 100 %, shows briefly, then hides.
     */
    const completeProgress = (doneLabel = "Done!") =>
        new Promise((resolve) => {
            stopSteps();
            setProgress(doneLabel, 100);
            setTimeout(() => {
                hideProgress();
                resolve();
            }, 500);
        });

    // ── Dropzone ─────────────────────────────────────────────
    const updateDropzone = (file) => {
        if (!file) {
            dropzoneIdle?.classList.remove("is-hidden");
            dropzoneReady?.classList.add("is-hidden");
        } else {
            dropzoneIdle?.classList.add("is-hidden");
            dropzoneReady?.classList.remove("is-hidden");
            if (filenameEl) filenameEl.textContent = file.name;
        }
    };

    dropzone?.addEventListener("click", (e) => {
        if (e.target.closest("[data-import-dropzone-clear]")) return;
        fileInput?.click();
    });

    fileInput?.addEventListener("change", () => {
        updateDropzone(fileInput.files?.[0] ?? null);
    });

    clearBtn?.addEventListener("click", (e) => {
        e.stopPropagation();
        if (fileInput) fileInput.value = "";
        updateDropzone(null);
    });

    dropzone?.addEventListener("dragover", (e) => {
        e.preventDefault();
        dropzone.classList.add("is-drag-over");
    });

    dropzone?.addEventListener("dragleave", (e) => {
        if (!dropzone.contains(e.relatedTarget))
            dropzone.classList.remove("is-drag-over");
    });

    dropzone?.addEventListener("drop", (e) => {
        e.preventDefault();
        dropzone.classList.remove("is-drag-over");
        const file = e.dataTransfer?.files?.[0];
        if (!file) return;
        try {
            const dt = new DataTransfer();
            dt.items.add(file);
            if (fileInput) fileInput.files = dt.files;
        } catch (_) {
            /* DataTransfer not universally supported */
        }
        updateDropzone(file);
    });

    // ── Row rendering ────────────────────────────────────────
    const rowKind = (row) =>
        row.status === "invalid"
            ? "invalid"
            : (row.warnings || []).length
              ? "warning"
              : "valid";

    const renderRows = () => {
        const visible = rows.filter((row) =>
            filter === "all" ? true : rowKind(row) === filter,
        );

        if (!visible.length) {
            rowsBody.innerHTML = `<tr><td colspan="5" class="import-table__empty">
                <span class="material-symbols-outlined">filter_list_off</span>
                <p>No rows match this filter.</p>
            </td></tr>`;
            return;
        }

        rowsBody.innerHTML = visible
            .map((row) => {
                const kind = rowKind(row);
                const badge =
                    kind === "valid"
                        ? "green"
                        : kind === "warning"
                          ? "amber"
                          : "red";
                const issues =
                    [...(row.errors || []), ...(row.warnings || [])]
                        .map((i) => i.message)
                        .join("<br>") || "—";
                const front = (row.data?.front_text || "").slice(0, 140);
                const back = (row.data?.back_text || "").slice(0, 140);
                return `<tr data-row-kind="${kind}">
                <td class="import-table__col-num" style="color:var(--muted);font-size:0.85rem">${row.index}</td>
                <td>${front}</td>
                <td>${back}</td>
                <td><span class="status-badge status-badge--${badge}">${kind}</span></td>
                <td class="import-issue-copy">${issues}</td>
            </tr>`;
            })
            .join("");
    };

    // ── Filter tabs ──────────────────────────────────────────
    app.querySelectorAll("[data-import-filter]").forEach((btn) =>
        btn.addEventListener("click", () => {
            filter = btn.dataset.importFilter || "all";
            app.querySelectorAll("[data-import-filter]").forEach((b) =>
                b.classList.remove("is-mode-active"),
            );
            btn.classList.add("is-mode-active");
            renderRows();
        }),
    );

    // ── Preview ──────────────────────────────────────────────
    previewButton?.addEventListener("click", async () => {
        const file = fileInput?.files?.[0];
        const deckId = Number(deckSelect?.value || "");

        if (!file) {
            showFeedback("Please select a TXT file first.", "warning");
            return;
        }
        if (!deckId) {
            showFeedback("Please select a target deck first.", "warning");
            return;
        }

        previewButton.disabled = true;
        previewButton.innerHTML =
            '<span class="material-symbols-outlined import-btn-spin">autorenew</span><span>Previewing…</span>';

        showProgress("Reading file…", 5);
        startSteps(
            [
                { label: "Parsing rows…", pct: 30 },
                { label: "Validating content…", pct: 60 },
                { label: "Saving preview data…", pct: 82 },
            ],
            700,
        );

        try {
            const formData = new FormData();
            formData.append("user_id", String(app.dataset.importUserId || ""));
            formData.append("deck_id", String(deckId));
            formData.append("file", file);

            const payload = await fetchJson(
                document.body.dataset.importPreviewUrl,
                { method: "POST", body: formData },
            );

            importJobId = payload.import_job_id;
            confirmed = false;
            rows = payload.rows || [];

            fileMeta.textContent = `${payload.file_name} · ${payload.detected_format} · ${payload.data_lines} data rows`;
            document.querySelector("[data-import-summary-total]").textContent =
                String(payload.summary?.total || 0);
            document.querySelector("[data-import-summary-valid]").textContent =
                String(payload.summary?.valid || 0);
            document.querySelector(
                "[data-import-summary-warning]",
            ).textContent = String(payload.summary?.warning || 0);
            document.querySelector(
                "[data-import-summary-invalid]",
            ).textContent = String(payload.summary?.invalid || 0);

            confirmButton.disabled = false;

            await completeProgress("Preview complete!");
            showFeedback(
                "Preview ready — review the rows below, then click Confirm Import when satisfied.",
                "success",
            );
            setStep(2);
            renderRows();
        } catch (err) {
            stopSteps();
            hideProgress();
            showFeedback(
                err.message || "Preview failed. Check your file and try again.",
                "error",
            );
        } finally {
            previewButton.disabled = false;
            previewButton.innerHTML =
                '<span class="material-symbols-outlined">preview</span><span>Preview Import</span>';
        }
    });

    // ── Confirm ──────────────────────────────────────────────
    confirmButton?.addEventListener("click", async () => {
        if (!importJobId || confirmed) return;

        confirmButton.disabled = true;
        confirmButton.innerHTML =
            '<span class="material-symbols-outlined import-btn-spin">autorenew</span><span>Importing…</span>';

        showProgress("Checking for duplicates…", 8);
        startSteps(
            [
                { label: "Creating notes…", pct: 28 },
                { label: "Creating flashcards…", pct: 54 },
                { label: "Finalising import…", pct: 80 },
            ],
            1100,
        );

        try {
            const payload = await fetchJson(
                document.body.dataset.importConfirmUrl,
                {
                    method: "POST",
                    body: JSON.stringify({
                        user_id: Number(app.dataset.importUserId || ""),
                        import_job_id: importJobId,
                    }),
                },
            );
            confirmed = true;

            const imported = payload.summary?.imported || 0;
            const skipped = payload.summary?.skipped || 0;
            const invalid = payload.summary?.invalid || 0;

            await completeProgress(`${imported} cards imported!`);
            showFeedback(
                `Import complete — ${imported} imported, ${skipped} skipped, ${invalid} invalid.`,
                "success",
            );
            setStep(3);
            confirmButton.innerHTML =
                '<span class="material-symbols-outlined">check</span><span>Imported</span>';
        } catch (err) {
            stopSteps();
            hideProgress();
            showFeedback(
                err.message || "Import failed. Please try again.",
                "error",
            );
            confirmButton.disabled = false;
            confirmButton.innerHTML =
                '<span class="material-symbols-outlined">task_alt</span><span>Confirm Import</span>';
        }
    });
};

const setupStudy = async () => {
    const app = document.querySelector("[data-study-app]");
    if (!app) return;

    const body = document.body;
    const sessionUrl = new URL(
        body.dataset.studySessionApiUrl,
        window.location.origin,
    );
    sessionUrl.searchParams.set("user_id", body.dataset.studyUserId || "");
    if (body.dataset.studyDeckId)
        sessionUrl.searchParams.set("deck_id", body.dataset.studyDeckId);
    sessionUrl.searchParams.set("mode", body.dataset.studyMode || "flip");

    const setText = (selector, value) => {
        const el = document.querySelector(selector);
        if (el) el.textContent = value;
    };

    const session = await fetchJson(sessionUrl.toString()).catch(() => null);

    if (session?.progress) {
        setText("[data-study-new-count]", session.progress.new);
        setText("[data-study-learning-count]", session.progress.learning);
        setText("[data-study-review-count]", session.progress.review);

        const compact = document.querySelector("[data-study-progress-compact]");
        if (compact) {
            compact.textContent = `${session.progress.completed} / ${session.progress.total}`;
        }

        const progressBar = document.querySelector("[data-study-progress-bar]");
        if (progressBar) {
            const percent =
                session.progress.total > 0
                    ? (session.progress.completed / session.progress.total) *
                      100
                    : 100;
            progressBar.style.width = `${percent}%`;
        }
    }

    if (!session?.current_card) {
        const hide = (sel) =>
            document.querySelectorAll(sel).forEach((el) => {
                el.classList.add("is-hidden");
                el.style.display = "none";
            });
        hide("[data-study-card]");
        hide(".rating-panel");
        hide(".answer-panel");
        hide(".study-actions");

        const emptyState = document.querySelector("[data-study-empty-state]");
        if (emptyState) {
            emptyState.classList.remove("is-hidden");
            emptyState.style.display = "block";
        }
        return;
    }

    const card = session.current_card;
    setText(
        "[data-study-front-text]",
        card.front_text || card.front_plain_text || "Untitled card",
    );
    setText(
        "[data-study-back-text]",
        card.back_text || card.back_plain_text || "No answer available.",
    );
    setText("[data-study-state-label]", card.state || "Card");
    setText("[data-study-state-tag]", card.state || "Card");
    setText("[data-study-mode-tag]", `Mode: ${session.mode}`);

    document.querySelectorAll("[data-study-tts-button]").forEach((button) => {
        button.disabled = false;
        button.addEventListener("click", async () => {
            const side =
                button.dataset.studyTtsButton === "back"
                    ? card.back_plain_text || card.back_text
                    : card.front_plain_text || card.front_text;
            const played = await playTts(side);
            if (!played)
                window.alert(
                    "Text-to-speech is not available in this browser.",
                );
        });
    });

    const revealButton = document.querySelector("[data-study-reveal-button]");
    if (revealButton) {
        revealButton.disabled = false;
        revealButton.addEventListener("click", () => {
            sessionStorage.setItem(
                "flashmind-study-reveal",
                JSON.stringify({ session, card, mode: session.mode }),
            );
            window.location.href =
                body.dataset.studyAnswerUrl +
                "?mode=" +
                (body.dataset.studyMode || "flip") +
                (body.dataset.studyDeckId
                    ? "&deck_id=" + body.dataset.studyDeckId
                    : "");
        });
    }

    const checkButton = document.querySelector("[data-study-check-button]");
    if (checkButton) {
        checkButton.disabled = false;
        checkButton.addEventListener("click", async () => {
            const userAnswer =
                document
                    .querySelector("[data-study-answer-input]")
                    ?.value?.trim() || "";
            if (!userAnswer) return;
            const result = await fetchJson(
                replaceToken(
                    body.dataset.studyCheckAnswerUrlTemplate,
                    "__CARD__",
                    card.id,
                ),
                {
                    method: "POST",
                    body: JSON.stringify({
                        mode: "typing",
                        user_answer: userAnswer,
                    }),
                },
            );
            sessionStorage.setItem(
                "flashmind-study-reveal",
                JSON.stringify({
                    session,
                    card,
                    mode: "typing",
                    user_answer: userAnswer,
                    judged_result: result.result,
                }),
            );
            window.location.href =
                body.dataset.studyAnswerUrl +
                "?mode=typing" +
                (body.dataset.studyDeckId
                    ? "&deck_id=" + body.dataset.studyDeckId
                    : "");
        });
    }

    if (body.dataset.studyScreen === "answer") {
        const stored = JSON.parse(
            sessionStorage.getItem("flashmind-study-reveal") || "{}",
        );
        if (stored.card) {
            setText(
                "[data-study-front-text]",
                stored.card.front_text ||
                    stored.card.front_plain_text ||
                    "Untitled card",
            );
            setText(
                "[data-study-back-text]",
                stored.card.back_text ||
                    stored.card.back_plain_text ||
                    "No answer available.",
            );
            setText("[data-study-user-answer]", stored.user_answer || "");
            document
                .querySelector("[data-study-user-answer-section]")
                ?.classList.toggle("is-hidden", !stored.user_answer);

            if (stored.session && stored.session.progress) {
                setText("[data-study-new-count]", stored.session.progress.new);
                setText(
                    "[data-study-learning-count]",
                    stored.session.progress.learning,
                );
                setText(
                    "[data-study-review-count]",
                    stored.session.progress.review,
                );
            }

            // Auto-play TTS for the back side
            const backText =
                stored.card.back_plain_text || stored.card.back_text;
            if (backText) {
                setTimeout(() => {
                    playTts(backText).catch((err) =>
                        console.warn("Auto-play TTS failed:", err),
                    );
                }, 300);
            }
        }

        document
            .querySelectorAll("[data-study-rate-button]")
            .forEach((button) =>
                button.addEventListener("click", async () => {
                    const rating = button.dataset.studyRateButton;
                    const storedPayload = JSON.parse(
                        sessionStorage.getItem("flashmind-study-reveal") ||
                            "{}",
                    );
                    if (!storedPayload.card || !rating) return;
                    await fetchJson(
                        replaceToken(
                            body.dataset.studyRateUrlTemplate,
                            "__CARD__",
                            storedPayload.card.id,
                        ),
                        {
                            method: "POST",
                            body: JSON.stringify({
                                mode: storedPayload.mode || "flip",
                                rating,
                                typed_answer: storedPayload.user_answer || null,
                                judged_result:
                                    storedPayload.judged_result || null,
                            }),
                        },
                    );
                    stopTts();
                    sessionStorage.removeItem("flashmind-study-reveal");
                    window.location.href =
                        (storedPayload.mode === "typing"
                            ? body.dataset.studyTypingUrl
                            : body.dataset.studyFrontUrl) +
                        "?mode=" +
                        (storedPayload.mode || "flip") +
                        (body.dataset.studyDeckId
                            ? "&deck_id=" + body.dataset.studyDeckId
                            : "");
                }),
            );
    }
};

document.addEventListener("DOMContentLoaded", async () => {
    // Password visibility toggles (auth + profile pages)
    document.querySelectorAll("[data-pw-toggle]").forEach((btn) => {
        btn.addEventListener("click", () => {
            const input = document.getElementById(btn.dataset.pwToggle);
            if (!input) return;
            const isHidden = input.type === "password";
            input.type = isHidden ? "text" : "password";
            const icon = btn.querySelector(".material-symbols-outlined");
            if (icon)
                icon.textContent = isHidden ? "visibility_off" : "visibility";
        });
    });

    setupCreateDeck();
    setupDashboard();
    setupDeckDetail();
    setupImport();
    await setupStudy();
});
