# Đặc tả API

## 1. Nguyên tắc
- Sử dụng `RESTful API`
- Request/response dạng `JSON`
- Tài nguyên tách theo `user`
- Khung API được giữ đủ rộng cho toàn hệ thống, không chỉ riêng Study Session

## 2. Phạm vi phase hiện tại
Phase hiện tại đang tập trung mạnh vào:
- import TXT
- study session
- answer checking
- rating

Các nhóm API khác vẫn được giữ trong tài liệu để phục vụ các màn hình và phase tiếp theo.

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
- không mâu thuẫn với Study Session hiện tại

## 5. Card
- `GET /api/cards`
- `POST /api/cards`
- `GET /api/cards/{id}`
- `PUT /api/cards/{id}`
- `DELETE /api/cards/{id}`
- `POST /api/cards/{id}/suspend`
- `POST /api/cards/{id}/unsuspend`

### Ghi chú
- giữ nguyên khung để dùng cho quản lý card về sau

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

### Rule cần khớp với UI hiện tại
- session có 2 mode:
  - `flip`
  - `typing`
- nếu mode là `typing`, người dùng vào thẳng màn nhập
- `answer revealed` là màn dùng chung cho cả 2 mode

### Gợi ý `GET /api/study/session`
Trả về:
- `session_id`
- `mode`
- `deck_id`
- `current_card`
- `progress`

### Gợi ý `POST /api/study/cards/{id}/check-answer`
Request:
- `mode`
- `user_answer`

Response:
- `correct_answer`
- `user_answer`
- `normalized_user_answer`
- `result`

### Gợi ý `POST /api/study/cards/{id}/rate`
Request:
- `mode`
- `rating`
- `typed_answer` nếu có

Response:
- `next_card_id`
- `next_due_at`
- `updated_progress`

### Ghi chú về `play-tts`
- giữ trong khung tổng thể
- có thể là `Future Phase` nếu phase hiện tại chưa nối TTS thật

## 8. Thống kê
- `GET /api/stats/dashboard`
- `GET /api/stats/reviews`
- `GET /api/stats/streak`

### Ghi chú
- cần cho Dashboard và Analytics về sau
- không cần thu gọn

## 9. Cài đặt
- `GET /api/settings`
- `PUT /api/settings`

### Ghi chú
- phục vụ các thiết lập như:
  - daily goal
  - reminder time
  - preferred voice
  - default study mode

## 10. Thông báo
- `GET /api/notifications/settings`
- `PUT /api/notifications/settings`

### Ghi chú
- thuộc phạm vi mở rộng
- giữ lại để đảm bảo khung hệ thống toàn vẹn
