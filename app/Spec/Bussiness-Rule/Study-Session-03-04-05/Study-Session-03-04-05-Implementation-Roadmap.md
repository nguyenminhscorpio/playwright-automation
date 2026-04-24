# Roadmap Implement Study Session 03-04-05, Import TXT Anki, FSRS

## 1. Implementation Scope

### Chức năng cần implement trong phase này
- Màn `03 - Study Session Flip Front`
- Màn `04 - Study Session Typing Input`
- Màn `05 - Study Session Answer Revealed`
- Session flow theo `mode` ở cấp phiên học: `flip` / `typing`
- API cho study session:
  - `GET /api/study/session`
  - `POST /api/study/cards/{id}/check-answer`
  - `POST /api/study/cards/{id}/rate`
  - `POST /api/study/cards/{id}/play-tts` nếu cần cho icon TTS hiện tại
- Import TXT Anki:
  - upload
  - preview
  - parse
  - validate dòng
  - confirm import
  - save `notes/cards`
  - lưu `import_jobs`, `import_job_rows`
  - xử lý duplicate ở mức phase hiện tại
- FSRS/Anki-like scheduling:
  - `new -> learning -> review -> relearning`
  - learning steps
  - relearning steps
  - review interval
  - review log
  - chọn card tiếp theo trong session
- Answer checking cho typing mode:
  - normalize text
  - so sánh với `back_plain_text`
  - trả `correct / close_match / incorrect` ở mức hỗ trợ
- Progress/session state:
  - tổng card
  - số card đã xử lý
  - current card
  - next card
  - trạng thái card

### Chức năng chưa làm ở phase này
- Không làm lại auth, profile, deck CRUD, card CRUD nếu chưa cần để chạy flow 03/04/05/import
- Không làm dashboard/streak/analytics hoàn chỉnh
- Không làm sync đa thiết bị
- Không làm notification
- Không làm handwriting
- Không làm import nâng cao cho audio/image/tag/media thật
- Không làm parser cho schema import khác ngoài TXT Anki theo 2 file mẫu
- Không làm full FSRS optimizer/phức tạp; chỉ làm theo spec `FSRS đơn giản + Anki-like phase`
- Không viết test case ở giai đoạn này

### Dependency giữa 03 / 04 / 05 / import / FSRS
- `Import TXT` tạo `notes/cards` đầu vào cho toàn bộ study flow
- `Database schema` là nền cho import + study + log + scheduling
- `GET /api/study/session` cấp dữ liệu cho màn `03` và `04`
- `03` và `04` chỉ là bước thu nhận hành vi trước khi reveal, chưa update lịch
- `05` là điểm commit nghiệp vụ:
  - gọi `check-answer` nếu mode typing
  - gọi `rate`
  - cập nhật state/due/log
- `FSRS/scheduler` phụ thuộc `cards` + `review_logs`
- `Session card ordering` phụ thuộc state `relearning > review > learning > new`

## 2. Implementation Order

1. Chốt `domain model + database schema` trước.
   Vì import, session, rate, review log đều dùng chung model `notes/cards/review_logs/import_jobs`.

2. Làm `repository/service layer` trước API/UI.
   Vì màn 03/04/05 và import đều cần cùng một nguồn nghiệp vụ, tránh nhét logic vào controller.

3. Làm `import parser + preview` trước `confirm import`.
   Vì parser là lõi, preview giúp khóa format/rule sớm và giảm sai logic save.

4. Làm `study session query + session selector` trước UI core.
   Vì 03/04 cần biết card nào đang học, state nào, progress ra sao.

5. Làm `màn 03` và `màn 04` trước, nhưng chỉ ở mức render + hành vi điều hướng.
   Vì 2 màn này nhẹ hơn, chưa commit scheduling.

6. Làm `màn 05 + rating API` sau khi scheduler đã rõ contract.
   Vì đây là điểm thay đổi state thực sự.

7. Tách `scheduler engine` thành module riêng rồi mới tích hợp vào `rate`.
   Vì nếu nhúng thẳng vào controller rất dễ sai phase `learning/review/relearning`.

