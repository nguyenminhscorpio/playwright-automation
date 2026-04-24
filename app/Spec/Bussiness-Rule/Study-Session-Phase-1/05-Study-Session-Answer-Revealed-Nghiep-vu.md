# 05. Nghiệp vụ màn hình Study Session - Answer Revealed

## 1. Mục đích
- Đây là màn hình hiển thị đáp án sau khi người dùng học xong một card.
- Màn hình này dùng chung cho cả `Lật thẻ` và `Nhập chữ`.

## 2. Vai trò nghiệp vụ
- Cho người dùng biết đáp án đúng
- Là nơi người dùng tự đánh giá mức độ nhớ
- Là bước chuyển tiếp để hệ thống ghi nhận kết quả học của card

## 3. Thành phần chính trên màn hình
- Khu vực `Prompt`
- Khu vực đáp án đúng
- Khu vực `Your Answer` nếu là typing mode
- Tag hoặc metadata phụ nếu có
- Rating panel:
  - `Again`
  - `Hard`
  - `Good`
  - `Easy`

## 4. Hành vi theo mode

### Khi là `Lật thẻ`
- Hiển thị prompt
- Hiển thị mặt sau hoặc đáp án đúng
- Không cần hiển thị `Your Answer`
- Người dùng chuyển sang bước chấm mức độ nhớ

### Khi là `Nhập chữ`
- Hiển thị prompt
- Hiển thị câu trả lời người dùng đã nhập
- Hiển thị đáp án đúng
- Về sau có thể hiển thị trạng thái `Đúng / Gần đúng / Sai`
- Người dùng chuyển sang bước chấm mức độ nhớ

## 5. Luồng nghiệp vụ
1. Hệ thống nhận kết quả từ `Flip Front` hoặc `Typing Input`
2. Hệ thống hiển thị đáp án đúng
3. Nếu là typing mode, hiển thị thêm `user_answer`
4. Người dùng chọn `Again / Hard / Good / Easy`
5. Hệ thống ghi nhận log học
6. Hệ thống chuẩn bị card tiếp theo

## 6. Điều kiện đầu vào
- Có card hiện tại đã đi qua bước xem hoặc nhập đáp án
- Có mode hiện tại của session để quyết định cách render màn answer

## 7. Điều kiện đầu ra
- Kết quả đánh giá của người dùng được ghi nhận
- Hệ thống có đủ dữ liệu để cập nhật tiến độ học và lịch ôn tập

## 8. Kết quả mong đợi
- Người dùng nhìn rõ đáp án đúng
- Người dùng chấm mức độ nhớ nhanh chóng
- Hệ thống sẵn sàng chuyển sang card tiếp theo
