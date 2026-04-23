# Đặc tả học và FSRS

## Mục tiêu
- Tổ chức phiên học theo Spaced Repetition.
- Áp dụng FSRS đơn giản để dễ implement và nâng cấp sau này.

## Mức đánh giá
- `Again`: quên.
- `Hard`: nhớ khó.
- `Good`: nhớ được.
- `Easy`: nhớ rất tốt.

## Trạng thái thẻ học
- New
- Learning
- Review
- Relearning
- Suspended

## Đầu vào của bộ lập lịch
- State hiện tại
- Due time
- Stability
- Difficulty
- Reps
- Lapses
- Rating mới

## Đầu ra của bộ lập lịch
- State mới
- Due time mới
- Scheduled days mới
- Stability mới
- Difficulty mới
- Reps mới
- Lapses mới

## Quy tắc đề xuất

### Thẻ mới
- Again: ôn lại ngay.
- Hard: ôn lại sớm.
- Good: sang ngày hôm sau hoặc mốc ngắn.
- Easy: xa hơn Good.

### Thẻ đang ôn
- Again: vào relearning, tăng lapse.
- Hard: tăng interval nhẹ.
- Good: tăng interval theo chuẩn.
- Easy: tăng interval nhiều hơn.

## Công thức hướng dẫn
- Again -> `0-1 day`
- Hard -> `max(1, scheduled_days * 1.2)`
- Good -> `max(2, scheduled_days * 2.0)`
- Easy -> `max(4, scheduled_days * 3.0)`

## Ghi log
- Mỗi lần review phải tạo log.
- Log phải lưu before/after của state, due, stability, difficulty.

## Quy tắc phiên học
- Due card ưu tiên hơn new card.
- Relearning ưu tiên cao.
- New card bị giới hạn theo settings.
