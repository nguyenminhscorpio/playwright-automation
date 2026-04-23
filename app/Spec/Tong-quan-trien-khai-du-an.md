# Tổng quan triển khai dự án

## 1. Mục tiêu hiện tại
- Hoàn thiện ứng dụng học từ vựng theo phong cách Anki.
- Bám theo UI Laravel/Vite hiện đang chạy.
- Giữ kiến trúc sẵn sàng để sau này nối MySQL qua HeidiSQL và backend Laravel.

## 2. Hiện trạng đang có
- Có project Laravel trong `C:\laragon\www\vibe-coding`
- Có bộ view cho dashboard, deck detail, study flow
- Có layout sidebar + topbar
- Có `Study Mode Switch` ở topbar
- Có 2 flow học riêng:
  - `Lật thẻ`
  - `Nhập chữ`

## 3. Luồng học theo UI hiện tại
- `Lật thẻ`
  - mở màn `Flip Front`
  - bấm `Show Answer`
  - sang `Answer Revealed`
- `Nhập chữ`
  - mở thẳng màn `Typing Input`
  - nhập đáp án
  - bấm `Check Answer`
  - sang `Answer Revealed`

## 4. Điều cần làm để hoàn thiện bản chạy được
- Hoàn thiện UI mock
- Làm sạch text tiếng Việt và mock data
- Hoàn thiện logic truyền dữ liệu giữa các màn study
- Hoàn thiện answer checking cơ bản ở phía UI hoặc controller
- Sau đó mới nối DB và backend thật

## 5. Điều cần làm để hoàn thiện bản lưu được dữ liệu
- Thiết kế schema MySQL
- Tạo database bằng HeidiSQL
- Cấu hình `.env`
- Tạo migration hoặc SQL script
- Nối controller với model/database
- Ghi log review, streak, progress
