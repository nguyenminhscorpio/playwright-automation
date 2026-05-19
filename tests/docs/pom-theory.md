# Page Object Model (POM) — Lý thuyết & Thực tế trong dự án

---

## 1. POM là gì? (Nói đơn giản)

**Vấn đề khi KHÔNG có POM:**

```typescript
// Trong test — selector nằm rải rác khắp nơi
await page.locator('[data-card-row]').filter({ hasText: 'Front A' }).click();
await page.getByPlaceholder('Search cards by front, back, or description...').fill('keyword');
await page.getByRole('button', { name: 'Apply' }).click();

// Nếu UI đổi placeholder text → phải tìm sửa ở TẤT CẢ test files
```

**Vấn đề được giải quyết khi có POM:**

```typescript
// Trong test — chỉ thấy tên hành động, không thấy selector
await deckDetailPage.search('keyword');

// Nếu UI đổi placeholder → chỉ sửa 1 chỗ trong DeckDetailPage
```

> **Nguyên tắc cốt lõi:** Tách "biết UI trông thế nào" ra khỏi "muốn test gì".

---

## 2. Cấu trúc file trong dự án

```
tests/e2e/
│
├── pages/                       ← Page Objects (biết UI trông thế nào)
│   ├── dashboard.page.ts        → DashboardPage
│   ├── deck-detail.page.ts      → DeckDetailPage
│   ├── import.page.ts           → ImportPage
│   └── study.page.ts            → StudyPage
│
├── helpers/
│   └── api-helpers.ts           ← API Helpers (biết cách gọi API)
│
├── dashboard.spec.ts            ← Test files (chỉ nói "muốn test gì")
├── deck-detail.spec.ts
├── import.spec.ts
├── study.spec.ts
└── api.spec.ts
```

**Mỗi file có vai trò riêng — ví dụ `deck-detail.spec.ts` cần 3 trợ lý:**

```
deck-detail.spec.ts = "đạo diễn"
  │
  ├── DeckDetailPage  = "trợ lý biết trang Deck Detail trông thế nào"
  ├── DashboardPage   = "trợ lý biết trang Dashboard" (dùng để login + lấy userId)
  └── api-helpers     = "trợ lý lo data trước và dọn dẹp sau test"
```

---

## 3. Giải phẫu một Page Object

Mỗi Page Object có 4 phần. Lấy `DeckDetailPage` làm ví dụ:

### Phần 1 & 2: Properties + Constructor — "Trang này có gì, tìm ở đâu"

```typescript
export class DeckDetailPage {

  // PHẦN 1: Đặt TÊN cho từng element (tên mô tả nghiệp vụ, không phải kỹ thuật)
  readonly cardRows: Locator;
  readonly bulkDeleteButton: Locator;
  readonly searchInput: Locator;
  readonly applyButton: Locator;
  readonly deleteCardModal: Locator;

  // PHẦN 2: Map tên → selector HTML thật (chỉ 1 chỗ duy nhất trong toàn bộ dự án)
  constructor(private readonly page: Page) {
    this.cardRows         = page.locator('[data-card-row]');
    this.bulkDeleteButton = page.locator('[data-action-bulk-delete]');
    this.searchInput      = page.getByPlaceholder('Search cards by front, back, or description...');
    this.applyButton      = page.getByRole('button', { name: 'Apply' });
    this.deleteCardModal  = page.locator('#delete-card-modal');
  }
}
```

**Quy tắc đặt tên selector:**

| Loại selector | Độ ổn định | Ví dụ trong dự án |
|---|---|---|
| `[data-*]` attribute | ✅ Cao nhất | `[data-card-row]`, `[data-action-bulk-delete]` |
| `getByRole` | ✅ Cao | `getByRole('button', { name: 'Apply' })` |
| `getByPlaceholder` | ⚠️ Trung bình | Dễ vỡ nếu đổi text |
| `.class-name` | ❌ Thấp | Vỡ khi refactor CSS |

---

### Phần 3: `goto()` — Navigation có verify

```typescript
// Không chỉ navigate, còn xác nhận trang đã load xong
async goto(deckId: number) {
  await this.page.goto(`/decks/${deckId}`);
  await expect(this.page).toHaveURL(new RegExp(`/decks/${deckId}$`)); // verify URL
  await expect(this.app).toBeVisible();                                // verify đã render
}
```

**Trong test:** chỉ cần `await deckDetailPage.goto(deck.id)` — 1 dòng, 3 bước bên trong.

---

### Phần 4: Methods — 3 loại

#### Loại A: Dynamic Locator — trả `Locator`, không `await`

> Dùng khi cần tham số để tìm element (không thể khai báo cố định).

```typescript
// Page Object
rowByFrontText(frontText: string) {
  return this.cardRows.filter({ hasText: frontText }); // KHÔNG await
}

// StudyPage
rateButton(rating: 'again' | 'hard' | 'good' | 'easy') {
  return this.page.locator(`[data-study-rate-button="${rating}"]`);
}
```

```typescript
// Trong test — không await method, chỉ await assert
await expect(deckDetailPage.rowByFrontText('Front A')).toHaveCount(1);
await studyPage.rateButton('good').click();
```

#### Loại B: Action — `async`, bọc nhiều bước thành 1

