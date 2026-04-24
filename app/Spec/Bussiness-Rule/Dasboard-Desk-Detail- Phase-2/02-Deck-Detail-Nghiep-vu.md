# 02. Nghiệp vụ màn hình Deck Detail (Phase 2)

## 1. Mục đích
- Màn hình Deck Detail tập trung vào duy nhất một Bộ thẻ (Deck) cụ thể (vd: Japanese Vocabulary).
- Cung cấp thông tin chi tiết về các thẻ (Cards) bên trong bộ thẻ đó, kèm theo trạng thái học tập của từng thẻ.
- Là nơi người dùng thao tác quản lý thẻ (Thêm mới, Import) đối với bộ thẻ đang xem.

## 2. Vai trò nghiệp vụ
- Quản lý nội dung vi mô (Micro-management) của một Deck.
- Minh bạch hoá trạng thái FSRS của từng card (New, Learning, Review).
- Đóng vai trò là trang đích (Landing Page) cho một chủ đề học tập cụ thể.

## 3. Thành phần UI chính trên màn hình (Dựa theo thiết kế)
1. **Breadcrumb:** Hiển thị đường dẫn điều hướng (`My Decks > Japanese Vocabulary`).
2. **Header (Khu vực Nút hành động):** 
   - Tên Deck lớn (Japanese Vocabulary) và Mô tả phụ.
   - Nút **"Create Card"**: Khi bấm vào sẽ mở ra Form (Modal hoặc trang riêng) để thêm thẻ mới bằng cách nhập chữ thủ công.
   - Nút **"Import"**: Khi bấm vào sẽ link thẳng tới màn hình "Import TXT into your deck", đồng thời phải chọn mặc định tên của Deck hiện tại ở ô chọn `Target deck`.
3. **Bộ lọc / Tìm kiếm (Filters & Search):**
   - Thanh tìm kiếm "Search cards by front, back, or tags..." ở trên cùng giúp tìm thẻ.
   - Các dropdown lọc: Lọc theo Status (New, Learning, Review).
   - Nút "More Filters" (Mở rộng bộ lọc nếu cần).
4. **Card Table (Danh sách thẻ dạng bảng):** Liệt kê các thẻ dưới dạng bảng (Data Table) thay vì dạng lưới (Grid). Các cột bao gồm:
   - **Checkbox:** Dùng để chọn nhiều thẻ cùng lúc (Bulk actions).
   - **FRONT:** Nội dung chính mặt trước của thẻ (vd: 食べる (Taberu)).
   - **BACK:** Nội dung mặt sau / Nghĩa của thẻ (vd: To eat).
   - **STATUS:** Nhãn trạng thái FSRS hiện tại của thẻ (vd: Review (Xanh), Learning (Vàng), New (Xám/Tím)).
   - **LAST REVIEWED:** Ngày giờ học lần cuối (vd: 2026-10-12 14:30 hoặc 2 days ago).
   - **MASTERY:** Thanh tiến trình (Progress bar) thể hiện độ thuần thục của thẻ.
   - **NEXT:** Ngày giờ cần học lại thẻ này (Ngày sẽ phải ôn lại).
   - **ACTIONS:** Các nút thao tác nhanh trên từng thẻ (Edit, Delete).
5. **Phân trang (Pagination):**
   - Hiển thị thông tin phân trang ở dưới cùng bảng (vd: "Showing 1 to 4 of 128 cards").
   - Nút Previous/Next.

## 4. Dữ liệu & Logic nghiệp vụ (Mapping Database Phase 1)
- **Deck Info:** Lấy từ bảng `decks` (`name`, `description`).
- **Tổng số Cards:** `COUNT(*)` từ bảng `cards` where `deck_id`.
- **Table Columns (Dữ liệu bảng):** Truy vấn từ bảng `cards` join với `notes`.
  - **FRONT:** `notes.front_plain_text` hoặc hiển thị rút gọn `front_text`.
  - **BACK:** `notes.back_plain_text`.
  - **STATUS:** Map từ `cards.state`:
    - `new` -> Nhãn "New".
    - `learning` / `relearning` -> Nhãn "Learning" (Màu vàng).
    - `review` -> Nhãn "Review" (Màu xanh).
  - **LAST REVIEWED:** Lấy từ trường `cards.last_review` (nếu có) hoặc thời gian log gần nhất. Hiển thị dạng "Never" nếu thẻ `new`.
  - **MASTERY:** Tính toán phần trăm thuần thục dựa trên `cards.stability` hoặc `cards.reps` để render độ dài của thanh Progress Bar.
  - **NEXT:** Lấy từ `cards.due_at`. Hiển thị chính xác ngày/giờ sẽ phải ôn lại.
    - Nếu `due_at` là NULL (thẻ `new`), hiển thị "-". 
    - Nếu `due_at` <= thời điểm hiện tại, hiển thị "Today" hoặc "Now". 
    - Nếu lớn hơn ngày hiện tại, hiển thị ngày giờ cụ thể.

## 5. Hành vi người dùng & Điều hướng
- **Tìm kiếm & Lọc:** Nhập text vào thanh tìm kiếm hoặc chọn Status ở dropdown để filter trực tiếp danh sách thẻ trong bảng.
- **Bulk Action (Chọn nhiều):** Checkbox đầu mỗi dòng cho phép chọn nhiều thẻ để xoá hoặc di chuyển (Future Phase).
- **Nút Import:** Chuyển hướng sang màn hình Import (`/imports`), và tự động đẩy ID của Deck hiện tại sang để ô `Target Deck` chọn sẵn mặc định.
- **Nút Create Card:** Bấm nút này mở ra Form (Modal pop-up hoặc trang mới) cho phép điền text thủ công để tạo Flashcard ngay lập tức.
- **Chỉnh sửa / Xóa Card:** 
  - Click vào nút Edit ở dòng thẻ sẽ mở ra popup form với dữ liệu cũ của thẻ để sửa nội dung Front/Back.
  - Click vào nút Delete ở dòng thẻ sẽ hiển thị hộp thoại xác nhận cảnh báo trước khi xóa. Sau khi xóa, cập nhật lại dữ liệu bảng mà không cần tải lại cả trang nếu có thể.

## 6. Kết quả mong đợi
- Người dùng có cái nhìn chi tiết và toàn diện về trạng thái các thẻ trong bộ từ vựng.
- Người dùng dễ dàng thêm thẻ mới, import từ vựng hoặc chỉnh sửa thẻ hiện có.

## 7. Ghi chú triển khai
- Các tham số "Review", "Learning", "New" lấy trực tiếp từ field `state` của bảng `cards`.
- Phân trang (Pagination): Do giao diện là Table list, việc load dữ liệu bắt buộc phải dùng phân trang (offset/limit) với các nút Prev/Next thay vì tải toàn bộ. Mặc định có thể load 20-50 thẻ/trang.
