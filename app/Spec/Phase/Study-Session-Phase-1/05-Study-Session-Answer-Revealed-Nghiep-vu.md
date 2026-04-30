# 05. Nghiệp vụ màn hình Study Session - Answer Revealed

## 1. Mục đích
- Đây là màn hình hiển thị đáp án sau bước `flip` hoặc `typing`.
- Màn hình này dùng chung cho cả 2 mode.
- Đây là nơi người dùng đưa ra rating để hệ thống cập nhật tiến độ học.

## 2. Vai trò nghiệp vụ
- Hiển thị prompt và đáp án đúng của card hiện tại.
- Với mode `typing`, hiển thị thêm câu trả lời người dùng và kết quả match.
- Là điểm duy nhất trong flow hiện tại thực hiện chấm rating, cập nhật scheduler và ghi review log.

## 3. Thành phần chính trên màn hình
- Thông tin deck và tiến độ.
- Khu vực `Prompt`.
- Khu vực `Your Answer` khi đi từ mode `typing`.
- Khu vực judgement:
  - `correct`
  - `close_match`
  - `incorrect`
- Khu vực đáp án đúng:
  - nhãn `Back Side` với mode `flip`
  - nhãn `Correct Answer` với mode `typing`
- Tag hiển thị `state` của card và `mode`.
- Rating panel gồm:
  - `Again`
  - `Hard`
  - `Good`
  - `Easy`

## 4. Hành vi theo mode

### Khi là `flip`
- Hiển thị prompt của card.
- Hiển thị đáp án đúng ở phần `Back Side`.
- Không hiển thị `Your Answer`.
- Tiêu đề rating là `How difficult was this to recall?`

### Khi là `typing`
- Hiển thị prompt của card.
- Hiển thị `Your Answer` nếu có dữ liệu đã nhập.
- Hiển thị judgement `correct / close_match / incorrect`.
- Hiển thị đáp án đúng ở phần `Correct Answer`.
- Tiêu đề rating là `How well did you know this after checking?`

## 5. Luồng nghiệp vụ
1. Màn answer đọc payload đã được lưu từ bước trước trong `sessionStorage`.
2. Nếu không có payload hợp lệ, hệ thống hiển thị empty state `No revealed card`.
3. Nếu có payload, hệ thống render lại card hiện tại và dữ liệu answer tương ứng.
4. Hệ thống hiển thị thời gian gợi ý cho từng rating theo `state` hiện tại của card.
5. Người dùng chọn một trong bốn rating `Again / Hard / Good / Easy`.
6. Hệ thống gọi API rate với:
   - `mode`
   - `rating`
   - `typed_answer` nếu có
   - `judged_result` nếu có
7. Hệ thống cập nhật card theo scheduler:
   - đổi `state`
   - đổi `current_step`
   - đổi `due_at`
   - đổi `scheduled_days`
   - đổi `stability`
   - đổi `difficulty`
   - tăng `reps`
   - tăng `lapses` khi phù hợp
8. Hệ thống ghi một bản ghi `review_logs`.
9. Hệ thống xóa payload answer tạm thời khỏi `sessionStorage`.
10. Hệ thống điều hướng về màn học tiếp theo:
   - về `study/front` nếu mode là `flip`
   - về `study/typing` nếu mode là `typing`
11. Nếu không còn card tiếp theo, hệ thống vẫn điều hướng về màn học tương ứng để render lại trạng thái `Session complete`.

## 6. Điều kiện đầu vào
- Có payload answer hợp lệ được lưu từ bước trước.
- Card hiện tại tồn tại và có thể rate được.
- Rating được chọn phải thuộc một trong 4 giá trị: `again`, `hard`, `good`, `easy`.

## 7. Điều kiện đầu ra
- Card được cập nhật theo thuật toán scheduler hiện tại.
- Review log được tạo thành công.
- Progress session được refresh.
- Người dùng được đưa sang card kế tiếp hoặc trạng thái kết thúc session.

## 8. Quy tắc nghiệp vụ quan trọng
- Đây là màn duy nhất trong flow hiện tại thực sự ghi nhận kết quả học.
- Kết quả `correct / close_match / incorrect` không tự động map sang rating.
- Rating hint đang hiển thị theo rule UI hiện tại:
  - với `review`: `hard ~1 day`, `good ~2 days`, `easy ~4 days`
  - với `new/learning/relearning`: `again <1 min`, `hard ~5 min`, `good ~10 min`, `easy ~4 days`
- Sau khi rate xong, màn answer không giữ lại card cũ.

## 9. Kết quả mong đợi
- Người dùng nhìn thấy đáp án rõ ràng trước khi tự đánh giá độ nhớ.
- Hệ thống chỉ cập nhật scheduling sau khi người dùng chọn rating, giúp flow nhất quán cho cả `flip` và `typing`.
