# 01. Nghiệp vụ màn hình Dashboard (Phase 2)

## 1. Mục đích
- Dashboard là màn hình tổng quan, điểm chạm đầu tiên khi người dùng đăng nhập vào ứng dụng FlashMind.
- Cung cấp cái nhìn toàn cảnh về tiến trình học tập, duy trì động lực (Streak, Milestone).
- Giúp người dùng nhanh chóng truy cập vào các bộ thẻ (Active Decks) cần ôn tập nhất.

## 2. Vai trò nghiệp vụ
- Khuyến khích thói quen học tập hàng ngày qua các chỉ số Gamification (Streak).
- Theo dõi mục tiêu học tập (Concepts Learned / Goal).
- Điều hướng nhanh (Quick actions) vào các màn hình Review/Study.

## 3. Thành phần UI chính trên màn hình (Dựa theo thiết kế)
1. **Header & Lời chào:** Lời chào cá nhân hoá ("Welcome back, Alex!") kèm câu khích lệ động lực.
2. **Khối thống kê nhanh (Quick Stats):**
   - **Daily Streak:** Hiển thị chuỗi ngày học liên tục (vd: 25 Days). Kèm thông điệp ("Keep it up!").
   - **Learning Milestone:** Thống kê số lượng thẻ (concepts) đã học trong tháng hiện tại so với mục tiêu (vd: 500 / Goal: 600), kèm theo thanh tiến trình (Progress Bar) và tỷ lệ phần trăm (83%).
3. **Danh sách Active Decks:**
   - Liệt kê các bộ thẻ đang được học tích cực nhất hoặc có nhiều thẻ đến hạn (Due) nhất.
   - Mỗi Deck Card hiển thị: 
     - Biểu tượng (Icon) riêng.
     - Nhãn thể loại (Category Badge - vd: Language, Programming, Geography).
     - Tiêu đề và Mô tả ngắn gọn của Deck.
     - **Thông số học tập:** Hiển thị theo định dạng "Số thẻ đã học (trạng thái khác New) / Tổng số thẻ" (Ví dụ: 120 / 500 cards).
     - Thanh tiến trình Mastery (vd: 75%) thể hiện mức độ thông thạo bộ thẻ.
     - Nút Action "Review X Cards" (hiển thị số lượng thẻ đang đến hạn hoặc cần học ngay).
     - **Nút Delete (Icon thùng rác):** Đặt ở góc phải/trên của Card để người dùng có thể xoá nhanh bộ thẻ (Kèm xác nhận xoá).
   - Nút "View All ->" để xem toàn bộ danh sách Deck nếu có nhiều hơn số lượng hiển thị trên trang chủ.

## 4. Dữ liệu & Logic nghiệp vụ (Mapping Database Phase 1)
- **User Info:** Lấy từ bảng `users` (`name`).
- **Daily Streak:** Tính toán dựa trên bảng `study_days` (các bản ghi liên tiếp có `review_count > 0` tính đến ngày hôm nay hoặc hôm qua).
- **Learning Milestone:**
  - Đếm tổng số lượng `review_logs` hoặc `cards` chuyển sang trạng thái `review` trong khoảng thời gian (VD: tháng hiện tại).
  - Mục tiêu (Goal) lấy từ `users.daily_goal` (hoặc tính quy đổi ra tháng).
- **Active Decks (Danh sách Deck nổi bật):**
  - Truy vấn bảng `decks` join với `cards`.
  - **Mastery %:** Tính bằng tỉ lệ thẻ đã thành thạo (ví dụ: `(số card có state = 'review' hoặc stability cao) / tổng số card trong deck * 100`).
  - **Review X Cards:** Tính bằng số lượng card thoả mãn điều kiện ôn tập (Due): `state` = `learning`/`relearning` hoặc (`state` = `review` và `due_at` <= ngày giờ hiện tại).
  - Ưu tiên hiển thị tối đa 3-4 Decks có `Review X Cards` lớn nhất.

## 5. Hành vi người dùng & Điều hướng
- **Theo dõi tiến độ:** Xem lướt các chỉ số để biết tiến độ tháng và chuỗi ngày học.
- **Bấm vào nút "Review X Cards":** Chuyển hướng trực tiếp sang màn hình `Study Session` của bộ thẻ đó (VD: `/study/front?deck_id={id}&sv=study-v2&mode=flip`). Chỉ lấy các thẻ Due/Learning để học.
- **Bấm vào tên/vùng trống của Deck Card:** Chuyển hướng sang màn hình **Deck Detail** (`/decks/{id}`).
- **Bấm "View All":** Chuyển hướng sang danh sách đầy đủ các Decks của User.

## 6. Chức năng Tạo Deck mới (Create New Deck)
- **Vị trí:** Nút "Create New Deck" (hoặc dấu cộng) trên màn hình Dashboard.
- **Thành phần Modal:**
  - **DECK NAME (Bắt buộc):** Ô nhập tên bộ thẻ (Ví dụ: English Vocabulary).
  - **DESCRIPTION (Tùy chọn):** Ô nhập mô tả ngắn gọn cho bộ thẻ (Ví dụ: Các từ vựng tiếng Anh giao tiếp cơ bản).
- **Hành vi:**
  - Sau khi bấm "Create Deck", hệ thống tạo record mới trong bảng `decks` và đóng modal.
  - Hiển thị thông báo thành công và cập nhật danh sách Deck trên Dashboard mà không cần load lại trang (nếu dùng AJAX).

## 7. Các luồng tương tác chính (User Flow)
- Áp dụng các token màu sắc và typography từ file `DESIGN.md` (Ví dụ: `primary: #004ac6`, font `Lexend`, `Inter`, bóng đổ Level 1/Level 2).
- Thêm CSS cho màn hình **Dashboard** và **My Decks / Deck Detail** để đảm bảo giao diện đồng bộ, sạch sẽ, chuẩn thiết kế (bo góc, khoảng cách chuẩn).
