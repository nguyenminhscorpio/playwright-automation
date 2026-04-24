# Đặc tả chức năng

## 1. Danh sách module
- Dashboard
- Quản lý deck
- Quản lý card
- Import txt từ Anki
- Study Session
- Kiểm tra đáp án
- TTS
- Streak
- Settings
- Notification reminder

## 2. Logic cốt lõi của Study Session
- Study Session có 2 mode học:
  - `Flip Mode`
  - `Typing Mode`
- Mode học là thiết lập ở cấp `phiên học`, không phải nút cục bộ trong card.
- Người dùng đổi mode tại `topbar`.
- Giao diện toàn phiên học đổi theo mode đang chọn.

## 3. Flow chuẩn theo UI hiện tại

### Flip Mode
1. Người dùng mở `Study Session`.
2. Hệ thống hiển thị `Flip Front`.
3. Người dùng xem từ/câu hỏi ở mặt trước.
4. Người dùng bấm `Show Answer`.
5. Hệ thống hiển thị `Answer Revealed`.
6. Người dùng chọn `Again / Hard / Good / Easy`.

### Typing Mode
1. Người dùng chọn `Nhập chữ` ở topbar.
2. Hệ thống mở thẳng màn hình `Typing Input`.
3. Người dùng xem câu hỏi ở mặt trước.
4. Người dùng nhập câu trả lời vào ô nhập liệu.
5. Người dùng bấm `Check Answer`.
6. Hệ thống hiển thị `Answer Revealed`.
7. Người dùng chọn `Again / Hard / Good / Easy`.

## 4. Dashboard
- Hiển thị thống kê tổng quan.
- Hiển thị tiến độ học hôm nay.
- Hiển thị deck đang học hoặc deck đề xuất.
- Có ô tìm kiếm.
- Có CTA để vào học hoặc mở deck.

## 5. Deck Detail
- Hiển thị thông tin deck.
- Hiển thị tiến độ hiện tại của deck.
- Hiển thị danh sách card trong deck.
- Có CTA `Import`.
- Có CTA `Add New Card`.
- Có CTA bắt đầu học.

## 6. Study Session - Flip Front
- Hiển thị tên nhóm học hoặc loại nội dung.
- Hiển thị `Session Progress`.
- Hiển thị card mặt trước.
- Có icon `TTS`.
- Có CTA `Show Answer`.
- Không hiển thị form nhập.

## 7. Study Session - Typing Input
- Hiển thị tiến độ của deck.
- Hiển thị card mặt trước.
- Hiển thị ô nhập `Your Answer`.
- Có `Show Hint`.
- Có CTA `Check Answer`.
- Đây là màn hình được mở trực tiếp khi mode là `Nhập chữ`.

## 8. Study Session - Answer Revealed
- Dùng chung cho cả 2 mode.
- Nếu mode là `Flip`:
  - hiển thị `Prompt`
  - hiển thị đáp án đúng hoặc back side
  - không cần `Your Answer`
- Nếu mode là `Typing`:
  - hiển thị `Prompt`
  - hiển thị `Your Answer`
  - hiển thị đáp án đúng
  - về sau có thể hiển thị trạng thái `Đúng / Gần đúng / Sai`

## 9. Kiểm tra đáp án
- Chỉ áp dụng cho `Typing Mode`.
- Phiên bản hiện tại dùng mock data và flow UI.
- Phiên bản triển khai sau sẽ:
  - normalize text
  - loại bỏ khác biệt hoa thường
  - bỏ khoảng trắng dư
  - so sánh với back side plain text
  - trả về `correct`, `close_match`, `incorrect`

### Rule chốt cho phase hiện tại
- nguồn chuẩn để so sánh là `back_plain_text`
- `close_match` dùng ngưỡng mặc định `0.85`
- kết quả chấm chỉ là dữ liệu hỗ trợ
- quyết định cuối cùng để đổi phase và tính lịch vẫn là rating người dùng chọn

## 10. TTS
- Có icon loa trên study card.
- Dùng để đọc từ/câu tiếng Anh.
- Vì file txt từ Anki có thể không có audio, TTS là phương án thay thế mặc định.

## 11. Mapping với giao diện hiện tại
- `Dashboard`: tổng quan học tập
- `Deck Detail`: trang chi tiết bộ từ
- `Study Session - Flip Front`: màn học kiểu lật thẻ
- `Study Session - Typing Input`: màn học kiểu nhập chữ
- `Study Session - Answer Revealed`: màn hiển thị đáp án và chấm độ nhớ
