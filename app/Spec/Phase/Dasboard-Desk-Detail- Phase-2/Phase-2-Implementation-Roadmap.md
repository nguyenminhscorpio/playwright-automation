# Roadmap Triển khai Phase 2: Dashboard & Deck Detail

Tài liệu này hướng dẫn chi tiết các bước thực hiện để hoàn thiện các yêu cầu nghiệp vụ của Phase 2, bao gồm nâng cấp Dashboard, quản lý thẻ (Deck Detail), tính năng TTS và các bản vá UI/UX.

---

## 1. Implementation Scope (Phạm vi triển khai)

### Chức năng chính
- **Màn hình Dashboard mới:**
  - UI/UX chuẩn thiết kế (Lexend, Inter, Shadows).
  - Thống kê Gamification: Daily Streak, Learning Milestone (Progress Bar).
  - Lưới các bộ thẻ đang học (Active Decks) với thông số "Đã học / Tổng số".
  - Chức năng Tạo bộ thẻ mới (Có thêm trường Description).
  - Chức năng Xóa bộ thẻ trực tiếp từ Dashboard.
- **Màn hình Deck Detail (Quản lý thẻ):**
  - Giao diện dạng Bảng (Table) chuyên nghiệp.
  - Tìm kiếm thẻ (Search by front/back/description).
  - Lọc thẻ theo trạng thái (Filter by status).
  - CRUD Thẻ: Tạo mới thủ công (Modal), Chỉnh sửa, Xóa thẻ.
  - Nút Import kết nối nhanh tới màn hình Import TXT.
- **Study Session & TTS:**
  - Tích hợp biểu tượng Loa (TTS) cho mặt Front/Back.
  - Xóa bỏ đoạn text phụ thừa thãi ở mặt Front.
- **Import Screen Fixes:**
  - Chỉnh sửa màu nút Confirm (Xanh), logic Disable.
  - Fix margin message và ẩn text mặc định của trình duyệt.

### Chức năng chưa làm / Để sau
- Logic FSRS nâng cao (vẫn dùng bản đơn giản đã chốt).
- Tính năng chia sẻ Deck công khai.
- Thống kê chi tiết (Analytics) chuyên sâu ngoài Dashboard.

---

## 2. Implementation Order (Thứ tự thực hiện)

1. **Giai đoạn 1: Chuẩn bị Backend & API (Nền tảng)**
   - Cập nhật Database (Migration/Model) nếu cần.
   - Hoàn thiện API `GET /api/decks` (thêm stats) và `POST /api/decks` (thêm description).
   - Xây dựng API `GET /api/cards` với đầy đủ Search/Filter/Pagination.
   - Xây dựng API `GET /api/stats/dashboard`.

2. **Giai đoạn 2: Triển khai Deck Detail (Lõi quản lý)**
   - Dựng giao diện Table UI mới cho màn hình chi tiết bộ thẻ.
   - Kết nối Logic Search và Filter Status.
   - **Implement các Modal: Create Card (Manual), Edit Card, Delete Card.**

3. **Giai đoạn 3: Triển khai Dashboard (Trải nghiệm người dùng)**
   - Dựng Layout Dashboard mới theo thiết kế hiện đại.
   - Đổ dữ liệu từ API Stats vào Streak và Milestone.
   - Render Active Decks Grid.
   - Implement Modal Create Deck và nút Delete Deck.

4. **Giai đoạn 4: TTS & UI UX Polish (Hoàn thiện)**
   - Tích hợp Web Speech API cho nút Loa ở Study Session.
   - Sửa các lỗi UI nhỏ ở màn hình Import và Study theo yêu cầu.

---

## 3. Phase Roadmap (Lộ trình chi tiết)

