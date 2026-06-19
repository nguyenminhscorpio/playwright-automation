@extends('layouts.app')

@section('content')
    <section class="page-section">
        <div class="hero">
            <div>
                <h1 class="hero__title">Import Cards</h1>
                <p class="hero__subtitle">Upload an Anki-style TXT file, review parsed rows, then confirm to add them to your deck.</p>
            </div>
        </div>

        {{-- Workflow stepper --}}
        <div class="import-stepper">
            <div class="import-stepper__step is-active" data-import-step="1">
                <div class="import-stepper__bubble">
                    <span class="material-symbols-outlined">upload_file</span>
                </div>
                <span class="import-stepper__label">Upload</span>
            </div>
            <div class="import-stepper__line"></div>
            <div class="import-stepper__step" data-import-step="2">
                <div class="import-stepper__bubble">
                    <span class="material-symbols-outlined">manage_search</span>
                </div>
                <span class="import-stepper__label">Preview</span>
            </div>
            <div class="import-stepper__line"></div>
            <div class="import-stepper__step" data-import-step="3">
                <div class="import-stepper__bubble">
                    <span class="material-symbols-outlined">task_alt</span>
                </div>
                <span class="import-stepper__label">Confirm</span>
            </div>
        </div>

        <div class="import-shell" data-import-app data-import-user-id="{{ $importUserId ?? '' }}" data-import-selected-deck-id="{{ $importSelectedDeckId ?? '' }}">

            {{-- Card 1: Configure --}}
            <section class="import-card">
                <div class="import-card__head">
                    <div class="import-card__head-icon">
                        <span class="material-symbols-outlined">tune</span>
                    </div>
                    <div>
                        <h2 class="import-card__title">Configure Import</h2>
                        <p class="import-card__desc">Choose a target deck and upload your TXT file to get started.</p>
                    </div>
                </div>

                <div class="import-form">
                    {{-- Deck select --}}
                    <label class="import-field">
                        <span class="import-field__label">Target deck</span>
                        <div class="import-select-wrap">
                            <span class="import-select-wrap__icon material-symbols-outlined">layers</span>
                            <select class="import-select" data-import-deck-select>
                                @forelse ($importDecks as $deck)
                                    <option value="{{ $deck->id }}" @selected(($importSelectedDeckId ?? null) === $deck->id)>{{ $deck->name }}</option>
                                @empty
                                    <option value="" disabled selected>No deck available</option>
                                @endforelse
                                <option value="NEW_DECK" style="font-weight: bold; color: var(--primary);">+ Create New Deck...</option>
                            </select>
                            <span class="import-select-wrap__chevron material-symbols-outlined">expand_more</span>
                        </div>
                    </label>

                    {{-- File drop zone --}}
                    <label class="import-field import-field--file">
                        <span class="import-field__label">TXT file</span>
                        <div class="import-dropzone" data-import-dropzone>
                            <div class="import-dropzone__idle" data-import-dropzone-idle>
                                <div class="import-dropzone__icon-wrap">
                                    <span class="material-symbols-outlined">cloud_upload</span>
                                </div>
                                <p class="import-dropzone__primary">Drop file here or <span class="import-dropzone__link">browse</span></p>
                                <p class="import-dropzone__hint">Accepts .txt — Anki tab-separated format</p>
                            </div>
                            <div class="import-dropzone__ready is-hidden" data-import-dropzone-ready>
                                <span class="material-symbols-outlined import-dropzone__ready-icon">description</span>
                                <span class="import-dropzone__ready-name" data-import-filename>—</span>
                                <button type="button" class="import-dropzone__clear" data-import-dropzone-clear>
                                    <span class="material-symbols-outlined">close</span>
                                </button>
                            </div>
                        </div>
                        <input class="import-file-input" type="file" accept=".txt,text/plain" data-import-file-input style="display:none" />
                    </label>
                </div>

                <div class="import-actions">
                    <button class="primary-button" type="button" data-import-preview-button>
                        <span class="material-symbols-outlined">preview</span>
                        <span>Preview Import</span>
                    </button>
                    <button class="primary-button primary-button--success" type="button" data-import-confirm-button disabled>
                        <span class="material-symbols-outlined">task_alt</span>
                        <span>Confirm Import</span>
                    </button>
                </div>

                <div class="import-feedback is-hidden" data-import-feedback></div>
            </section>

            {{-- Card 2: Preview Summary --}}
            <section class="import-card">
                <div class="section-header">
                    <div>
                        <h2 class="section-title">Preview Summary</h2>
                        <p class="section-subtitle" data-import-file-meta>No file previewed yet.</p>
                    </div>
                </div>
                <div class="import-summary-grid">
                    <div class="import-summary-box">
                        <div class="import-summary-box__icon">
                            <span class="material-symbols-outlined">dataset</span>
                        </div>
                        <div class="import-summary-box__body">
                            <span class="import-summary-box__label">Total rows</span>
                            <strong class="import-summary-box__val" data-import-summary-total>0</strong>
                        </div>
                    </div>
                    <div class="import-summary-box import-summary-box--valid">
                        <div class="import-summary-box__icon">
                            <span class="material-symbols-outlined">check_circle</span>
                        </div>
                        <div class="import-summary-box__body">
                            <span class="import-summary-box__label">Valid</span>
                            <strong class="import-summary-box__val" data-import-summary-valid>0</strong>
                        </div>
                    </div>
                    <div class="import-summary-box import-summary-box--warning">
                        <div class="import-summary-box__icon">
                            <span class="material-symbols-outlined">warning</span>
                        </div>
                        <div class="import-summary-box__body">
                            <span class="import-summary-box__label">Warnings</span>
                            <strong class="import-summary-box__val" data-import-summary-warning>0</strong>
                        </div>
                    </div>
                    <div class="import-summary-box import-summary-box--invalid">
                        <div class="import-summary-box__icon">
                            <span class="material-symbols-outlined">cancel</span>
                        </div>
                        <div class="import-summary-box__body">
                            <span class="import-summary-box__label">Invalid</span>
                            <strong class="import-summary-box__val" data-import-summary-invalid>0</strong>
                        </div>
                    </div>
                </div>
            </section>

            {{-- Card 3: Row Preview --}}
            <section class="import-card">
                <div class="section-header">
                    <div>
                        <h2 class="section-title">Row Preview</h2>
                        <p class="section-subtitle">Rows grouped by status — invalid rows are skipped automatically on confirm.</p>
                    </div>
                </div>

                <div class="import-tabs">
                    <button class="import-tab is-mode-active" type="button" data-import-filter="all">All</button>
                    <button class="import-tab" type="button" data-import-filter="valid">
                        <span class="import-tab__dot import-tab__dot--valid"></span>Valid
                    </button>
                    <button class="import-tab" type="button" data-import-filter="warning">
                        <span class="import-tab__dot import-tab__dot--warning"></span>Warnings
                    </button>
                    <button class="import-tab" type="button" data-import-filter="invalid">
                        <span class="import-tab__dot import-tab__dot--invalid"></span>Errors
                    </button>
                </div>

                <div class="import-table-wrap">
                    <table class="import-table">
                        <thead>
                            <tr>
                                <th class="import-table__col-num">#</th>
                                <th>Front</th>
                                <th>Back</th>
                                <th class="import-table__col-status">Status</th>
                                <th>Issues</th>
                            </tr>
                        </thead>
                        <tbody data-import-rows-body>
                            <tr>
                                <td colspan="5" class="import-table__empty">
                                    <span class="material-symbols-outlined">upload_file</span>
                                    <p>Run preview to see parsed rows.</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>

        </div>
    </section>
@endsection
