# Mapping giao diện HTML với chức năng thực tế

## 1. Nguyên tắc mapping
- Mapping phải bám theo UI Laravel/Vite hiện tại, không bám theo giả định cũ.
- Study Session hiện đã có `mode switch` ở topbar.
- `Typing Mode` là một flow trực tiếp, không còn là bước phụ xuất phát từ một nút dưới card.

## 2. Mapping màn hình
- `flashmind_dashboard`
  - map sang `Dashboard`
- `japanese_vocabulary_deck`
  - map sang `Deck Detail`
- `study_session_front_side`
  - map sang `Study Session - Flip Front`
- `study_session_typing_mode`
  - map sang `Study Session - Typing Input`
- `study_session_answer_revealed`
  - map sang `Study Session - Answer Revealed`
- `study_session_back_side`
  - không tách thành màn hình riêng trong bản hiện tại
  - được gộp vào `Answer Revealed` của `Flip Mode`

## 3. Mapping hành vi Study Mode
- `Lật thẻ`
  - dùng UI `Front Side`
  - hành động chính là `Show Answer`
- `Nhập chữ`
  - dùng UI `Typing Input`
  - hành động chính là `Check Answer`
- `Answer Revealed`
  - dùng chung cho cả 2 mode
  - thay đổi nội dung theo mode

## 4. Mapping topbar hiện tại
- Search input: tìm deck hoặc nội dung
- Study mode switch: đổi mode toàn session
- Notification icon
- Help icon
- Avatar người dùng
