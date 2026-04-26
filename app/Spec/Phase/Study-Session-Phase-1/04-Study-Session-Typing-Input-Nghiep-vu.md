# 04. Nghiệp vụ màn hình Study Session - Typing Input

## 1. Mục đích
- Đây là màn hình học dành cho mode `typing`.
- Người dùng nhìn mặt trước của card, nhập câu trả lời, sau đó bấm `Check Answer` để xem đáp án và mức độ khớp.

## 2. Vai trò nghiệp vụ
- Là flow học yêu cầu active recall mạnh hơn `flip`.
- Kiểm tra câu trả lời người dùng trước khi sang bước rating.
- Chưa cập nhật lịch học và chưa ghi review log ở màn này.

## 3. Thành phần chính trên màn hình
- Tên deck hiện tại.
- Tiến độ session dạng compact.
- Nội dung mặt trước của card.
- Ô nhập `Your Answer`.
- Nút `Show Hint`.
- Nút `Check Answer`.
- Topbar có mode switch giữa `flip` và `typing`.

## 4. Dữ liệu hiển thị
- `deck_name`
- `front_text` hoặc `front_plain_text`
- `back_text` hoặc `back_plain_text` dùng làm hint khi cần
- `progress.total`
- `progress.completed`
- `progress.remaining`
- Câu trả lời người dùng vừa nhập

## 5. Hành vi người dùng
- Mở thẳng màn `Typing Input` khi học ở mode `typing`.
- Đọc mặt trước của card.
- Nhập câu trả lời vào ô text.
- Có thể bấm `Show Hint` để xem gợi ý nhanh.
- Bấm `Check Answer` để hệ thống kiểm tra đáp án và chuyển sang màn `Answer Revealed`.

## 6. Luồng nghiệp vụ
1. Người dùng mở Study Session ở mode `typing`.
2. Hệ thống gọi API session với `mode=typing`.
3. Hệ thống chọn card theo cùng thứ tự ưu tiên của session hiện tại.
4. Hệ thống render mặt trước của card và ô nhập đáp án.
5. Nếu người dùng bấm `Show Hint`, hệ thống hiển thị nội dung `back_plain_text` hoặc `back_text` dưới dạng feedback.
6. Khi người dùng bấm `Check Answer`:
   - nếu ô nhập rỗng thì chặn thao tác và báo lỗi
   - nếu card không có `back_plain_text` thì API trả lỗi, không sang màn answer
7. Nếu dữ liệu hợp lệ, hệ thống gọi API kiểm tra đáp án với `mode=typing` và `user_answer`.
8. Hệ thống normalize câu trả lời và so sánh với đáp án đúng:
   - trùng hoàn toàn sau normalize => `correct`
   - độ tương tự từ `85%` trở lên => `close_match`
   - còn lại => `incorrect`
9. Hệ thống lưu tạm payload gồm card hiện tại, `user_answer`, `judged_result`, `check_result` vào `sessionStorage`.
10. Hệ thống chuyển sang màn `Answer Revealed`.

## 7. Điều kiện đầu vào
- Có `user_id` hợp lệ để build study session.
- Mode hiện tại là `typing`.
- Card hiện tại có dữ liệu đáp án ở `back_plain_text` nếu muốn dùng tính năng check answer.

## 8. Điều kiện đầu ra
- Nếu check answer thành công:
  - `user_answer` được giữ lại
  - `judged_result` được xác định là `correct`, `close_match` hoặc `incorrect`
  - người dùng được chuyển sang màn `Answer Revealed`
- Nếu check answer thất bại:
  - vẫn ở màn `Typing Input`
  - hiển thị lỗi tương ứng để người dùng sửa

## 9. Quy tắc nghiệp vụ quan trọng
- `Show Hint` hiện tại dùng trực tiếp nội dung mặt sau làm gợi ý.
- Việc so khớp đáp án dùng text đã normalize:
  - chuyển về chữ thường
  - bỏ ký tự đặc biệt
  - gom khoảng trắng
- Kết quả `correct / close_match / incorrect` chỉ là thông tin hỗ trợ; người dùng vẫn tự chọn rating ở màn answer.
- Màn này không tự động chấm `Again / Hard / Good / Easy`.

## 10. Kết quả mong đợi
- Người dùng đi thẳng vào flow nhập đáp án, không qua bước lật thẻ.
- Trước khi rating, người dùng nhìn thấy cả đáp án đúng lẫn mức độ khớp của câu đã nhập.
