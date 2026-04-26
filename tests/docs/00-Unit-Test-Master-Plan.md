# 🧪 Unit Test Master Plan — FlashMind Project

> **Role:** Senior Developer + QA Engineer
> **Framework:** Laravel 13 + PHPUnit 12
> **Workflow:** TEST → FAIL → FIX → REFACTOR → PASS
> **Pattern:** AAA (Arrange → Act → Assert) + given_when_then naming

---

## 📊 Phân Tích Hiện Trạng

| Layer | Hiện tại | Cần có |
|---|---|---|
| **Unit Tests** | 0 test thật (chỉ ExampleTest) | ~40 test cases |
| **Feature Tests** | 2 tests (ImportController) | ~22 test cases |

---

## 🏗️ Kiến Trúc Test

```
tests/
├── Unit/
│   ├── Services/
│   │   ├── Study/
│   │   │   ├── AnswerCheckingServiceTest.php      ← Priority 1
│   │   │   ├── StudySchedulerServiceTest.php       ← Priority 1
│   │   │   ├── StudySessionServiceTest.php         ← Priority 2
│   │   │   └── StudyRatingServiceTest.php          ← Priority 3
│   │   ├── Import/
│   │   │   └── TxtImportParserServiceTest.php      ← Priority 1
│   │   └── DashboardStatsServiceTest.php           ← Priority 3
│   ├── 00-Unit-Test-Master-Plan.md                 ← Tài liệu này
│   └── ExampleTest.php
├── Feature/
│   ├── Api/
│   │   ├── DeckControllerTest.php                  ← Priority 2
│   │   ├── CardControllerTest.php                  ← Priority 2
│   │   ├── StudySessionControllerTest.php          ← Priority 2
│   │   └── DashboardStatsControllerTest.php        ← Priority 3
│   ├── ImportControllerTest.php (đã có ✅)
│   └── ExampleTest.php
└── TestCase.php
```

---

## 📋 Thứ Tự Ưu Tiên

### 🔴 Priority 1 — Core Business Logic (Tuần 1)

> Logic thuần túy, KHÔNG cần DB, test nhanh, giá trị cao nhất.

---

### FILE 1: `AnswerCheckingServiceTest.php`

**Source:** `app/Services/Study/AnswerCheckingService.php` (36 dòng)

**Phân tích code:**
- `check()` — So sánh đáp án: exact → close_match (≥85%) → incorrect
- `normalize()` — Lowercase, strip special chars, collapse whitespace

**Edge cases:** Unicode, khoảng trắng thừa, case sensitivity, empty string
**Khả năng bug:** `similar_text()` xử lý Unicode không nhất quán

| # | Test Case | Type | Expected |
|---|---|---|---|
| 1 | `given_exact_match_when_check_then_correct` | Normal | correct |
| 2 | `given_case_diff_when_check_then_correct` | Normal | correct |
| 3 | `given_extra_whitespace_when_check_then_correct` | Edge | correct |
| 4 | `given_special_chars_when_check_then_correct` | Edge | correct |
| 5 | `given_similar_answer_when_check_then_close_match` | Normal | close_match |
| 6 | `given_very_different_when_check_then_incorrect` | Normal | incorrect |
| 7 | `given_empty_strings_when_check_then_correct` | Edge | correct |
| 8 | `given_unicode_japanese_when_check_then_correct` | Edge | correct |
| 9 | `given_mixed_case_unicode_when_normalize_then_lowered` | Edge | lowercased |
| 10 | `given_multiple_spaces_when_normalize_then_single_space` | Edge | collapsed |

---

### FILE 2: `StudySchedulerServiceTest.php`

**Source:** `app/Services/Study/StudySchedulerService.php` (327 dòng)

**Phân tích code:**
- State machine: `new → learning → review ⇄ relearning`
- FSRS: stability × factor, difficulty ± delta, due_at calculation
- Learning steps default: `[1, 10]` minutes
- Graduation: learning → review khi hết steps hoặc "easy"

**Khả năng bug:**
- `adjustStability('again')` khi `stability = 0` → clamp 0.1
- `scheduleReview('hard')` khi `scheduled_days = 0` → baseDays = 1

**A. New Card (4 cases):**

| # | Test Case | Expected |
|---|---|---|
| 1 | `given_new_when_again_then_learning_step0` | state=learning, due +1min |
| 2 | `given_new_when_hard_then_learning_step0` | state=learning, due +1min |
| 3 | `given_new_when_good_then_learning_step0` | state=learning, due +1min |
| 4 | `given_new_when_easy_then_review_4days` | state=review, due +4days |

**B. Learning Card (4 cases):**

| # | Test Case | Expected |
|---|---|---|
| 5 | `given_learning_step0_when_again_then_reset_step0` | step=0, due +1min |
| 6 | `given_learning_step0_when_good_then_advance_step1` | step=1, due +10min |
| 7 | `given_learning_last_step_when_good_then_graduates` | state=review |
| 8 | `given_learning_when_easy_then_graduates_4days` | state=review, +4days |

**C. Review Card (4 cases):**

| # | Test Case | Expected |
|---|---|---|
| 9 | `given_review_when_again_then_relearning` | state=relearning, lapses+1 |
| 10 | `given_review_10d_when_hard_then_12days` | ceil(10×1.2) = 12 |
| 11 | `given_review_10d_when_good_then_20days` | ceil(10×2.0) = 20 |
| 12 | `given_review_10d_when_easy_then_30days` | ceil(10×3.0) = 30 |

**D. Edge Cases (4 cases):**