8. Hoàn tất `confirm import + transaction + duplicate handling` sau preview ổn định.
   Vì save DB cần bám kết quả parser/validation đã khóa.

9. Cuối cùng mới làm `error handling + logging + polish`.
   Vì cần dựa trên luồng hoàn chỉnh để biết chỗ nào cần bắt lỗi và hiển thị UX.

## 3. Phase Roadmap

| Phase | Mục tiêu | Task implement | File/spec tham chiếu | Output cần đạt | Dependency | Priority |
|---|---|---|---|---|---|---|
| 1 | Project structure + routing | Tạo module route/controller/service cho `study`, `imports`, `scheduler` | `README.md`, `Functional-Spec.md`, `HTML-Feature-Mapping.md` | Khung code rõ boundary | None | P0 |
| 2 | Database schema + repository/service layer | Tạo bảng/model/repository cho `notes`, `cards`, `review_logs`, `import_jobs`, `import_job_rows` | `Database-Spec.md` | DB layer chạy được | Phase 1 | P0 |
| 3 | API implementation core | Dựng API import + study session + rate/check-answer | `API-Spec.md` | API contract khớp spec | Phase 2 | P0 |
| 4 | Màn 03 core | Render `Flip Front`, progress, TTS trigger, `Show Answer` | `03-...md`, `Screen-Design-Spec.md` | UI 03 hoạt động | Phase 3 | P1 |
| 5 | Màn 04 core | Render `Typing Input`, giữ `user_answer`, `Check Answer` | `04-...md`, `Functional-Spec.md` | UI 04 hoạt động | Phase 3 | P1 |
| 6 | Màn 05 core | Render answer theo mode, panel rating | `05-...md` | UI 05 hoạt động | Phase 4,5 | P1 |
| 7 | Import TXT parser | Đọc file, bỏ header, tách line/tab, clean text, map front/back | `Import-Anki-TXT-Spec.md`, sample files | Parser service chuẩn | Phase 2 | P0 |
| 8 | Import preview + validation | Preview rows, invalid rows, lỗi dòng, thống kê | `Import-Anki-TXT-Spec.md`, `API-Spec.md` | Preview API/UI dùng được | Phase 7 | P0 |
| 9 | Confirm import + save DB | Transaction save notes/cards/import logs, duplicate handling | `Database-Spec.md`, `API-Spec.md` | Import hoàn chỉnh | Phase 8 | P0 |
| 10 | FSRS scheduling logic | Implement phase engine `learning/review/relearning` + due calculator | `FSRS-Logic.md`, `FSRS-So-do-va-Timeline.md` | Scheduler module độc lập | Phase 2 | P0 |
| 11 | Learning/review flow integration | Nối `03/04/05` với `check-answer`, `rate`, chọn next card, update progress | `FSRS-Logic.md`, `API-Spec.md`, screen specs | End-to-end study flow | Phase 4,5,6,10 | P0 |
| 12 | Error handling + logging + UX polish | Message lỗi, loading state, retry guard, audit log, empty state | Tất cả spec liên quan | Flow ổn định, ít sai thao tác | Phase 9,11 | P1 |

## 4. Task Breakdown chi tiết

