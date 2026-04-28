# Dashboard Test Matrix

> File này dùng để theo dõi coverage kiểm thử cho màn Dashboard theo hướng thực tế: spec nào đã có test, case nào manual, case nào đã automation.

---

## 1. Mục đích

File này không thay thế spec nghiệp vụ.

Nó dùng để:

- map từ nghiệp vụ sang test case
- theo dõi case nào đã auto
- theo dõi case nào vẫn cần test manual
- giúp QA, dev, BA nhìn coverage nhanh

---

## 2. Tài liệu liên quan

- Spec nghiệp vụ: `app/Spec/Bussiness-Rule/Dasboard-Desk-Detail- Phase-2/01-Dashboard-Nghiep-vu.md`
- Roadmap học Playwright: `tests/Playwright/02-Playwright-Learning-Roadmap.md`
- File automation: `tests/e2e/dashboard.spec.ts`

---

## 3. Nguyên tắc dùng file này

- `Auto`: đã hoặc sẽ được cover bằng Playwright
- `Manual`: nên test tay vì khó auto, liên quan UI/UX, hoặc chưa ưu tiên automation
- `Pending`: chưa làm
- `Done`: đã có test và đã verify

---

## 4. Dashboard Coverage Matrix

| TC ID | Test Case | Mapping Spec | Type | Auto File | Priority | Status | Notes |
|---|---|---|---|---|---|---|---|
| DASH-01 | Trang Dashboard load thành công, hiển thị lời chào | Mục 3.1 Header | Auto | `tests/e2e/dashboard.spec.ts` | High | Done | Kiểm tra heading `Welcome back` và subtitle |
| DASH-02 | Quick Stats hiển thị Streak | Mục 3.2 Quick Stats | Auto | `tests/e2e/dashboard.spec.ts` | High | Done | Kiểm tra `Daily Streak` hiển thị |
| DASH-03 | Quick Stats hiển thị Learning Milestone | Mục 3.2 Quick Stats | Auto | `tests/e2e/dashboard.spec.ts` | High | Done | Kiểm tra `Learning Milestone` và `Monthly progress` |
| DASH-04 | Active Decks grid hiển thị danh sách deck | Mục 3.3 Active Decks | Auto | `tests/e2e/dashboard.spec.ts` | High | Done | Tạo deck test qua API rồi verify hiển thị trên grid |
| DASH-05 | Tạo Deck mới qua modal với name + description | Mục 6 Create Deck | Auto | `tests/e2e/dashboard.spec.ts` | High | Done | Đi qua UI modal thật |
| DASH-06 | Xóa Deck với confirm dialog | Mục 3.3 Nút Delete | Auto | `tests/e2e/dashboard.spec.ts` | High | Done | Accept confirm dialog và verify deck biến mất |
| DASH-07 | Từ deck card mở sang Deck Detail | Mục 5 Điều hướng | Auto | `tests/e2e/dashboard.spec.ts` | High | Done | UI hiện tại dùng nút `Open Deck` |
| DASH-08 | Click `Review X Cards` để sang Study | Mục 5 Điều hướng | Auto | `tests/e2e/dashboard.spec.ts` | High | Done | Verify chuyển sang `/study/front` |
| DASH-09 | Nút `Create New Deck` mở modal đúng giao diện | Mục 6 Create Deck | Manual | - | Medium | Pending | Kiểm tra layout modal, focus input, spacing |
| DASH-10 | Trạng thái empty state khi không có deck | Mục 3.3 Active Decks | Auto | Chưa có | Medium | Pending | Có thể thêm bằng API cleanup hoặc mock dữ liệu |
| DASH-11 | Nút `Go To Import` điều hướng đúng | Mục điều hướng liên quan Dashboard | Auto | Chưa có | Low | Pending | Có thể thêm nhanh nếu muốn tăng coverage |
| DASH-12 | Responsive layout của Dashboard trên mobile | Ngoài spec chi tiết | Manual | - | Medium | Pending | Nên test tay vì thiên về UX/layout |
| DASH-13 | Nội dung số liệu thống kê có đúng dữ liệu nghiệp vụ | Mục 3.2 Quick Stats | Manual | - | Medium | Pending | Nên đối chiếu dữ liệu DB hoặc fixture nghiệp vụ |
| DASH-14 | Delete deck với nút Cancel trên dialog | Mục 3.3 Nút Delete | Auto | Chưa có | Medium | Pending | Có thể thêm case cancel để đảm bảo không bị xóa |

---

## 5. Gợi ý phân chia Manual và Auto theo thực tế

### Nên ưu tiên Auto

- luồng CRUD rõ ràng
- điều hướng giữa các màn hình
- kiểm tra text quan trọng
- kiểm tra phần tử hiển thị
- verify action gọi API thành công

### Nên giữ Manual

- kiểm tra giao diện đẹp/xấu
- responsive layout
- animation
- spacing, alignment
- cảm nhận UX
- exploratory testing

---

## 6. Quy ước cập nhật

Khi thêm test mới:

1. thêm 1 dòng mới trong bảng này
2. ghi rõ `TC ID`
3. ghi file `.spec.ts` đang cover
4. cập nhật `Status`

Khi đổi spec:

1. rà lại cột `Mapping Spec`
2. kiểm tra test auto nào bị ảnh hưởng
3. cập nhật notes nếu UI đã đổi

---

## 7. Kết luận

Với project này, cách làm thực tế và gọn nhất là:

- có spec nghiệp vụ
- có test matrix để theo dõi coverage
- có file Playwright automation để chạy thật

Không cần viết một file manual dài cho từng test script, nhưng nên có test matrix như file này để quản lý rõ ràng hơn.
