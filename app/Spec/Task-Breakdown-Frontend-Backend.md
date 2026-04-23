# Phân rã task triển khai theo UI hiện tại

## 1. Ghi chú phạm vi hiện tại
- Bản hiện tại ưu tiên `UI mock`.
- Chưa nối database thật.
- Chưa triển khai API thật.
- Task dưới đây vẫn giữ cấu trúc `Frontend / Backend` để sẵn sàng cho giai đoạn tiếp theo, nhưng phần đang hoàn thiện chủ yếu là frontend.

## 2. Task Frontend đã bám theo UI hiện tại

### Dashboard
- Dựng layout dashboard
- Hiển thị stat cards
- Hiển thị progress block
- Hiển thị deck cards
- Gắn search input trong topbar

### Deck Detail
- Dựng heading và metadata deck
- Dựng progress của deck
- Dựng grid danh sách card
- Dựng CTA import và add card

### Study Session - Flip Front
- Dựng session progress
- Dựng progress bar
- Dựng front card
- Dựng TTS icon
- Dựng CTA `Show Answer`

### Study Session - Typing Input
- Dựng current deck progress
- Dựng front card
- Dựng panel `Your Answer`
- Dựng `Show Hint`
- Dựng `Check Answer`
- Cho mode `Nhập chữ` mở trực tiếp màn hình này

### Study Session - Answer Revealed
- Dựng prompt section
- Dựng correct answer section
- Dựng `Your Answer` section cho typing mode
- Dựng rating panel

### Study Mode Switch
- Đưa mode switch lên topbar
- Thiết kế `segmented control`
- Đồng bộ active state
- Ghi nhớ mode theo session frontend
- Đổi route/flow theo mode đang chọn

## 3. Task Frontend còn lại
- Hoàn thiện hiển thị tiếng Việt có dấu ổn định ở toàn bộ view
- Chuẩn hóa mock data dùng chung cho study flow
- Truyền `user_answer` từ màn typing sang answer screen
- Hiển thị trạng thái so sánh `đúng / gần đúng / sai` ở answer screen
- Tối ưu responsive cho iPad dọc và mobile

## 4. Task Backend cho giai đoạn sau
- Thiết kế schema `decks`, `cards`, `review_logs`, `study_sessions`
- Tạo API lấy danh sách deck
- Tạo API lấy card cần học theo FSRS
- Tạo API chấm đáp án typing mode
- Tạo API ghi log `Again / Hard / Good / Easy`
- Tạo API import txt từ Anki

## 5. Ưu tiên triển khai
1. Hoàn thiện toàn bộ UI study flow
2. Chuẩn hóa dữ liệu mock
3. Chốt spec import và answer checking
4. Thiết kế database
5. Nối backend và persistence