| Task ID | Task | Mô tả implement | Input | Output | File cần sửa/tạo | Done Definition |
|---|---|---|---|---|---|---|
| P1-01 | Route map | Khai báo route nhóm `study`/`imports` | Screen/API spec | Route list | routes api/web | Có endpoint/page đúng flow |
| P1-02 | Module boundary | Tách `controllers/services/repositories/scheduler/parsers` | Overall spec | Cấu trúc thư mục | app modules | Không còn logic nghiệp vụ nằm rải ở UI/controller |
| P2-01 | Cards schema | Tạo schema `cards` đúng field state/step/due/fsrs | DB spec | Table/model cards | migration/model | CRUD domain hoạt động |
| P2-02 | Notes schema | Tạo schema `notes` cho front/back/source | Import spec | Table/model notes | migration/model | Import save được note |
| P2-03 | Review logs schema | Tạo schema log review | FSRS + DB spec | Table/model review_logs | migration/model | Lưu đủ before/after |
| P2-04 | Import jobs schema | Tạo `import_jobs`, `import_job_rows` | Import/API/DB spec | Table/model import | migration/model | Preview/confirm có chỗ lưu |
| P2-05 | Repository layer | Tạo repository cho cards/imports/review logs | DB schema | Query abstraction | repositories | Service không query trực tiếp |
| P3-01 | Study session API | Implement `GET /api/study/session` | deck/user/mode | session payload | controller/service | Trả current card + progress |
| P3-02 | Check answer API | Implement normalize + compare | user_answer/back_plain_text | judged_result | controller/service | Trả `correct/close/incorrect` |
| P3-03 | Rate API | Nhận rating, gọi scheduler, persist card + log | card state + rating | next state + next card | controller/service | Update atomically |
| P3-04 | Import preview API | Upload txt, parse, validate, preview | txt file | preview summary | controller/parser | Trả counts + preview rows |
| P3-05 | Import confirm API | Confirm import job vào deck | preview/import_job | inserted/skipped/errors | controller/service | Save DB có transaction |
| P4-01 | Screen 03 layout | Render progress/front/state/TTS/CTA | session payload | màn flip | page/component | `Show Answer` sang 05 |
| P4-02 | Mode switch behavior | Topbar switch `flip`/`typing` ở cấp session | current session | mode updated | page/store | Switch đổi đúng flow |
| P5-01 | Screen 04 layout | Render input/hint/check answer | session payload | màn typing | page/component | Vào thẳng 04 khi mode typing |
| P5-02 | Typing draft state | Giữ `user_answer` trước reveal | typed text | temp answer | state/store | Không mất dữ liệu khi sang 05 |
| P6-01 | Screen 05 layout | Render prompt/correct answer/rating panel | reveal payload | màn answer | page/component | Dùng chung cho 2 mode |
| P6-02 | Mode-specific reveal | Flip ẩn `Your Answer`, Typing hiện `Your Answer` | mode + answer | conditional render | page/component | Render đúng theo mode |
| P7-01 | File reader | Đọc file txt, detect line endings/encoding fallback | txt file | raw content | parser service | Đọc được 2 sample files |
| P7-02 | Header skipper | Bỏ dòng bắt đầu `#` | raw lines | data lines | parser | Header không vào data |
| P7-03 | Field parser | Tách line theo tab | data line | raw fields | parser | Parse ổn định |
| P7-04 | Field filtering | Loại `[sound]`, `<img>`, tag, empty | raw fields | text fields | parser | Chỉ còn field text hợp lệ |
| P7-05 | Text cleaner | strip HTML, decode entity, trim spaces | text field | clean text | parser util | Front/back sạch |
| P7-06 | Front/back mapper | field đầu = front, còn lại join = back | clean fields | parsed row | parser | Khớp 2 sample files |
| P8-01 | Row validation | Kiểm tra còn ít nhất 2 text fields | parsed row | valid/invalid | validator | Row lỗi có message |
| P8-02 | Preview assembler | Tính total/data/valid/invalid/sample preview | parsed rows | preview DTO | import service | API preview trả đúng số liệu |
| P8-03 | Error presentation | Chuẩn hóa lỗi theo dòng | invalid rows | ui error list | DTO/UI | User thấy lỗi theo row |
| P9-01 | Duplicate strategy | Quy ước duplicate theo user+deck+normalized front+normalized back | parsed rows + DB | skip/create decision | import service | Không insert lặp ngoài ý muốn |
| P9-02 | Transactional import | Save note/card/import rows trong transaction | confirmed job | DB committed | import service | Lỗi giữa chừng rollback |
| P9-03 | Import history | Lưu `import_jobs` và `import_job_rows` | import result | history records | import service | Truy vết được lần import |
| P10-01 | Scheduler state machine | Implement `new/learning/review/relearning` | card + rating | next state/step | scheduler service | Chạy đúng phase spec |
| P10-02 | Learning/relearning steps | Implement due phút cố định | steps config | next due | scheduler | `1m/10m` và `10m` chạy đúng |
| P10-03 | Review interval calculator | Implement rule `Hard/Good/Easy` đơn giản | scheduled_days + rating | next interval | scheduler | Review tăng interval đúng |
| P10-04 | Card selector | Ưu tiên `relearning > review > learning > new` | due cards | next card | session service | Session pick đúng card |
| P11-01 | Reveal integration | 03/04 sang 05 bằng current card thống nhất | session state | reveal flow | ui/service | Done: dùng `sessionStorage` để giữ current card/reveal payload |
| P11-02 | Rating integration | 05 gọi `rate`, cập nhật card/log/progress/next | rating action | next card payload | ui/api/service | Done: rating xong quay lại flow học và refresh card tiếp theo |
| P11-03 | Check-answer integration | Typing mode gọi `check-answer` trước reveal | typed answer | judged result | ui/api/service | Done: 05 hiển thị `judged_result` + `user_answer` |
| P12-01 | Guard rails | Double-submit guard, loading, empty session | user action | safe UX | UI/service | Done: khóa nút submit/rate, có empty/error state |
| P12-02 | Error logging | Log import error, scheduler error, API error | exceptions | log entries | logger/middleware | Chưa làm: logging chuyên biệt vẫn để phase sau |
| P12-03 | Empty/end state | Hết card, import rỗng, preview toàn lỗi | service result | end-state UI | UI | Done cho study session: có empty/end state ở 03/04/05 |

