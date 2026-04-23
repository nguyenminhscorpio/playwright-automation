# Tài liệu chức năng ứng dụng học từ vựng tiếng Anh

## Bộ tài liệu liên quan
- `Overview.md`
- `Requirements.md`
- `Functional-Spec.md`
- `Import-Anki-TXT-Spec.md`
- `Study-FSRS-Spec.md`
- `API-Spec.md`
- `Database-Spec.md`
- `UI-UX-Spec.md`
- `Roadmap.md`
- `HTML-Feature-Mapping.md`
- `Screen-Design-Spec.md`
- `Task-Breakdown-Frontend-Backend.md`
- `Tong-quan-trien-khai-du-an.md`

## Tổng quan sản phẩm
- Ứng dụng lấy cảm hứng từ Anki để học từ vựng tiếng Anh theo `Spaced Repetition`.
- Người dùng học theo từng `deck`, xem `card`, tự nhớ hoặc nhập đáp án, sau đó chấm mức độ nhớ bằng `Again / Hard / Good / Easy`.
- Giai đoạn hiện tại ưu tiên hoàn thiện `UI mock` bám sát giao diện Laravel/Vite đang chạy.
- Hệ thống chưa nối dữ liệu thật, nhưng toàn bộ spec cần bám theo flow màn hình hiện tại để sẵn sàng cho bước triển khai tiếp theo.

## Cấu trúc giao diện hiện tại
- Sidebar trái: `Dashboard`, `My Decks`, `Study Session`, `Statistics`, `Create New Deck`.
- Topbar: ô tìm kiếm, cụm chuyển mode học của Study Session, notification, help, avatar.
- Nội dung chính: dashboard, deck detail, study flow.

## Logic học cốt lõi theo UI hiện tại
- Study Session có 2 mode toàn cục:
  - `Lật thẻ`
  - `Nhập chữ`
- Mode được chọn ở `top-right` của Study Session, hoạt động như một `session preference`, tương tự theme switch.
- Khi chọn `Lật thẻ`:
  - hệ thống đưa người dùng về luồng `Front Side`
  - người dùng bấm `Show Answer`
  - sang màn hình `Answer Revealed`
- Khi chọn `Nhập chữ`:
  - hệ thống vào thẳng màn hình nhập đáp án
  - không cần bấm nút trung gian để chuyển sang màn hình nhập
  - người dùng nhập câu trả lời và bấm `Check Answer`
  - sang màn hình `Answer Revealed`

## Phạm vi 5 màn hình chính
- Dashboard
- Deck Detail
- Study Session - Flip Front
- Study Session - Typing Input
- Study Session - Answer Revealed

## Trạng thái hiện tại của UI
- Giao diện đã có layout thống nhất cho desktop và tablet.
- Study mode switch đã được đưa lên topbar.
- Typing mode là một flow riêng, vào trực tiếp khi chọn mode.
- Answer screen dùng chung cho cả 2 mode nhưng hiển thị khác nhau theo mode.
