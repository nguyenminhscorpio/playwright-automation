import { expect, test } from '@playwright/test';

type DeckPayload = {
  id: number;
  name: string;
  description: string | null;
};

type CardPayload = {
  id: number;
  deck_id: number;
  front_text: string | null;
  back_text: string | null;
  front_plain_text: string | null;
  back_plain_text: string | null;
  state: string;
};

const getDashboardUserId = async (page: import('@playwright/test').Page) => {
  const userId = await page.locator('[data-dashboard-app]').getAttribute('data-dashboard-user-id');
  expect(userId, 'Dashboard should expose a user id for API-backed setup.').toBeTruthy();
  return Number(userId);
};

const createDeckViaApi = async (
  request: import('@playwright/test').APIRequestContext,
  userId: number,
  name: string,
  description: string
) => {
  const response = await request.post('/api/decks', {
    data: {
      user_id: userId,
      name,
      description,
    },
  });

  expect(response.ok(), 'Deck creation via API should succeed.').toBeTruthy();
  return (await response.json()) as DeckPayload;
};

const deleteDeckViaApi = async (
  request: import('@playwright/test').APIRequestContext,
  userId: number,
  deckId: number
) => {
  const response = await request.delete(`/api/decks/${deckId}`, {
    data: {
      user_id: userId,
    },
  });

  expect.soft(response.ok(), `Cleanup should remove deck ${deckId}.`).toBeTruthy();
};

const createCardViaApi = async (
  request: import('@playwright/test').APIRequestContext,
  userId: number,
  deckId: number,
  frontText: string,
  backText: string
) => {
  const response = await request.post('/api/cards', {
    data: {
      user_id: userId,
      deck_id: deckId,
      front_text: frontText,
      back_text: backText,
    },
  });

  expect(response.ok(), 'Card creation via API should succeed.').toBeTruthy();
  return (await response.json()) as CardPayload;
};

const rateCardViaApi = async (
  request: import('@playwright/test').APIRequestContext,
  cardId: number,
  rating: 'again' | 'hard' | 'good' | 'easy'
) => {
  const response = await request.post(`/api/study/cards/${cardId}/rate`, {
    data: {
      mode: 'flip',
      rating,
    },
  });

  expect(response.ok(), `Rating card ${cardId} with ${rating} should succeed.`).toBeTruthy();
};

const openDeckDetail = async (page: import('@playwright/test').Page, deckId: number) => {
  await page.goto(`/decks/${deckId}`);
  await expect(page).toHaveURL(new RegExp(`/decks/${deckId}$`));
  await expect(page.locator('[data-deck-detail-app]')).toBeVisible();
};

const findCardRow = (page: import('@playwright/test').Page, frontText: string) =>
  page.locator('[data-card-row]').filter({ hasText: frontText });

