# Đặc tả import TXT

## 1. Mục tiêu
- Dùng `1 file spec` duy nhất để mô tả chức năng import TXT.
- Bám theo `2 file mẫu` hiện có trong project.
- Ưu tiên cách làm đơn giản, đúng với nhu cầu hiện tại của app.

## 2. Hai file mẫu dùng để phân tích
- `Sample-File-Import/1000 Cụm từ Tiếng Anh.txt`
- `Sample-File-Import/3000 từ thông dụng.txt`

## 3. Đặc điểm chung của 2 file mẫu
- đều là file `.txt`
- đều có các dòng header bắt đầu bằng `#`
- dữ liệu phân tách bằng `tab`
- có thể chứa các field phụ như:
  - `[sound:...]`
  - HTML
  - tag
  - field rỗng

## 4. Phạm vi import đã chốt
Chức năng import hiện tại chỉ cần lấy ra:
- `mặt trước`
- `mặt sau`

Không cần lưu:
- `mp3`
- `image HTML`
- `tags`
- `phiên âm`
- `loại từ`
- các metadata phụ khác

## 5. Rule import chính thức

### Bước 1. Đọc file
- chỉ nhận file `.txt`
- đọc toàn bộ nội dung
- bỏ các dòng header bắt đầu bằng `#`

### Bước 2. Tách dòng và tách field
- tách từng dòng theo xuống dòng
- tách từng field theo `tab`

### Bước 3. Loại bỏ field không dùng
Trong mỗi dòng dữ liệu:
- bỏ field dạng `[sound:...]`
- bỏ field HTML chỉ chứa hình ảnh như `<img ...>`
- bỏ field tag
- bỏ field rỗng

### Bước 4. Xác định mặt trước và mặt sau
- `field text đầu tiên` = `mặt trước`
- `các field text còn lại` = `mặt sau`

Nếu chỉ còn đúng 2 field text:
- field 1 = mặt trước
- field 2 = mặt sau

Nếu còn nhiều hơn 2 field text:
- field đầu tiên = mặt trước
- nối các field text còn lại thành mặt sau

## 6. Rule làm sạch text
- bỏ HTML nếu có
- decode HTML entities
- bỏ `&nbsp;`
- trim khoảng trắng đầu cuối
- chuẩn hóa nhiều khoảng trắng liên tiếp thành 1 khoảng trắng

## 7. Áp dụng cho file mẫu 1
Ví dụ:
```txt
<div>Trong phòng có điều hòa không khí không?</div>	[sound:isThereAirConditioningInTheRoom_2.mp3]	Is there air conditioning in the room?&nbsp;
```

Kết quả:
- `mặt trước` = `Trong phòng có điều hòa không khí không?`
- `mặt sau` = `Is there air conditioning in the room?`

## 8. Áp dụng cho file mẫu 2
Ví dụ:
```txt
advantage	[sound:xxx.mp3]	(n) Phiên âm: /.../ Nghĩa: sự thuận lợi, lợi ích, lợi thế. take advantage of lợi dụng
```

Kết quả:
- `mặt trước` = `advantage`
- `mặt sau` = `(n) Phiên âm: /.../ Nghĩa: sự thuận lợi, lợi ích, lợi thế. take advantage of lợi dụng`

## 9. Validation tối giản
Một dòng hợp lệ khi:
- sau khi loại field phụ, còn ít nhất `2 field text hợp lệ`

Nếu không đủ:
- bỏ qua dòng đó

## 10. Kết quả import cần trả về
- tổng số dòng đọc được
- tổng số dòng dữ liệu
- tổng số dòng hợp lệ
- tổng số dòng bị bỏ qua
- preview một số dòng đầu

## 11. Cấu trúc record sau import
- `front_text`
- `back_text`
- `source_file`
- `source_line_number`

## 12. Cách dùng trong app

### Lật thẻ
- `front_text` hiển thị ở mặt trước
- `back_text` hiển thị ở mặt sau

### Nhập chữ
- `front_text` là câu hỏi
- `back_text` là đáp án chuẩn để so sánh

## 13. Rule chốt thêm cho preview / confirm / duplicate

### 13.1. Preview và Confirm phải nhất quán dữ liệu
- sau khi preview, hệ thống phải lưu snapshot kết quả parse của file
- confirm import phải dùng lại snapshot này
- không parse lại file trong lúc confirm nếu không có lý do đặc biệt
- mỗi preview nên gắn với `file_hash` để bảo đảm file không bị đổi giữa preview và confirm
- nếu `file_hash` không khớp thì bắt buộc preview lại

### 13.2. Rule duplicate của phase hiện tại
- duplicate được xác định trong phạm vi:
  - `user`
  - `deck`
  - `front_text` đã normalize
  - `back_text` đã normalize
- không dùng `source_file + source_line_number` làm khóa duplicate chính
- nếu dòng bị trùng:
  - không insert card mới
  - đánh dấu `skip`
  - vẫn ghi log vào import history

### 13.3. Text dùng cho checking về sau
- `back_text` dùng để hiển thị
- cần sinh thêm phiên bản plain text sạch để phục vụ `check-answer`
- plain text này phải được tạo ngay từ bước import

## 14. Những gì chưa làm ở giai đoạn này
- chưa lưu audio
- chưa lưu image
- chưa lưu tags
- chưa tách nghĩa riêng
- chưa tách phiên âm riêng
- chưa hỗ trợ các schema import nâng cao

## 15. Kết luận
Spec import TXT hiện tại đã được chốt theo hướng tối giản:
- bỏ `mp3`
- bỏ `image`
- bỏ `tag`
- lấy `text đầu tiên` làm `mặt trước`
- lấy phần text còn lại làm `mặt sau`

Rule này đủ để xử lý cả 2 file mẫu hiện có và đủ để phục vụ flow học hiện tại của app.
