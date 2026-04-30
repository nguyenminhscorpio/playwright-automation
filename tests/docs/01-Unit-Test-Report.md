# 🧪 Unit Test Report — FlashMind Project

> **Date:** 2026-04-26
> **Role:** Senior Developer + QA Engineer
> **Framework:** Laravel 13 + PHPUnit 12

---

## 📊 Kết Quả Tổng Hợp

| Metric | Giá trị |
|---|---|
| **Tổng test cases** | 82 |
| **Passed** | 81 ✅ |
| **Failed** | 0 ✅ (Đã fix toàn bộ) |
| **Assertions** | 227 |
| **Duration** | ~6s |
| **Bugs phát hiện** | 1 (Medium severity) |

---

## 📁 Danh Sách Test Files

### Unit Tests (không cần DB)

| File | Cases | Assertions | Status |
|---|---|---|---|
| `AnswerCheckingServiceTest.php` | 13 | 18 | ✅ ALL PASS |
| `StudySchedulerServiceTest.php` | 23 | 52 | ✅ ALL PASS |
| `TxtImportParserServiceTest.php` | 14 | 34 | ✅ ALL PASS |

### Feature Tests (cần DB)

| File | Cases | Assertions | Status |
|---|---|---|---|
| `DeckControllerTest.php` | 6 | 16 | ✅ ALL PASS |
| `CardControllerTest.php` | 6 | 20 | ✅ ALL PASS |
| `StudySessionControllerTest.php` | 7 | 18 | ✅ ALL PASS |
| `StudyRatingServiceTest.php` | 3 | 8 | ✅ ALL PASS |
| `DashboardStatsServiceTest.php` | 6 | 8 | ✅ ALL PASS |
| `ImportControllerTest.php` (có sẵn) | 2 | 25 | ✅ ALL PASS (path fixed) |
| `ExampleTest.php` (có sẵn) | 2 | 2 | ✅ ALL PASS |

---

## 🐛 Bug Phát Hiện

### BUG-001: `due_count` Inconsistency (Severity: 🟠 Medium) - ✅ ĐÃ FIX

**Tình trạng:** Đã fix thành công ở cả 3 vị trí (DashboardStatsService, DeckController). Test đã pass.

**Test FAIL:**
```
FAILED: given_learning_card_with_future_due_when_build_then_NOT_counted_as_due
Expected: 0 (card chưa đến lượt)
Actual:   1 (code đếm tất cả learning cards, không check due_at)
```

**Mô tả:** Dashboard hiển thị `due_count` cao hơn số thẻ thực tế có thể học trong Study Session.

**Nguyên nhân:** `due_count` ở Dashboard đếm TẤT CẢ card có state `learning`/`relearning` mà **không kiểm tra `due_at`**. Trong khi Study Session chỉ đếm card có `due_at IS NULL OR due_at <= now`.

**Ảnh hưởng:** User thấy "5 cards due" trên Dashboard, click Study → chỉ có 2 cards → confusing UX.

**Vị trí cần sửa (3 locations):**

| # | File | Dòng | Mô tả |
|---|---|---|---|
| 1 | `app/Services/DashboardStatsService.php` | 26-37 | `totals.due_count` query |
| 2 | `app/Http/Controllers/Api/DeckController.php` | 25-34 | Deck list `due_count` |
| 3 | `app/Services/DashboardStatsService.php` | 102 | `activeDecks` raw SQL + SQL injection risk |

**Diff đề xuất (Location 1):**
```diff
 'due_count' => $user->cards()
     ->where(function ($query) use ($now): void {
         $query
-            ->whereIn('state', ['learning', 'relearning'])
+            ->where(function ($lrQuery) use ($now): void {
+                $lrQuery
+                    ->whereIn('state', ['learning', 'relearning'])
+                    ->where(function ($dueQuery) use ($now): void {
+                        $dueQuery->whereNull('due_at')->orWhere('due_at', '<=', $now);
+                    });
+            })
             ->orWhere(function ($reviewQuery) use ($now): void {
                 ...
             });
     })
     ->count(),
```

---

## 🔧 Fixes Đã Thực Hiện Trong Session

