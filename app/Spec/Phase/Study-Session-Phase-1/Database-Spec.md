# Đặc tả cơ sở dữ liệu

## 1. Nguyên tắc
- Tài liệu giữ khung tổng thể cho toàn hệ thống
- Không thu gọn chỉ vì phase hiện tại chưa dùng hết
- Với các phần chưa dùng ở phase hiện tại, sẽ ghi rõ `Future Phase`
- Phần study phải khớp với mô hình:
  - `Learning`
  - `Review`
  - `Relearning`

## 2. users
- `id`
- `name`
- `email`
- `password`
- `timezone`
- `locale`
- `preferred_tts_voice`
- `daily_goal`
- `reminder_time`
- `created_at`
- `updated_at`

### Ghi chú
- thuộc khung tổng thể của hệ thống

## 3. decks
- `id`
- `user_id`
- `name`
- `description`
- `color`
- `is_archived`
- `created_at`
- `updated_at`

## 4. notes
- `id`
- `user_id`
- `deck_id`
- `front_text`
- `back_text`
- `front_plain_text`
- `back_plain_text`
- `note_text`
- `source_type`
- `source_file_name`
- `source_raw_line`
- `created_at`
- `updated_at`

### Current Phase
- Với rule import hiện tại, dữ liệu thực dùng chủ yếu là:
  - `front_text`
  - `back_text`
  - `back_plain_text`
  - `source_file_name`
  - `source_raw_line`

### Rule chốt thêm
- `back_plain_text` là nguồn chuẩn để dùng cho `check-answer`
- `back_text` giữ vai trò hiển thị cho UI
- `front_plain_text` và `back_plain_text` nên được sinh ngay ở bước import hoặc khi tạo card thủ công

### Ghi chú
- `note_text` có thể giữ để phục vụ mở rộng về sau

## 5. cards
- `id`
- `note_id`
- `user_id`
- `deck_id`
- `state`
- `current_step`
- `learning_steps_json`
- `relearning_steps_json`
- `due_at`
- `last_reviewed_at`
- `stability`
- `difficulty`
- `elapsed_days`
- `scheduled_days`
- `reps`
- `lapses`
- `last_rating`
- `is_suspended`
- `created_at`
- `updated_at`

### Giải thích các field mới quan trọng
- `state`
  - `new`
  - `learning`
  - `review`
  - `relearning`
  - `suspended`
- `current_step`
  - step hiện tại trong `Learning` hoặc `Relearning`
- `learning_steps_json`
  - ví dụ: `[1, 10]` tính theo phút
- `relearning_steps_json`
  - ví dụ: `[10]` tính theo phút

### Current Phase
- Đây là bảng quan trọng nhất để nối UI với logic Anki-like
- phase hiện tại chốt giá trị mặc định:
  - `stability = 1.0`
  - `difficulty = 5.0`
- nếu card mới chưa dùng tới review thật, vẫn có thể set sẵn 2 giá trị này để dữ liệu ổn định cho lần nâng cấp FSRS sau

## 6. review_logs
- `id`
- `user_id`
- `card_id`
- `mode`
- `rating`
- `typed_answer`
- `judged_result`
- `previous_state`
- `next_state`
- `previous_step`
- `next_step`
- `previous_due_at`
- `next_due_at`
- `previous_stability`
- `next_stability`
- `previous_difficulty`
- `next_difficulty`
- `reviewed_at`

### Current Phase
- `typed_answer` chỉ có ý nghĩa khi mode là `typing`
- `judged_result` phục vụ so sánh đáp án nhập chữ
- `previous_step` và `next_step` cần để theo dõi:
  - learning
  - relearning

## 7. import_jobs
- `id`
- `user_id`
- `deck_id`
- `file_name`
- `file_path`
- `file_hash`
- `status`
- `total_rows`
- `success_rows`
- `failed_rows`
- `started_at`
- `finished_at`
- `error_summary`

### Current Phase
- phù hợp cho flow:
  - upload file
  - preview
  - confirm import
- `file_hash` dùng để bảo đảm confirm import bám đúng snapshot preview

## 8. import_job_rows
- `id`
- `import_job_id`
- `row_number`
- `raw_content`
- `parsed_front`
- `parsed_back`
- `parsed_audio_token`
- `parsed_tags`
- `status`
- `error_message`

### Current Phase
- Phase hiện tại chỉ cần dùng chắc chắn:
  - `parsed_front`
  - `parsed_back`
  - `status`
  - `error_message`

### Future Phase
- `parsed_audio_token`
- `parsed_tags`

### Ghi chú
- các field audio/tag không sai về mặt khung tổng thể
- nhưng hiện chưa dùng vì rule import đã chốt là bỏ audio, image, tag

## 9. study_days
- `id`
- `user_id`
- `studied_on`
- `review_count`
- `achieved_goal`

### Ghi chú
- phục vụ streak và dashboard sau này

## 10. media_assets
- `id`
- `user_id`
- `note_id`
- `provider`
- `file_name`
- `file_path`
- `mime_type`
- `size`
- `metadata_json`

### Future Phase
- bảng này giữ lại cho khả năng mở rộng về sau
- ví dụ:
  - audio thật
  - image thật
  - media từ nguồn import khác

### Ghi chú
- không dùng trong phase import TXT tối giản hiện tại
- vẫn nên giữ để khung database toàn vẹn
