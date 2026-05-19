# Lý Thuyết Tổng Hợp: Playwright API Automation

## 1. Tổng quan — Playwright làm được gì với API?

Playwright không chỉ là công cụ click UI. Nó kiểm soát toàn bộ **tầng network** giữa browser và server:

```
┌─────────────────────────────────────────────────────────┐
│                    Playwright Test                       │
│                                                         │
│   ┌──────────┐    ┌────────────┐    ┌───────────────┐   │
│   │  { page }│    │ { request }│    │  page.route() │   │
│   │  UI Test │    │  API Test  │    │  Mock/Intercpt│   │
│   └────┬─────┘    └─────┬──────┘    └──────┬────────┘   │
│        │                │                  │             │
└────────┼────────────────┼──────────────────┼────────────┘
         ↓                ↓                  ↓
    Browser HTTP      Direct HTTP       Interceptor
    (với cookies,     (không browser,   (chặn & thay
     session...)      không UI)          thế response)
         ↓                ↓                  ↓
    ┌──────────────────────────────────────────────────┐
    │                   Web Server                     │
    └──────────────────────────────────────────────────┘
```

---

## 2. Ba Pattern Cốt Lõi

### Pattern 1: `request` — Gọi API trực tiếp

**Dùng khi:** Kiểm tra API contract (shape, status code, auth)

```typescript
test('GET /api/decks trả về đúng shape', async ({ request }) => {
  const response = await request.get('/api/decks');

  // Kiểm tra HTTP status
  expect(response.ok()).toBeTruthy();       // 200-299
  expect(response.status()).toBe(200);

  // Kiểm tra response shape (API contract)
  const data = await response.json();
  expect(Array.isArray(data.items)).toBeTruthy();
});
```

**Đặc điểm kỹ thuật:**
- Fixture `{ request }` = `APIRequestContext` — HTTP client thuần, không mở browser
- Tự động mang cookie/session của test context (đã đăng nhập sẵn)
- Methods: `request.get()`, `request.post()`, `request.put()`, `request.delete()`
- Mỗi method nhận `{ data, headers, params }` trong options

**Khi nào test này FAIL:**
- Backend đổi tên field trong response
- Route bị xóa hoặc đổi URL
- Auth bị break → trả 401 thay vì 200
- Server exception → trả 500

---

### Pattern 2: `page.route + fulfill` — Giả lập response

**Dùng khi:** Test UI behavior độc lập với backend

```typescript
test('UI render đúng khi nhận data từ API', async ({ page }) => {
  // Bước 1: Đăng ký intercept TRƯỚC KHI navigate
  await page.route('**/api/imports/txt/preview', async (route) => {
    await route.fulfill({
      status: 200,
      contentType: 'application/json',
      body: JSON.stringify({
        rows: [
          {
            status: 'valid',
            data: { front_text: 'Mock Front 1', back_text: 'Mock Back 1' },
            errors: [],
            warnings: [],
          },
          {
            status: 'invalid',
            errors: [{ message: 'Mock invalid row' }],
          },
        ],
      }),
    });
  });

  // Bước 2: Navigate và thao tác UI bình thường
  await page.goto('/imports');
  await page.getByRole('button', { name: /Preview Import/i }).click();

  // Bước 3: Assert UI dùng đúng dữ liệu mock
  const rows = page.locator('[data-import-rows-body] tr');
  await expect(rows).toHaveCount(2);
  await expect(rows.nth(0)).toContainText('Mock Front 1');
  await expect(rows.nth(1)).toContainText('invalid');
});
```

**URL Pattern matching:**
```typescript
'**/api/decks'           // khớp bất kỳ origin + path /api/decks
'**/api/decks/**'        // khớp /api/decks/1, /api/decks/abc
/api\/decks\/\d+/        // dùng regex
`**/api/decks/${id}`     // template literal với biến
```

**`route.fulfill()` options:**
```typescript
await route.fulfill({
  status: 200,                      // HTTP status code
  contentType: 'application/json',
  body: JSON.stringify(data),       // response body
  headers: { 'X-Custom': 'value' },
});
```

