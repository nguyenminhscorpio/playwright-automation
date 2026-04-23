# 04. Nghiệp vụ màn hình Study Session - Typing Input

## 1. Mục đích
- Đây là màn hình học dành cho mode `Nhập chữ`.
- Người dùng nhìn mặt trước và phải tự gõ câu trả lời vào ô nhập liệu.

## 2. Vai trò nghiệp vụ
- Kiểm tra khả năng nhớ chủ động của người học.
- Tăng độ chính xác khi đánh giá người dùng có thực sự nhớ đáp án hay không.
- Là flow học có mức chủ động cao hơn `Lật thẻ`.

## 3. Thành phần chính trên màn hình
- Current deck progress
- Progress bar
- Card mặt trước
- Ô nhập `Your Answer`
- Nút `Show Hint`
- Nút `Check Answer`
- Topbar có mode switch

## 4. Dữ liệu cần hiển thị
- Nội dung mặt trước của card
- Tiến độ hiện tại của deck hoặc session
- Hint nếu card có gợi ý
- Nội dung người dùng vừa nhập

## 5. Hành vi người dùng
- Chọn mode `Nhập chữ`
- Hệ thống vào thẳng màn hình này
- Người dùng đọc câu hỏi ở mặt trước
- Người dùng nhập đáp án vào ô text
- Người dùng bấm `Check Answer`
- Hệ thống chuyển sang màn `Answer Revealed`

## 6. Luồng nghiệp vụ
1. Người dùng chọn `Nhập chữ` ở topbar
2. Hệ thống mở trực tiếp màn `Typing Input`
3. Người dùng nhập câu trả lời
4. Người dùng có thể xem hint nếu cần
5. Người dùng bấm `Check Answer`
6. Hệ thống lưu tạm `user_answer`
7. Hệ thống chuyển sang màn `Answer Revealed`

## 7. Điều kiện đầu vào
- Có ít nhất 1 card cần học trong session
- Mode hiện tại của session là `typing`

## 8. Điều kiện đầu ra
- Câu trả lời của người dùng được giữ lại để hiển thị ở màn `Answer Revealed`
- Hệ thống chuẩn bị bước so sánh với đáp án đúng

## 9. Kết quả mong đợi
- Người dùng vào thẳng trải nghiệm nhập chữ, không qua bước trung gian
- Flow học nhập chữ mạch lạc, rõ ràng, đúng với UI hiện tại