## 5. Mapping tài liệu -> phần cần implement

| Tài liệu | Dùng để implement phần nào | Ghi chú quan trọng |
|---|---|---|
| `README.md` | Scope tổng cho bộ 03/04/05 + import + API + DB + FSRS | Dùng để khóa phạm vi phase hiện tại |
| `03-Study-Session-Flip-Front-Nghiep-vu.md` | Màn 03 | 03 chỉ xem front, chưa update lịch |
| `04-Study-Session-Typing-Input-Nghiep-vu.md` | Màn 04 | Mode typing vào thẳng màn nhập |
| `05-Study-Session-Answer-Revealed-Nghiep-vu.md` | Màn 05 | 05 là điểm rating và chuyển card |
| `Import-Anki-TXT-Spec.md` | Parser/import preview/confirm | Rule lõi: bỏ mp3/image/tag, field đầu = front |
| `API-Spec.md` | Contract API study/import | `rate` là endpoint commit scheduling |
| `Database-Spec.md` | Schema tables/models | `cards`, `review_logs`, `import_jobs` là trọng tâm |
| `FSRS-Logic.md` | State machine và rule rating | `Learning` chưa dùng FSRS thật; `Review` mới dùng |
| `FSRS-So-do-va-Timeline.md` | Thứ tự card lifecycle, timeline, card priority | Dùng để tránh sai flow chọn card |
| `Sample-File-Import/1000...txt` | Kiểm chứng parser case HTML + sound + text | Front là câu VN, back là câu EN |
| `Sample-File-Import/3000...txt` | Kiểm chứng parser case text + sound + image + metadata | Phải bỏ image/audio/tag nhưng giữ nghĩa |
| `Functional-Spec.md` | Ràng buộc flow session/mode/check-answer | Mode ở cấp session, không phải per-card |
| `Screen-Design-Spec.md` | Mapping layout/hành vi màn 03/04/05 | Dùng để tránh build sai luồng topbar switch |
| `HTML-Feature-Mapping.md` | Mapping HTML/page hiện tại sang chức năng thực | Xác định đúng page/template cần nối |
| `Requirements.md` | Khóa functional/non-functional phase hiện tại | Nhắc responsive, import ổn định, review logs |
| `Roadmap.md` | Kiểm tra hướng triển khai tổng thể | Dùng tham chiếu, không thay roadmap implement này |

## 6. Import TXT Implementation Plan

1. Đọc 2 file mẫu trước để chốt pattern thật.
2. File reader:
   - nhận `.txt`
   - hỗ trợ đọc UTF-8 trước
   - fallback detect encoding nếu nội dung lỗi decode
   - chuẩn hóa line ending
3. Parser:
   - bỏ dòng header bắt đầu bằng `#`
   - split từng dòng theo `\t`
