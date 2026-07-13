import { expect, type Locator, type Page } from '@playwright/test';
import { gotoAuthenticated } from '../helpers/auth-helper';

export class StudyPage {
  readonly frontText: Locator;
  readonly backText: Locator;
  readonly revealButton: Locator;
  readonly checkButton: Locator;
  readonly answerInput: Locator;
  readonly ratingPanel: Locator;
  readonly emptyState: Locator;
  readonly emptyMessage: Locator;
  readonly progressBar: Locator;
  readonly userAnswerSection: Locator;
  readonly userAnswer: Locator;
  readonly modeTag: Locator;

  constructor(private readonly page: Page) {
    this.frontText = page.locator('[data-study-front-text]');
    this.backText = page.locator('[data-study-back-text]');
    this.revealButton = page.locator('[data-study-reveal-button]');
    this.checkButton = page.locator('[data-study-check-button]');
    this.answerInput = page.locator('[data-study-answer-input]');
    this.ratingPanel = page.locator('.rating-panel');
    this.emptyState = page.locator('[data-study-empty-state]');
    this.emptyMessage = page.locator('[data-study-empty-message]');
    this.progressBar = page.locator('[data-study-progress-bar]');
    this.userAnswerSection = page.locator('[data-study-user-answer-section]');
    this.userAnswer = page.locator('[data-study-user-answer]');
    this.modeTag = page.locator('[data-study-mode-tag]');
  }

  async gotoFront(deckId: number) {
    await gotoAuthenticated(this.page, `/study/front?deck_id=${deckId}`);
    await expect(this.page).toHaveURL(new RegExp(`/study/front\\?deck_id=${deckId}`));
  }

  async gotoTyping(deckId: number) {
    await gotoAuthenticated(this.page, `/study/typing?deck_id=${deckId}`);
    await expect(this.page).toHaveURL(new RegExp(`/study/typing\\?deck_id=${deckId}`));
  }

  async revealAnswer() {
    await this.revealButton.click();
    await expect(this.page).toHaveURL(/\/study\/answer/);
  }

  async submitTypedAnswer(answer: string) {
    await this.answerInput.fill(answer);
    await this.checkButton.click();
    await expect(this.page).toHaveURL(/\/study\/answer/);
  }

  async getProgressWidth() {
    return this.progressBar.evaluate((el) => (el as HTMLElement).style.width);
  }

  rateButton(rating: 'again' | 'hard' | 'good' | 'easy') {
    return this.page.locator(`[data-study-rate-button="${rating}"]`);
  }

  ttsButton(side?: 'front' | 'back') {
    return side
      ? this.page.locator(`[data-study-tts-button="${side}"]`)
      : this.page.locator('[data-study-tts-button]');
  }
}
