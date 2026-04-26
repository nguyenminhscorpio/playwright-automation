# Đặc tả API

## 1. Nguyên tắc
- Sử dụng `RESTful API`
- Request/response dạng `JSON`
- Tài nguyên tách theo `user`
- Tài liệu giữ khung tổng thể cho toàn hệ thống, không chỉ riêng Study Session

## 2. Phạm vi phase hiện tại
Phase hiện tại tập trung mạnh vào:
- import TXT
- study session
- answer checking
- rating
- mô hình `Learning / Review / Relearning`

Các nhóm API khác vẫn được giữ để dùng cho các màn hình và phase sau.

## 3. Tài khoản
- `POST /api/auth/register`
- `POST /api/auth/login`
- `POST /api/auth/logout`
- `GET /api/me`
- `PUT /api/me`

### Ghi chú
- `Future Phase`
- chưa phải trọng tâm của nhóm màn hình 3, 4, 5

## 4. Deck
- `GET /api/decks`
- `POST /api/decks`
- `GET /api/decks/{id}`
- `PUT /api/decks/{id}`
- `DELETE /api/decks/{id}`

### Ghi chú
- cần cho Dashboard, Deck Detail và quản lý dữ liệu sau này

## 5. Card
- `GET /api/cards`
- `POST /api/cards`
- `GET /api/cards/{id}`
- `PUT /api/cards/{id}`
- `DELETE /api/cards/{id}`
- `POST /api/cards/{id}/suspend`
- `POST /api/cards/{id}/unsuspend`

### Ghi chú
- giữ nguyên khung cho quản lý card
- `Card` là đối tượng được áp dụng `Learning / Review / Relearning`

## 6. Import
- `POST /api/imports/txt/preview`
- `POST /api/imports/txt/confirm`
- `GET /api/imports`
- `GET /api/imports/{id}`
- `GET /api/imports/{id}/rows`

### Current Phase
- Đây là nhóm API trọng tâm của phase hiện tại
- Rule import đã chốt:
  - bỏ `mp3`
  - bỏ `image`
  - bỏ `tag`
  - lấy `text đầu tiên` làm `mặt trước`
  - lấy phần text còn lại làm `mặt sau`

### Gợi ý request/response cho `preview`
Request:
- file `.txt`

Response:
- `detected_format`
- `total_lines`
- `data_lines`
- `valid_rows`
- `invalid_rows`
- `preview_rows`

### Gợi ý response cho `confirm`
- `import_job_id`
- `inserted_rows`
- `skipped_rows`
- `errors`

## 7. Học và ôn tập
- `GET /api/study/session`
- `POST /api/study/cards/{id}/check-answer`
- `POST /api/study/cards/{id}/rate`
- `POST /api/study/cards/{id}/play-tts`

### Current Phase
- Đây là nhóm API trọng tâm của màn hình 3, 4, 5
- Cần khớp hoàn toàn với mô hình:
  - `Learning`
  - `Review`
  - `Relearning`

### Rule cần khớp với UI hiện tại
- session có 2 mode:
  - `flip`
  - `typing`
- nếu mode là `typing`, người dùng vào thẳng màn nhập
- `Answer Revealed` là màn dùng chung cho cả 2 mode
- rating luôn hiển thị 4 nút:
  - `Again`
  - `Hard`
  - `Good`
  - `Easy`

### Rule cần khớp với logic Anki-like
- `Learning`
  - dùng step ngắn
  - chưa dùng FSRS thật
- `Review`
  - dùng FSRS
- `Relearning`
  - dùng step ngắn để sửa sai
  - sau đó quay lại `Review`

### Gợi ý `GET /api/study/session`
Trả về:
- `session_id`
- `mode`
- `deck_id`
- `current_card`
- `progress`
- `current_card.state`
- `current_card.current_step`
- `current_card.learning_steps`
- `current_card.relearning_steps`

### Rule chốt cho `GET /api/study/session`
- phase hiện tại chưa cần lưu `session` như một entity riêng trong database
- dữ liệu session được dựng động từ card state hiện có
- thứ tự chọn card:
  - `Relearning`
  - `Review` đã đến hạn
  - `Learning` đã đến hạn
  - `New`
- nếu không còn card phù hợp thì API trả trạng thái kết thúc session

### Gợi ý `POST /api/study/cards/{id}/check-answer`
Request:
- `mode`
- `user_answer`

Response:
- `correct_answer`
- `user_answer`
- `normalized_user_answer`
- `result`

### Rule chốt cho `check-answer`
- nguồn so sánh chuẩn là `back_plain_text`
- `back_text` chỉ dùng để hiển thị cho UI
- normalize tối thiểu:
  - lowercase
  - trim khoảng trắng đầu cuối
  - gom nhiều khoảng trắng liên tiếp thành 1 khoảng trắng
  - bỏ khác biệt dấu câu cơ bản nếu cần
- kết quả trả về gồm:
  - `correct`
  - `close_match`
  - `incorrect`
- rule mặc định phase này:
  - `correct` khi khớp hoàn toàn sau normalize
  - `close_match` khi độ tương đồng >= `0.85`
  - còn lại là `incorrect`
- kết quả này chỉ mang tính hỗ trợ, không thay thế rating

### Gợi ý `POST /api/study/cards/{id}/rate`
Request:
- `mode`
- `rating`
- `typed_answer` nếu có
- `judged_result` nếu có

Response:
- `card_id`
- `state_before`
- `state_after`
- `step_before`
- `step_after`
- `next_due_at`
- `scheduled_days`
- `next_stability`
- `next_difficulty`
- `updated_progress`
- `next_card_id`

### Logic của `rate`

#### Nếu card đang ở `Learning`
- `Again`
  - quay về step đầu
- `Hard`
  - vẫn ở learning
  - due ngắn hơn `Good`
- `Good`
  - sang step tiếp theo
- `Easy`
  - graduate sang `Review`

#### Nếu card đang ở `Review`
- `Again`
  - vào `Relearning`
- `Hard`
  - tăng interval ít
- `Good`
  - tăng interval vừa
- `Easy`
  - tăng interval mạnh

#### Nếu card đang ở `Relearning`
- `Again`
  - quay về step đầu
- `Hard`
  - vẫn ở relearning
- `Good`
  - sang step tiếp hoặc quay lại review nếu hết step
- `Easy`
  - có thể quay lại review nhanh hơn

### Ghi chú về `play-tts`
- giữ trong khung tổng thể
- có thể là `Future Phase` nếu phase hiện tại chưa nối TTS thật
- phase hiện tại không để TTS block flow học chính
- nếu chưa có TTS backend thật thì chỉ cần giữ hook UI và abstraction service

## 8. Thống kê
- `GET /api/stats/dashboard`
- `GET /api/stats/reviews`
- `GET /api/stats/streak`

### Ghi chú
- cần cho Dashboard và Analytics về sau

## 9. Cài đặt
- `GET /api/settings`
- `PUT /api/settings`

### Ghi chú
- phục vụ các thiết lập như:
  - daily goal
  - reminder time
  - preferred voice
  - default study mode
  - learning steps
  - relearning steps

## 10. Thông báo
- `GET /api/notifications/settings`
- `PUT /api/notifications/settings`

### Ghi chú
- thuộc phạm vi mở rộng
- giữ lại để đảm bảo khung hệ thống toàn vẹn
