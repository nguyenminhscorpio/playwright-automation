# 04. Nghiệp vụ bổ sung màn hình Study Session (Phase 2)

Tài liệu này tổng hợp các yêu cầu bổ sung, nâng cấp tính năng cho màn hình **Study Session** đã được xây dựng ở Phase 1, nhằm gia tăng trải nghiệm học tập trong Phase 2.

---

## 1. Tính năng Text-To-Speech (TTS)

### 1.1. Mục đích
- Cho phép người dùng nghe phát âm nội dung của thẻ từ vựng (Flashcards).
- Hỗ trợ tối đa cho việc học ngoại ngữ, giúp người dùng ghi nhớ từ vựng qua âm thanh, cải thiện kỹ năng nghe và phát âm.

### 1.2. Vai trò nghiệp vụ
- Tăng cường trải nghiệm học tập đa giác quan (nghe - nhìn - gõ phím).
- Trở thành tính năng bổ trợ cốt lõi trong màn hình Study Session.
- Cá nhân hoá trải nghiệm thông qua việc lưu lại tuỳ chọn giọng đọc của người dùng.

### 1.3. Vị trí tích hợp (UI/UX)
- **Màn hình Study Session:**
  - Nút biểu tượng "Cái loa" (Speaker icon) hiển thị ngay cạnh văn bản của mặt trước (Front) và mặt sau (Back).
  - Cho phép người dùng bấm vào loa để nghe lại phát âm bất cứ lúc nào.

### 1.4. Cơ chế hoạt động (Technical Logic)
- **Công nghệ:** Ưu tiên sử dụng **Web Speech API** (API có sẵn trên các trình duyệt hiện đại Chrome, Firefox, Safari, Edge) để thực hiện tính năng này. Ưu điểm: Miễn phí, không độ trễ mạng, không tốn tài nguyên Server.
- **Dữ liệu đầu vào (Text to read):** Bắt buộc phải sử dụng trường `front_plain_text` và `back_plain_text` (lấy từ bảng `notes`). Lý do: Cần đọc văn bản thô, tránh việc hệ thống đọc luôn cả các thẻ HTML (`<b>`, `<br>`) có trong trường hiển thị.

### 1.5. Dữ liệu Mapping Database (Từ Phase 1)
- Bảng `users` đã có sẵn các trường chuẩn bị cho tính năng này:
  - `locale`: Lưu trữ ngôn ngữ ưu tiên của người dùng (Ví dụ: `en-US`, `ja-JP`). Sẽ dùng làm ngôn ngữ mặc định khi tìm kiếm giọng đọc trong Web Speech API.
  - `preferred_tts_voice`: Tên giọng đọc cụ thể mà người dùng yêu thích (Ví dụ: `Google US English`, `Microsoft Ichiro`).

### 1.6. Hành vi người dùng
- **Phát âm thủ công:** Bấm vào icon loa -> Hệ thống đọc ngay dòng text đó. Bấm thêm lần nữa -> Phát lại từ đầu.
- **Cấu hình giọng đọc (Optional):** Chọn giọng Nam/Nữ hoặc chọn Ngôn ngữ (Language) ở màn hình Setting để lưu vào Database (`preferred_tts_voice`).
- **Auto-play (Tương lai):** Có thể thêm setting cho phép tự động đọc mặt trước ngay khi thẻ xuất hiện, hoặc tự động đọc mặt sau khi bấm lật thẻ. 

### 1.7. Ghi chú triển khai (Lưu ý quan trọng)
- **Hạn chế của Trình duyệt:** Trình duyệt web hiện đại (đặc biệt Chrome/Safari) có chính sách "Auto-play Policy". Không cho phép tự động phát âm thanh nếu người dùng chưa tương tác (click, chạm) vào trang web. Tuy nhiên, với Study Session, người dùng đã phải click nút "Bắt đầu học", nên việc phát âm thanh sau đó sẽ không bị block.
- Về mặt code, nên viết riêng một Service File ở Frontend (VD: `resources/js/tts.js`) để đóng gói các hàm: `play()`, `stop()`, `getAvailableVoices()`. Không nên viết trực tiếp logic Speech Synthesis vào trong các file View.

---

## 2. Các Yêu cầu bổ sung khác (UI/UX Fixes)
- **Xóa phần text thừa ở mặt Front:** Hiện tại ở màn hình Study Session (chế độ Flip/Typing), bên dưới dòng text chính (chữ to) đang bị lặp lại một dòng text nhỏ hơn y hệt (front_plain_text). Cần phải ẩn hoặc xóa bỏ dòng text nhỏ này đi để giao diện đỡ rối và tránh trùng lặp.
- **Ẩn chữ "No file chosen" ở màn hình Import:** Trình duyệt đang tự động render dòng chữ "No file chosen" mặc định kế bên nút Choose File. Cần custom lại CSS của thẻ `<input type="file">` để ẩn dòng chữ này đi cho UI được đẹp mắt và chuyên nghiệp hơn.
- **Thêm margin cho Message trạng thái Import:** Ở màn hình Import, khoảng cách giữa thanh input/button và ô message thông báo trạng thái (Ví dụ: "Import complete...") đang bị quá sát nhau. Cần thêm margin (khoảng cách) để tách biệt rõ ràng hơn.
- **Nút "Confirm Import":** Đổi màu nút sang màu xanh (Success Green) để nhấn mạnh đây là bước xác nhận cuối cùng. Sau khi người dùng bấm xác nhận và hệ thống Import thành công, nút này cần phải được **Disable** (vô hiệu hóa) để tránh việc người dùng bấm nhầm nhiều lần gây trùng lặp dữ liệu.

---

## 3. Chức năng Tạo Deck mới (Create New Deck)
- **Thành phần Modal:**
  - **DECK NAME (Bắt buộc):** Ô nhập tên bộ thẻ (Ví dụ: English Vocabulary).
  - **DESCRIPTION (Tùy chọn):** Thêm ô nhập mô tả ngắn gọn (Textarea) để người dùng có thể giới thiệu về bộ thẻ.
- **Hành vi:** Lưu cả `name` và `description` vào database khi tạo.
