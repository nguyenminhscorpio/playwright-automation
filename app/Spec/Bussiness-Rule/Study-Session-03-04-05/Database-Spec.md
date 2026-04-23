# Đặc tả cơ sở dữ liệu

## 1. Nguyên tắc
- Tài liệu giữ khung tổng thể cho toàn hệ thống
- Không thu gọn chỉ vì phase hiện tại chưa dùng hết
- Với các phần chưa dùng ở phase hiện tại, sẽ ghi rõ `Future Phase`

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
  - `source_file_name`
  - `source_raw_line`

### Ghi chú
- `note_text` có thể giữ để phục vụ mở rộng về sau
- không mâu thuẫn với phase hiện tại

## 5. cards
- `id`
- `note_id`
- `user_id`
- `deck_id`
- `state`
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

### Ghi chú
- bảng này cần cho FSRS và study session sau này
- giữ nguyên khung

## 6. review_logs
- `id`
- `user_id`
- `card_id`
- `rating`
- `typed_answer`
- `judged_result`
- `previous_state`
- `next_state`
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

## 7. import_jobs
- `id`
- `user_id`
- `deck_id`
- `file_name`
- `file_path`
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
