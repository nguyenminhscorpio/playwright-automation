# CI/CD Pipeline — Playwright Tests

## 1. Tổng quan

Dự án dùng **GitHub Actions** để tự động chạy Playwright E2E tests.

**File cấu hình:** `.github/workflows/playwright.yml`

**Trigger:** Tự động chạy khi:
- `push` lên bất kỳ branch nào
- `pull_request` được tạo hoặc cập nhật

```yaml
on:
  push:
  pull_request:
```

---

## 2. Luồng chạy CI (8 bước)

```
Developer push code / tạo PR
        │
        ▼
┌─────────────────────────────────────────────────────┐
│                 GitHub Actions Runner                │
│                   (ubuntu-latest)                    │
│                                                     │
│  Bước 1: Checkout code                              │
│      ↓                                              │
│  Bước 2: Setup Node.js 22                           │
│      ↓                                              │
│  Bước 3: Setup PHP 8.3 + extensions                 │
│      ↓                                              │
│  Bước 4: composer install                           │
│      ↓                                              │
│  Bước 5: npm ci                                     │
│      ↓                                              │
│  Bước 6: playwright install chromium                │
│      ↓                                              │
│  Bước 7: Chuẩn bị Laravel (DB, migrate, build)     │
│      ↓                                              │
│  Bước 8: npx playwright test                        │
│      ↓                                              │
│  Bước 9: Upload report (dù pass hay fail)           │
└─────────────────────────────────────────────────────┘
```

---

## 3. Chi tiết từng bước

### Bước 1–3: Cài môi trường

```yaml
- uses: actions/checkout@v4           # clone code về runner

- uses: actions/setup-node@v4         # cài Node.js 22
  with:
    node-version: 22
    cache: npm                         # cache node_modules → lần sau nhanh hơn

- uses: shivammathur/setup-php@v2     # cài PHP 8.3
  with:
    php-version: '8.3'
    extensions: mbstring, dom, pdo_sqlite, sqlite3
    # ↑ sqlite3: dùng SQLite thay MySQL/Postgres → không cần setup DB server
```

### Bước 4–6: Cài dependencies

```yaml
- run: composer install --no-interaction --prefer-dist
  # --no-interaction: không hỏi gì, tự chạy
  # --prefer-dist:    tải bản nén, nhanh hơn clone git

- run: npm ci
  # ci (clean install): dùng package-lock.json, không update lock file
  # → đảm bảo CI dùng đúng version như local

- run: npx playwright install --with-deps chromium
  # --with-deps: cài cả system dependencies (libglib, fonts...)
  # chỉ cài chromium, không cài firefox/webkit → tiết kiệm thời gian
```

### Bước 7: Chuẩn bị Laravel

```yaml
- run: |
    cp .env.example .env              # copy config mẫu
    mkdir -p database
    touch database/database.sqlite    # tạo file SQLite rỗng
    php artisan key:generate          # generate APP_KEY
    php artisan migrate --force       # chạy migration (--force bỏ qua confirm)
    php artisan config:clear          # clear config cache
```

> ⚠️ **Quan trọng:** CI dùng **SQLite** thay vì MySQL. Nhanh hơn vì không cần start database server. File DB là 1 file đơn giản trong repo.

### Bước 8: Chạy tests

```yaml
- run: npx playwright test
  env:
    CI: true                           # ← kích hoạt chế độ CI
    APP_URL: http://127.0.0.1:8000
    PLAYWRIGHT_BASE_URL: http://127.0.0.1:8000
    PHP_EXECUTABLE: php                # dùng php từ PATH (đã setup ở bước 3)
```

**Biến `CI: true` ảnh hưởng gì trong `playwright.config.ts`:**

```typescript
// playwright.config.ts
forbidOnly: !!process.env.CI,    // CI=true → cấm dùng test.only (tránh quên)
retries: process.env.CI ? 2 : 0, // CI=true → retry 2 lần nếu fail (tránh flaky)
workers: process.env.CI ? 1 : undefined, // CI=true → chạy tuần tự, không song song
headless: !!process.env.CI,      // CI=true → chạy không mở browser (không có màn hình)
```

**Playwright tự động boot Laravel server:**

```typescript
// playwright.config.ts
webServer: {
  command: `"${phpExecutable}" artisan serve --host=127.0.0.1 --port=8000`,
  port: 8000,
  reuseExistingServer: !process.env.CI,
  // CI=true → reuseExistingServer=false → luôn boot server mới
  timeout: 120 * 1000,
}
```

> → Trong CI, Playwright tự start `php artisan serve` trước khi chạy test, không cần step riêng.

### Bước 9: Upload artifacts

```yaml
- name: Upload Playwright report
  if: always()                         # ← chạy DÙ test pass hay fail
  uses: actions/upload-artifact@v4
  with:
    name: playwright-report
    path: playwright-report/           # HTML report

- name: Upload Playwright test results
  if: always()
  with:
    name: playwright-test-results
    path: test-results/                # screenshots + videos + traces khi fail
    if-no-files-found: ignore          # không lỗi nếu không có file (tất cả pass)
```

> → Sau khi CI chạy xong, vào tab **Actions → chọn run → Artifacts** để tải report về xem.

---

## 4. So sánh CI vs Local

| | Local | CI (GitHub Actions) |
|---|---|---|
| **OS** | Windows | Ubuntu |
| **Database** | MySQL (Laragon) | SQLite (file) |
| **Browser mode** | Headed (thấy UI) | Headless (không màn hình) |
| **Workers** | Song song (nhiều tab) | Tuần tự (1 worker) |
| **Retry khi fail** | 0 lần | 2 lần |
| **Server** | Reuse nếu đang chạy | Luôn boot mới |
| **PHP** | `C:/laragon/bin/php/...` | `php` từ PATH |

---

## 5. Khi nào CI fail?

```
1. Test fail thật (bug trong code)
   → Xem HTML report trong Artifacts để biết test nào fail, lý do gì

2. Môi trường setup fail
   → composer install / npm ci / migrate lỗi
   → Xem log từng step trong GitHub Actions

3. Flaky test (test không ổn định)
   → CI retry 2 lần tự động
   → Nếu vẫn fail sau 3 lần → cần xem lại timing/race condition trong test

4. PHP artisan serve không khởi động kịp
   → timeout: 120 * 1000 (2 phút) để chờ server boot
```

---

## 6. Cách xem kết quả trên GitHub

```
GitHub repo
  └── Actions tab
        └── Playwright Tests (workflow name)
              └── [tên commit/PR]
                    ├── Jobs: test ✅/❌
                    │     └── Steps: xem chi tiết từng bước
                    └── Artifacts:
                          ├── playwright-report/     ← mở index.html để xem
                          └── playwright-test-results/ ← screenshots/videos khi fail
```
