# Logic FSRS cho Study Session 03 - 04 - 05

## 1. Mục tiêu
- Thống nhất logic học của app theo cách Anki thực tế hoạt động
- Bám theo 3 phase bắt buộc:
  - `Learning`
  - `Review`
  - `Relearning`
- Ghép logic này với flow UI hiện tại:
  - `03 - Study Session Flip Front`
  - `04 - Study Session Typing Input`
  - `05 - Study Session Answer Revealed`

## 2. Kết luận cốt lõi
Nếu build app theo hướng Anki clone thì card phải đi qua 3 phase:

1. `Learning`
   - học lần đầu
   - dùng step ngắn cố định
   - chưa dùng FSRS
2. `Review`
   - ôn dài hạn
   - bắt đầu dùng FSRS
3. `Relearning`
   - xảy ra khi đang review mà người dùng quên
   - quay lại step ngắn
   - sau đó quay lại review

## 3. Phân biệt 3 phase

### 3.1. Learning
- áp dụng cho card mới
- mục tiêu: nhồi nhanh trong ngắn hạn để card vượt qua giai đoạn học đầu
- dùng `learning steps`
- chưa tính interval bằng FSRS

### 3.2. Review
- áp dụng cho card đã graduate khỏi learning
- mục tiêu: ôn dài hạn
- dùng `FSRS`
- interval được tính theo thuật toán và rating

### 3.3. Relearning
- áp dụng khi card đang ở review mà người dùng bấm `Again`
- mục tiêu: sửa sai ngắn hạn
- dùng `relearning steps`
- sau khi xong relearning thì quay lại review

## 4. Cấu hình mặc định đơn giản theo Anki

### Learning steps
- `1 phút`
- `10 phút`

### Relearning steps
- `10 phút`

### Graduating interval
- khoảng `1 ngày`

### Easy interval
- khoảng `4 ngày`

## 5. Flow thực tế của một card mới

### Bước 1. Card mới
- card ở trạng thái `New`
- khi xuất hiện lần đầu, hệ thống đưa card vào `Learning`

### Bước 2. Learning step 1
- step đầu là `1 phút`

Nếu người dùng:
- `Again`
  - quay lại step đầu
- `Good`
  - sang step tiếp theo
- `Easy`
  - bỏ qua learning, graduate thẳng sang review

### Bước 3. Learning step 2
- step tiếp theo là `10 phút`

Nếu người dùng:
- `Again`
  - quay lại step đầu `1 phút`
- `Good`
  - graduate sang `Review`
- `Easy`
  - graduate sang `Review`

## 6. Sau khi graduate
- card rời khỏi `Learning`
- card chuyển sang `Review`
- từ đây mới dùng `FSRS`

## 7. Review phase
Trong `Review`, rating có ý nghĩa:
- `Again`
  - quên
  - reset mạnh
  - card vào `Relearning`
- `Hard`
  - nhớ khó
  - tăng interval ít
- `Good`
  - nhớ bình thường
  - tăng interval vừa
- `Easy`
  - nhớ rất dễ
  - tăng interval mạnh

## 8. Rule interval đơn giản cho Review
Ở phase hiện tại, có thể dùng rule đơn giản sau:

- `Again` -> vào `Relearning`
- `Hard` -> `max(1, scheduled_days * 1.2)`
- `Good` -> `max(2, scheduled_days * 2.0)`
- `Easy` -> `max(4, scheduled_days * 3.0)`

Ghi chú:
- `Again` ở review không chỉ là interval ngắn hơn
- mà là đổi phase sang `Relearning`

## 9. Relearning phase
Khi card đang ở `Review` mà người dùng bấm `Again`:
- card chuyển sang `Relearning`
- card đi vào step ngắn

Mặc định tối giản:
- `10 phút`

Nếu muốn gần Anki hơn sau này:
- có thể mở rộng thành `1 phút -> 10 phút`

### Trong Relearning
- `Again`
  - quay lại step đầu
- `Good`
  - sang step tiếp theo hoặc hoàn tất relearning
- `Easy`
  - có thể cho về `Review` nhanh hơn nếu muốn hỗ trợ

Khi hoàn tất toàn bộ relearning steps:
- card quay lại `Review`

## 10. Mapping với UI hiện tại

