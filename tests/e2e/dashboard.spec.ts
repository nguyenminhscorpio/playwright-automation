import { expect, test } from '@playwright/test';

type DeckPayload = {
  id: number;
  name: string;
  description: string | null;
};

// Lấy user id đang được dashboard dùng.
// Test cần user id này để gọi API tạo/xóa deck đúng user.
const getDashboardUserId = async (page: import('@playwright/test').Page) => {
  const userId = await page.locator('[data-dashboard-app]').getAttribute('data-dashboard-user-id');
  expect(userId, 'Dashboard should expose a user id for API-backed setup.').toBeTruthy();
  return Number(userId);
};

// Helper tạo deck bằng API.
// Cách này nhanh và ổn định hơn thao tác UI khi mình chỉ cần chuẩn bị dữ liệu test.
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

// Helper dọn dữ liệu test sau khi chạy xong.
// Dùng expect.soft để nếu cleanup lỗi thì vẫn không che mất lỗi chính của test.
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

// Tìm đúng deck card theo tên deck trên dashboard.
// Việc bọc thành helper giúp các test bên dưới dễ đọc hơn.
const findDeckCard = (page: import('@playwright/test').Page, deckName: string) =>
  page.locator('[data-deck-card]').filter({
    has: page.locator('.deck-card__title', { hasText: deckName }),
  });

test.describe('Dashboard', () => {
  test.beforeEach(async ({ page }) => {
    // Mỗi test đều bắt đầu từ dashboard để có cùng điểm xuất phát.
    await page.goto('/dashboard');
    await expect(page).toHaveURL(/\/dashboard$/);
  });

  test('trang Dashboard load thanh cong, hien thi loi chao', async ({ page }) => {
    // Kiểm tra phần welcome header có hiển thị đúng không.
    await expect(page.getByRole('heading', { level: 1, name: /Welcome back,/ })).toBeVisible();
    await expect(page.getByText('Track your streak, monthly milestone, and the decks that need attention most today.')).toBeVisible();
  });

  test('Quick Stats hien thi Streak va Milestone', async ({ page }) => {
    // Kiểm tra các block số liệu chính trên dashboard.
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
      // Reload để dashboard lấy lại dữ liệu mới nhất sau khi tạo deck qua API.
      await page.reload();

      // Kiểm tra section Active Decks có xuất hiện.
      await expect(page.getByRole('heading', { level: 2, name: 'Active Decks' })).toBeVisible();

      // Kiểm tra deck vừa tạo có hiện trên dashboard hay không.
      await expect(findDeckCard(page, deck.name)).toBeVisible();

      // Kiểm tra tối thiểu là dashboard có card deck hiển thị.
      expect(await page.locator('[data-deck-card]').count()).toBeGreaterThan(0);
    } finally {
      // Luôn dọn deck test dù test pass hay fail.
      await deleteDeckViaApi(request, userId, deck.id);
    }
  });

  test('hero khong hien thi nut Create New Deck', async ({ page }) => {
    // Sau khi đổi UI, khu vực hero không còn nút Create New Deck nữa.
    await expect(page.locator('.hero [data-create-deck-button]')).toHaveCount(0);
  });

  test('xoa Deck voi confirm popup', async ({ page, request }) => {
    const userId = await getDashboardUserId(page);
    const deckName = `PW Delete ${Date.now()}`;
    const deck = await createDeckViaApi(request, userId, deckName, 'Deck created to verify delete flow.');

    // Sau khi tạo dữ liệu bằng API, reload để UI nhìn thấy deck mới.
    await page.reload();

    const card = findDeckCard(page, deck.name);
    await expect(card).toBeVisible();

    // Dashboard dùng window.confirm khi xóa deck.
    // Mình lắng nghe dialog rồi bấm Accept.
    await card.getByRole('button', { name: 'Delete deck' }).click();
    await expect(page.locator('#delete-deck-modal')).toBeVisible();
    await expect(page.locator('[data-delete-deck-modal-message]')).toContainText(`delete "${deck.name}"?`);

    // Chờ đúng response DELETE trả về để chắc thao tác xóa đã chạy xong.
    const deleteResponsePromise = page.waitForResponse(
      (response) => response.url().includes(`/api/decks/${deck.id}`) && response.request().method() === 'DELETE'
    );

    await page.locator('[data-delete-deck-submit-button]').click();
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

      // Từ dashboard, click nút Open Deck để sang màn quản lý card.
      await card.getByRole('link', { name: 'Open Deck' }).click();
      await page.waitForURL(new RegExp(`/decks/${deck.id}$`));

      // Kiểm tra đã sang đúng trang deck detail.
      await expect(page.getByRole('heading', { level: 1, name: /Card Management/ })).toBeVisible();
      await expect(page.locator('.card-manager-deck-badge', { hasText: deck.name })).toBeVisible();
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

      // Từ dashboard, click CTA review để sang study session.
      await card.getByRole('link', { name: /Review \d+ Cards/ }).click();
      await page.waitForURL(new RegExp(`/study/front\\?deck_id=${deck.id}`));

      // Kiểm tra study page đã load thành công.
      await expect(page.getByRole('heading', { level: 1, name: 'Session Progress' })).toBeVisible();
    } finally {
      await deleteDeckViaApi(request, userId, deck.id);
    }
  });
});
