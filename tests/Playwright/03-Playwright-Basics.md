# Playwright Cơ Bản: Locator, Assertion, Navigation

> Bài ghi chú ngắn để nắm nhanh nền tảng Playwright trước khi viết test thực tế.

---

## 1. Playwright là gì?

Playwright là công cụ dùng để tự động hóa trình duyệt và kiểm thử giao diện web.

Khi viết test với Playwright, ta thường làm 3 việc chính:

- Tìm phần tử trên trang
- Kiểm tra phần tử hoặc dữ liệu có đúng không
- Điều hướng sang trang khác và chờ trang tải xong

Ba nhóm kiến thức này chính là:

- `Locator`
- `Assertion`
- `Navigation`

---

## 2. Locator là gì?

Locator là cách Playwright tìm đúng phần tử trên màn hình để thao tác hoặc kiểm tra.

Hiểu đơn giản:

- Muốn bấm nút, phải tìm được nút
- Muốn nhập ô input, phải tìm được ô input
- Muốn kiểm tra tiêu đề, phải tìm được tiêu đề

### 2.1 `page.locator()`

Đây là cách tìm phần tử bằng CSS selector hoặc selector tùy ý.

```ts
const loginButton = page.locator('#login-button');
await loginButton.click();
```

Ví dụ khác:

```ts
await page.locator('.card').first().click();
await page.locator('input[name="email"]').fill('test@example.com');
```

Khi nên dùng:

- Khi phần tử có `id`, `class`, `data-*`
- Khi bạn cần selector cụ thể

Lưu ý:

- `locator()` mạnh nhưng dễ phụ thuộc vào cấu trúc HTML
- Nếu giao diện đổi class hoặc đổi DOM, test có thể hỏng

### 2.2 `page.getByRole()`

Đây là cách Playwright khuyến khích dùng nhiều nhất vì gần với cách người dùng thật tương tác.

```ts
await page.getByRole('button', { name: 'Login' }).click();
```

Ví dụ:

```ts
await page.getByRole('textbox', { name: 'Email' }).fill('test@example.com');
await page.getByRole('link', { name: 'Dashboard' }).click();
```

Khi nên dùng:

- Khi phần tử có vai trò rõ ràng như `button`, `link`, `textbox`, `checkbox`
- Khi muốn test ổn định và dễ đọc

Ưu điểm:

- Dễ hiểu
- Gần với accessibility
- Ít phụ thuộc vào HTML chi tiết

### 2.3 `page.getByText()`

Tìm phần tử dựa trên nội dung chữ hiển thị trên màn hình.

```ts
await page.getByText('Welcome back').click();
```

Ví dụ:

```ts
await expect(page.getByText('Login successful')).toBeVisible();
await page.getByText('Create Deck').click();
```

Khi nên dùng:

- Khi bạn muốn kiểm tra hoặc bấm vào nội dung nhìn thấy rõ
- Khi text là dấu hiệu quan trọng của nghiệp vụ

Lưu ý:

- Nếu text thay đổi do đa ngôn ngữ hoặc sửa UI, test có thể phải cập nhật

### 2.4 Nên ưu tiên locator nào?

Thứ tự nên ưu tiên:

1. `getByRole()`
2. `getByText()`
3. `locator()`

Nếu dự án có `data-testid` hoặc `data-*` rõ ràng, `locator()` cũng rất tốt.

Ví dụ:

```ts
await page.locator('[data-create-deck-button]').click();
```

---

## 3. Assertion là gì?

Assertion là câu lệnh kiểm tra xem kết quả thực tế có đúng như mong đợi không.

Nếu đúng thì test pass.
Nếu sai thì test fail.

Playwright thường dùng `expect(...)`.

---

## 4. Các assertion cơ bản

### 4.1 `toBeVisible()`

Kiểm tra phần tử có hiển thị trên màn hình không.

```ts
await expect(page.getByRole('button', { name: 'Save' })).toBeVisible();
```

Ý nghĩa:

- Nút có tồn tại
- Nút không bị ẩn
- Người dùng có thể nhìn thấy

### 4.2 `toHaveText()`

Kiểm tra phần tử có đúng nội dung text hay không.

```ts
await expect(page.locator('h1')).toHaveText('Dashboard');
```

Ví dụ:

```ts
await expect(page.getByRole('heading')).toHaveText('My Decks');
```

Phù hợp khi:

- Kiểm tra tiêu đề
- Kiểm tra thông báo thành công hoặc lỗi
- Kiểm tra nội dung của một ô, label, button

### 4.3 `toHaveCount()`

Kiểm tra số lượng phần tử.

```ts
await expect(page.locator('.deck-card')).toHaveCount(3);
```

Phù hợp khi:

- Kiểm tra danh sách có bao nhiêu item
- Kiểm tra bảng có bao nhiêu dòng
- Kiểm tra có bao nhiêu thông báo lỗi

Ví dụ:

```ts
await expect(page.locator('[data-deck-card]')).toHaveCount(5);
```

---

## 5. Navigation là gì?

Navigation là việc chuyển trang hoặc đi đến một URL nào đó trong ứng dụng.

