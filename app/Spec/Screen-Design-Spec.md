# Tài liệu thiết kế chi tiết cho 5 màn hình

## 1. Danh sách 5 màn hình
- Dashboard
- Deck Detail
- Study Session - Flip Front
- Study Session - Typing Input
- Study Session - Answer Revealed

## 2. Layout chung
- Sidebar cố định bên trái.
- Topbar nằm trên cùng.
- Khung nội dung ở giữa màn hình.
- Nền sáng với gradient nhẹ.
- Card bo góc lớn, bóng đổ nhẹ.

## 3. Topbar Study Session
- Có ô tìm kiếm ở bên trái.
- Có cụm chuyển mode ở bên phải:
  - `Lật thẻ`
  - `Nhập chữ`
- Cụm này là `segmented control`.
- Mode đang chọn có trạng thái active rõ ràng.
- Đổi mode sẽ đổi luồng học của toàn bộ session.

## 4. Màn hình Dashboard
- Mục đích: hiển thị tổng quan học tập.
- Thành phần chính:
  - tiêu đề
  - thẻ thống kê
  - tiến độ trong ngày
  - danh sách deck
- Hành vi:
  - người dùng chọn deck để mở deck detail
  - người dùng vào study session từ deck hoặc CTA học nhanh

## 5. Màn hình Deck Detail
- Mục đích: xem chi tiết một bộ từ.
- Thành phần chính:
  - breadcrumb hoặc heading
  - tên deck
  - mô tả
  - tiến độ
  - danh sách card
  - nút import
  - nút thêm card
  - nút bắt đầu học

## 6. Màn hình Study Session - Flip Front
- Mục đích: học theo kiểu nhớ trong đầu rồi lật đáp án.
- Thành phần chính:
  - session progress
  - progress bar
  - card mặt trước
  - chip trạng thái như `New Concept`
  - icon TTS
  - nút `Show Answer`
- Hành vi:
  - khi mode là `Lật thẻ`, đây là màn hình mặc định
  - bấm `Show Answer` sang `Answer Revealed`

## 7. Màn hình Study Session - Typing Input
- Mục đích: học bằng cách nhập đáp án.
- Thành phần chính:
  - current deck progress
  - progress bar
  - card mặt trước
  - panel nhập liệu `Your Answer`
  - nút `Show Hint`
  - nút `Check Answer`
- Hành vi:
  - khi chọn mode `Nhập chữ`, hệ thống vào thẳng màn hình này
  - không cần nút trung gian ở màn `Front`
  - người dùng nhập câu trả lời rồi sang `Answer Revealed`

## 8. Màn hình Study Session - Answer Revealed
- Mục đích: hiển thị đáp án đúng và chấm độ nhớ.
- Thành phần chính:
  - prompt
  - đáp án đúng
  - rating panel
  - `Again / Hard / Good / Easy`
- Hành vi theo mode:
  - `Flip`:
    - chỉ hiển thị prompt và đáp án
  - `Typing`:
    - hiển thị prompt
    - hiển thị `Your Answer`
    - hiển thị đáp án đúng
    - về sau có thể thêm trạng thái so sánh đúng sai

## 9. Quy tắc chuyển màn hình
- `Lật thẻ`:
  - `Flip Front` -> `Answer Revealed`
- `Nhập chữ`:
  - `Typing Input` -> `Answer Revealed`
- Đổi mode ở topbar:
  - nếu chọn `Lật thẻ`, quay về flow flip
  - nếu chọn `Nhập chữ`, chuyển sang flow typing ngay
