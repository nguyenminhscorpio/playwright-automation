# 🧪 Playwright vs Selenium — Phân Tích Cho Project FlashMind

## 📋 Tổng Quan Project

| Thuộc tính | Chi tiết |
|---|---|
| **Framework** | Laravel 13 (PHP 8.3) |
| **Frontend** | Vanilla JS + Blade Templates + TailwindCSS v4 |
| **Build Tool** | Vite 8 |
| **Existing Tests** | PHPUnit (Feature + Unit) |
| **Screens** | Dashboard, Deck Detail, Import, Study (Front/Typing/Answer) |
| **API** | REST API (Decks, Cards, Study Sessions, Import) |
| **UI Pattern** | Server-rendered Blade + Client-side JS (`<dialog>` modals, fetch API, DOM manipulation) |

---

## ⚖️ So Sánh Chi Tiết

### 1. 🚀 Setup & Cấu Hình

| Tiêu chí | Playwright | Selenium |
|---|---|---|
| **Cài đặt** | `npm init playwright@latest` — 1 lệnh duy nhất | Cần cài WebDriver riêng cho từng browser + binding library |
| **Browser** | Tự động tải Chromium, Firefox, WebKit | Phải quản lý ChromeDriver/GeckoDriver thủ công hoặc qua `webdriver-manager` |
| **Config** | `playwright.config.ts` — tích hợp sẵn | Cần tự cấu hình, không có standard config |
| **Ngôn ngữ** | TypeScript/JavaScript (native) | Python, Java, C#, JS (Selenium WebDriver) |

> [!TIP]
> Project đã dùng **Node.js ecosystem** (package.json, Vite, npm). Playwright tích hợp seamless vào đây mà **không cần thêm runtime nào khác** (không cần Python, Java).

### 2. ⚡ Tốc Độ & Hiệu Suất

| Tiêu chí | Playwright | Selenium |
|---|---|---|
| **Kiến trúc** | Giao tiếp trực tiếp qua CDP/Browser Protocol | Giao tiếp qua WebDriver HTTP protocol (thêm 1 lớp trung gian) |
| **Auto-wait** | ✅ Tự động chờ element sẵn sàng | ❌ Phải viết explicit/implicit wait thủ công |
| **Parallel** | ✅ Native worker isolation | ⚠️ Cần Selenium Grid hoặc config phức tạp |
| **Headless** | ✅ Mặc định headless, rất nhanh | ✅ Hỗ trợ nhưng chậm hơn |

> [!IMPORTANT]
> Project có nhiều **async operations** (fetch API calls, modal dialogs, page reloads). Playwright auto-wait giúp giảm **đáng kể** số lượng flaky tests so với Selenium.

### 3. 🎯 Selectors & Tương Tác UI

Project sử dụng `data-*` attributes rất nhiều — đây là **best practice cho automation testing**:

```html
<!-- Ví dụ từ project -->
<button data-create-deck-button>Create Deck</button>
<input data-card-front-input />
<tr data-card-row data-card-id="123">...</tr>
<dialog id="card-modal">...</dialog>
```

| Tiêu chí | Playwright | Selenium |
|---|---|---|
| **Data attributes** | `page.locator('[data-create-deck-button]')` | `driver.find_element(By.CSS_SELECTOR, '[data-create-deck-button]')` |
| **Text-based** | `page.getByText('Create Card')` — built-in | Phải dùng XPath: `//button[text()='Create Card']` |
| **Role-based** | `page.getByRole('button', { name: 'Delete' })` | Không có built-in, phải tự viết |
| **`<dialog>` support** | ✅ Native support cho HTML `<dialog>` | ⚠️ Hỗ trợ hạn chế, cần workaround |
| **Chaining** | `page.locator('[data-card-row]').filter({ hasText: 'Hello' })` | Phải viết XPath phức tạp |

> [!WARNING]
> Project sử dụng HTML native `<dialog>` (showModal/close) — Selenium có **vấn đề đã biết** với việc tương tác `<dialog>` elements. Playwright xử lý `<dialog>` một cách tự nhiên.

### 4. 🔌 API Testing Integration

Project có REST API endpoints cần test song song với UI:

