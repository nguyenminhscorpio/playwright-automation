# 02. Nghiệp vụ màn hình Deck Detail

## 1. Mục đích
- Deck Detail là màn hình chi tiết của một bộ từ vựng.
- Màn hình này cho người dùng xem thông tin deck và quản lý nội dung bên trong deck.

## 2. Vai trò nghiệp vụ
- Là nơi quản lý một deck cụ thể.
- Cho phép kiểm tra tình trạng học của deck.
- Là điểm vào study session theo ngữ cảnh của deck đó.

## 3. Thành phần chính trên màn hình
- Tên deck
- Mô tả deck
- Thống kê hoặc tiến độ của deck
- Danh sách card trong deck
- Nút import
- Nút thêm card
- Nút bắt đầu học

## 4. Dữ liệu cần hiển thị
- Tên deck
- Số lượng card
- Số card mới
- Số card đến hạn
- Tỷ lệ hoàn thành hoặc mastered
- Danh sách card mẫu trong deck

## 5. Hành vi người dùng
- Xem danh sách card hiện có
- Kiểm tra tiến độ học của deck
- Import dữ liệu từ file txt
- Thêm card mới thủ công
- Bắt đầu học từ deck hiện tại

## 6. Kết quả mong đợi
- Người dùng hiểu deck này có gì
- Người dùng dễ dàng quyết định import, chỉnh sửa hoặc bắt đầu học

## 7. Ghi chú triển khai
- Trong bản UI hiện tại, danh sách card có thể là mock
- Sau này màn hình này sẽ là nơi nối trực tiếp với dữ liệu `decks` và `cards`