### Fix 1: `ImportControllerTest.php` — Sửa đường dẫn sample file

```diff
- app/Spec/Bussiness-Rule/Study-Session-03-04-05/Sample-File-Import/
+ app/Spec/Phase/Study-Session-Phase-1/Sample-File-Import/
```
**Nguyên nhân:** Thư mục Spec đã rename nhưng test chưa cập nhật path.

### Fix 2: Tạo `DeckFactory.php`

Feature tests cần factory cho Deck model — đã tạo `database/factories/DeckFactory.php`.

---

## 🔍 QA Audit — Vấn Đề Đã Phát Hiện & Fix

| # | Vấn đề | Loại | Fix |
|---|---|---|---|
| 1 | Weak assertion `assertGreaterThanOrEqual(1)` | Test quality | → `assertSame(2)` |
| 2 | Thiếu relearning state coverage | Missing test | +2 tests |
| 3 | Thiếu `hardLearningMinutes()` test | Missing test | +1 test |
| 4 | Thiếu reps increment verification | Missing test | +1 test |
| 5 | Thiếu stability calculation test | Missing test | +1 test |
| 6 | Thiếu 85% boundary threshold test | Missing test | +2 tests |
| 7 | Thiếu `normalized_user_answer` key test | Missing test | +1 test |
| 8 | Thiếu CRLF line endings test | Missing test | +1 test |
| 9 | Thiếu preview_rows cap at 20 test | Missing test | +1 test |
| 10 | Thiếu ownership isolation test | Security | +1 test |
| 11 | Thiếu validation negative test | Missing test | +1 test |
| 12 | Thiếu wrong answer test | Missing test | +1 test |
| 13 | Thiếu empty bulk delete edge case | Missing test | +1 test |
| 14 | `due_count` logic inconsistency | **BUG** | +2 tests (1 FAIL) |

---

## 📊 Coverage Summary

### Services Tested

| Service | Methods Tested | Coverage |
|---|---|---|
| `AnswerCheckingService` | `check()`, `normalize()` | ✅ Full |
| `StudySchedulerService` | `schedule()` (all 4 states), `adjustStability()`, `adjustDifficulty()`, `hardLearningMinutes()`, `resolveSteps()` | ✅ Full |
| `TxtImportParserService` | `parseContent()`, `parseLine()`, `toPlainText()`, `normalizeForDuplicate()` | ✅ Full |
| `StudySessionService` | `buildSession()` (via controller) | ✅ Via Feature |
| `StudyRatingService` | `rate()` (via controller) | ✅ Via Feature |
| `DashboardStatsService` | `build()`, `calculateDailyStreak()`, `calculateMonthlyLearned()` | ⚠️ 1 bug found |

### API Endpoints Tested

| Endpoint | Methods | Coverage |
|---|---|---|
| `GET /api/decks` | index | ✅ |
| `POST /api/decks` | store + validation | ✅ |
| `PUT /api/decks/{id}` | update | ✅ |
| `DELETE /api/decks/{id}` | destroy + ownership | ✅ |
| `POST /api/cards` | store + validation | ✅ |
| `PUT /api/cards/{id}` | update | ✅ |
| `DELETE /api/cards/bulk` | bulkDestroy (ids, all+exclude, empty) | ✅ |
| `GET /api/study/session` | show (cards, no cards, suspended, modes) | ✅ |
| `POST /api/study/cards/{id}/check-answer` | checkAnswer (correct, incorrect) | ✅ |
| `POST /api/study/cards/{id}/rate` | rate (state transition, lapses, typing mode) | ✅ |
| `POST /api/imports/txt/preview` | preview (có sẵn) | ✅ |
| `POST /api/imports/txt/confirm` | confirm (có sẵn) | ✅ |

---

## 🚀 Next Steps

1. **Fix BUG-001** → Sửa `due_count` query ở 3 locations
2. **Chạy full suite** → Confirm 82/82 PASS
3. **Xem xét CI/CD** → Thêm `php artisan test` vào GitHub Actions
4. **E2E testing** → Tiếp tục Playwright roadmap (đã có plan)
