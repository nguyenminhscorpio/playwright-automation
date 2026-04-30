# 03. Nghiệp vụ màn hình Study Session - Flip Front

## 1. Mục đích
- Đây là màn hình học dành cho mode `flip`.
- Người dùng chỉ nhìn mặt trước của card, tự nhớ đáp án, rồi bấm `Show Answer` để chuyển sang màn đánh giá.

## 2. Vai trò nghiệp vụ
- Là điểm bắt đầu của flow học kiểu lật thẻ.
- Giúp người dùng tự recall trước khi thấy đáp án.
- Chưa chấm điểm, chưa cập nhật lịch học, chưa ghi review log ở màn này.

## 3. Thành phần chính trên màn hình
- Tên deck hiện tại.
- Thông tin tiến độ session:
  - `Card X of Y`
  - `Reviewed`
  - progress bar
- Nội dung mặt trước của card.
- Nhãn trạng thái card: `New Card`, `Learning`, `Review`, `Relearning`.
- Nút TTS.
- Nút `Show Answer`.
- Topbar có mode switch giữa `flip` và `typing`.

## 4. Dữ liệu hiển thị
- `deck_name`
- `front_text` hoặc `front_plain_text`
- `state`
- `progress.total`
- `progress.completed`
- `progress.remaining`

## 5. Hành vi người dùng
- Xem nội dung mặt trước của card hiện tại.
- Có thể bấm nút TTS để gửi request phát âm.
- Có thể đổi mode sang `typing` bằng mode switch.
- Bấm `Show Answer` để sang màn `Answer Revealed`.

## 6. Luồng nghiệp vụ
1. Người dùng mở Study Session ở mode `flip`.
2. Hệ thống gọi API session với `mode=flip`.
3. Hệ thống chọn 1 card hiện tại theo thứ tự ưu tiên:
   - `relearning` đến hạn
   - `review` đến hạn
   - `learning` đến hạn
   - nếu không có card đến hạn thì lấy card `new`
4. Hệ thống render màn `Flip Front` với mặt trước của card.
5. Người dùng quan sát câu hỏi hoặc nội dung cần nhớ.
6. Nếu bấm TTS, hệ thống gọi endpoint TTS và hiện thông báo scaffold, chưa phát âm thật ở phase hiện tại.
7. Khi bấm `Show Answer`, hệ thống lưu tạm payload của card hiện tại vào `sessionStorage`.
8. Hệ thống chuyển sang màn `Answer Revealed`.

## 7. Điều kiện đầu vào
- Có `user_id` hợp lệ để build study session.
- Có ít nhất 1 card khả dụng trong phạm vi deck đang học hoặc toàn bộ deck.
- Mode hiện tại là `flip`.

## 8. Điều kiện đầu ra
- Nếu có card:
  - người dùng được chuyển sang màn `Answer Revealed`
  - payload của card hiện tại được lưu tạm để màn answer dùng lại
- Nếu không có card:
  - màn hình hiển thị trạng thái `Session complete`
  - không có hành vi reveal tiếp theo

## 9. Quy tắc nghiệp vụ quan trọng
- Màn này không kiểm tra đáp án.
- Màn này không cập nhật `state`, `due_at`, `stability`, `difficulty`.
- Màn này không ghi `review_logs`.
- `Answer Revealed` mới là nơi người dùng chọn `Again / Hard / Good / Easy`.

## 10. Kết quả mong đợi
- Người dùng có một bước recall rõ ràng trước khi xem đáp án.
- Flow `flip -> answer` đơn giản, nhanh và đúng với hành vi flashcard cơ bản.
