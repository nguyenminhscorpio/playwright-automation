# 02. Nghiệp vụ màn hình Deck Detail (Phase 2)

Tài liệu này được cập nhật theo source hiện tại của project `vibe-coding`.

## 1. Mục đích
- Route màn hình: `/decks/{deck}`
- Đây là màn hình quản lý card trong một deck cụ thể.
- Mục tiêu hiện tại:
  - xem danh sách card theo deck
  - tìm kiếm và lọc theo trạng thái
  - tạo, sửa, xóa card
  - bulk delete card
  - điều hướng nhanh sang màn hình Import của đúng deck đó

## 2. Phạm vi hiện đang có trong source
- Breadcrumb `My Decks / [deck switcher]`
- Header quản lý deck
- Nút `Create Card`
- Nút `Import`
- Nút `Delete Selected`
- Bộ lọc search + status
- Bảng card có phân trang
- Modal tạo/sửa card
- Modal xác nhận xóa card

## 3. Thành phần UI và hành vi hiện tại

### 3.1. Breadcrumb và chuyển deck
- Dòng breadcrumb gồm:
  - link `My Decks` về Dashboard
  - dropdown deck switcher
- Khi đổi deck trong dropdown:
  - frontend redirect sang `/decks/{id}` của deck được chọn

### 3.2. Header quản lý
- Tiêu đề hiện tại: `Card Management`
- Tên deck được hiển thị dưới dạng badge cạnh tiêu đề.
- Subtitle ưu tiên dùng `deck.description`, nếu trống sẽ hiển thị câu mô tả mặc định.

### 3.3. Thanh action
- `Delete Selected`
  - mặc định ẩn
  - chỉ hiện khi có card được chọn
- `Create Card`
  - mở modal tạo card
- `Import`
  - chuyển sang `/imports?deck_id={id}`
  - deck hiện tại sẽ được chọn sẵn ở màn Import

### 3.4. Bộ lọc và tìm kiếm
- Ô search cho phép tìm theo:
  - front
  - back
  - plain text
  - `note_text`
- Dropdown status hiện có:
  - `Any Status`
  - `Learning`
  - `Review`
  - `New`
- Nút `Apply` submit lại trang với query string hiện tại.

### 3.5. Card table
- Bảng hiện có các cột:
  - checkbox
  - `FRONT`
  - `BACK`
  - `STATUS`
  - `LAST REVIEWED`
  - `MASTERY`
  - `NEXT`
  - `ACTIONS`

### 3.6. Render dữ liệu từng dòng
- `FRONT`
  - ưu tiên `front_plain_text`
  - fallback sang `front_text`
- `BACK`
  - dùng `back_plain_text`
  - đang truncate khoảng 50 ký tự
- `STATUS`
  - `review` -> `Review`
  - `learning` hoặc `relearning` -> `Learning`
  - còn lại -> `New`
- `LAST REVIEWED`
  - dùng `diffForHumans()`
  - nếu chưa học lần nào thì hiện `Never`
- `MASTERY`
  - hiện là progress bar ước lượng theo UI
  - `review`: `round(stability * 10)`, tối đa 100%
  - `learning/relearning`: 20%
  - `new`: 0%
- `NEXT`
  - `NULL` -> `-`
  - quá hạn hoặc cùng ngày nhưng đã qua giờ -> `Today`
  - cùng ngày và còn dưới 60 phút -> `In Xm`
  - cùng ngày và còn trên 60 phút -> `In Xh`
  - các ngày sau -> `In X day(s)`
- `ACTIONS`
  - nút `Edit`
  - nút `Delete`

### 3.7. Empty state và pagination
- Nếu không có card khớp điều kiện lọc:
  - hiển thị `No cards found for the current search or status filter.`
- Phân trang đang dùng Laravel paginator.
- Số bản ghi mỗi trang hiện tại: `20`

## 4. Dữ liệu và logic nghiệp vụ theo source hiện tại

### 4.1. User và deck context
- Màn hình dùng cùng cơ chế resolve user như Dashboard:
  - ưu tiên `dev.study@example.com`
  - hoặc user đầu tiên có card
- Deck được phép xem phải thuộc đúng `user_id` đó.

### 4.2. Lấy danh sách card
- `ScreenController::deckDetail()` gọi `CardRepository::paginateForUser()`
- Filter gửi xuống repository:
  - `deck_id`
  - `q`
  - `status`
- Sort hiện tại:
  - `id` giảm dần

### 4.3. Search logic
- Search đang match trên bảng `notes` với các trường:
  - `front_text`
  - `back_text`
  - `front_plain_text`
  - `back_plain_text`
  - `note_text`

### 4.4. Status filter logic
- `all` hoặc rỗng -> không filter
- `learning` -> gom cả `learning` và `relearning`
- `new`, `review`, `relearning` -> filter đúng theo state tương ứng

## 5. CRUD card theo source hiện tại

### 5.1. Tạo card
- Modal có 2 trường:
  - `Front`
  - `Back`
- Submit gọi `POST /api/cards`
- Payload:
  - `deck_id`
  - `front_text`
  - `back_text`
- Backend tạo:
  - 1 `note`
  - 1 `card`
- Card mới được khởi tạo với:
  - `state = new`
  - `due_at = null`
  - `learning_steps_json = [1, 10]`
  - `relearning_steps_json = [10]`

### 5.2. Sửa card
- Nút `Edit` mở lại đúng modal create nhưng ở mode edit.
- Submit gọi `PUT /api/cards/{id}`
- Backend cập nhật:
  - `front_text`
  - `back_text`
  - `front_plain_text`
  - `back_plain_text`

### 5.3. Xóa một card
- Nút `Delete` mở modal xác nhận.
- UI hiện tại dùng `DELETE /api/cards/bulk` cho cả single delete và bulk delete.
- Nếu note không còn card nào tham chiếu sau khi xóa, note cũng bị xóa.

### 5.4. Bulk delete
- Có thể chọn nhiều card bằng checkbox.
- Checkbox ở header bật chế độ chọn tất cả.
- Khi submit bulk delete:
  - gọi `DELETE /api/cards/bulk`
  - payload có thể là:
    - `ids` cho tập card được chọn cụ thể
    - hoặc `all = true` + `deck_id` + `exclude_ids`

## 6. Ghi chú hiện trạng quan trọng
- `Delete Selected` hiện đang reload toàn trang sau khi thao tác xong.
- `Create Card` và `Edit Card` cũng reload trang sau khi lưu thành công.
- Chưa có `More Filters`.
- Chưa có chức năng move card hay bulk action khác ngoài delete.
- Chưa có chỉnh sửa thông tin deck tại màn này.
- Checkbox `Select all` hiện đang hoạt động theo `deck_id` ở backend, không truyền `q/status`.
  - Nghĩa là nếu dùng chế độ `all = true`, thao tác xóa có thể ảnh hưởng toàn bộ card của deck, không chỉ danh sách đang nhìn thấy theo filter hiện tại.
