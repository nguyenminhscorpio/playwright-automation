# 01. Nghiep vu man hinh Dashboard (Phase 2)

Tai lieu nay duoc cap nhat theo source hien tai cua project `vibe-coding`.

## 1. Muc dich
- Route man hinh: `/dashboard`
- Day la man hinh tong quan chinh cua FlashMind, dung de:
  - theo doi nhip hoc hien tai
  - xem nhanh so card den han
  - mo nhanh deck can xu ly
  - kiem tra nhanh ket qua import gan day

## 2. Pham vi hien co trong source
- Hero chao mung nguoi dung voi tieu de `Welcome back, {name}`
- 3 o thong ke nhanh:
  - `Daily Streak`
  - `Due Today`
  - `Decks`
- 1 khoi `Learning Milestone` voi progress bar
- Khu vuc `Active Decks`
- Khu vuc `Recent Imports`
- Link tat sang man hinh Import

## 3. Thanh phan UI va hanh vi hien tai

### 3.1. Hero
- Dashboard hien thi loi chao va mo ta ngan ve muc tieu man hinh.
- Hero header khong con hien thi nut `Create New Deck`.

### 3.2. Quick stats
- `Daily Streak`: so ngay hoc lien tiep.
- `Due Today`: tong so card dang den han hoc/review.
- `Decks`: tong so deck, kem tong `cards` va `notes`.

### 3.3. Learning Milestone
- Hien thi:
  - so card da graduate vao `review` trong thang hien tai
  - muc tieu thang hien tai
  - phan tram tien do
- Progress bar duoc tinh truc tiep tu ti le thuc te / muc tieu thang.

### 3.4. Active Decks
- Hien thi danh sach deck active tren Dashboard, khong con gioi han toi da 4 deck.
- Moi deck card hien thi:
  - icon co dinh
  - chip trang thai:
    - `X due` neu con card den han
    - `On track` neu khong co card den han
  - nut xoa deck
  - ten deck
  - mo ta deck
  - chi so `Learned / Total`
  - progress bar mastery
  - nut `Open Deck`
  - nut `Review X Cards`
- Neu chua co deck:
  - hien thi empty state
  - cho phep tao deck dau tien ngay tai Dashboard qua nut `Create First Deck`

### 3.5. Xoa deck
- Nut xoa deck mo custom popup dang `<dialog>`, khong dung browser `window.confirm`.
- Popup hien thi noi dung xac nhan xoa deck va canh bao se xoa notes/cards lien quan.
- Khi nguoi dung bam `Delete` trong popup:
  - goi `DELETE /api/decks/{id}`
  - dong popup
  - xoa deck card khoi giao dien hien tai
- Neu request xoa that bai:
  - popup van mo
  - loi hien thi ngay trong popup
- Viec xoa deck se keo theo xoa du lieu lien quan nho rang buoc cascade o database.

### 3.6. Recent Imports
- Hien thi toi da 5 lan import gan nhat.
- Bang hien thi cac cot:
  - `Job`
  - `File`
  - `Deck`
  - `Status`
  - `Imported`
  - `Skipped/Invalid`
  - `Finished`

## 4. Du lieu va logic nghiep vu theo source hien tai

### 4.1. User context
- Dashboard hien chua lay user tu auth session thuc.
- `ScreenController::resolveStudyContext()` dang chon:
  - user co email `dev.study@example.com`, hoac
  - user dau tien co card

### 4.2. Daily Streak
- Duoc tinh tu bang `review_logs`, khong dung bang `study_days`.
- Logic:
  - gom nhom theo `DATE(reviewed_at)`
  - chi bat dau tinh streak neu ngay gan nhat la hom nay hoac hom qua
  - sau do dem lui lien tiep tung ngay

### 4.3. Due Today
- Dem tong card cua user voi logic:
  - `learning` hoac `relearning` va `due_at` la `NULL` hoac `<= now()`
  - hoac `review` va `due_at <= now()`

### 4.4. Learning Milestone
- `monthly_learned` duoc tinh bang so `card_id` distinct trong `review_logs`:
  - co `next_state = 'review'`
  - nam trong thang hien tai
- `monthly_goal` hien dang hard-code la `600`
- Chua lay tu `users.daily_goal`

### 4.5. Active Decks
- Query lay tu `decks` join `cards`
- Du lieu moi deck gom:
  - `total_count`
  - `learned_count`: so card co `state <> 'new'`
  - `due_count`
  - `last_reviewed_at`
- `mastery_percent` duoc tinh theo:
  - `review_count / total_count * 100`
- Thu tu uu tien hien thi:
  - `due_count` giam dan
  - `last_reviewed_at` giam dan
  - `deck id` giam dan
- Khong con gioi han toi da 4 deck

### 4.6. Recent Imports
- Lay tu `import_jobs`
- Join them `deck`
- Sap xep theo `id` moi nhat
- Gioi han 5 ban ghi

## 5. Dieu huong hien tai
- `Open Deck` -> `/decks/{id}`
- `Review X Cards` -> `/study/front?deck_id={id}`
- `Go To Import` -> `/imports`

## 6. Ghi chu hien trang can phan anh dung trong tai lieu
- Chua co `View All` cho danh sach deck tren Dashboard.
- Chua co `Category badge`, icon rieng theo deck, hay khoi `Quick actions`.
- Muc tieu thang chua cau hinh theo user, dang co dinh la `600`.
- Streak dang dua tren `review_logs`, khong dung `study_days`.
- Nut `Review X Cards` dung `due_count` de hien thi label, nhung Study Session hien co fallback sang card `new` neu deck khong con card due.
