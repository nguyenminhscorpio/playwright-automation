# Danh sách công việc Phase 2 (Master Todo List)

Tài liệu này tổng hợp toàn bềEcác đầu việc cần thực hiện cho Phase 2, dựa trên các đặc tả nghiệp vụ và kỹ thuật đã chốt.

---

## 1. Màn hình Deck Detail (Quản lý thẻ)

### A. Giao diện Bảng (Table UI)
- [ ] **Xoá cột DECK:** Vì dư thừa trong trang chi tiết.
- [x] **Thêm cột LAST REVIEWED:** Hiển thềEngày giềEhọc gần nhất.
- [x] **Cập nhật cột NEXT:** Format lại hiển thềEngày giềEphải ôn lại.
- [x] **Thêm cột ACTIONS:** Chứa biểu tượng Sửa (Edit) và Xóa (Delete).
- [ ] **CSS Styling:** Áp dụng thiết kế từ `DESIGN.md` (Bo góc 16px cho bảng, màu chữ charcoal #191c1e, hover row).

### B. Chức năng tương tác (Actions)
- [x] **Tìm kiếm (Search):** Code logic tìm kiếm thẻ theo Front/Back/Description qua API (`GET /api/cards?q=...`).
- [x] **Lọc trạng thái (Filter):** Code logic lọc theo Status (New, Learning, Review).
- [x] **Nút "Create Card":** Xây dựng Modal Form nhập tay Front/Back đềEtạo thẻ mới.
- [x] **Nút "Import":** Link sang trang Import TXT và tự động chọn bềEthẻ hiện tại trong ô "Target Deck".
- [x] **Chỉnh sửa thẻ (Edit):** Modal hiển thềEdữ liệu cũ đềEcập nhật nội dung thẻ.
- [x] **Xóa thẻ (Delete):** Modal xác nhận (Confirm) và xóa thẻ khỏi Database.

---

## 2. Màn hình Dashboard (Tổng quan)

### A. Giao diện & Gamification (UI/UX)
- [x] **Header:** Hiển thềElời chào cá nhân hóa "Welcome back, [Name]!".
- [ ] **Khối Quick Stats:** 
  - [x] **Daily Streak:** Hiển thềEchuỗi ngày học liên tiếp.
  - [x] **Learning Milestone:** Thanh tiến trình tiến đềEhọc trong tháng so với mục tiêu.
- [x] **Active Decks Grid:** Hiển thềElưới các bềEthẻ đang học tích cực.
- [x] **Thông sềECard:** Thay "Cards/Notes/Imports" thành "Đã học / Tổng sềE (VD: 120 / 500).
- [x] **Nút Xóa Deck (Delete):** Thêm icon thùng rác và logic xác nhận xóa toàn bềEbềEthẻ.
- [x] **Nút "Create New Deck":** Modal tạo Deck mới với 2 trường: **Name** và **Description**.

### B. Logic & API (Backend)
- [x] **API Stats:** Hoàn thiện `GET /api/stats/dashboard` đềEcung cấp dữ liệu cho Streak và Milestone.
- [x] **API Decks:** Cập nhật `GET /api/decks` trả vềEthêm thông sềEthẻ đã học/tổng sềEthẻ.
- [x] **API Create Deck:** HềEtrợ lưu trường `description`.

---

## 3. Study Session & TTS (BềEsung)

### A. Text-To-Speech (TTS)
- [x] **Giao diện:** Thêm icon Loa cạnh nội dung Front/Back.
- [x] **Service:** Viết `tts-service.js` sử dụng **Web Speech API** đềEphát âm thanh.
- [x] **Logic:** Đọc dữ liệu từ `plain_text` đềEtránh đọc nhầm thẻ HTML.

### B. Fix lỗi UI/UX
- [x] **Xóa Text thừa:** Ẩn dòng `front_plain_text` hiển thềElặp lại bên dưới thẻ chính ềEmàn hình Study.
- [ ] **Import Screen:**
  - [x] Đổi nút "Confirm Import" sang màu xanh Success.
  - [x] Disable nút Confirm sau khi đã bấm Import thành công.
  - [x] Thêm margin-top cho các Message thông báo kết quả.
  - [x] Custom CSS đềEẩn chữ "No file chosen" mặc định của trình duyệt.

---

## 4. Thứ tự ưu tiên triển khai
1. **Ưu tiên 1:** Hoàn thiện toàn bềElogic và UI của **Deck Detail** (Bao gồm Sửa/Xóa/Tìm kiếm).
2. **Ưu tiên 2:** Tích hợp **TTS** và sửa các lỗi **UI/UX** lặt vặt.
3. **Ưu tiên 3:** Nâng cấp toàn diện màn hình **Dashboard**.


