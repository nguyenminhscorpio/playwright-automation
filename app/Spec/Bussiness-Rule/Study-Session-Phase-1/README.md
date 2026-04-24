# Tài liệu Study Session 03 - 04 - 05

## 1. Phạm vi
Bộ tài liệu này tập trung cho luồng học chính của ứng dụng:
- `03` Study Session - Flip Front
- `04` Study Session - Typing Input
- `05` Study Session - Answer Revealed

Ngoài 3 màn hình trên, folder này cũng bao gồm:
- rule import TXT
- API
- database
- logic FSRS
- sơ đồ và timeline

## 2. Danh sách tài liệu

### Nghiệp vụ màn hình
- `03-Study-Session-Flip-Front-Nghiep-vu.md`
- `04-Study-Session-Typing-Input-Nghiep-vu.md`
- `05-Study-Session-Answer-Revealed-Nghiep-vu.md`

### Import
- `Import-Anki-TXT-Spec.md`

### API và Database
- `API-Spec.md`
- `Database-Spec.md`

### FSRS
- `FSRS-Logic.md`
- `FSRS-So-do-va-Timeline.md`

### File mẫu import
- `Sample-File-Import/1000 Cụm từ Tiếng Anh.txt`
- `Sample-File-Import/3000 từ thông dụng.txt`

## 3. Cách đọc khuyến nghị

### Nếu muốn hiểu nhanh flow học
Đọc theo thứ tự:
1. `03-Study-Session-Flip-Front-Nghiep-vu.md`
2. `04-Study-Session-Typing-Input-Nghiep-vu.md`
3. `05-Study-Session-Answer-Revealed-Nghiep-vu.md`

### Nếu muốn hiểu logic ôn tập
Đọc:
1. `FSRS-Logic.md`
2. `FSRS-So-do-va-Timeline.md`

### Nếu muốn hiểu import dữ liệu
Đọc:
1. `Import-Anki-TXT-Spec.md`
2. các file trong `Sample-File-Import`

### Nếu muốn hiểu backend contract
Đọc:
1. `API-Spec.md`
2. `Database-Spec.md`

## 4. Điểm đã chốt trong bộ tài liệu này

### Study mode
- UI có 2 mode:
  - `Lật thẻ`
  - `Nhập chữ`
- `Typing mode` vào thẳng màn nhập
- `Answer Revealed` là màn dùng chung cho cả 2 mode

### Import TXT
- bỏ `mp3`
- bỏ `image`
- bỏ `tag`
- lấy `text đầu tiên` làm `mặt trước`
- lấy phần text còn lại làm `mặt sau`

### Logic học kiểu Anki
- có 3 phase:
  - `Learning`
  - `Review`
  - `Relearning`
- `Learning` dùng step ngắn
- `Review` mới dùng FSRS
- `Relearning` dùng step ngắn để sửa sai rồi quay lại review

## 5. Mục tiêu của folder này
- gom toàn bộ tài liệu quan trọng của `Study Session` vào một nơi
- giúp đọc spec không bị rải rác
- làm nền cho việc triển khai UI, API và database về sau
