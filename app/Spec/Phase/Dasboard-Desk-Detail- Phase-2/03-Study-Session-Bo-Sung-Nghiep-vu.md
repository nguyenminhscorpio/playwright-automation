# 03. Nghiệp vụ bổ sung màn hình Study Session (Phase 2)

Tài liệu này được cập nhật theo source hiện tại của project `vibe-coding`.

## 1. Phạm vi hiện đang có trong source
- Các route Study hiện tại:
  - `/study/front`
  - `/study/typing`
  - `/study/answer`
- Màn Study có 2 mode chính:
  - `flip`
  - `typing`
- Có tích hợp TTS phía client bằng Web Speech API
- Có luồng `check answer` cho mode typing
- Có luồng `rate` để ghi review log và schedule card tiếp theo
- Có các cải tiến liên quan tới màn Import vì flow Study phụ thuộc trực tiếp vào dữ liệu import

## 2. Tính năng TTS theo source hiện tại

### 2.1. Mục tiêu
- Cho phép người dùng nghe đọc nội dung card ngay trong Study Session.
- Ưu tiên đơn giản, chạy phía trình duyệt, không phụ thuộc server audio.

### 2.2. Cách triển khai hiện tại
- Frontend dùng file `resources/js/tts.js`
- Cơ chế:
  - dùng `window.speechSynthesis`
  - tạo `SpeechSynthesisUtterance`
  - gọi `speak()` trực tiếp ở trình duyệt
- Khi phát audio mới:
  - frontend gọi `cancel()` trước để dừng lượt đọc cũ

### 2.3. Vị trí nút TTS
- `study/front`
  - 1 nút loa cho mặt trước
- `study/typing`
  - 1 nút loa cho mặt trước
- `study/answer`
  - 1 nút loa cho `Prompt`
  - 1 nút loa cho `Back Side`

### 2.4. Nguồn text để đọc
- Frontend ưu tiên:
  - `front_plain_text` hoặc `back_plain_text`
- Nếu không có plain text thì fallback:
  - `front_text`
  - `back_text`

### 2.5. Hành vi hiện tại
- Nút TTS chỉ được enable sau khi Study Session load được `current_card`
- Nếu trình duyệt không hỗ trợ `speechSynthesis`
  - frontend hiển thị alert `Text-to-speech is not available in this browser.`
- Ở màn `study/answer`
  - sau khi có reveal payload trong `sessionStorage`
  - frontend tự động phát `back text` sau khoảng `300ms`
- Sau khi người dùng bấm rating
  - frontend gọi `stopTts()` trước khi chuyển sang card tiếp theo

### 2.6. Giới hạn hiện tại
- Chưa có chọn giọng đọc
- Chưa có cấu hình `locale` hoặc `preferred_tts_voice`
- Chưa có lưu preference TTS vào database
- Chưa có server-side TTS
- API `POST /api/study/cards/{id}/play-tts` mới chỉ là scaffold, UI hiện tại không dùng endpoint này

## 3. Luồng Study Session theo source hiện tại

### 3.1. Lấy dữ liệu session
- Frontend gọi `GET /api/study/session`
- Query hiện dùng:
  - `user_id`
  - `deck_id` (optional)
  - `mode`
- Response trả về:
  - `session_id`
  - `mode`
  - `deck_id`
  - `current_card`
  - `progress`

### 3.2. Cách chọn card hiện tại
- `StudySessionService` ưu tiên chọn theo thứ tự:
  - `relearning` đến hạn
  - `review` đến hạn
  - `learning` đến hạn
  - nếu không còn due card thì fallback sang card `new` đầu tiên

### 3.3. Progress hiện có
- `progress` trả về các số:
  - `new`
  - `learning`
  - `review`
  - `total`
  - `completed`
  - `remaining`
  - `has_cards`
  - `ended`
- Hiện trạng source:
  - `completed` đang luôn là `0`
  - `remaining` đang bằng `total`
  - chưa có bộ đếm tiến độ theo session thực sự

### 3.4. Mode Flip
- Màn `/study/front`
  - hiển thị card hiện tại
  - hiển thị state chip
  - có nút `Show Answer`
- Khi bấm `Show Answer`
  - frontend lưu payload vào `sessionStorage`
  - chuyển sang `/study/answer`

### 3.5. Mode Typing
- Màn `/study/typing`
  - hiển thị front side
  - có textarea nhập đáp án
  - có nút `Check Answer`
  - có nút `Show Hint`