| Tiêu chí | Playwright | Selenium |
|---|---|---|
| **API request** | ✅ `request.post('/api/decks', { data: {...} })` — built-in | ❌ Không có, phải dùng thêm requests/axios |
| **Mock API** | ✅ `page.route('/api/**', route => ...)` — native | ❌ Không hỗ trợ |
| **Network intercept** | ✅ Bắt và kiểm tra mọi request/response | ❌ Không có |

> [!TIP]
> Khả năng **mock API** của Playwright cực kỳ hữu ích khi test UI mà không cần backend chạy, hoặc simulate edge cases (server error, empty data).

### 5. 🐛 Debugging & Developer Experience

| Tiêu chí | Playwright | Selenium |
|---|---|---|
| **Trace Viewer** | ✅ Timeline chi tiết với screenshots từng step | ❌ Không có |
| **Codegen** | ✅ `npx playwright codegen localhost:8000` — record & generate test | ❌ Cần IDE plugin riêng (Selenium IDE) |
| **Inspector** | ✅ `npx playwright test --debug` — step-by-step | ❌ Chỉ dùng debugger của IDE |
| **Screenshots** | ✅ Auto-capture on failure | ⚠️ Phải code thủ công |
| **Video recording** | ✅ Built-in | ❌ Không có |
| **HTML Report** | ✅ `npx playwright show-report` — interactive | ❌ Cần Allure hoặc third-party |

### 6. 🔄 CI/CD Integration

| Tiêu chí | Playwright | Selenium |
|---|---|---|
| **GitHub Actions** | ✅ Official action: `playwright-github-action` | ⚠️ Cần tự setup browser + driver |
| **Docker** | ✅ Official Docker image | ⚠️ Cần Selenium Grid Docker compose |
| **Retry** | ✅ Built-in retry mechanism | ❌ Phải tự implement |
| **Sharding** | ✅ Native test sharding | ❌ Cần Selenium Grid |

### 7. 📦 Ecosystem & Community

| Tiêu chí | Playwright | Selenium |
|---|---|---|
| **Tuổi đời** | 2020 — hiện đại, phát triển nhanh | 2004 — mature, nhưng API cũ |
| **Maintainer** | Microsoft | Community-driven |
| **Stars (GitHub)** | ~70k+ ⭐ | ~32k+ ⭐ |
| **npm downloads/week** | ~8M+ | ~2M (selenium-webdriver) |
| **Cập nhật** | Rất tích cực (hàng tháng) | Chậm hơn |

### 8. 🌐 Cross-Browser

| Browser | Playwright | Selenium |
|---|---|---|
| **Chromium** | ✅ | ✅ |
| **Firefox** | ✅ | ✅ |
| **WebKit (Safari)** | ✅ | ❌ (cần SafariDriver trên macOS) |
| **Edge** | ✅ (Chromium-based) | ✅ |
| **Mobile emulation** | ✅ Built-in device profiles | ⚠️ Hạn chế |

---

## 💻 Code Examples — Cùng Một Test Case

### Test Case: Tạo Deck mới từ Dashboard

#### Playwright (TypeScript/JavaScript)

```typescript
import { test, expect } from '@playwright/test';

test('should create a new deck from dashboard', async ({ page }) => {
  // Navigate to dashboard
  await page.goto('/dashboard');

  // Click create deck button
  await page.locator('[data-create-deck-button]').click();

  // Fill in deck details
  await page.fill('#new-deck-name', 'Japanese N5 Vocabulary');
  await page.fill('#new-deck-description', 'Basic JLPT N5 words');

  // Submit form
  await page.click('#create-deck-submit-btn');

  // Verify deck appears on dashboard (auto-waits for reload)
  await expect(page.locator('[data-deck-card]').filter({ hasText: 'Japanese N5 Vocabulary' })).toBeVisible();
});

test('should delete a card via bulk action', async ({ page }) => {
  await page.goto('/decks/1');

  // Select cards
  await page.locator('[data-select-all-checkbox]').check();

  // Click bulk delete
  await page.locator('[data-action-bulk-delete]').click();

  // Confirm in delete modal
  const deleteModal = page.locator('#delete-card-modal');
  await expect(deleteModal).toBeVisible();
  await page.locator('[data-delete-card-submit-button]').click();

  // Verify cards are removed
  await expect(page.locator('[data-card-row]')).toHaveCount(0);
});
```

#### Selenium (Python)