### Màn 03 - Flip Front
- là nơi người dùng xem mặt trước
- chưa cập nhật FSRS
- chưa đổi phase
- chỉ chuẩn bị cho bước reveal

### Màn 04 - Typing Input
- là nơi người dùng nhập đáp án
- chưa cập nhật FSRS
- chỉ thu `user_answer`

### Màn 05 - Answer Revealed
- là nơi người dùng chọn rating
- đây là điểm kích hoạt logic phase và FSRS

Khi người dùng bấm:
- `Again`
- `Hard`
- `Good`
- `Easy`

Hệ thống sẽ:
- xác định card đang ở `Learning`, `Review` hay `Relearning`
- áp dụng rule tương ứng của phase đó
- cập nhật:
  - `state`
  - `step` nếu có
  - `due_at`
  - `stability`
  - `difficulty`
  - `reps`
  - `lapses`

## 11. Mapping nút bấm theo phase

### Trong Learning
- `Again`
  - sai
  - quay về step đầu
- `Good`
  - đúng
  - sang step tiếp theo
- `Easy`
  - rất dễ
  - skip learning và vào review

### Trong Review
- `Again`
  - sai
  - vào relearning
- `Hard`
  - tăng ít
- `Good`
  - tăng vừa
- `Easy`
  - tăng mạnh

### Trong Relearning
- `Again`
  - quay về step đầu
- `Good`
  - sang step tiếp
- khi hết step:
  - quay lại review

## 12. Timeline ví dụ

### Ví dụ card mới
Day 0:
- New
- Learning step `1 phút`
- Learning step `10 phút`
- Graduate

Day 1:
- Review

Day 3:
- Review

Day 7:
- Review

Nếu ở Day 7 người dùng bấm `Again`:
- card vào `Relearning`
- due sau `10 phút`
- hoàn tất relearning rồi quay lại `Review`

## 13. Input của bộ lập lịch
- `card_id`
- `state`
- `current_step`
- `due_at`
- `scheduled_days`
- `stability`
- `difficulty`
- `reps`
- `lapses`
- `rating`
- `mode`
- `typed_answer` nếu có
- `judged_result` nếu có

## 14. Output của bộ lập lịch
- `next_state`
- `next_step`
- `next_due_at`
- `scheduled_days`
- `next_stability`
- `next_difficulty`
- `next_reps`
- `next_lapses`

## 15. Pseudo code chuẩn hóa theo hướng Anki
```text
if card.state == "new":
    card.state = "learning"
    card.step = 0

if card.state == "learning":
    if rating == "again":
        card.step = 0
    else if rating == "good":
        card.step += 1
    else if rating == "easy":
        graduate_to_review()

    if card.step >= learning_steps.length:
        graduate_to_review()

if card.state == "review":
    if rating == "again":
        card.state = "relearning"
        card.step = 0
    else:
        update_fsrs(card, rating)

if card.state == "relearning":
    if rating == "again":
        card.step = 0
    else:
        card.step += 1

    if card.step >= relearning_steps.length:
        card.state = "review"
```

## 16. Quy tắc chọn card trong session
- ưu tiên `Relearning`
- sau đó đến `Review`
- sau đó đến `Learning`
- cuối cùng mới đến `New`

## 17. Rule riêng cho Typing Mode
- `typed_answer` không thay thế rating
- kết quả so sánh chỉ là dữ liệu hỗ trợ
- quyết định cuối cùng để đổi phase và tính lịch vẫn là rating người dùng chọn

Ví dụ:
- người dùng nhập sai
- hệ thống vẫn hiển thị đáp án đúng
- người dùng chọn `Again`
- lúc đó card mới chính thức vào `Relearning`

## 18. Log cần lưu sau mỗi lần review
- `card_id`
- `mode`
- `state_before`
- `state_after`
- `step_before`
- `step_after`
- `rating`
- `typed_answer` nếu có
- `judged_result` nếu có
- `due_before`
- `due_after`
- `stability_before`
- `stability_after`
- `difficulty_before`
- `difficulty_after`
- `reviewed_at`

## 19. Kết luận
Logic chuẩn cần thống nhất là:
- `Learning` dùng step ngắn, chưa dùng FSRS
- `Review` mới dùng FSRS
- `Relearning` dùng step ngắn để sửa sai rồi quay lại review

Đây là khung logic đúng với cách Anki vận hành và phù hợp để app phát triển theo hướng clone Anki về sau.
