import { expect, test, type APIRequestContext } from '@playwright/test';
import { createCardViaApi, createDeckViaApi, deleteDeckViaApi, rateCardViaApi } from './helpers/api-helpers';
import { DashboardPage } from './pages/dashboard.page';
import { StudyPage } from './pages/study.page';

test.describe('Study Session', () => {
  test('Flip Mode: Hien thi front -> Show Answer -> Rating panel', async ({ page, request }) => {
    const dashboardPage = new DashboardPage(page);
    const studyPage = new StudyPage(page);
    await dashboardPage.goto();
    const userId = await dashboardPage.getUserId();
    const deck = await createDeckViaApi(request, userId, `PW Flip ${Date.now()}`, 'Deck for flip mode flow.');
    const card = await createCardViaApi(request, userId, deck.id, `Flip front ${Date.now()}`, `Flip back ${Date.now()}`);

    try {
      await studyPage.gotoFront(deck.id);

      await expect(studyPage.frontText).toContainText(card.front_plain_text ?? '');
      await expect(studyPage.revealButton).toBeEnabled();
      await studyPage.revealAnswer();

      await expect(studyPage.frontText).toContainText(card.front_plain_text ?? '');
      await expect(studyPage.backText).toContainText(card.back_plain_text ?? '');
      await expect(studyPage.ratingPanel).toBeVisible();
      await expect(studyPage.rateButton('again')).toBeVisible();
      await expect(studyPage.rateButton('hard')).toBeVisible();
      await expect(studyPage.rateButton('good')).toBeVisible();
      await expect(studyPage.rateButton('easy')).toBeVisible();
    } finally {
      await deleteDeckViaApi(request, userId, deck.id);
    }
  });

  test('Typing Mode: Nhap answer -> Check -> Answer Revealed', async ({ page, request }) => {
    const dashboardPage = new DashboardPage(page);
    const studyPage = new StudyPage(page);
    await dashboardPage.goto();
    const userId = await dashboardPage.getUserId();
    const deck = await createDeckViaApi(request, userId, `PW Typing ${Date.now()}`, 'Deck for typing flow.');
    const card = await createCardViaApi(request, userId, deck.id, `Typing front ${Date.now()}`, `Typing back ${Date.now()}`);

    try {
      await studyPage.gotoTyping(deck.id);

      await expect(studyPage.frontText).toContainText(card.front_plain_text ?? '');
      await expect(studyPage.checkButton).toBeEnabled();
      await expect(studyPage.ttsButton()).toBeEnabled();

      await studyPage.submitTypedAnswer(card.back_plain_text ?? '');

      await expect(studyPage.frontText).toContainText(card.front_plain_text ?? '');
      await expect(studyPage.userAnswerSection).toBeVisible();
      await expect(studyPage.userAnswer).toContainText(card.back_plain_text ?? '');
      await expect(studyPage.backText).toContainText(card.back_plain_text ?? '');
      await expect(studyPage.modeTag).toContainText('Mode');
      await expect(studyPage.ratingPanel).toBeVisible();
    } finally {
      await deleteDeckViaApi(request, userId, deck.id);
    }
  });

  test('Progress bar cap nhat sau moi card', async ({ page, request }) => {
    const dashboardPage = new DashboardPage(page);
    const studyPage = new StudyPage(page);
    await dashboardPage.goto();
    const userId = await dashboardPage.getUserId();
    const deck = await createDeckViaApi(request, userId, `PW Progress ${Date.now()}`, 'Deck for progress bar flow.');
    const card = await createCardViaApi(request, userId, deck.id, `Progress front ${Date.now()}`, `Progress back ${Date.now()}`);

    try {
      await studyPage.gotoFront(deck.id);
      await expect(await studyPage.getProgressWidth()).toBe('0%');

      await studyPage.revealAnswer();
      await rateCardViaApi(request, card.id, 'good');
      await studyPage.gotoFront(deck.id);

      await expect(studyPage.emptyState).toBeVisible();
      await expect(await studyPage.getProgressWidth()).toBe('100%');
    } finally {
      await deleteDeckViaApi(request, userId, deck.id);
    }
  });

  test('Empty state khi het cards de hoc', async ({ page, request }) => {
    const dashboardPage = new DashboardPage(page);
    const studyPage = new StudyPage(page);
    await dashboardPage.goto();
    const userId = await dashboardPage.getUserId();
    const deck = await createDeckViaApi(request, userId, `PW Empty ${Date.now()}`, 'Deck for empty study state.');

    try {
      await studyPage.gotoFront(deck.id);

      await expect(studyPage.emptyState).toBeVisible();
      await expect(page.locator('[data-study-card]')).toBeHidden();
      await expect(studyPage.emptyMessage).toContainText('No cards are ready right now');
    } finally {
      await deleteDeckViaApi(request, userId, deck.id);
    }
  });

  test('TTS button hoat dong khong bi disabled', async ({ page, request }) => {
    const dashboardPage = new DashboardPage(page);
    const studyPage = new StudyPage(page);
    await dashboardPage.goto();
    const userId = await dashboardPage.getUserId();
    const deck = await createDeckViaApi(request, userId, `PW TTS ${Date.now()}`, 'Deck for study TTS.');
    await createCardViaApi(request, userId, deck.id, `TTS front ${Date.now()}`, `TTS back ${Date.now()}`);

    try {
      await studyPage.gotoFront(deck.id);
      await expect(studyPage.ttsButton()).toBeEnabled();

      await studyPage.revealAnswer();
      await expect(studyPage.ttsButton('front')).toBeEnabled();
      await expect(studyPage.ttsButton('back')).toBeEnabled();
    } finally {
      await deleteDeckViaApi(request, userId, deck.id);
    }
  });

  test('Rating Again Hard Good Easy -> chuyen card tiep', async ({ page, request }) => {
    const dashboardPage = new DashboardPage(page);
    const studyPage = new StudyPage(page);
    await dashboardPage.goto();
    const userId = await dashboardPage.getUserId();
    const ratings: Array<'again' | 'hard' | 'good' | 'easy'> = ['again', 'hard', 'good', 'easy'];

    for (const rating of ratings) {
      const deck = await createDeckViaApi(request, userId, `PW Rating ${rating} ${Date.now()}`, 'Deck for rating transitions.');
      const firstCard = await createCardViaApi(
        request,
        userId,
        deck.id,
        `Rating front A ${rating} ${Date.now()}`,
        `Rating back A ${rating} ${Date.now()}`
      );
      const secondCard = await createCardViaApi(
        request,
        userId,
        deck.id,
        `Rating front B ${rating} ${Date.now()}`,
        `Rating back B ${rating} ${Date.now()}`
      );

      try {
        await studyPage.gotoFront(deck.id);
        await expect(studyPage.frontText).toContainText(firstCard.front_plain_text ?? '');

        await studyPage.revealAnswer();
        await expect(studyPage.rateButton(rating)).toBeVisible();
        await rateCardViaApi(request, firstCard.id, rating);
        await studyPage.gotoFront(deck.id);
        await expect(studyPage.frontText).toContainText(secondCard.front_plain_text ?? '');
      } finally {
        await deleteDeckViaApi(request, userId, deck.id);
      }
    }
  });
});