Trong test UI, điều hướng xuất hiện rất thường xuyên:

- Mở trang ban đầu
- Click vào link hoặc button để sang trang khác
- Chờ URL đổi xong rồi mới tiếp tục kiểm tra

---

## 6. Các lệnh navigation cơ bản

### 6.1 `page.goto()`

Dùng để mở một URL.

```ts
await page.goto('http://localhost:8000/dashboard');
```

Nếu đã cấu hình `baseURL` trong `playwright.config.ts`, có thể viết ngắn hơn:

```ts
await page.goto('/dashboard');
```

Đây là lệnh thường dùng nhất trong `beforeEach`.

Ví dụ:

```ts
test.beforeEach(async ({ page }) => {
  await page.goto('/dashboard');
});
```

### 6.2 `page.waitForURL()`

Dùng để chờ tới khi trình duyệt chuyển sang đúng URL mong muốn.

```ts
await page.getByRole('link', { name: 'Dashboard' }).click();
await page.waitForURL('**/dashboard');
```

Ví dụ khác:

```ts
await page.getByText('Review 10 Cards').click();
await page.waitForURL(/\/study/);
```

Khi nên dùng:

- Sau khi click mà ứng dụng chuyển trang
- Khi bạn cần chắc chắn URL đã đổi xong rồi mới assert tiếp

---

## 7. Ví dụ hoàn chỉnh ngắn

Ví dụ một test kết hợp đủ `locator`, `assertion`, `navigation`:

```ts
import { test, expect } from '@playwright/test';

test('user mở dashboard và thấy danh sách deck', async ({ page }) => {
  await page.goto('/dashboard');

  await expect(page.getByRole('heading', { name: 'Dashboard' })).toBeVisible();
  await expect(page.locator('[data-deck-card]')).toHaveCount(3);

  await page.getByText('English Basics').click();
  await page.waitForURL(/\/decks\/\d+/);

  await expect(page.getByRole('button', { name: 'Add Card' })).toBeVisible();
});
```

Phân tích nhanh:

- `page.goto('/dashboard')`: mở trang dashboard
- `getByRole(...)`: tìm heading
- `toBeVisible()`: kiểm tra heading hiển thị
- `locator(...).toHaveCount(3)`: kiểm tra có 3 deck
- `getByText(...)`: click vào deck
- `waitForURL(...)`: chờ sang trang deck detail

---

## 8. Mẹo nhớ nhanh cho người mới

Bạn có thể nhớ theo công thức:

`Tìm -> Hành động -> Kiểm tra -> Chờ điều hướng`

Ví dụ:

```ts
await page.getByRole('button', { name: 'Create Deck' }).click();
await expect(page.getByText('Deck created successfully')).toBeVisible();
await page.waitForURL('**/dashboard');
```

---

## 9. Các lỗi người mới hay gặp

### 9.1 Dùng selector quá yếu

Ví dụ:

```ts
page.locator('div > div > button')
```

Cách này dễ hỏng khi UI đổi.

Nên ưu tiên:

```ts
page.getByRole('button', { name: 'Create Deck' })
```

### 9.2 Không `await`

Sai:

```ts
page.goto('/dashboard');
```

Đúng:

```ts
await page.goto('/dashboard');
```

Playwright chạy bất đồng bộ nên đa số thao tác cần `await`.

### 9.3 Assert quá sớm

Sau khi click chuyển trang mà kiểm tra ngay, test có thể fail ngẫu nhiên.

Nên:

```ts
await page.getByText('Study Now').click();
await page.waitForURL(/\/study/);
await expect(page.getByText('Session Progress')).toBeVisible();
```

---

## 10. Tóm tắt cần nhớ

- `page.locator()` dùng khi cần selector cụ thể
- `page.getByRole()` là cách nên ưu tiên vì dễ đọc và ổn định
- `page.getByText()` tốt khi text là dấu hiệu nghiệp vụ rõ ràng
- `expect(...).toBeVisible()` để kiểm tra phần tử hiển thị
- `expect(...).toHaveText()` để kiểm tra nội dung
- `expect(...).toHaveCount()` để kiểm tra số lượng phần tử
- `page.goto()` để mở trang
- `page.waitForURL()` để chờ chuyển trang xong

---

## 11. Bài tập tự luyện

Hãy tự viết 3 test ngắn:

1. Mở `/dashboard` và kiểm tra tiêu đề hiển thị
2. Kiểm tra số lượng deck card trên trang
3. Click một deck và chờ sang trang chi tiết

Mẫu khởi đầu:

```ts
import { test, expect } from '@playwright/test';

test('bai tap 1', async ({ page }) => {
  await page.goto('/dashboard');
  await expect(page.getByRole('heading')).toBeVisible();
});
```

---

## 12. Kết luận

Nếu bạn nắm chắc 3 phần sau, bạn đã có nền tảng rất tốt để bắt đầu với Playwright:

- Tìm phần tử bằng locator
- Kiểm tra bằng assertion
- Điều hướng bằng navigation

Sau bước này, bạn có thể chuyển sang viết test thật cho Dashboard, Deck Detail và Import Flow.
