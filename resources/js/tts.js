export const play = async (text) => {
    const value = String(text || '').trim();
    if (!value || !('speechSynthesis' in window) || typeof SpeechSynthesisUtterance === 'undefined') {
        return false;
    }

    window.speechSynthesis.cancel();
    const utterance = new SpeechSynthesisUtterance(value);
    window.speechSynthesis.speak(utterance);
    return true;
};

export const stop = () => {
    if ('speechSynthesis' in window) {
        window.speechSynthesis.cancel();
    }
};
