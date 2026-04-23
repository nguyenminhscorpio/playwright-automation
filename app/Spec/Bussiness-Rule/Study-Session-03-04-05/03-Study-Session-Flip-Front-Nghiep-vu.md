# 03. Nghiệp vụ màn hình Study Session - Flip Front

## 1. Mục đích
- Đây là màn hình học dành cho mode `Lật thẻ`.
- Người dùng nhìn mặt trước của card, tự nhớ câu trả lời trong đầu, rồi mới xem đáp án.

## 2. Vai trò nghiệp vụ
- Là điểm bắt đầu của flow học kiểu flashcard truyền thống.
- Giúp người dùng tự đánh giá khả năng nhớ trước khi thấy đáp án.
- Giữ cho trải nghiệm học nhanh, ít thao tác và tập trung vào ghi nhớ.

## 3. Thành phần chính trên màn hình
- Session progress
- Progress bar
- Card mặt trước
- Chip trạng thái như `New Concept`
- Icon TTS
- Nút `Show Answer`
- Topbar có mode switch

## 4. Dữ liệu cần hiển thị
- Tên deck hoặc nhóm nội dung
- Tổng số card trong session
- Số card đã đi qua
- Nội dung mặt trước của card
- Trạng thái card như `new`, `learning`, `review`

## 5. Hành vi người dùng
- Xem nội dung card ở mặt trước
- Nghe phát âm bằng TTS nếu cần
- Tự nhớ nghĩa hoặc câu trả lời
- Bấm `Show Answer` để xem đáp án
- Có thể đổi sang mode `Nhập chữ` tại topbar

## 6. Luồng nghiệp vụ
1. Người dùng vào Study Session khi mode hiện tại là `Lật thẻ`
2. Hệ thống hiển thị màn `Flip Front`
3. Người dùng quan sát nội dung card
4. Người dùng có thể dùng TTS để nghe phát âm
5. Người dùng bấm `Show Answer`
6. Hệ thống chuyển sang màn `Answer Revealed`

## 7. Điều kiện đầu vào
- Có ít nhất 1 card cần học trong session
- Mode hiện tại của session là `flip`

## 8. Điều kiện đầu ra
- Hệ thống chuyển sang màn hình hiển thị đáp án
- Card hiện tại vẫn là card đang được xử lý cho đến khi người dùng chấm `Again / Hard / Good / Easy`

## 9. Kết quả mong đợi
- Người dùng tập trung vào việc nhớ trước khi nhìn đáp án
- Flow học rõ ràng và đúng với hành vi flashcard