4. Field filtering:
   - bỏ `[sound:...]`
   - bỏ field chỉ chứa `<img ...>`
   - bỏ tag/field rỗng
5. Text cleaning:
   - strip HTML
   - decode entity
   - replace `&nbsp;`
   - trim và normalize space
6. Mapping field:
   - text field đầu tiên => `front_text`
   - các text field còn lại => join thành `back_text`
7. Validation dòng:
   - ít nhất còn `2` text fields hợp lệ
   - row lỗi phải có `row_number`, `raw_content`, `error_message`
8. Preview import:
   - `total_lines`, `data_lines`, `valid_rows`, `invalid_rows`
   - trả một phần `preview_rows`
9. Hiển thị lỗi:
   - lỗi theo dòng
   - tách `invalid_rows`
   - không chặn preview toàn file nếu chỉ một số dòng lỗi
10. Confirm import:
   - xác nhận deck đích
   - dùng snapshot kết quả preview hoặc parse lại cùng checksum
11. Save DB:
   - insert `import_jobs`
   - insert `import_job_rows`
   - insert `notes`
   - insert `cards` khởi tạo `state=new`
12. Import history:
   - lưu file name, status, counts, error_summary, started/finished_at
13. Duplicate handling:
   - phase này nên chốt một rule đơn giản, ví dụ `same user + same deck + same normalized front + same normalized back`
   - duplicate => `skip` và ghi lý do
14. Rollback/transaction:
   - confirm import phải nằm trong transaction
   - nếu fail khi save `notes/cards/logs` thì rollback toàn bộ job
15. Pseudo-flow ngắn:

```text
upload -> parse -> validate -> preview
confirm -> begin transaction
-> create import_job
-> foreach valid row: dedupe -> insert note -> insert card -> log row
-> update import_job summary
-> commit
```

## 7. FSRS Implementation Plan

### Entity/model cần có
- `cards`
  - `state`
  - `current_step`
  - `learning_steps_json`
  - `relearning_steps_json`
  - `due_at`
  - `stability`
  - `difficulty`
  - `elapsed_days`
  - `scheduled_days`
  - `reps`
  - `lapses`
  - `last_rating`
- `review_logs`
  - lưu full before/after
- `notes`
  - nguồn front/back cho study/check-answer

### Logic tính next review
- `new`
  - khi được đưa vào session lần đầu thì chuyển sang `learning`, `current_step=0`
- `learning`
  - dùng steps ngắn cố định
  - chưa tính FSRS thật
- `review`
  - dùng rule interval đơn giản theo spec
- `relearning`
  - dùng steps ngắn để sửa sai rồi quay lại `review`

### Xử lý rating
- `Again`
  - `learning`: về step đầu
  - `review`: sang `relearning`, reset step, tăng `lapses`
  - `relearning`: về step đầu
- `Hard`
  - `learning`: vẫn ở learning, due ngắn hơn `Good`
  - `review`: tăng interval ít
  - `relearning`: vẫn relearning, due ngắn
- `Good`
  - `learning`: sang step tiếp theo; hết step thì graduate sang review
  - `review`: tăng interval vừa
  - `relearning`: sang step tiếp; hết step thì quay lại review
- `Easy`
  - `learning`: graduate thẳng sang review
  - `review`: tăng interval mạnh
  - `relearning`: có thể quay lại review nhanh

### Update memory state
- Phase này nên làm theo `simple FSRS-compatible fields`, không cần optimizer:
  - `stability`: tăng/giảm theo rating
  - `difficulty`: tăng khi Again/Hard, giảm nhẹ khi Good/Easy
- Quan trọng là giữ schema và contract tương thích để nâng cấp sau

### Update due date
- `learning steps`: `1 phút`, `10 phút`
- `relearning steps`: `10 phút`
- `graduating interval`: khoảng `1 ngày`
- `easy interval`: khoảng `4 ngày`
- `review`:
  - `Hard -> max(1, scheduled_days * 1.2)`
  - `Good -> max(2, scheduled_days * 2.0)`
  - `Easy -> max(4, scheduled_days * 3.0)`

