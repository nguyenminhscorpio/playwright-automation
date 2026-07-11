import { expect, test } from '@playwright/test';
import { createCardViaApi, createDeckViaApi, deleteDeckViaApi } from './helpers/api-helpers';
import { DashboardPage } from './pages/dashboard.page';

test.describe('Dashboard', () => {
  test.beforeEach(async ({ page }) => {
    await new DashboardPage(page).goto();
  });

  test('trang Dashboard load thanh cong, hien thi loi chao', async ({ page }) => {
    await expect(page.getByRole('heading', { level: 1, name: /Good (morning|afternoon|evening),/ })).toBeVisible();
    await expect(page.getByText('Track your streak, review due cards, and manage your decks.')).toBeVisible();
  });

  test('Quick Stats hien thi Streak va Milestone', async ({ page }) => {
    await expect(page.getByText('Daily Streak')).toBeVisible();
    await expect(page.getByText('Due Today')).toBeVisible();
    await expect(page.getByText('Library')).toBeVisible();
    await expect(page.getByText('Monthly Goal')).toBeVisible();
    await expect(page.locator('.dash-stat__val').first()).toBeVisible();
  });

  test('Active Decks grid hien thi danh sach deck', async ({ page, request }) => {
    const dashboardPage = new DashboardPage(page);
    const userId = await dashboardPage.getUserId();
    const deck = await createDeckViaApi(
      request,
      userId,
      `PW Grid ${Date.now()}`,
      'Deck created to verify dashboard grid.'
    );

    try {
      await createCardViaApi(request, userId, deck.id, `Grid front ${Date.now()}`, `Grid back ${Date.now()}`);
      await page.reload();
      await expect(page.getByRole('heading', { level: 2, name: 'Active Decks' })).toBeVisible();
      await expect(dashboardPage.deckCardByName(deck.name)).toBeVisible();
      expect(await dashboardPage.deckCards.count()).toBeGreaterThan(0);
    } finally {
      await deleteDeckViaApi(request, userId, deck.id);
    }
  });

  test('hero khong hien thi nut Create New Deck', async ({ page }) => {
    await expect(page.locator('.hero [data-create-deck-button]')).toHaveCount(0);
  });

  test('xoa Deck voi confirm popup', async ({ page, request }) => {
    const dashboardPage = new DashboardPage(page);
    const userId = await dashboardPage.getUserId();
    const deck = await createDeckViaApi(
      request,
      userId,
      `PW Delete ${Date.now()}`,
      'Deck created to verify delete flow.'
    );

    await createCardViaApi(request, userId, deck.id, `Delete front ${Date.now()}`, `Delete back ${Date.now()}`);
    await page.reload();

    const card = dashboardPage.deckCardByName(deck.name);
    await expect(card).toBeVisible();
    await dashboardPage.openDeleteModalForDeck(deck.name);
    await expect(dashboardPage.deleteDeckModalMessage).toContainText(`delete "${deck.name}"?`);

    const deleteResponsePromise = page.waitForResponse(
      (response) => response.url().includes(`/api/decks/${deck.id}`) && response.request().method() === 'DELETE'
    );

    await dashboardPage.deleteDeckSubmitButton.click();
    const deleteResponse = await deleteResponsePromise;

    expect(deleteResponse.ok(), 'Deleting a deck from the dashboard should succeed.').toBeTruthy();
    await expect(card).toHaveCount(0);
  });

  test('click Open Deck trong deck card navigate den Deck Detail', async ({ page, request }) => {
    const dashboardPage = new DashboardPage(page);
    const userId = await dashboardPage.getUserId();
    const deck = await createDeckViaApi(
      request,
      userId,
      `PW Detail ${Date.now()}`,
      'Deck created to verify deck-detail navigation.'
    );

    try {
      await createCardViaApi(request, userId, deck.id, `Detail front ${Date.now()}`, `Detail back ${Date.now()}`);
      await page.reload();
      const card = dashboardPage.deckCardByName(deck.name);

      await card.getByRole('link', { name: 'Manage' }).click();
      await page.waitForURL(new RegExp(`/decks/${deck.id}$`));

      await expect(page.getByRole('heading', { level: 1, name: deck.name })).toBeVisible();
    } finally {
      await deleteDeckViaApi(request, userId, deck.id);
    }
  });

  test('click Review X Cards navigate den Study', async ({ page, request }) => {
    const dashboardPage = new DashboardPage(page);
    const userId = await dashboardPage.getUserId();
    const deck = await createDeckViaApi(
      request,
      userId,
      `PW Study ${Date.now()}`,
      'Deck created to verify study navigation.'
    );

    try {
      await createCardViaApi(request, userId, deck.id, `Study front ${Date.now()}`, `Study back ${Date.now()}`);
      await page.reload();
      const card = dashboardPage.deckCardByName(deck.name);

      await card.getByRole('link', { name: /Review/ }).click();
      await page.waitForURL(new RegExp(`/study/typing\\?deck_id=${deck.id}`));
      await expect(page.locator('[data-study-app]')).toBeVisible();
    } finally {
      await deleteDeckViaApi(request, userId, deck.id);
    }
  });
});
