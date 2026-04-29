import { expect, test } from '@playwright/test';
import { createCardViaApi, createDeckViaApi, deleteDeckViaApi, rateCardViaApi } from './helpers/api-helpers';
import { DashboardPage } from './pages/dashboard.page';
import { DeckDetailPage } from './pages/deck-detail.page';

test.describe('Deck Detail', () => {
  test.beforeEach(async ({ page }) => {
    await new DashboardPage(page).goto();
  });

  test('Table hien thi day du cot Front, Back, Status, Last Reviewed, Next, Actions', async ({ page, request }) => {
    const dashboardPage = new DashboardPage(page);
    const deckDetailPage = new DeckDetailPage(page);
    const userId = await dashboardPage.getUserId();
    const deck = await createDeckViaApi(
      request,
      userId,
      `PW Deck Columns ${Date.now()}`,
      'Deck created to verify table columns.'
    );

    try {
      await createCardViaApi(request, userId, deck.id, `Front columns ${Date.now()}`, `Back columns ${Date.now()}`);
      await deckDetailPage.goto(deck.id);

      await expect(page.getByRole('columnheader', { name: 'FRONT' })).toBeVisible();
      await expect(page.getByRole('columnheader', { name: 'BACK' })).toBeVisible();
      await expect(page.getByRole('columnheader', { name: 'STATUS' })).toBeVisible();
      await expect(page.getByRole('columnheader', { name: 'LAST REVIEWED' })).toBeVisible();
      await expect(page.getByRole('columnheader', { name: 'NEXT' })).toBeVisible();
      await expect(page.getByRole('columnheader', { name: 'ACTIONS' })).toBeVisible();
      await expect(deckDetailPage.cardRows).toHaveCount(1);
    } finally {
      await deleteDeckViaApi(request, userId, deck.id);
    }
  });

  test('Search cards theo front va back text', async ({ page, request }) => {
    const dashboardPage = new DashboardPage(page);
    const deckDetailPage = new DeckDetailPage(page);
    const userId = await dashboardPage.getUserId();
    const deck = await createDeckViaApi(request, userId, `PW Deck Search ${Date.now()}`, 'Deck created to verify search.');
    const frontCard = await createCardViaApi(
      request,
      userId,
      deck.id,
      `Front keyword ${Date.now()}`,
      `Back filler ${Date.now()}`
    );
    const backCard = await createCardViaApi(
      request,
      userId,
      deck.id,
      `Front filler ${Date.now()}`,
      `Back keyword ${Date.now()}`
    );

    try {
      await deckDetailPage.goto(deck.id);

      await deckDetailPage.search(frontCard.front_plain_text ?? '');
      await expect(deckDetailPage.rowByFrontText(frontCard.front_plain_text ?? '')).toHaveCount(1);
      await expect(deckDetailPage.rowByFrontText(backCard.front_plain_text ?? '')).toHaveCount(0);

      await deckDetailPage.search(backCard.back_plain_text ?? '');
      await expect(deckDetailPage.rowByFrontText(backCard.front_plain_text ?? '')).toHaveCount(1);
      await expect(deckDetailPage.rowByFrontText(frontCard.front_plain_text ?? '')).toHaveCount(0);
    } finally {
      await deleteDeckViaApi(request, userId, deck.id);
    }
  });

  test('Filter theo Status New Learning Review', async ({ page, request }) => {
    const dashboardPage = new DashboardPage(page);
    const deckDetailPage = new DeckDetailPage(page);
    const userId = await dashboardPage.getUserId();
    const deck = await createDeckViaApi(request, userId, `PW Deck Filter ${Date.now()}`, 'Deck created to verify status filter.');
    const newCard = await createCardViaApi(request, userId, deck.id, `New row ${Date.now()}`, 'Back new');
    const learningCard = await createCardViaApi(request, userId, deck.id, `Learning row ${Date.now()}`, 'Back learning');
    const reviewCard = await createCardViaApi(request, userId, deck.id, `Review row ${Date.now()}`, 'Back review');

    try {
      await rateCardViaApi(request, learningCard.id, 'good');
      await rateCardViaApi(request, reviewCard.id, 'easy');
      await deckDetailPage.goto(deck.id);

      await deckDetailPage.filterByStatus('new');
      await expect(deckDetailPage.rowByFrontText(newCard.front_plain_text ?? '')).toHaveCount(1);
      await expect(deckDetailPage.rowByFrontText(learningCard.front_plain_text ?? '')).toHaveCount(0);
      await expect(deckDetailPage.rowByFrontText(reviewCard.front_plain_text ?? '')).toHaveCount(0);

      await deckDetailPage.filterByStatus('learning');
      await expect(deckDetailPage.rowByFrontText(newCard.front_plain_text ?? '')).toHaveCount(0);
      await expect(deckDetailPage.rowByFrontText(learningCard.front_plain_text ?? '')).toHaveCount(1);
      await expect(deckDetailPage.rowByFrontText(reviewCard.front_plain_text ?? '')).toHaveCount(0);

      await deckDetailPage.filterByStatus('review');
      await expect(deckDetailPage.rowByFrontText(newCard.front_plain_text ?? '')).toHaveCount(0);
      await expect(deckDetailPage.rowByFrontText(learningCard.front_plain_text ?? '')).toHaveCount(0);
      await expect(deckDetailPage.rowByFrontText(reviewCard.front_plain_text ?? '')).toHaveCount(1);
    } finally {
      await deleteDeckViaApi(request, userId, deck.id);
    }
  });

  test('Create Card qua modal', async ({ page, request }) => {
    const dashboardPage = new DashboardPage(page);
    const deckDetailPage = new DeckDetailPage(page);
    const userId = await dashboardPage.getUserId();
    const deck = await createDeckViaApi(
      request,
      userId,
      `PW Deck Create Modal ${Date.now()}`,
      'Deck created to verify create-card modal.'
    );
    const frontText = `Created in modal ${Date.now()}`;
    const backText = `Back created in modal ${Date.now()}`;

    try {
      await deckDetailPage.goto(deck.id);
      await deckDetailPage.openCreateCardModal();
      await deckDetailPage.fillCardModal(frontText, backText);

      const createResponsePromise = page.waitForResponse(
        (response) => response.url().includes('/api/cards') && response.request().method() === 'POST'
      );
      await deckDetailPage.submitCardModal();
      const createResponse = await createResponsePromise;

      expect(createResponse.ok(), 'Creating a card from the modal should succeed.').toBeTruthy();
      await expect(deckDetailPage.rowByFrontText(frontText)).toHaveCount(1);
      await expect(deckDetailPage.rowByFrontText(frontText)).toContainText(backText);
    } finally {
      await deleteDeckViaApi(request, userId, deck.id);
    }
  });

  test('Edit Card modal hien thi data cu', async ({ page, request }) => {
    const dashboardPage = new DashboardPage(page);
    const deckDetailPage = new DeckDetailPage(page);
    const userId = await dashboardPage.getUserId();
    const deck = await createDeckViaApi(request, userId, `PW Deck Edit ${Date.now()}`, 'Deck created to verify edit-card modal.');
    const card = await createCardViaApi(request, userId, deck.id, `Edit front ${Date.now()}`, `Edit back ${Date.now()}`);

    try {
      await deckDetailPage.goto(deck.id);
      await deckDetailPage.openEditCardModal(card.front_plain_text ?? '');

      await expect(page.locator('[data-card-modal-title]')).toHaveText('Edit Card');
      await expect(page.locator('[data-card-front-input]')).toHaveValue(card.front_text ?? '');
      await expect(page.locator('[data-card-back-input]')).toHaveValue(card.back_text ?? '');
    } finally {
      await deleteDeckViaApi(request, userId, deck.id);
    }
  });

  test('Delete single card voi confirm', async ({ page, request }) => {
    const dashboardPage = new DashboardPage(page);
    const deckDetailPage = new DeckDetailPage(page);
    const userId = await dashboardPage.getUserId();
    const deck = await createDeckViaApi(request, userId, `PW Deck Delete ${Date.now()}`, 'Deck created to verify single delete.');
    const card = await createCardViaApi(request, userId, deck.id, `Delete row ${Date.now()}`, 'Delete back');

    try {
      await deckDetailPage.goto(deck.id);
      await deckDetailPage.openDeleteCardModal(card.front_plain_text ?? '');

      await expect(page.locator('[data-delete-modal-message]')).toContainText('delete this card');

      const deleteResponsePromise = page.waitForResponse(
        (response) => response.url().includes('/api/cards/bulk') && response.request().method() === 'DELETE'
      );
      await page.locator('[data-delete-card-submit-button]').click();
      const deleteResponse = await deleteResponsePromise;

      expect(deleteResponse.ok(), 'Deleting a single card should succeed.').toBeTruthy();
      await expect(deckDetailPage.rowByFrontText(card.front_plain_text ?? '')).toHaveCount(0);
    } finally {
      await deleteDeckViaApi(request, userId, deck.id);
    }
  });

  test('Bulk select va bulk delete', async ({ page, request }) => {
    const dashboardPage = new DashboardPage(page);
    const deckDetailPage = new DeckDetailPage(page);
    const userId = await dashboardPage.getUserId();
    const deck = await createDeckViaApi(request, userId, `PW Deck Bulk ${Date.now()}`, 'Deck created to verify bulk delete.');
    const cardA = await createCardViaApi(request, userId, deck.id, `Bulk A ${Date.now()}`, 'Bulk back A');
    const cardB = await createCardViaApi(request, userId, deck.id, `Bulk B ${Date.now()}`, 'Bulk back B');
    const cardC = await createCardViaApi(request, userId, deck.id, `Bulk C ${Date.now()}`, 'Bulk back C');

    try {
      await deckDetailPage.goto(deck.id);

      await deckDetailPage.selectCard(cardA.front_plain_text ?? '');
      await deckDetailPage.selectCard(cardB.front_plain_text ?? '');
      await expect(deckDetailPage.bulkDeleteButton).toBeVisible();

      await deckDetailPage.bulkDeleteButton.click();
      await expect(deckDetailPage.deleteCardModal).toBeVisible();
      await expect(page.locator('[data-delete-modal-message]')).toContainText('delete 2 selected cards');

      const deleteResponsePromise = page.waitForResponse(
        (response) => response.url().includes('/api/cards/bulk') && response.request().method() === 'DELETE'
      );
      await page.locator('[data-delete-card-submit-button]').click();
      const deleteResponse = await deleteResponsePromise;

      expect(deleteResponse.ok(), 'Bulk deleting cards should succeed.').toBeTruthy();
      await expect(deckDetailPage.rowByFrontText(cardA.front_plain_text ?? '')).toHaveCount(0);
      await expect(deckDetailPage.rowByFrontText(cardB.front_plain_text ?? '')).toHaveCount(0);
      await expect(deckDetailPage.rowByFrontText(cardC.front_plain_text ?? '')).toHaveCount(1);
    } finally {
      await deleteDeckViaApi(request, userId, deck.id);
    }
  });

  test('Select All checkbox behavior', async ({ page, request }) => {
    const dashboardPage = new DashboardPage(page);
    const deckDetailPage = new DeckDetailPage(page);
    const userId = await dashboardPage.getUserId();
    const deck = await createDeckViaApi(
      request,
      userId,
      `PW Deck Select All ${Date.now()}`,
      'Deck created to verify select-all behavior.'
    );
    const cardA = await createCardViaApi(request, userId, deck.id, `Select all A ${Date.now()}`, 'Back A');
    const cardB = await createCardViaApi(request, userId, deck.id, `Select all B ${Date.now()}`, 'Back B');
    const cardC = await createCardViaApi(request, userId, deck.id, `Select all C ${Date.now()}`, 'Back C');

    try {
      await deckDetailPage.goto(deck.id);

      const rowCheckboxes = page.locator('[data-row-checkbox]');

      await deckDetailPage.selectAllCheckbox.check();
      await expect(rowCheckboxes).toHaveCount(3);
      await expect(rowCheckboxes.nth(0)).toBeChecked();
      await expect(rowCheckboxes.nth(1)).toBeChecked();
      await expect(rowCheckboxes.nth(2)).toBeChecked();
      await expect(deckDetailPage.bulkDeleteButton).toBeVisible();

      await deckDetailPage.unselectCard(cardB.front_plain_text ?? '');
      await expect(deckDetailPage.selectAllCheckbox).toBeChecked();
      expect(await deckDetailPage.selectAllCheckbox.evaluate((el) => (el as HTMLInputElement).indeterminate)).toBe(true);

      await deckDetailPage.selectCard(cardB.front_plain_text ?? '');
      expect(await deckDetailPage.selectAllCheckbox.evaluate((el) => (el as HTMLInputElement).indeterminate)).toBe(false);

      await deckDetailPage.selectAllCheckbox.uncheck();
      await expect(rowCheckboxes.nth(0)).not.toBeChecked();
      await expect(rowCheckboxes.nth(1)).not.toBeChecked();
      await expect(rowCheckboxes.nth(2)).not.toBeChecked();
      await expect(deckDetailPage.bulkDeleteButton).toBeHidden();

      expect(cardA.id).toBeGreaterThan(0);
      expect(cardC.id).toBeGreaterThan(0);
    } finally {
      await deleteDeckViaApi(request, userId, deck.id);
    }
  });

  test('Deck Switcher navigation', async ({ page, request }) => {
    const dashboardPage = new DashboardPage(page);
    const deckDetailPage = new DeckDetailPage(page);
    const userId = await dashboardPage.getUserId();
    const firstDeck = await createDeckViaApi(request, userId, `PW Deck Switch A ${Date.now()}`, 'First deck for switcher.');
    const secondDeck = await createDeckViaApi(request, userId, `PW Deck Switch B ${Date.now()}`, 'Second deck for switcher.');

    try {
      await deckDetailPage.goto(firstDeck.id);

      await deckDetailPage.deckSwitcher.selectOption(String(secondDeck.id));
      await expect(page).toHaveURL(new RegExp(`/decks/${secondDeck.id}$`));
      await expect(page.locator('.card-manager-deck-badge')).toHaveText(secondDeck.name);
    } finally {
      await deleteDeckViaApi(request, userId, firstDeck.id);
      await deleteDeckViaApi(request, userId, secondDeck.id);
    }
  });
});