### Liên kết với màn học/review
- Màn `03`: chỉ load current card
- Màn `04`: lưu `user_answer`
- Màn `05`: gọi `rate`, cập nhật `cards`, thêm `review_logs`, lấy `next_card`
- `typing result` chỉ là dữ liệu hỗ trợ, không thay thế rating

### Dữ liệu cần lưu DB
- trạng thái trước/sau
- step trước/sau
- due trước/sau
- stability/difficulty trước/sau
- rating
- mode
- typed_answer
- judged_result
- reviewed_at

## 8. Checklist implement (Legacy - superseded by section 10)

- [x] Khởi tạo route/module cho `study`, `imports`, `scheduler`
  - [x] File cần xử lý: route files, controller skeleton, service folders
  - [x] Done condition: có khung endpoint/page rõ ràng
- [x] Tạo schema `notes/cards/review_logs/import_jobs/import_job_rows`
  - [x] File cần xử lý: migrations, models
  - [x] Done condition: DB đủ field theo spec phase hiện tại
- [x] Implement parser TXT Anki
  - [x] File cần xử lý: parser service, text cleaner
  - [x] Done condition: parse đúng 2 sample files
  - [x] Đã implement: `TxtImportParserService` — header skipper, tab split, field filtering (`[sound]`/`<img>`/tag), text cleaner (HTML strip, entity decode, nbsp), front/back mapper, row validation (≥2 text fields)
- [x] Implement preview import
  - [x] File cần xử lý: import controller/service/DTO/UI
  - [x] Done condition: trả summary + invalid rows + preview rows
  - [x] Đã implement: `POST /api/imports/txt/preview` trả `summary`, `rows`, `errors`, `warnings`, `preview_rows`. UI `imports.blade.php` có flow upload → preview → filter (all/valid/warning/error)
- [x] Implement confirm import
  - [x] File cần xử lý: import service/repository/transaction
  - [x] Done condition: save notes/cards/import history atomically
  - [x] Đã implement: `POST /api/imports/txt/confirm` — `DB::transaction`, insert `notes`/`cards`/`import_job_rows`, update `import_jobs`, duplicate skip, idempotent guard (nếu status=imported thì trả lại kết quả cũ)
- [x] Implement session selector
  - [x] File cần xử lý: study service/repository
  - [x] Done condition: chọn card theo `relearning > review > learning > new`
  - [x] Đã verify bằng fixture seed tối thiểu và gọi thật `GET /api/study/session`
- [x] Implement màn 03
  - [x] File cần xử lý: page/component/state
  - [x] Done condition: `Show Answer` chuyển đúng sang 05
  - [x] Ghi chú: đã nối `study/front` với `GET /api/study/session`, render current card/progress/state/TTS scaffold
- [x] Implement màn 04
  - [x] File cần xử lý: page/component/state
  - [x] Done condition: giữ được `user_answer` và sang 05
  - [x] Ghi chú: đã gọi `check-answer`, lưu reveal payload và hiện hint/validation cơ bản
- [x] Implement màn 05
  - [x] File cần xử lý: page/component/state
  - [x] Done condition: render đúng theo `flip` và `typing`
  - [x] Ghi chú: đã render mode-specific answer, judged result, và gọi `rate`
- [x] Implement `check-answer`
  - [x] File cần xử lý: study controller/service/normalizer
  - [x] Done condition: trả `correct/close/incorrect`
  - [x] Đã verify với fixture thật: case đúng trả `correct`, case gần đúng trả `close_match`
- [x] Implement scheduler `learning/review/relearning`
  - [x] File cần xử lý: scheduler service/domain policy
  - [x] Done condition: đổi state/step/due đúng rule
  - [x] Đã verify với fixture thật: `review -> again`, `relearning -> good`, `new -> easy`
- [x] Implement `rate`
  - [x] File cần xử lý: study controller/service/review log repository
  - [x] Done condition: update card + log + next progress trong 1 transaction
  - [x] Đã verify với fixture thật: `review -> again` update card, insert `review_logs`, trả `next_card_id`
