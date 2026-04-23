# 01. Nghiệp vụ màn hình Dashboard

## 1. Mục đích
- Dashboard là màn hình tổng quan sau khi người dùng vào ứng dụng.
- Màn hình này giúp người dùng nắm nhanh tình trạng học tập hiện tại.
- Từ đây người dùng có thể chuyển sang deck detail hoặc study session.

## 2. Vai trò nghiệp vụ
- Hiển thị bức tranh tổng quan của việc học trong ngày.
- Tạo điểm bắt đầu nhanh cho người dùng quay lại học.
- Là nơi điều hướng sang các chức năng quan trọng khác.

## 3. Thành phần chính trên màn hình
- Sidebar điều hướng
- Topbar có ô tìm kiếm
- Khu vực tiêu đề tổng quan
- Các khối thống kê
- Khu vực tiến độ học
- Danh sách deck hoặc nội dung học nổi bật

## 4. Dữ liệu cần hiển thị
- Số lượng card đến hạn
- Số lượng card mới
- Số lượt học trong ngày
- Streak hiện tại
- Tiến độ học của một deck đang hoạt động
- Danh sách deck người dùng đang có

## 5. Hành vi người dùng
- Xem nhanh các chỉ số học tập
- Chọn một deck để mở chi tiết
- Chọn vào study session để bắt đầu học
- Dùng ô tìm kiếm để lọc deck

## 6. Kết quả mong đợi
- Người dùng hiểu ngay hôm nay cần học gì
- Người dùng vào đúng deck hoặc đúng flow học chỉ với vài thao tác

## 7. Ghi chú triển khai
- Giai đoạn hiện tại có thể dùng mock data
- Khi nối dữ liệu thật, dashboard sẽ lấy dữ liệu tổng hợp từ deck, card và review log
