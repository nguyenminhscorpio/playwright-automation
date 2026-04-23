const STUDY_MODE_KEY = 'flashmind-study-mode';

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

document.addEventListener('DOMContentLoaded', () => {
    const body = document.body;
    const switchElement = document.querySelector('[data-study-mode-switch]');

    if (!switchElement) {
        return;
    }

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
});
