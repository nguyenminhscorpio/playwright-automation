# Danh sách công việc Phase 2 (Master Todo List)

Tài liệu này tổng hợp toàn bộ các đầu việc cần thực hiện cho Phase 2, dựa trên các đặc tả nghiệp vụ và kỹ thuật đã chốt.

---

## 1. Màn hình Deck Detail (Quản lý thẻ)

### A. Giao diện Bảng (Table UI)
- [ ] **Xoá cột DECK:** Vì dư thừa trong trang chi tiết.
- [ ] **Thêm cột LAST REVIEWED:** Hiển thị ngày giờ học gần nhất.
- [ ] **Cập nhật cột NEXT:** Format lại hiển thị ngày giờ phải ôn lại.
- [ ] **Thêm cột ACTIONS:** Chứa biểu tượng Sửa (Edit) và Xóa (Delete).
- [ ] **CSS Styling:** Áp dụng thiết kế từ `DESIGN.md` (Bo góc 16px cho bảng, màu chữ charcoal #191c1e, hover row).

### B. Chức năng tương tác (Actions)
- [ ] **Tìm kiếm (Search):** Code logic tìm kiếm thẻ theo Front/Back/Description qua API (`GET /api/cards?q=...`).
- [ ] **Lọc trạng thái (Filter):** Code logic lọc theo Status (New, Learning, Review).
- [ ] **Nút "Create Card":** Xây dựng Modal Form nhập tay Front/Back để tạo thẻ mới.
- [ ] **Nút "Import":** Link sang trang Import TXT và tự động chọn bộ thẻ hiện tại trong ô "Target Deck".
- [ ] **Chỉnh sửa thẻ (Edit):** Modal hiển thị dữ liệu cũ để cập nhật nội dung thẻ.
- [ ] **Xóa thẻ (Delete):** Modal xác nhận (Confirm) và xóa thẻ khỏi Database.

---

## 2. Màn hình Dashboard (Tổng quan)

### A. Giao diện & Gamification (UI/UX)
- [ ] **Header:** Hiển thị lời chào cá nhân hóa "Welcome back, [Name]!".
- [ ] **Khối Quick Stats:** 
  - [ ] **Daily Streak:** Hiển thị chuỗi ngày học liên tiếp.
  - [ ] **Learning Milestone:** Thanh tiến trình tiến độ học trong tháng so với mục tiêu.
- [ ] **Active Decks Grid:** Hiển thị lưới các bộ thẻ đang học tích cực.
- [ ] **Thông số Card:** Thay "Cards/Notes/Imports" thành "Đã học / Tổng số" (VD: 120 / 500).
- [ ] **Nút Xóa Deck (Delete):** Thêm icon thùng rác và logic xác nhận xóa toàn bộ bộ thẻ.
- [ ] **Nút "Create New Deck":** Modal tạo Deck mới với 2 trường: **Name** và **Description**.

### B. Logic & API (Backend)
- [ ] **API Stats:** Hoàn thiện `GET /api/stats/dashboard` để cung cấp dữ liệu cho Streak và Milestone.
- [ ] **API Decks:** Cập nhật `GET /api/decks` trả về thêm thông số thẻ đã học/tổng số thẻ.
- [ ] **API Create Deck:** Hỗ trợ lưu trường `description`.

---

## 3. Study Session & TTS (Bổ sung)

### A. Text-To-Speech (TTS)
- [ ] **Giao diện:** Thêm icon Loa cạnh nội dung Front/Back.
- [ ] **Service:** Viết `tts-service.js` sử dụng **Web Speech API** để phát âm thanh.
- [ ] **Logic:** Đọc dữ liệu từ `plain_text` để tránh đọc nhầm thẻ HTML.

### B. Fix lỗi UI/UX
- [ ] **Xóa Text thừa:** Ẩn dòng `front_plain_text` hiển thị lặp lại bên dưới thẻ chính ở màn hình Study.
- [ ] **Import Screen:**
  - [ ] Đổi nút "Confirm Import" sang màu xanh Success.
  - [ ] Disable nút Confirm sau khi đã bấm Import thành công.
  - [ ] Thêm margin-top cho các Message thông báo kết quả.
  - [ ] Custom CSS để ẩn chữ "No file chosen" mặc định của trình duyệt.

---

## 4. Thứ tự ưu tiên triển khai
1. **Ưu tiên 1:** Hoàn thiện toàn bộ logic và UI của **Deck Detail** (Bao gồm Sửa/Xóa/Tìm kiếm).
2. **Ưu tiên 2:** Tích hợp **TTS** và sửa các lỗi **UI/UX** lặt vặt.
3. **Ưu tiên 3:** Nâng cấp toàn diện màn hình **Dashboard**.