```python
from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
import time

def test_create_deck():
    driver = webdriver.Chrome()
    wait = WebDriverWait(driver, 10)
    
    try:
        driver.get("http://localhost:8000/dashboard")
        
        # Click create deck button
        btn = wait.until(EC.element_to_be_clickable(
            (By.CSS_SELECTOR, "[data-create-deck-button]")
        ))
        btn.click()
        
        # Wait for modal to open
        time.sleep(0.5)  # <dialog> animation
        
        # Fill in deck details
        name_input = wait.until(EC.visibility_of_element_located(
            (By.ID, "new-deck-name")
        ))
        name_input.send_keys("Japanese N5 Vocabulary")
        
        desc_input = driver.find_element(By.ID, "new-deck-description")
        desc_input.send_keys("Basic JLPT N5 words")
        
        # Submit
        driver.find_element(By.ID, "create-deck-submit-btn").click()
        
        # Wait for page reload
        time.sleep(2)
        
        # Verify
        cards = driver.find_elements(By.CSS_SELECTOR, "[data-deck-card]")
        found = any("Japanese N5 Vocabulary" in c.text for c in cards)
        assert found, "Deck not found on dashboard"
        
    finally:
        driver.quit()
```

> [!NOTE]
> Để ý sự khác biệt:
> - Playwright: **15 dòng**, auto-wait, assertions rõ ràng
> - Selenium: **30+ dòng**, cần explicit waits, `time.sleep()`, try/finally cleanup

---

## 🏆 Kết Luận & Khuyến Nghị

### ✅ **Khuyến nghị: Playwright**

Dựa trên phân tích project FlashMind, Playwright là lựa chọn **vượt trội** vì:

| Lý do | Giải thích |
|---|---|
| **1. Cùng ecosystem** | Project đã dùng Node.js/npm/Vite — Playwright tích hợp tự nhiên, không cần thêm Python/Java |
| **2. `<dialog>` support** | Project dùng nhiều HTML `<dialog>` modals — Playwright hỗ trợ tốt hơn |
| **3. Auto-wait** | Nhiều async operations (fetch API, page.reload) — auto-wait giảm flaky tests |
| **4. API testing built-in** | Test cả REST API endpoints song song với UI test |
| **5. `data-*` selectors** | Project đã follow best practice với `data-*` attributes — Playwright tận dụng tối đa |
| **6. Network mocking** | Mock API responses để test edge cases (empty decks, import errors) |
| **7. Developer experience** | Codegen, Trace Viewer, HTML Report — workflow nhanh hơn nhiều |
| **8. Thời gian viết test** | Code ngắn gọn hơn 40-50% so với Selenium |

### ❌ Khi nào KHÔNG nên chọn Playwright?

- Team đã có kinh nghiệm sâu với Selenium và không muốn chuyển đổi
- Cần test trên **real mobile devices** (Appium/Selenium tốt hơn)
- Project cần test trên browser rất cũ (IE11 — tuy nhiên đã ngừng support)

### 📊 Điểm Tổng

| Tiêu chí (trọng số) | Playwright | Selenium |
|---|---|---|
| Setup dễ dàng (15%) | ⭐⭐⭐⭐⭐ | ⭐⭐⭐ |
| Tốc độ (20%) | ⭐⭐⭐⭐⭐ | ⭐⭐⭐ |
| Selectors & UI (20%) | ⭐⭐⭐⭐⭐ | ⭐⭐⭐ |
| API Testing (10%) | ⭐⭐⭐⭐⭐ | ⭐⭐ |
| Debugging (15%) | ⭐⭐⭐⭐⭐ | ⭐⭐⭐ |
| CI/CD (10%) | ⭐⭐⭐⭐⭐ | ⭐⭐⭐ |
| Cross-browser (5%) | ⭐⭐⭐⭐⭐ | ⭐⭐⭐⭐ |
| Community (5%) | ⭐⭐⭐⭐ | ⭐⭐⭐⭐⭐ |
| **Tổng điểm** | **⭐ 4.85/5** | **⭐ 3.05/5** |

---

## 🚀 Next Steps (nếu chọn Playwright)

1. **Cài đặt**: `npm init playwright@latest`
2. **Cấu hình**: Setup `playwright.config.ts` với `baseURL: 'http://localhost:8000'`
3. **Viết test đầu tiên**: Bắt đầu với Dashboard flow (create/delete deck)
4. **Mở rộng**: Import flow → Study flow → API tests
5. **CI/CD**: Thêm GitHub Actions workflow
