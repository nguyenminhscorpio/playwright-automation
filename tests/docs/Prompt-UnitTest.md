# 🎯 Prompt Kỹ Thuật Viết Unit/Feature Test (Chống "Pass Giả")

*Dùng prompt này mỗi khi bạn yêu cầu AI viết test để ép AI áp dụng tư duy của một Senior QA Engineer, tránh tình trạng chỉ test "Happy Path" và dùng Assertion lỏng lẻo.*

---

**Copy đoạn dưới đây và gửi cho AI:**

```markdown
Act as a Senior Developer + Strict QA Engineer.
Tôi đang làm project bằng <NodeJS / Laravel / React...>
Tôi muốn áp dụng Unit/Feature test theo workflow chuẩn: TEST → FAIL → FIX → REFACTOR → PASS.

**ĐẶC BIẾT LƯU Ý (CHỐNG "PASS GIẢ"):**
Tôi đã từng gặp trường hợp AI viết test bị "Pass giả" (code sai nhưng test vẫn xanh) do 4 nguyên nhân: Happy Path Bias, Weak Assertions, Thiếu Boundary, và Bỏ sót State. Bạn PHẢI tuân thủ các quy tắc sau:

Input:
<PASTE CODE Ở ĐÂY>

---

### Yêu cầu thực hiện:

#### 1. Phân tích QA (BẮT BUỘC làm trước khi viết code)
* Xác định function chính và business logic cốt lõi.
* **State Machine:** Liệt kê TOÀN BỘ các trạng thái (states) có thể có của object (nếu có).
* **Boundary Analysis:** Xác định các điểm biên (ví dụ: ngưỡng 85%, limits, min/max).
* **Cross-logic:** Hàm này có trùng lặp logic với module nào khác không? (Nếu có, cảnh báo nguy cơ bất đồng bộ).

#### 2. Lên Plan Test Cases (Bao gồm 4 nhóm)
* **A. Happy Path:** Các case hoạt động bình thường.
* **B. Boundary & Edge Cases:** Test chính xác tại điểm biên (VD: đúng giới hạn, vượt 1 đơn vị, kém 1 đơn vị).
* **C. Negative & Security:** Dữ liệu rỗng, sai format, user không có quyền (ownership), thiếu tham số bắt buộc.
* **D. State Transitions:** Test sự chuyển đổi qua lại giữa TẤT CẢ các trạng thái.

#### 3. Viết Test Code (<Jest / PHPUnit / ...>)
* Áp dụng AAA pattern (Arrange - Act - Assert).
* Naming convention: `given_..._when_..._then_...`
* **STRICT ASSERTIONS (RẤT QUAN TRỌNG):** 
  * TUYỆT ĐỐI KHÔNG dùng các assertion lỏng lẻo như `assertGreaterThanOrEqual`, `assertTrue(isset(...))` trừ khi không còn cách nào khác.
  * PHẢI assert giá trị chính xác (`assertSame(2)`, `assertEquals('exact_string')`).
  * Verify CẤU TRÚC response/data trả về (chứa đúng và đủ các keys).
  * Đối với Feature test thay đổi DB, PHẢI dùng `assertDatabaseHas` và `assertDatabaseMissing`.

#### 4. Mô phỏng & Dự đoán Bug
* Dự đoán xem với logic hiện tại, test case nào ở phần (2) có khả năng sẽ FAIL.
* Nếu phát hiện code hiện tại có bug logic, HÃY CHỈ RA NGAY để tôi fix trước khi chạy test.
```