test.describe('Deck Detail', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/dashboard');
    await expect(page).toHaveURL(/\/dashboard$/);
  });

  test('Table hien thi day du cot Front, Back, Status, Last Reviewed, Next, Actions', async ({ page, request }) => {
    const userId = await getDashboardUserId(page);
    const deck = await createDeckViaApi(request, userId, `PW Deck Columns ${Date.now()}`, 'Deck created to verify table columns.');

    try {
      await createCardViaApi(request, userId, deck.id, `Front columns ${Date.now()}`, `Back columns ${Date.now()}`);
      await openDeckDetail(page, deck.id);

      await expect(page.getByRole('columnheader', { name: 'FRONT' })).toBeVisible();
      await expect(page.getByRole('columnheader', { name: 'BACK' })).toBeVisible();
      await expect(page.getByRole('columnheader', { name: 'STATUS' })).toBeVisible();
      await expect(page.getByRole('columnheader', { name: 'LAST REVIEWED' })).toBeVisible();
      await expect(page.getByRole('columnheader', { name: 'NEXT' })).toBeVisible();
      await expect(page.getByRole('columnheader', { name: 'ACTIONS' })).toBeVisible();
      await expect(page.locator('[data-card-row]')).toHaveCount(1);
    } finally {
      await deleteDeckViaApi(request, userId, deck.id);
    }
  });

  test('Search cards theo front va back text', async ({ page, request }) => {
    const userId = await getDashboardUserId(page);
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
      await openDeckDetail(page, deck.id);

      await page.getByPlaceholder('Search cards by front, back, or description...').fill(frontCard.front_plain_text ?? '');
      await page.getByRole('button', { name: 'Apply' }).click();
      await expect(findCardRow(page, frontCard.front_plain_text ?? '')).toHaveCount(1);
      await expect(findCardRow(page, backCard.front_plain_text ?? '')).toHaveCount(0);

      await page.getByPlaceholder('Search cards by front, back, or description...').fill(backCard.back_plain_text ?? '');
      await page.getByRole('button', { name: 'Apply' }).click();
      await expect(findCardRow(page, backCard.front_plain_text ?? '')).toHaveCount(1);
      await expect(findCardRow(page, frontCard.front_plain_text ?? '')).toHaveCount(0);
    } finally {
      await deleteDeckViaApi(request, userId, deck.id);
    }
  });

  test('Filter theo Status New Learning Review', async ({ page, request }) => {
    const userId = await getDashboardUserId(page);
    const deck = await createDeckViaApi(request, userId, `PW Deck Filter ${Date.now()}`, 'Deck created to verify status filter.');
    const newCard = await createCardViaApi(request, userId, deck.id, `New row ${Date.now()}`, 'Back new');
    const learningCard = await createCardViaApi(request, userId, deck.id, `Learning row ${Date.now()}`, 'Back learning');
    const reviewCard = await createCardViaApi(request, userId, deck.id, `Review row ${Date.now()}`, 'Back review');

    try {
      await rateCardViaApi(request, learningCard.id, 'good');
      await rateCardViaApi(request, reviewCard.id, 'easy');
      await openDeckDetail(page, deck.id);

      await page.locator('select[name="status"]').selectOption('new');
      await page.getByRole('button', { name: 'Apply' }).click();
      await expect(findCardRow(page, newCard.front_plain_text ?? '')).toHaveCount(1);
      await expect(findCardRow(page, learningCard.front_plain_text ?? '')).toHaveCount(0);
      await expect(findCardRow(page, reviewCard.front_plain_text ?? '')).toHaveCount(0);

      await page.locator('select[name="status"]').selectOption('learning');
      await page.getByRole('button', { name: 'Apply' }).click();
      await expect(findCardRow(page, newCard.front_plain_text ?? '')).toHaveCount(0);
      await expect(findCardRow(page, learningCard.front_plain_text ?? '')).toHaveCount(1);
      await expect(findCardRow(page, reviewCard.front_plain_text ?? '')).toHaveCount(0);

      await page.locator('select[name="status"]').selectOption('review');
      await page.getByRole('button', { name: 'Apply' }).click();
      await expect(findCardRow(page, newCard.front_plain_text ?? '')).toHaveCount(0);
      await expect(findCardRow(page, learningCard.front_plain_text ?? '')).toHaveCount(0);
      await expect(findCardRow(page, reviewCard.front_plain_text ?? '')).toHaveCount(1);
    } finally {
      await deleteDeckViaApi(request, userId, deck.id);
    }
  });

  test('Create Card qua modal', async ({ page, request }) => {
    const userId = await getDashboardUserId(page);
    const deck = await createDeckViaApi(request, userId, `PW Deck Create Modal ${Date.now()}`, 'Deck created to verify create-card modal.');
    const frontText = `Created in modal ${Date.now()}`;
    const backText = `Back created in modal ${Date.now()}`;

    try {
      await openDeckDetail(page, deck.id);

      await page.getByRole('button', { name: 'Create Card' }).click();
      await expect(page.locator('#card-modal')).toBeVisible();
      await page.locator('[data-card-front-input]').fill(frontText);
      await page.locator('[data-card-back-input]').fill(backText);

      const createResponsePromise = page.waitForResponse(
        (response) => response.url().includes('/api/cards') && response.request().method() === 'POST'
      );
      await page.locator('[data-card-submit-button]').click();
      const createResponse = await createResponsePromise;

      expect(createResponse.ok(), 'Creating a card from the modal should succeed.').toBeTruthy();
      await expect(findCardRow(page, frontText)).toHaveCount(1);
      await expect(findCardRow(page, frontText)).toContainText(backText);
    } finally {
      await deleteDeckViaApi(request, userId, deck.id);
    }
  });

  test('Edit Card modal hien thi data cu', async ({ page, request }) => {
    const userId = await getDashboardUserId(page);
    const deck = await createDeckViaApi(request, userId, `PW Deck Edit ${Date.now()}`, 'Deck created to verify edit-card modal.');
    const card = await createCardViaApi(request, userId, deck.id, `Edit front ${Date.now()}`, `Edit back ${Date.now()}`);

    try {
      await openDeckDetail(page, deck.id);

      await findCardRow(page, card.front_plain_text ?? '').getByRole('button', { name: 'Edit card' }).click();
      await expect(page.locator('#card-modal')).toBeVisible();
      await expect(page.locator('[data-card-modal-title]')).toHaveText('Edit Card');
      await expect(page.locator('[data-card-front-input]')).toHaveValue(card.front_text ?? '');
      await expect(page.locator('[data-card-back-input]')).toHaveValue(card.back_text ?? '');
    } finally {
      await deleteDeckViaApi(request, userId, deck.id);
    }
  });

  test('Delete single card voi confirm', async ({ page, request }) => {
    const userId = await getDashboardUserId(page);
    const deck = await createDeckViaApi(request, userId, `PW Deck Delete ${Date.now()}`, 'Deck created to verify single delete.');
    const card = await createCardViaApi(request, userId, deck.id, `Delete row ${Date.now()}`, 'Delete back');

    try {
      await openDeckDetail(page, deck.id);

      await findCardRow(page, card.front_plain_text ?? '').getByRole('button', { name: 'Delete card' }).click();
      await expect(page.locator('#delete-card-modal')).toBeVisible();
      await expect(page.locator('[data-delete-modal-message]')).toContainText('delete this card');

      const deleteResponsePromise = page.waitForResponse(
        (response) => response.url().includes('/api/cards/bulk') && response.request().method() === 'DELETE'
      );
      await page.locator('[data-delete-card-submit-button]').click();
      const deleteResponse = await deleteResponsePromise;

      expect(deleteResponse.ok(), 'Deleting a single card should succeed.').toBeTruthy();
      await expect(findCardRow(page, card.front_plain_text ?? '')).toHaveCount(0);
    } finally {
      await deleteDeckViaApi(request, userId, deck.id);
    }
  });

  test('Bulk select va bulk delete', async ({ page, request }) => {
    const userId = await getDashboardUserId(page);
    const deck = await createDeckViaApi(request, userId, `PW Deck Bulk ${Date.now()}`, 'Deck created to verify bulk delete.');
    const cardA = await createCardViaApi(request, userId, deck.id, `Bulk A ${Date.now()}`, 'Bulk back A');
    const cardB = await createCardViaApi(request, userId, deck.id, `Bulk B ${Date.now()}`, 'Bulk back B');
    const cardC = await createCardViaApi(request, userId, deck.id, `Bulk C ${Date.now()}`, 'Bulk back C');

    try {
      await openDeckDetail(page, deck.id);

      await findCardRow(page, cardA.front_plain_text ?? '').locator('[data-row-checkbox]').check();
      await findCardRow(page, cardB.front_plain_text ?? '').locator('[data-row-checkbox]').check();
      await expect(page.locator('[data-action-bulk-delete]')).toBeVisible();

      await page.locator('[data-action-bulk-delete]').click();
      await expect(page.locator('#delete-card-modal')).toBeVisible();
      await expect(page.locator('[data-delete-modal-message]')).toContainText('delete 2 selected cards');

      const deleteResponsePromise = page.waitForResponse(
        (response) => response.url().includes('/api/cards/bulk') && response.request().method() === 'DELETE'
      );
      await page.locator('[data-delete-card-submit-button]').click();
      const deleteResponse = await deleteResponsePromise;

      expect(deleteResponse.ok(), 'Bulk deleting cards should succeed.').toBeTruthy();
      await expect(findCardRow(page, cardA.front_plain_text ?? '')).toHaveCount(0);
      await expect(findCardRow(page, cardB.front_plain_text ?? '')).toHaveCount(0);
      await expect(findCardRow(page, cardC.front_plain_text ?? '')).toHaveCount(1);
    } finally {
      await deleteDeckViaApi(request, userId, deck.id);
    }
  });

  test('Select All checkbox behavior', async ({ page, request }) => {
    const userId = await getDashboardUserId(page);
    const deck = await createDeckViaApi(request, userId, `PW Deck Select All ${Date.now()}`, 'Deck created to verify select-all behavior.');
    const cardA = await createCardViaApi(request, userId, deck.id, `Select all A ${Date.now()}`, 'Back A');
    const cardB = await createCardViaApi(request, userId, deck.id, `Select all B ${Date.now()}`, 'Back B');
    const cardC = await createCardViaApi(request, userId, deck.id, `Select all C ${Date.now()}`, 'Back C');

    try {
      await openDeckDetail(page, deck.id);

      const selectAll = page.locator('[data-select-all-checkbox]');
      const bulkDeleteButton = page.locator('[data-action-bulk-delete]');
      const rowCheckboxes = page.locator('[data-row-checkbox]');

      await selectAll.check();
      await expect(rowCheckboxes).toHaveCount(3);
      await expect(rowCheckboxes.nth(0)).toBeChecked();
      await expect(rowCheckboxes.nth(1)).toBeChecked();
      await expect(rowCheckboxes.nth(2)).toBeChecked();
      await expect(bulkDeleteButton).toBeVisible();

      await findCardRow(page, cardB.front_plain_text ?? '').locator('[data-row-checkbox]').uncheck();
      await expect(selectAll).toBeChecked();
      expect(await selectAll.evaluate((el) => (el as HTMLInputElement).indeterminate)).toBe(true);

      await findCardRow(page, cardB.front_plain_text ?? '').locator('[data-row-checkbox]').check();
      expect(await selectAll.evaluate((el) => (el as HTMLInputElement).indeterminate)).toBe(false);

      await selectAll.uncheck();
      await expect(rowCheckboxes.nth(0)).not.toBeChecked();
      await expect(rowCheckboxes.nth(1)).not.toBeChecked();
      await expect(rowCheckboxes.nth(2)).not.toBeChecked();
      await expect(bulkDeleteButton).toBeHidden();

      expect(cardA.id).toBeGreaterThan(0);
      expect(cardC.id).toBeGreaterThan(0);
    } finally {
      await deleteDeckViaApi(request, userId, deck.id);
    }
  });

  test('Deck Switcher navigation', async ({ page, request }) => {
    const userId = await getDashboardUserId(page);
    const firstDeck = await createDeckViaApi(request, userId, `PW Deck Switch A ${Date.now()}`, 'First deck for switcher.');
    const secondDeck = await createDeckViaApi(request, userId, `PW Deck Switch B ${Date.now()}`, 'Second deck for switcher.');

    try {
      await openDeckDetail(page, firstDeck.id);

      await page.locator('[data-deck-switcher]').selectOption(String(secondDeck.id));
      await expect(page).toHaveURL(new RegExp(`/decks/${secondDeck.id}$`));
      await expect(page.locator('.card-manager-deck-badge')).toHaveText(secondDeck.name);
    } finally {
      await deleteDeckViaApi(request, userId, firstDeck.id);
      await deleteDeckViaApi(request, userId, secondDeck.id);
    }
  });
});
