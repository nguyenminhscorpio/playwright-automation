# Tài liệu yêu cầu - App học từ vựng tiếng Anh

## Yêu cầu nghiệp vụ
- Ứng dụng giúp người dùng học từ vựng bằng phương pháp Spaced Repetition.
- Người dùng có thể tái sử dụng dữ liệu từ Anki ở định dạng `Notes in Plain Text (.txt)`.
- Ứng dụng phải hỗ trợ học trên iPhone, iPad và máy tính.
- Ứng dụng phải có khả năng mở rộng sang đồng bộ đa thiết bị và nhắc học.

## Yêu cầu chức năng

### Tài khoản
- FR-001: Đăng ký tài khoản.
- FR-002: Đăng nhập, đăng xuất.
- FR-003: Cập nhật thông tin cá nhân và cài đặt học tập.

### Bộ thẻ và thẻ học
- FR-010: Tạo, sửa, xóa deck.
- FR-011: Tạo, sửa, xóa card thủ công.
- FR-012: Xem thống kê cơ bản của từng deck.

### Nhập file txt
- FR-020: Upload file txt từ Anki.
- FR-021: Parse được `#separator`, `#html`, `#tags column`.
- FR-022: Hiển thị preview trước khi import.
- FR-023: Ghi log dòng lỗi và kết quả import.

### Học và ôn tập
- FR-030: Tạo phiên học từ card due, learning và new.
- FR-031: Hiển thị front và back.
- FR-032: Hỗ trợ 4 mức đánh giá `Again`, `Hard`, `Good`, `Easy`.
- FR-033: Cập nhật lịch học theo FSRS đơn giản.
- FR-034: Lưu lịch sử review.

### Kiểm tra đáp án
- FR-040: Người dùng nhập đáp án.
- FR-041: Hệ thống chấm đúng, gần đúng hoặc sai.
- FR-042: Kết quả chấm chỉ mang tính hỗ trợ, không thay thế rating.

### TTS
- FR-050: Đọc nội dung tiếng Anh bằng TTS.
- FR-051: Cho phép nghe lại.

### Thống kê
- FR-060: Theo dõi streak.
- FR-061: Hiển thị Dashboard thống kê.
- FR-062: Hiển thị lịch sử review theo ngày.

### Nâng cao
- FR-070: Đồng bộ đa thiết bị.
- FR-071: Notification nhắc học.
- FR-072: Handwriting recognition cho giai đoạn sau.

## Yêu cầu phi chức năng
- NFR-001: Responsive trên mobile, tablet, desktop.
- NFR-002: Chuyển card nhanh, thao tác mượt.
- NFR-003: Bảo mật dữ liệu người dùng.
- NFR-004: Có khả năng mở rộng để nâng cấp FSRS và đồng bộ.
- NFR-005: Import ổn định với file txt đúng chuẩn Anki.