**Khi nào dùng:**
- Frontend dev test UI khi backend chưa xong
- Test với dữ liệu edge case (1000 rows, ký tự đặc biệt, null values)
- Test UI với response chậm (thêm `await new Promise(r => setTimeout(r, 3000))` trước fulfill)

---

### Pattern 3: `page.route + abort` — Giả lập lỗi mạng

**Dùng khi:** Test nhánh `catch` / error handling trong UI

```typescript
test('UI hiện lỗi khi request fail', async ({ page, request }) => {
  // Tạo data thật để test có thể interact
  const deck = await createDeckViaApi(request, undefined, 'Test Deck', '');

  // Chặn request ở tầng network — browser nhận NetworkError
  await page.route(`**/api/decks/${deck.id}`, async (route) => {
    await route.abort(); // ← không có response, browser throw error
  });

  await page.goto('/dashboard');
  await page.locator('[data-delete-deck-submit-button]').click();

  // Assert UI xử lý lỗi đúng: hiện feedback, KHÔNG đóng modal
  const feedback = page.locator('[data-delete-deck-form-feedback]');
  await expect(feedback).toBeVisible();
  await expect(feedback).not.toHaveText('');
});
```

**`route.abort()` error types:**
```typescript
await route.abort('failed');             // ERR_FAILED (default)
await route.abort('timedout');           // ERR_TIMED_OUT
await route.abort('connectionrefused');  // ERR_CONNECTION_REFUSED
```

**Các options khác ngoài abort:**
```typescript
await route.continue();   // cho request đi tiếp bình thường
await route.fallback();   // bỏ qua handler này, dùng handler tiếp theo
await route.fulfill({ status: 500, body: 'Server Error' }); // trả lỗi server
```

**Các tình huống không thể test nếu không có mock:**

| Tình huống | Cách mock |
|---|---|
| Server bị down | `route.abort('connectionrefused')` |
| Request timeout | `route.abort('timedout')` |
| Server trả 500 | `route.fulfill({ status: 500 })` |
| Server trả 422 validation error | `route.fulfill({ status: 422, body: ... })` |
| Network chậm | `await new Promise(r => setTimeout(r, 5000))` + `route.continue()` |

---

## 3. API Helpers — Pattern tái sử dụng

**Mục đích:** Tách logic setup/teardown ra khỏi test logic.

```typescript
// helpers/api-helpers.ts

// Type definitions — đảm bảo type safety
export type DeckPayload = {
  id: number;
  name: string;
  description: string | null;
};

// Helper tạo deck — dùng ở nhiều spec file
export const createDeckViaApi = async (
  request: APIRequestContext,
  userId: number | undefined,
  name: string,
  description: string
) => {
  const response = await request.post('/api/decks', {
    data: {
      ...(userId ? { user_id: userId } : {}),  // conditional spread
      name,
      description,
    },
  });

  // Assert ngay trong helper — nếu setup fail thì fail rõ ràng
  expect(response.ok(), 'Deck creation via API should succeed.').toBeTruthy();
  return (await response.json()) as DeckPayload;
};

// Helper xóa — cleanup sau test
export const deleteDeckViaApi = async (
  request: APIRequestContext,
  userId: number,
  deckId: number
) => {
  const response = await request.delete(`/api/decks/${deckId}`);

  // .soft() — không dừng test nếu cleanup fail (chấp nhận được)
  expect.soft(response.ok(), `Cleanup deck ${deckId}`).toBeTruthy();
};
```

**Pattern cleanup chuẩn trong test:**
```typescript
test('test có tạo data thật', async ({ page, request }) => {
  const deck = await createDeckViaApi(request, userId, `PW ${Date.now()}`, '');

  try {
    // Test logic ở đây
    await page.reload();
    await expect(page.locator(`[data-deck-id="${deck.id}"]`)).toBeVisible();
  } finally {
    // Luôn chạy dù test pass hay fail
    await deleteDeckViaApi(request, userId, deck.id);
  }
});
```