- Hiện trạng source:
  - `Check Answer` đã hoạt động
  - `Show Hint` đang disabled, chưa có logic xử lý

### 3.6. Kiểm tra đáp án mode Typing
- Frontend gọi `POST /api/study/cards/{id}/check-answer`
- Backend so sánh `user_answer` với `back_plain_text`
- Logic chuẩn hóa hiện tại:
  - chuyển lowercase
  - bỏ dấu câu/ký tự đặc biệt
  - normalize khoảng trắng
- Kết quả trả về:
  - `correct`
  - `close_match`
  - `incorrect`
- Ngưỡng `close_match` hiện dùng `similar_text` với độ tương đồng từ `85%` trở lên

### 3.7. Màn Answer
- `/study/answer` hiển thị:
  - `Prompt`
  - `Your Answer` nếu đi từ mode typing
  - `Back Side`
  - state tag
  - mode tag
  - 4 nút rating:
    - `Again`
    - `Hard`
    - `Good`
    - `Easy`

### 3.8. Rating và scheduling
- Khi người dùng bấm rating:
  - frontend gọi `POST /api/study/cards/{id}/rate`
- Backend:
  - cập nhật `cards.state`
  - cập nhật `current_step`
  - cập nhật `due_at`
  - cập nhật `last_reviewed_at`
  - cập nhật các field FSRS hiện có như `stability`, `difficulty`, `reps`, `lapses`
  - ghi 1 bản ghi vào `review_logs`
- Sau đó frontend:
  - xóa reveal payload trong `sessionStorage`
  - redirect về `/study/front` hoặc `/study/typing` để load card tiếp theo

### 3.9. Empty state
- Nếu session không có `current_card`
  - frontend ẩn card/rating/actions
  - hiện `Session complete`

## 4. Import-related bổ sung đang có trong source

### 4.1. Màn Import
- Route: `/imports`
- Mục tiêu:
  - upload file TXT kiểu Anki
  - preview trước
  - confirm sau

### 4.2. Thành phần hiện có
- `Target deck`
- `TXT file`
- nút `Preview Import`
- nút `Confirm Import`
- khối `Summary`
- bảng `Row Preview`
- filter tab:
  - `All`
  - `Valid`
  - `Warnings`
  - `Errors`

### 4.3. Flow preview và confirm
- `Preview Import`
  - gọi `POST /api/imports/txt/preview`
  - tạo `import_job`
  - lưu các dòng preview vào `import_job_rows`
- `Confirm Import`
  - gọi `POST /api/imports/txt/confirm`
  - insert `notes` + `cards`
  - update lại `import_job`

### 4.4. Rule hiện tại
- Invalid rows không chặn confirm
- Khi confirm:
  - row invalid sẽ bị bỏ qua
  - row duplicate trong cùng `user + deck + normalized front + normalized back` sẽ bị `skipped`
- Sau khi confirm thành công:
  - nút `Confirm Import` bị disable
  - feedback hiển thị số `imported`, `skipped`, `invalid`

### 4.5. Tạo deck mới từ màn Import
- Dropdown `Target deck` có option `+ Create New Deck...`
- Khi chọn option này:
  - mở modal tạo deck dùng chung
  - sau khi tạo thành công:
    - thêm option mới vào dropdown
    - tự chọn deck vừa tạo

## 5. Dữ liệu và ràng buộc cần phản ánh đúng theo source
- Bảng `users` hiện tại chỉ có các field mặc định của Laravel.
- Chưa có các field:
  - `locale`
  - `preferred_tts_voice`
  - `daily_goal`
- Dashboard/Study hiện không dùng `study_days`.
- TTS hiện không lấy cấu hình từ DB.

## 6. Ghi chú hiện trạng quan trọng
- Study pages đang gắn header `no-store/no-cache` để giảm rủi ro browser cache HTML cũ.
- Link Study trong layout dùng query `sv=study-v2` để hỗ trợ cache busting phía route.
- Màn `study/answer` phụ thuộc vào reveal payload trong `sessionStorage` để hiển thị đầy đủ ngữ cảnh trả lời typing và để rating flow hoạt động đúng.
- Nếu người dùng mở thẳng `/study/answer` mà không đi qua bước reveal/check-answer, UI có thể vẫn render card hiện tại từ session API nhưng không có đầy đủ ngữ cảnh của lượt học trước đó.