| # | Test Case | Expected |
|---|---|---|
| 13 | `given_invalid_rating_then_throws` | InvalidArgumentException |
| 14 | `given_invalid_state_then_throws` | InvalidArgumentException |
| 15 | `given_review_0days_when_hard_then_min_1day` | baseDays clamped to 1 |
| 16 | `given_empty_steps_then_uses_defaults` | fallback [1,10] |

**E. Stability / Difficulty (2 cases):**

| # | Test Case | Expected |
|---|---|---|
| 17 | `given_stability_0_when_again_then_clamp_0_1` | max(0.1, 0×0.6)=0.1 |
| 18 | `given_difficulty_10_when_again_then_clamp_10` | min(10, 10+0.5)=10 |

---

### FILE 3: `TxtImportParserServiceTest.php`

**Source:** `app/Services/Import/TxtImportParserService.php` (161 dòng)

**Phân tích code:**
- `parseContent()` — Split lines, skip empty/#comments
- `parseLine()` — Tab-split, filter audio/img/tags, validate ≥2 fields
- `toPlainText()` — Strip HTML, decode entities, normalize spaces
- `normalizeForDuplicate()` — Lowercase + strip special chars

| # | Test Case | Type |
|---|---|---|
| 1 | `given_valid_tab_line_then_extracts_front_back` | Normal |
| 2 | `given_3_fields_then_back_joins_remaining` | Normal |
| 3 | `given_single_field_then_invalid` | Edge |
| 4 | `given_empty_line_then_skipped` | Edge |
| 5 | `given_comment_line_then_skipped` | Edge |
| 6 | `given_sound_token_then_extracts_audio` | Normal |
| 7 | `given_img_tag_then_filtered_out` | Normal |
| 8 | `given_tag_field_then_extracted` | Normal |
| 9 | `given_html_when_toPlainText_then_stripped` | Normal |
| 10 | `given_nbsp_when_toPlainText_then_space` | Edge |
| 11 | `given_entities_when_toPlainText_then_decoded` | Edge |
| 12 | `given_mixed_when_normalize_then_clean` | Normal |

---

### 🟡 Priority 2 — Feature Tests (Tuần 2)

> Cần DB (RefreshDatabase), test API endpoints + service integration.

### FILE 4: `StudySessionServiceTest.php`

| # | Test Case |
|---|---|
| 1 | `given_due_cards_when_build_then_relearning_first` |
| 2 | `given_no_due_when_build_then_picks_new` |
| 3 | `given_no_cards_when_build_then_null` |
| 4 | `given_suspended_when_build_then_excluded` |
| 5 | `given_invalid_mode_when_build_then_defaults_flip` |
| 6 | `given_deck_id_when_build_then_filters` |

### FILE 5: `DeckControllerTest.php`

| # | Test Case |
|---|---|
| 1 | `given_valid_data_when_post_then_201` |
| 2 | `given_no_name_when_post_then_422` |
| 3 | `given_deck_when_put_then_updates` |
| 4 | `given_deck_when_delete_then_cascade` |
| 5 | `given_decks_when_get_then_list` |

### FILE 6: `CardControllerTest.php`

| # | Test Case |
|---|---|
| 1 | `given_valid_data_when_post_then_creates` |
| 2 | `given_card_when_put_then_updates_note` |
| 3 | `given_ids_when_bulk_delete_then_removes` |
| 4 | `given_all_flag_when_bulk_delete_then_excludes` |

---

### 🟢 Priority 3 — Nice-to-have (Tuần 3)

### FILE 7: `StudyRatingServiceTest.php`

| # | Test Case |
|---|---|
| 1 | `given_new_card_when_rate_then_state_updates` |
| 2 | `given_review_when_again_then_lapses_up` |
| 3 | `given_rate_then_log_created_and_session_returned` |

### FILE 8: `DashboardStatsServiceTest.php`

| # | Test Case |
|---|---|
| 1 | `given_consecutive_reviews_then_streak_counts` |
| 2 | `given_gap_then_streak_stops` |
| 3 | `given_no_reviews_then_streak_0` |
| 4 | `given_month_reviews_then_counts_unique` |

---

## 📊 Tổng Kết

| File | Type | Cases | Priority | DB? |
|---|---|---|---|---|
| AnswerCheckingServiceTest | Unit | 10 | 🔴 P1 | ❌ |
| StudySchedulerServiceTest | Unit | 18 | 🔴 P1 | ❌ |
| TxtImportParserServiceTest | Unit | 12 | 🔴 P1 | ❌ |
| StudySessionServiceTest | Feature | 6 | 🟡 P2 | ✅ |
| DeckControllerTest | Feature | 5 | 🟡 P2 | ✅ |
| CardControllerTest | Feature | 4 | 🟡 P2 | ✅ |
| StudyRatingServiceTest | Feature | 3 | 🟢 P3 | ✅ |
| DashboardStatsServiceTest | Feature | 4 | 🟢 P3 | ✅ |
| **TỔNG** | | **62** | | |

---

## 🔄 Workflow: TEST → FAIL → FIX → REFACTOR → PASS

```
Bước 1: VIẾT TEST (RED)
  → php artisan test --filter=AnswerCheckingServiceTest
  → Một số test FAIL ← Expected!

Bước 2: PHÂN TÍCH FAIL
  → Xác định: bug code hay test sai?

Bước 3: FIX CODE (GREEN)
  → Sửa source code, KHÔNG sửa test

Bước 4: REFACTOR (BLUE)
  → Clean code, không đổi behavior
  → Chạy lại → tất cả PASS

Bước 5: COMMIT
  → git commit -m "test: add unit tests for [ServiceName]"
```

---

## 🚀 Bắt đầu: `AnswerCheckingServiceTest` — 30 phút, 10 cases, không cần DB.
