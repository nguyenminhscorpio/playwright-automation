import { expect, test, type APIRequestContext, type Page } from '@playwright/test';

type DeckPayload = {
  id: number;
  name: string;
  description: string | null;
};

const getDashboardUserId = async (page: Page) => {
  const userId = await page.locator('[data-dashboard-app]').getAttribute('data-dashboard-user-id');
  expect(userId, 'Dashboard should expose a user id for API-backed setup.').toBeTruthy();
  return Number(userId);
};

const createDeckViaApi = async (request: APIRequestContext, userId: number, name: string, description: string) => {
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

const deleteDeckViaApi = async (request: APIRequestContext, userId: number, deckId: number) => {
  const response = await request.delete(`/api/decks/${deckId}`, {
    data: {
      user_id: userId,
    },
  });

  expect.soft(response.ok(), `Cleanup should remove deck ${deckId}.`).toBeTruthy();
};

const findDeckCard = (page: Page, deckName: string) =>
  page.locator('[data-deck-card]').filter({ has: page.locator('.deck-card__title', { hasText: deckName }) });

test.describe('Dashboard', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/dashboard');
    await expect(page).toHaveURL(/\/dashboard$/);
  });

  test('trang Dashboard load thanh cong, hien thi loi chao', async ({ page }) => {
    await expect(page.getByRole('heading', { level: 1, name: /Welcome back,/ })).toBeVisible();
    await expect(page.getByText('Track your streak, monthly milestone, and the decks that need attention most today.')).toBeVisible();
  });

  test('Quick Stats hien thi Streak va Milestone', async ({ page }) => {
    await expect(page.getByText('Daily Streak')).toBeVisible();
    await expect(page.getByText('Learning Milestone')).toBeVisible();
    await expect(page.getByText('Monthly progress')).toBeVisible();
    await expect(page.locator('.stat-card__value').first()).toBeVisible();
  });

  test('Active Decks grid hien thi danh sach deck', async ({ page, request }) => {
    const userId = await getDashboardUserId(page);
    const deckName = `PW Grid ${Date.now()}`;
    const deck = await createDeckViaApi(request, userId, deckName, 'Deck created to verify dashboard grid.');

    try {
      await page.reload();
      await expect(page.getByRole('heading', { level: 2, name: 'Active Decks' })).toBeVisible();
      await expect(findDeckCard(page, deck.name)).toBeVisible();
      expect(await page.locator('[data-deck-card]').count()).toBeGreaterThan(0);
    } finally {
      await deleteDeckViaApi(request, userId, deck.id);
    }
  });

  test('tao Deck moi qua modal voi name va description', async ({ page, request }) => {
    const userId = await getDashboardUserId(page);
    const deckName = `PW Modal ${Date.now()}`;
    const deckDescription = 'Deck created from Playwright modal flow.';
    let createdDeckId: number | null = null;

    try {
      await page.locator('[data-create-deck-button]').first().click();
      await expect(page.locator('#create-deck-modal')).toBeVisible();

      await page.locator('#new-deck-name').fill(deckName);
      await page.locator('#new-deck-description').fill(deckDescription);

      const createResponsePromise = page.waitForResponse((response) => response.url().includes('/api/decks') && response.request().method() === 'POST');
      await page.locator('#create-deck-submit-btn').click();

      const createResponse = await createResponsePromise;
      expect(createResponse.ok(), 'Creating a deck from the modal should succeed.').toBeTruthy();

      const createdDeck = (await createResponse.json()) as DeckPayload;
      createdDeckId = createdDeck.id;

      await page.waitForLoadState('networkidle');
      await expect(findDeckCard(page, deckName)).toBeVisible();
      await expect(findDeckCard(page, deckName).getByText(deckDescription)).toBeVisible();
    } finally {
      if (createdDeckId) {
        await deleteDeckViaApi(request, userId, createdDeckId);
      }
    }
  });

  test('xoa Deck voi confirm dialog', async ({ page, request }) => {
    const userId = await getDashboardUserId(page);
    const deckName = `PW Delete ${Date.now()}`;
    const deck = await createDeckViaApi(request, userId, deckName, 'Deck created to verify delete flow.');

    await page.reload();
    const card = findDeckCard(page, deck.name);
    await expect(card).toBeVisible();

    page.once('dialog', async (dialog) => {
      expect(dialog.message()).toContain(`Delete "${deck.name}"?`);
      await dialog.accept();
    });

    const deleteResponsePromise = page.waitForResponse((response) => response.url().includes(`/api/decks/${deck.id}`) && response.request().method() === 'DELETE');
    await card.getByRole('button', { name: 'Delete deck' }).click();
    const deleteResponse = await deleteResponsePromise;

    expect(deleteResponse.ok(), 'Deleting a deck from the dashboard should succeed.').toBeTruthy();
    await expect(card).toHaveCount(0);
  });

  test('click Open Deck trong deck card navigate den Deck Detail', async ({ page, request }) => {
    const userId = await getDashboardUserId(page);
    const deckName = `PW Detail ${Date.now()}`;
    const deck = await createDeckViaApi(request, userId, deckName, 'Deck created to verify deck-detail navigation.');

    try {
      await page.reload();
      const card = findDeckCard(page, deck.name);

      await card.getByRole('link', { name: 'Open Deck' }).click();
      await page.waitForURL(new RegExp(`/decks/${deck.id}$`));

      await expect(page.getByRole('heading', { level: 1, name: /Card Management/ })).toBeVisible();
      await expect(page.getByText(deck.name).first()).toBeVisible();
    } finally {
      await deleteDeckViaApi(request, userId, deck.id);
    }
  });

  test('click Review X Cards navigate den Study', async ({ page, request }) => {
    const userId = await getDashboardUserId(page);
    const deckName = `PW Study ${Date.now()}`;
    const deck = await createDeckViaApi(request, userId, deckName, 'Deck created to verify study navigation.');

    try {
      await page.reload();
      const card = findDeckCard(page, deck.name);

      await card.getByRole('link', { name: /Review \d+ Cards/ }).click();
      await page.waitForURL(new RegExp(`/study/front\\?deck_id=${deck.id}`));

      await expect(page.getByRole('heading', { level: 1, name: 'Session Progress' })).toBeVisible();
    } finally {
      await deleteDeckViaApi(request, userId, deck.id);
    }
  });
});