**Tại sao dùng `Date.now()` trong tên:**
```typescript
`PW Grid ${Date.now()}`  // → "PW Grid 1716134567890"
// ← Tránh conflict khi chạy nhiều test song song (fullyParallel: true)
// ← Tránh dữ liệu cũ từ lần chạy trước ảnh hưởng
```

---

## 4. Fixtures — Nguồn dữ liệu cho test

**Playwright fixtures** là các dependency được inject vào test function:

```typescript
test('tên test', async ({
  page,      // Browser page — E2E UI test
  request,   // HTTP client — API test không qua UI
  context,   // Browser context — manage cookies, permissions
  browser,   // Browser instance
}) => { ... });
```

**File fixtures (dữ liệu tĩnh):**
```typescript
// import.page.ts
async uploadFixture(fileName: string) {
  const fixturePath = path.resolve(
    process.cwd(), 'tests', 'e2e', 'fixtures', fileName
  );
  await this.fileInput.setInputFiles(fixturePath);
}

// tests/e2e/fixtures/sample-import.txt
// Front 1	Back 1
// Front 2	Back 2
```

---

## 5. Test Isolation — Các test không được phụ thuộc nhau

**Nguyên tắc:** Mỗi test phải tự setup và tự cleanup. Không được dùng data từ test khác.

```typescript
// ❌ Sai — test phụ thuộc DB có sẵn data
test('hiển thị deck', async ({ page }) => {
  await page.goto('/dashboard');
  await expect(page.locator('[data-deck-card]').first()).toBeVisible();
  // → Nếu DB rỗng → test fail dù code đúng
});

// ✅ Đúng — tự tạo data, tự dọn
test('hiển thị deck', async ({ page, request }) => {
  const deck = await createDeckViaApi(request, undefined, `Test ${Date.now()}`, '');
  try {
    await page.reload();
    await expect(page.locator(`[data-deck-id="${deck.id}"]`)).toBeVisible();
  } finally {
    await deleteDeckViaApi(request, undefined, deck.id);
  }
});
```

---

## 6. Khi nào dùng loại test nào?

| Câu hỏi cần trả lời | Dùng loại test nào |
|---|---|
| "API trả đúng status code không?" | `request.get/post` |
| "API trả đúng field name không?" | `request.get/post` |
| "UI render đúng khi có data không?" | `page.route + fulfill` |
| "UI xử lý data rỗng `[]` đúng không?" | `page.route + fulfill` |
| "UI hiện lỗi khi mạng chết không?" | `page.route + abort` |
| "UI hiện lỗi khi server trả 500?" | `page.route + fulfill (status: 500)` |
| "Toàn bộ flow user thực tế đúng?" | E2E test (không mock) |

---

## 7. Test Pyramid áp dụng trong dự án

```
            ▲
           /E2E\          dashboard.spec.ts, study.spec.ts
          /Tests\         → Chậm, test toàn bộ flow thật
         /────────\
        / API+Mock \      api.spec.ts
       /   Tests    \     → Nhanh, test contract & UI behavior
      /──────────────\
     /  Unit Tests    \   PHPUnit (Feature/, Unit/)
    /   (PHP backend)  \  → Rất nhanh, test business logic
   /────────────────────\
```

**Tỉ lệ lý tưởng:** 70% Unit : 20% API/Mock : 10% E2E

---

## 8. Chạy test theo nhóm

```bash
# Chạy tất cả
npm run test:e2e

# Chỉ chạy API tests
npx playwright test tests/e2e/api.spec.ts

# Chỉ chạy test có mock (lọc theo describe name)
npx playwright test --grep "Network Mocking"

# Chỉ chạy API contract tests
npx playwright test --grep "API Tests"

# Debug 1 test cụ thể (có UI, có pause)
npx playwright test tests/e2e/api.spec.ts:39 --headed --debug

# Xem report sau khi chạy
npx playwright show-report
```