> Dùng khi thao tác gồm nhiều bước liên quan nhau.

```typescript
// Page Object
async search(keyword: string) {
  await this.searchInput.fill(keyword); // bước 1
  await this.applyButton.click();       // bước 2
}

async openDeleteCardModal(frontText: string) {
  await this.rowByFrontText(frontText)
    .getByRole('button', { name: 'Delete card' })
    .click();
  await expect(this.deleteCardModal).toBeVisible(); // verify modal mở
}

async selectCard(frontText: string) {
  await this.rowByFrontText(frontText)
    .locator('[data-row-checkbox]')
    .check();
}
```

```typescript
// Trong test — đọc như câu chuyện
await deckDetailPage.goto(deck.id);
await deckDetailPage.selectCard('Front A');
await deckDetailPage.selectCard('Front B');
await deckDetailPage.bulkDeleteButton.click();
// → Không thấy 1 selector nào, đọc như văn xuôi
```

#### Loại C: Utility — đọc giá trị từ DOM

> Dùng khi cần lấy dữ liệu từ trang để so sánh.

```typescript
// StudyPage
async getProgressWidth() {
  return this.progressBar.evaluate(
    (el) => (el as HTMLElement).style.width // chạy JS trong browser
  );
}
```

```typescript
// Trong test
const before = await studyPage.getProgressWidth(); // "0%"
await studyPage.rateButton('good').click();
const after = await studyPage.getProgressWidth();  // "50%"
expect(after).not.toBe(before);
```

---

## 4. API Helpers — Tách biệt setup/cleanup khỏi test

**Mục đích:** Test tự tạo data cần dùng, tự dọn sau khi xong. Không phụ thuộc DB có sẵn gì.

```typescript
// helpers/api-helpers.ts — tái sử dụng ở nhiều spec file

export const createDeckViaApi = async (request, userId, name, description) => {
  const response = await request.post('/api/decks', { data: { name, description } });
  expect(response.ok()).toBeTruthy(); // assert trong helper — fail rõ ràng nếu setup hỏng
  return (await response.json()) as DeckPayload;
};

export const deleteDeckViaApi = async (request, userId, deckId) => {
  const response = await request.delete(`/api/decks/${deckId}`);
  expect.soft(response.ok()).toBeTruthy(); // .soft() — không dừng test nếu cleanup fail
};
```

**Pattern chuẩn trong mọi test có tạo data:**

```typescript
test('tên test', async ({ page, request }) => {
  // Setup — tạo data qua API (nhanh, không qua UI)
  const deck = await createDeckViaApi(request, userId, `PW ${Date.now()}`, '');
  const card = await createCardViaApi(request, userId, deck.id, 'Front', 'Back');

  try {
    // Test logic — chỉ test 1 việc
    await deckDetailPage.goto(deck.id);
    await expect(deckDetailPage.rowByFrontText('Front')).toHaveCount(1);

  } finally {
    // Cleanup — luôn chạy dù test pass hay fail
    await deleteDeckViaApi(request, userId, deck.id);
  }
});
```

**Tại sao dùng `Date.now()` trong tên:**

```typescript
`PW Search ${Date.now()}` // → "PW Search 1716134567890"
// ← Tránh trùng tên khi nhiều test chạy song song (fullyParallel: true)
// ← Tránh dữ liệu cũ từ lần chạy trước gây nhiễu
```

---

## 5. Dấu hiệu nhận biết code chưa đúng POM

```typescript
// ❌ Raw selector trong spec file → nên vào Page Object
await page.locator('[data-delete-card-submit-button]').click();
await page.locator('[data-card-modal-title]').toHaveText('Edit Card');
await page.locator('[data-delete-modal-message]').toContainText('delete');

// ✅ Đúng POM — gọi method của Page Object
await deckDetailPage.submitDeleteModal();
await expect(deckDetailPage.cardModalTitle).toHaveText('Edit Card');
await expect(deckDetailPage.deleteModalMessage).toContainText('delete');
```

---

## 6. Mức độ tuân thủ POM hiện tại của dự án

```
import.spec.ts       ████████████  ~90%  Tốt
study.spec.ts        ████████████  ~90%  Tốt
deck-detail.spec.ts  ████████░░░░  ~75%  Còn 7 raw selector lọt vào test
dashboard.spec.ts    ████████░░░░  ~65%  Còn raw selector và getByRole inline
api.spec.ts          ████░░░░░░░░  ~30%  Mock tests không dùng Page Object
```

**Những selector cần chuyển vào Page Object:**

| File | Selector cần chuyển | Vào đâu |
|---|---|---|
| `deck-detail.spec.ts:149` | `[data-card-modal-title]` | `DeckDetailPage.cardModalTitle` |
| `deck-detail.spec.ts:173` | `[data-delete-card-submit-button]` | `DeckDetailPage.deleteCardSubmitButton` |
| `deck-detail.spec.ts:168` | `[data-delete-modal-message]` | `DeckDetailPage.deleteModalMessage` |
| `dashboard.spec.ts:21` | `.stat-card__value` | `DashboardPage.statCardValues` |
| `api.spec.ts:84` | `[data-import-file-input]` | Dùng `ImportPage` đã có sẵn |