| Phase | Mục tiêu | Task cụ thể | Tài liệu tham chiếu | Ưu tiên |
|---|---|---|---|---|
| **1** | **Backend Refactor** | Update `Deck` & `Card` API, viết `DashboardStatsService`. | `API-Phase-2-Spec.md` | P0 |
| **2** | **Deck Detail UI** | Dựng Table UI, cột Last Reviewed, Next, Actions. | `02-Deck-Detail...md` | P0 |
| **3** | **Card Actions** | Viết Logic CRUD Card (Create/Edit/Delete) + Search/Filter. | `02-Deck-Detail...md` | P0 |
| **4** | **Dashboard UI** | Dựng Layout, Header, Streak, Milestone, Decks Grid. | `01-Dashboard...md` | P1 |
| **5** | **Deck Actions** | Viết Logic Create Deck (với Description) và Delete Deck. | `01-Dashboard...md` | P1 |
| **6** | **TTS Integration** | Viết `tts-service.js` và gắn icon vào Study Session. | `03-Study-Session...md` | P1 |
| **7** | **Final Polish** | Fix UI Import Screen, ẩn text thừa, margin message. | `03-Study-Session...md` | P2 |

---

## 4. Checklist Thực hiện (Checklist cho AI)

### A. Deck Detail (Quản lý thẻ)
- [x] Cập nhật `CardRepository` hỗ trợ `search` và `filter status`.
- [x] Cập nhật `CardController@index` trả về dữ liệu phân trang chuẩn.
- [x] Sửa file `deck-detail.blade.php`:
  - [x] Thay đổi list view thành Table UI.
  - [x] Thêm input Search và dropdown Status.
  - [x] Xoá cột `DECK` vì dư thừa trong trang chi tiết.
  - [x] Thêm cột `LAST REVIEWED`.
  - [x] Cập nhật cột `NEXT` theo format ngày/giờ phải ôn lại.
  - [x] Cập nhật CSS Table UI theo `DESIGN.md`.
  - [x] Thêm cột Actions và các nút Sửa/Xóa.
- [x] **Tạo Modal "Create New Card":** Form nhập tay Front/Back cho thẻ mới.
- [x] **Tạo Modal "Edit Card":** Form chỉnh sửa nội dung thẻ hiện có.
- [x] **Tạo Modal "Confirm Delete":** Xác nhận trước khi xóa thẻ.
- [x] Viết API/Controller cho `store`, `update`, `destroy` Card.
- [x] Link nút `Import` sang màn hình Import TXT và preselect deck hiện tại.

### B. Dashboard (Tổng quan)
- [x] Viết `DashboardController@index` gọi `DashboardStatsService`.
- [x] Hiển thị Header chào `Welcome back, [Name]!`.
- [x] Tính toán Streak từ bảng `review_logs` hoặc `study_days`.
- [x] Sửa file `dashboard.blade.php`:
  - [x] Dựng khung UI mới theo `DESIGN.md`.
  - [x] Render Milestone Progress Bar.
  - [x] Render khối `Quick Stats`.
  - [x] Thay hiển thị `Cards/Notes/Imports` bằng `Learned / Total` trên Deck card.
  - [x] Thêm nút/icon `Delete Deck` và logic xác nhận xóa.
  - [x] Render Active Decks với thông số "Learned / Total".
- [x] Thêm Modal "Create New Deck" với trường Description.
- [x] Cập nhật API `GET /api/decks` trả về thông số learned/total.
- [x] Cập nhật API Create Deck hỗ trợ trường `description`.

### C. Study & TTS
- [x] Viết file `resources/js/tts.js` sử dụng `window.speechSynthesis`.
- [x] Dùng dữ liệu `plain_text` cho TTS để tránh đọc nhầm HTML.
- [x] Thêm nút Loa vào `study/front.blade.php` và `study/answer.blade.php`.
- [x] Xóa code hiển thị `front_plain_text` lặp lại.

### D. Import Screen
- [x] Sửa CSS `imports.blade.php` để ẩn text mặc định của input file.
- [x] Thêm class màu xanh và thuộc tính `disabled` cho nút Confirm sau khi bấm.
- [x] Thêm `mt-4` hoặc khoảng cách cho các alert messages.

---

## 5. Mapping Tài liệu thực hiện

| Phần việc | Tài liệu Spec chính |
|---|---|
| Dashboard UI & Logic | `01-Dashboard-Nghiep-vu.md` |
| Deck Detail & Card CRUD | `02-Deck-Detail-Nghiep-vu.md` |
| TTS & UI UX Fixes | `03-Study-Session-Bo-Sung-Nghiep-vu.md` |
| API & Database Layer | `API-Phase-2-Spec.md`, `Database-Phase-2-Spec.md` |
| Danh sách Checklist | `Phase-2-Todo-List.md` |
