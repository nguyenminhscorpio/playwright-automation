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

**URL trực tiếp của dự án:**

```
# Danh sách tất cả CI runs
https://github.com/nguyenminhscorpio/playwright-automation/actions

# Chỉ xem workflow Playwright Tests
https://github.com/nguyenminhscorpio/playwright-automation/actions/workflows/playwright.yml
```

**Điều hướng trên GitHub:**

```
Bước 1: Vào tab "Actions" (thanh menu trên cùng của repo)
         → Thấy danh sách các lần chạy, mỗi dòng là 1 commit/PR

Bước 2: Click vào 1 run (dòng gần nhất)
         → Thấy trang "Summary" của run đó

Bước 3: Trang Summary có 2 vùng quan trọng:
   ┌─────────────────────────────────────────────────┐
   │  Jobs (phần trên)                               │
   │  └── test ✅ / ❌                               │
   │       → Click vào để thấy log từng step         │
   │                                                 │
   │  Artifacts (phần dưới cùng trang)               │
   │  ├── playwright-report        [Download ⬇]      │
   │  └── playwright-test-results  [Download ⬇]      │
   └─────────────────────────────────────────────────┘

Bước 4: Download artifact → giải nén → mở file:
   playwright-report/index.html        → HTML report đẹp, có filter
   test-results/.../test-failed-1.png  → screenshot lúc fail
   test-results/.../video.webm         → video recording
   test-results/.../trace.zip          → mở bằng: npx playwright show-trace trace.zip
```

---

## 7. Đọc Artifacts sau khi download

```
playwright-report.zip (giải nén)
  └── index.html          ← mở bằng trình duyệt
       ├── Filter: All / Passed / Failed / Flaky / Skipped
       ├── Mỗi test: tên, thời gian chạy, status
       └── Click test fail → thấy:
            ├── Error message + stack trace
            ├── Screenshot (nếu có)
            └── Nút "Trace" để xem từng bước

playwright-test-results.zip (giải nén)
  └── [tên-test-fail]-chromium/
       ├── test-failed-1.png    ← screenshot lúc fail
       ├── video.webm           ← video toàn bộ test
       ├── trace.zip            ← mở bằng npx playwright show-trace
       └── error-context.md     ← mô tả lỗi dạng text
```

**Xem trace locally:**
```bash
npx playwright show-trace test-results/[folder]/trace.zip
# → Mở Playwright Trace Viewer trong browser
# → Thấy từng action, network request, DOM snapshot tại thời điểm fail
```

---

## 8. Full YAML với annotation đầy đủ

```yaml
name: Playwright Tests      # Tên hiển thị trên tab Actions

on:
  push:                     # Chạy khi push lên BẤT KỲ branch
  pull_request:             # Chạy khi tạo/update PR

jobs:
  test:
    runs-on: ubuntu-latest  # Máy ảo GitHub cung cấp miễn phí (Linux)

    steps:
      # ── BƯỚC 1: Lấy code về ───────────────────────────────────────────
      - name: Checkout repository
        uses: actions/checkout@v4

      # ── BƯỚC 2: Cài Node.js ──────────────────────────────────────────
      - name: Setup Node.js
        uses: actions/setup-node@v4
        with:
          node-version: 22
          cache: npm          # Cache ~/.npm → lần chạy sau skip download

      # ── BƯỚC 3: Cài PHP ──────────────────────────────────────────────
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.3'
          extensions: mbstring, dom, pdo_sqlite, sqlite3
          # pdo_sqlite + sqlite3: để Laravel dùng SQLite thay MySQL
          coverage: none      # Không đo code coverage → nhanh hơn

      # ── BƯỚC 4: Cài PHP packages ─────────────────────────────────────
      - name: Install Composer dependencies
        run: composer install --no-interaction --prefer-dist
        # --no-interaction: không hỏi gì cả
        # --prefer-dist: tải zip thay vì clone git → nhanh hơn

      # ── BƯỚC 5: Cài JS packages ──────────────────────────────────────
      - name: Install Node dependencies
        run: npm ci
        # ci = clean install: dùng đúng package-lock.json
        # Khác npm install: không tự update lock file

      # ── BƯỚC 6: Cài Chromium cho Playwright ──────────────────────────
      - name: Install Playwright browsers
        run: npx playwright install --with-deps chromium
        # --with-deps: cài thêm system libs (fonts, libglib...)
        # Chỉ chromium: tiết kiệm ~500MB so với cài cả 3 browser

      # ── BƯỚC 7: Chuẩn bị Laravel ─────────────────────────────────────
      - name: Prepare Laravel environment
        run: |
          cp .env.example .env
          mkdir -p database
          touch database/database.sqlite   # Tạo file DB rỗng
          php artisan key:generate         # Generate APP_KEY vào .env
          php artisan migrate --force      # --force: bỏ qua confirm production
          php artisan config:clear         # Clear cache cũ

      # ── BƯỚC 8: Build JS/CSS ──────────────────────────────────────────
      - name: Build frontend assets
        run: npm run build
        # Vite build → tạo public/build/ để Laravel phục vụ

      # ── BƯỚC 9: CHẠY TESTS ───────────────────────────────────────────
      - name: Run Playwright tests
        env:
          CI: true                         # Kích hoạt CI mode trong playwright.config.ts
          APP_URL: http://127.0.0.1:8000
          PLAYWRIGHT_BASE_URL: http://127.0.0.1:8000
          PHP_EXECUTABLE: php              # Playwright dùng để boot 'php artisan serve'
        run: npx playwright test
        # Playwright tự boot: php artisan serve --port=8000
        # Rồi mới chạy tất cả test files trong tests/e2e/

      # ── BƯỚC 10: Lưu HTML Report ──────────────────────────────────────
      - name: Upload Playwright report
        if: always()                       # Chạy DÙ step trước pass hay fail
        uses: actions/upload-artifact@v4
        with:
          name: playwright-report          # Tên file zip trên GitHub
          path: playwright-report/         # Thư mục Playwright tạo ra
          if-no-files-found: ignore        # Không báo lỗi nếu không có

      # ── BƯỚC 11: Lưu Screenshots/Videos khi fail ──────────────────────
      - name: Upload Playwright test results
        if: always()
        uses: actions/upload-artifact@v4
        with:
          name: playwright-test-results
          path: test-results/              # Chứa .png, .webm, trace.zip khi fail
          if-no-files-found: ignore
```

---

## 9. Logic quyết định CI pass hay fail

```
npx playwright test chạy xong
    │
    ├── TẤT CẢ test pass → exit code 0 → CI ✅ PASS
    │
    └── CÓ test fail
          ├── Retry lần 1 (tự động, CI=true retries: 2)
          │     ├── Pass → CI ✅ PASS (đánh dấu "Flaky")
          │     └── Fail → Retry lần 2
          │                 ├── Pass → CI ✅ PASS (đánh dấu "Flaky")
          │                 └── Fail → exit code 1 → CI ❌ FAIL
          │
          └── PR bị block merge (nếu có branch protection rule)
```
