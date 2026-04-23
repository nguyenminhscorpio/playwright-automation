# Đặc tả UI/UX

## 1. Mục tiêu trải nghiệm
- Gọn, sáng, dễ tập trung vào card học.
- Người dùng luôn nhìn rõ trạng thái hiện tại của phiên học.
- Hành động chính của mỗi màn hình phải nổi bật.
- Chuyển mode học phải nhanh và rõ ràng.

## 2. Ngôn ngữ thiết kế hiện tại
- Giao diện sáng.
- Gradient nền rất nhẹ.
- Sidebar trắng mờ có blur.
- Card trắng bo góc lớn.
- Màu nhấn chính là xanh dương.
- Font tiêu đề mang tính hiện đại, rõ trọng tâm.

## 3. Quy tắc Study Mode Switch
- Đặt tại topbar bên phải.
- Dùng `segmented control`.
- Có 2 lựa chọn:
  - `Lật thẻ`
  - `Nhập chữ`
- Active state phải nhìn thấy ngay.
- Click vào một mode phải đổi toàn bộ flow đang học.
- Đây là điều khiển ở cấp phiên học, không phải action của riêng một card.

## 4. Quy tắc nút hành động chính
- `Show Answer` dùng cho Flip Mode.
- `Check Answer` dùng cho Typing Mode.
- Nút chính phải có màu xanh dương đậm, bo tròn, dễ bấm trên desktop và tablet.

## 5. Quy tắc card học
- Card phải nằm ở trung tâm.
- Card có chip trạng thái ở góc trên trái.
- Card có icon TTS ở góc trên phải nếu là card có đọc âm thanh.
- Nội dung card phải ưu tiên typography lớn, dễ đọc.

## 6. Quy tắc màn Typing Input
- Ô nhập phải rõ ràng, đủ lớn để gõ nhiều dòng.
- Label `Your Answer` phải tách biệt với card hỏi.
- `Show Hint` là action phụ.
- `Check Answer` là action chính.

## 7. Quy tắc màn Answer Revealed
- Phải hiển thị rõ phần nào là câu hỏi, phần nào là đáp án đúng.
- Nếu ở `Typing Mode`, phải hiển thị thêm `Your Answer`.
- Khu vực rating phải tách rõ với phần đáp án.

## 8. Responsive
- Desktop: sidebar cố định, nội dung ở giữa.
- Tablet ngang: giữ cấu trúc gần desktop.
- Tablet dọc và mobile:
  - topbar co giãn
  - mode switch không bị vỡ layout
  - card và panel nhập liệu vẫn dễ thao tác
