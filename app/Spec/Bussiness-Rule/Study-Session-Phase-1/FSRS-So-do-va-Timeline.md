# Sơ đồ và Timeline FSRS cho Study Session

## 1. Mục đích
- Giúp nhìn nhanh toàn bộ vòng đời của một card
- Tách riêng phần trực quan khỏi file logic chi tiết
- Bám theo mô hình Anki-like:
  - `Learning`
  - `Review`
  - `Relearning`

## 2. Sơ đồ tổng quan
```text
                    +----------------------+
                    |       Card New       |
                    +----------------------+
                               |
                               v
                    +----------------------+
                    |      LEARNING        |
                    |   step 1: 1 phút     |
                    |   step 2: 10 phút    |
                    +----------------------+
                               |
                 +-------------+-------------+-------------+
                 |             |                           |
                 |           Hard                        Good
                 |      (vẫn learning)                    |
                 |                                         |
              Again                                        v
      quay lại step đầu                          sang step tiếp
                 |                                         |
                 +-----------------------------------------+
                               |
                               v
                    +----------------------+
                    |      GRADUATE        |
                    |   ra khỏi learning   |
                    +----------------------+
                               |
                               v
                    +----------------------+
                    |       REVIEW         |
                    |     dùng FSRS        |
                    +----------------------+
                      |         |         |         |
                      |         |         |         |
                    Again      Hard      Good      Easy
                      |         |         |         |
                      v         v         v         v
               +-------------+ tăng ít  tăng vừa  tăng mạnh
               | RELEARNING  |
               |  step 10p   |
               +-------------+
                      |
             +--------+--------+--------+
             |        |        |        |
           Again     Hard     Good     Easy
             |        |        |        |
             |        |        +--------+-------> quay lại REVIEW
             |        |
             |        +-------> vẫn ở RELEARNING, due ngắn
             |
             +-------> quay lại step đầu
```

## 3. Mapping với UI hiện tại
```text
Lật thẻ:
03 Flip Front
   -> 05 Answer Revealed
   -> chọn Again / Hard / Good / Easy
   -> cập nhật phase và lịch học

Nhập chữ:
04 Typing Input
   -> 05 Answer Revealed
   -> chọn Again / Hard / Good / Easy
   -> cập nhật phase và lịch học
```

## 4. Timeline thực tế của một card mới

### Giai đoạn 1. Card mới
Trạng thái:
- `New`

Khi người dùng học lần đầu:
- card được đưa vào `Learning`

### Giai đoạn 2. Learning step 1
Due:
- `1 phút`

Các khả năng:
- `Again`
  - quay lại step đầu
  - due lại sau `1 phút`
- `Hard`
  - vẫn ở learning
  - có thể giữ step hiện tại hoặc lên step tiếp theo tùy cấu hình đơn giản
- `Good`
  - sang step tiếp theo
  - due sau `10 phút`
- `Easy`
  - graduate ngay sang `Review`
  - due theo `easy interval`

### Giai đoạn 3. Learning step 2
Due:
- `10 phút`

Các khả năng:
- `Again`
  - quay lại step đầu `1 phút`
- `Hard`
  - vẫn ở learning
  - due ngắn hơn `Good`
- `Good`
  - graduate sang `Review`
- `Easy`
  - graduate sang `Review`

## 5. Timeline ví dụ đầy đủ

### Ví dụ A. Học thuận lợi
Day 0:
- Card New
- Học lần đầu
- `Good`
- Due sau `1 phút`

Sau 1 phút:
- gặp lại card
- `Good`
- Due sau `10 phút`

Sau 10 phút:
- gặp lại card
- `Good`
- Graduate sang `Review`

Day 1:
- Review lần 1
- nếu `Good` -> interval tăng

Day 3:
- Review lần 2

Day 7:
- Review lần 3

## 6. Timeline ví dụ khi quên ở Review
Giả sử:
- card đang ở `Review`
- current interval = `7 ngày`

Tại Day 7:
- người dùng gặp lại card
- người dùng bấm `Again`

Kết quả:
- card rời `Review`
- card vào `Relearning`
- due sau `10 phút`

Sau 10 phút:
- người dùng gặp lại card
- nếu `Good`
  - card quay lại `Review`
- nếu `Again`
  - reset relearning step

## 7. Timeline ví dụ cho Typing Mode
Day 0:
- người dùng chọn mode `Nhập chữ`
- vào màn `04 Typing Input`
- nhập đáp án
- sang màn `05 Answer Revealed`
- chọn rating

Nếu card đang ở `Learning`:
- hệ thống cập nhật step learning

Nếu card đang ở `Review`:
- hệ thống cập nhật interval FSRS

Nếu card đang ở `Review` và người dùng chọn `Again`:
- card vào `Relearning`

## 8. Ý nghĩa thời gian trong app

### `1 phút`
- dùng cho learning step đầu
- mục đích: nhắc lại rất nhanh

### `10 phút`
- dùng cho learning step tiếp theo
- cũng là relearning step mặc định

### `1 ngày`, `3 ngày`, `7 ngày` hoặc công thức FSRS
- thuộc giai đoạn `Review`
- là ôn dài hạn

## 9. Phân biệt rất quan trọng

### Learning
- thời gian ngắn
- cố định
- chưa dùng FSRS

### Review
- thời gian dài hơn
- dùng FSRS

### Relearning
- ngắn hạn
- để sửa khi quên
- xong rồi quay lại Review

## 10. Tóm tắt siêu ngắn
```text
New
 -> Learning (1m -> 10m)
 -> Review (FSRS)
 -> nếu quên ở Review
 -> Relearning (10m)
 -> quay lại Review
```

## 11. Kết luận
- `03` là bước xem card
- `04` là bước nhập đáp án
- `05` là bước reveal và chấm rating
- timeline của card phía sau chạy theo:
  - `Learning`
  - `Review`
  - `Relearning`

File này dùng để nhìn flow nhanh.
File `FSRS-Logic.md` dùng để đọc logic chi tiết.
