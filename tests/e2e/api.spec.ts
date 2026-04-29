import { expect, test } from '@playwright/test';
import { createDeckViaApi } from './helpers/api-helpers';

// Helper tạo payload deck mới với tên unique để tránh đụng dữ liệu giữa các lần chạy.
const createDeckPayload = (suffix: string) => ({
  name: `API Test Deck ${suffix}`,
  description: 'Created via API test',
});

test.describe('API Tests', () => {
  test('GET /api/decks tra ve danh sach deck trong object items', async ({ request }) => {
    // Gọi API trực tiếp, không đi qua UI.
    const response = await request.get('/api/decks');

    expect(response.ok()).toBeTruthy();

    // API hiện trả về object có field items, không phải mảng thuần.
    const data = await response.json();
    expect(Array.isArray(data.items)).toBeTruthy();
  });

  test('POST /api/decks tao deck moi', async ({ request }) => {
    // Tạo deck mới để verify API create hoạt động đúng và trả về dữ liệu vừa tạo.
    const response = await request.post('/api/decks', {
      data: createDeckPayload(String(Date.now())),
    });

    expect(response.ok()).toBeTruthy();
    expect(response.status()).toBe(201);

    const createdDeck = await response.json();
    expect(createdDeck.name).toContain('API Test Deck');
    expect(createdDeck.description).toBe('Created via API test');
    expect(createdDeck.id).toBeTruthy();
  });
});

test.describe('Network Mocking', () => {
  test('Import page hien thi mocked preview rows khi mock API preview', async ({ page }) => {
    // Chặn API preview và trả về dữ liệu giả để UI render preview theo dữ liệu mock.
    await page.route('**/api/imports/txt/preview', async (route) => {
      await route.fulfill({
        status: 200,
        contentType: 'application/json',
        body: JSON.stringify({
          import_job_id: 999,
          file_name: 'mocked-import.txt',
          file_hash: 'mocked-hash',
          detected_format: 'anki_txt_tab',
          total_lines: 2,
          data_lines: 2,
          valid_rows: 1,
          invalid_rows: 1,
          rows: [
            {
              index: 1,
              data: { front_text: 'Mock Front 1', back_text: 'Mock Back 1' },
              status: 'valid',
              errors: [],
              warnings: [],
            },
            {
              index: 2,
              data: { front_text: null, back_text: null },
              status: 'invalid',
              errors: [{ index: 2, field: null, message: 'Mock invalid row' }],
              warnings: [],
            },
          ],
          errors: [{ index: 2, field: null, message: 'Mock invalid row' }],
          warnings: [],
          summary: {
            total: 2,
            valid: 1,
            warning: 0,
            invalid: 1,
          },
          preview_rows: [],
        }),
      });
    });

    await page.goto('/imports');
    await page.locator('[data-import-file-input]').setInputFiles({
      name: 'mocked-import.txt',
      mimeType: 'text/plain',
      buffer: Buffer.from('anything'),
    });

    // Trigger preview rồi verify bảng hiển thị đúng dữ liệu đã mock.
    await page.getByRole('button', { name: /Preview Import/i }).click();

    const rows = page.locator('[data-import-rows-body] tr');
    await expect(rows).toHaveCount(2);
    await expect(rows.nth(0)).toContainText('Mock Front 1');
    await expect(rows.nth(0)).toContainText('Mock Back 1');
    await expect(rows.nth(0)).toContainText('valid');
    await expect(rows.nth(1)).toContainText('invalid');
    await expect(rows.nth(1)).toContainText('Mock invalid row');
  });

  test('Dashboard hien thi loi khi API delete deck bi abort', async ({ page, request }) => {
    // Tạo sẵn một deck thật để dashboard có card cần thao tác xóa.
    const payload = createDeckPayload(`delete-${Date.now()}`);
    const createdDeck = await createDeckViaApi(
      request,
      undefined,
      payload.name,
      payload.description
    );

    // Mock lỗi network ở API delete để verify UI hiện feedback lỗi trong popup.
    await page.route(`**/api/decks/${createdDeck.id}`, async (route) => {
      await route.abort();
    });

    await page.goto('/dashboard');
    const deckCard = page.locator(`[data-deck-card][data-deck-id="${createdDeck.id}"]`);
    await expect(deckCard).toBeVisible();

    await deckCard.locator('[data-delete-deck-button]').click();
    await expect(page.locator('#delete-deck-modal')).toBeVisible();

    await page.locator('[data-delete-deck-submit-button]').click();

    // Khi xóa thất bại, popup vẫn mở và feedback lỗi phải xuất hiện.
    const feedback = page.locator('[data-delete-deck-form-feedback]');
    await expect(feedback).toBeVisible();
    await expect(feedback).not.toHaveText('');
  });
});