- [~] Implement guard/error/polish
  - [x] File cần xử lý: UI states — double-submit guard (disable buttons), empty/end state, error feedback
  - [ ] File cần xử lý: middleware/logger — chưa có audit logging chuyên biệt
  - [x] Done condition: không double-submit, có empty/end/error state
  - [ ] Còn lại: logging/audit lỗi chuyên biệt cho import/scheduler/API (phase sau)

## 9. Decision Đã Chốt

- `Hard` trong `Learning` và `Relearning`:
  - giữ nguyên `current_step`
  - `Learning 1 phút` -> `Hard` due `1 phút`
  - `Learning 10 phút` -> `Hard` due `5 phút`
  - `Relearning 10 phút` -> `Hard` due `5 phút`
- `check-answer` dùng `back_plain_text` làm nguồn so sánh chuẩn
- `close_match` dùng ngưỡng mặc định `0.85`
- Duplicate import theo `user + deck + normalized front + normalized back`
- Confirm import dùng `preview snapshot + file_hash`
- `play-tts` là optional trong phase này, không block flow học chính
- Khi user đổi mode:
  - giữ nguyên `current_card`
  - chỉ đổi presentation flow
  - nếu đang ở `Answer Revealed` thì không reset card hiện tại
- `GET /api/study/session` được dựng động từ due cards/state hiện có, chưa cần session table riêng
- Khi hết card due/learning/relearning:
  - nếu còn `new` thì lấy tiếp theo rule session hiện tại
  - nếu không còn card phù hợp thì kết thúc session
- Seed mặc định phase đầu:
  - `stability = 1.0`
  - `difficulty = 5.0`

## 10. Implementation Update 2026-04-24

### Clean checklist
- [x] Route/module cho `study`, `imports`, `scheduler`
- [x] Schema `notes`, `cards`, `review_logs`, `import_jobs`, `import_job_rows`
- [x] Parser TXT Anki
- [x] Preview import API + UI
- [x] Confirm import + transaction save `notes/cards/import_jobs/import_job_rows`
- [x] Rule duplicate -> `skip`
- [x] Rule invalid row -> khong chan confirm, bo qua khi import
- [x] Study Session 03/04/05
- [x] `GET /api/study/session`
- [x] `POST /api/study/cards/{id}/check-answer`
- [x] `POST /api/study/cards/{id}/rate`
- [x] Dashboard check du lieu sau import
- [x] UI polish cho import: bo khung `Issues`, giu `summary` + `row preview`
- [x] Cache guard cho study pages: `no-store/no-cache` + `sv=study-v2`
- [ ] Logging/audit chuyen biet cho import/scheduler/API

### Da hoan tat trong dot nay
- Import TXT UI:
  - Them man `Import` trong sidebar.
  - Co flow upload TXT -> preview -> confirm.
  - UI hien `summary`, `row preview`, filter `all/valid/warning/error`.
  - Da bo khung `Issues` trung lap de giao dien gon hon.
- Import preview/confirm backend:
  - Preview API da tra them `rows`, `errors`, `warnings`, `summary` de UI render truc tiep.
  - Confirm import van luu `notes`, `cards`, `import_jobs`, `import_job_rows`.
  - Duplicate rows duoc `skip`.
  - Invalid rows hien tai khong chan confirm; se bi bo qua khi import.
- Dashboard check sau import:
  - Dashboard da duoc noi vao du lieu that tu `decks`, `notes`, `cards`, `import_jobs`.
  - Co the xem nhanh tong so deck/card/note/import va recent import jobs sau khi confirm.
- Study Session cache guard:
  - Da them response header `no-store/no-cache` cho `study/front`, `study/typing`, `study/answer`.
  - Da gan query version `sv=study-v2` cho study URLs de giam rui ro browser cache HTML cu.

### Da verify
- `ImportControllerTest`: pass.
- `route:list` cho `imports` va `study`: pass.
- Vite build frontend: pass.

### Ghi chu trang thai
- Import parser/preview/confirm: da implement.
- Study 03/04/05 + `check-answer` + `rate`: da implement tu truoc va van giu nguyen.
- Logging/audit chuyen biet cho import/scheduler/API: chua lam, van de phase sau.
