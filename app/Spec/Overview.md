# TAI LIEU CHUC NANG UNG DUNG HOC TU VUNG TIENG ANH

## 1. Tong quan du an

### 1.1 Ten tam thoi
- Vibe Coding Vocabulary

### 1.2 Muc tieu san pham
- Xay dung ung dung hoc tu vung tieng Anh lay cam hung tu Anki.
- Tap trung vao hoc nho dai han bang phuong phap Spaced Repetition.
- Ho tro hoc tren nhieu thiet bi: iPhone, iPad, may tinh.
- Cho phep nhap du lieu tu file txt xuat tu Anki de tai su dung bo the co san.
- Su dung TTS de doc cau/tu tieng Anh do file txt khong co audio.
- Cho phep nguoi hoc tu nhap cau tra loi va he thong kiem tra dung sai.

### 1.3 Tam nhin san pham
- San pham la mot he thong hoc tu vung don gian, de dung, toi uu cho viec on tap hang ngay.
- Ban dau uu tien tinh on dinh, import de, hoc va on bai nhanh.
- Ve sau mo rong sang dong bo da thiet bi, thong bao nhac hoc, viet tay, phan tich tien do hoc tap.

### 1.4 Doi tuong nguoi dung
- Hoc sinh, sinh vien hoc tieng Anh.
- Nguoi di lam can hoc tu vung giao tiep, TOEIC, IELTS, business English.
- Nguoi da dung Anki va muon co giao dien don gian hon tren di dong.
- Nguoi hoc ngon ngu can luyen ghi nho bang flashcard.

## 2. Pham vi du an

### 2.1 Pham vi phien ban MVP
- Dang ky, dang nhap, dang xuat.
- Quan ly bo tu vung/colection/deck co ban.
- Import file txt theo dinh dang Notes in Plain Text cua Anki.
- Hien thi the hoc theo mat truoc, mat sau.
- Chay lich on tap theo FSRS phien ban don gian.
- 4 muc danh gia ghi nho: Again, Hard, Good, Easy.
- Cho phep nguoi dung nhap dap an de kiem tra.
- TTS doc noi dung tieng Anh.
- Responsive UI cho mobile, tablet, desktop.
- Theo doi streak hoc hang ngay.
- Thong ke co ban: so the moi, so the can on, ty le dung.

### 2.2 Pham vi phien ban nang cao
- Dong bo du lieu giua nhieu thiet bi.
- Push notification nhac hoc.
- Import CSV ngoai txt.
- Upload media/file hinh len Cloudflare.
- Viet tay/ve chu de nhan dien.
- Phan quyen, chia se bo tu vung.
- Offline mode va local caching.

### 2.3 Ngoai pham vi giai doan dau
- Mang xa hoi, comment, chat.
- Marketplace bo tu vung cong khai quy mo lon.
- AI tao bai hoc tu dong tu van ban dai.
- Cham diem phat am chuyen sau bang speech recognition nang cao.

## 3. Muc tieu nghiep vu

### 3.1 Bai toan can giai quyet
- Nguoi hoc khong duy tri duoc lich on deu.
- Nho ngan han nhung quen nhanh sau vai ngay.
- Bo tu vung dang luu trong Anki kho tai su dung tren he thong rieng.
- Audio khong co san trong file txt, lam giam kha nang luyen nghe va doc.
- Trai nghiem hoc tren dien thoai va iPad can mem, nhanh, de tap trung.

### 3.2 Chi so thanh cong du kien
- Ty le nguoi dung hoan thanh it nhat 1 phien hoc moi ngay.
- So ngay streak trung binh tang dan theo thoi gian.
- Ty le on bai dung tang len sau 2-4 tuan.
- Ty le import file thanh cong > 95% voi file txt dung chuan Anki.
- Thoi gian vao phien hoc va tra loi 1 the phai nhanh, thao tac it.

## 4. Cong nghe de xuat

### 4.1 Frontend
- ReactJS phien ban moi nhat.
- Co the dung Vite de build.
- UI responsive, uu tien PWA de dung tren iPhone/iPad.
- De xuat dung component architecture ro rang, tach hoc, on, import, thong ke.

### 4.2 Backend
- PHP Laravel.
- Xay dung RESTful API cho mobile web/frontend.
- Dung queue cho cac tac vu nen nhu import, tao audio cache, gui notification.

### 4.3 Database
- MySQL.
- Thiet ke database uu tien truy van lich on, lich su tra loi, tracking streak.

### 4.4 Luu tru file
- Cloudflare R2 hoac dich vu phu hop cho GET/PUT/DELETE file, image, media.
- Giai doan dau co the chi luu metadata va audio TTS cache.

### 4.5 CI/CD
- GitHub Actions cho CI:
- Chay test backend.
- Chay test frontend.
- Check lint/format.
- Build artifact.
- Deploy theo moi truong staging/production.

## 5. Nguyen tac thiet ke san pham

### 5.1 Nguyen tac trai nghiem
- Vao hoc nhanh.
- It thao tac.
- Tap trung vao 1 the tai 1 thoi diem.
- Uu tien kha nang dung bang mot tay tren mobile.
- Chuyen man hinh mem tren iPad ngang/doc.

### 5.2 Nguyen tac nghiep vu
- Moi the deu co trang thai hoc ro rang.
- Lich on phai duoc cap nhat ngay sau moi lan danh gia.
- Khong de mat du lieu import goc.
- Cho phep hoc linh hoat, nhung van ton trong quy tac spaced repetition.

### 5.3 Nguyen tac ky thuat
- API first.
- Frontend va backend tach lop ro rang.
- De mo rong sang sync, notification, handwriting.
- Luu lich su hoc day du de sau nay cai tien thuat toan.

## 6. Kich ban nguoi dung chinh

### 6.1 Kich ban 1: Dang ky va bat dau hoc
1. Nguoi dung mo ung dung.
2. Nguoi dung dang ky bang email/password hoac dang nhap.
3. He thong tao ho so hoc tap mac dinh.
4. He thong hien dashboard voi thong tin:
- So the moi.
- So the den han on.
- Streak hien tai.
- Nut bat dau hoc.

### 6.2 Kich ban 2: Import bo the tu Anki txt
1. Nguoi dung vao trang Import.
2. Chon file txt.
3. He thong phan tich header cua file.
4. He thong xem truoc du lieu.
5. Nguoi dung map cac cot:
- Mat truoc.
- Mat sau.
- Audio goc neu co.
- Tag.
6. Nguoi dung chon deck dich.
7. He thong import.
8. He thong thong bao so dong thanh cong, so dong loi, so dong bo qua.

### 6.3 Kich ban 3: Hoc 1 phien on tap
1. Nguoi dung bam "Bat dau hoc".
2. He thong tai danh sach the can hoc theo muc uu tien.
3. Hien thi mat truoc.
4. Nguoi dung co the:
- Tu nghi trong dau.
- Nhap cau tra loi.
- Bam nghe TTS.
- Lat the xem dap an.
5. Sau khi xem dap an, nguoi dung chon:
- Again
- Hard
- Good
- Easy
6. He thong cap nhat lich hoc theo FSRS don gian.
7. He thong chuyen sang the tiep theo.

### 6.4 Kich ban 4: Nhap dap an va kiem tra
1. Nguoi dung nhin nghia tieng Viet hoac cau hoi.
2. Nguoi dung nhap tu/cau tieng Anh vao o tra loi.
3. He thong chuan hoa chuoi de so sanh.
4. He thong hien:
- Dung hoan toan.
- Gan dung.
- Sai.
5. Nguoi dung van tu danh gia muc do nho qua 4 nut Again/Hard/Good/Easy.

### 6.5 Kich ban 5: Theo doi streak
1. Moi ngay nguoi dung hoc it nhat nguong toi thieu.
2. He thong ghi nhan ngay hoc thanh cong.
3. Neu hom sau tiep tuc hoc, streak tang.
4. Neu bo qua qua quy dinh, streak bi ngat.

## 7. Yeu cau chuc nang chi tiet

### 7.1 Module tai khoan

#### 7.1.1 Chuc nang
- Dang ky tai khoan.
- Dang nhap.
- Dang xuat.
- Quen mat khau.
- Cap nhat thong tin co ban.
- Cai dat ngon ngu giao dien.
- Cai dat gio nhac hoc.

#### 7.1.2 Truong du lieu co ban
- Ho ten.
- Email.
- Password.
- Mui gio.
- Ngon ngu giao dien.
- TTS voice uu tien.
- So the toi da moi ngay.
- Muc tieu hoc moi ngay.

#### 7.1.3 Quy tac nghiep vu
- Email la duy nhat.
- Password ma hoa.
- Co the bat buoc xac thuc email o giai doan sau.

### 7.2 Module deck va card

#### 7.2.1 Deck
- Tao deck.
- Sua ten deck.
- Xoa deck.
- Gan mau sac/icon.
- Loc theo tag.
- Hien thi tong so the, the moi, the dang hoc, the den han.

#### 7.2.2 Card
- Tao card thu cong.
- Sua mat truoc.
- Sua mat sau.
- Sua ghi chu.
- Gan tag.
- Kich hoat/tam an/xoa mem.

#### 7.2.3 Card field du kien
- front_text
- back_text
- note_text
- example_text
- tags
- source_type
- source_audio_name
- source_raw_line

### 7.3 Module import file txt Anki

#### 7.3.1 Muc tieu
- Ho tro import file txt xuat tu Anki dang Notes in Plain Text.
- Uu tien doc dung cac dong meta nhu:
- `#separator:tab`
- `#html:true`
- `#tags column:4`

#### 7.3.2 Dinh dang input can ho tro
- File txt UTF-8.
- Tach cot bang tab.
- Co the co HTML trong noi dung.
- Co the co tag column.
- Co the co token audio dang `[sound:file.mp3]`.

#### 7.3.3 Vi du dong hop le
```txt
#separator:tab
#html:true
#tags column:4
<div>Trong phong co dieu hoa khong khi khong?</div>	[sound:isThereAirConditioningInTheRoom_2.mp3]	Is there air conditioning in the room?&nbsp;
```

#### 7.3.4 Hanh vi import
- Bo qua cac dong header bat dau bang `#`.
- Doc separator tu header.
- Phan tich so cot thuc te.
- Map cot vao field he thong.
- Luu audio token neu co.
- Luu noi dung HTML goc.
- Tao preview truoc khi xac nhan import.

#### 7.3.5 Man hinh import
- Chon file.
- Xem thong tin file.
- Preview 10-20 dong dau.
- Chon cach map cot.
- Chon deck dich.
- Chon cach xu ly ban ghi trung:
- Bo qua.
- Ghi de.
- Tao ban sao.
- Bat dau import.
- Xem ket qua import.

#### 7.3.6 Xu ly loi
- File rong.
- Sai separator.
- Khong doc duoc encoding.
- So cot khong dong deu.
- Dong du lieu thieu front/back.
- HTML loi.
- File qua lon.

#### 7.3.7 Ket qua import can luu
- Tong so dong.
- So dong thanh cong.
- So dong loi.
- Chi tiet dong loi.
- Thoi gian import.
- User thuc hien import.

### 7.4 Module hoc va on tap

#### 7.4.1 Trang thai hoc cua card
- New
- Learning
- Review
- Relearning
- Suspended
- Buried

#### 7.4.2 Nguon card trong phien hoc
- Card moi trong ngay.
- Card den han on.
- Card hoc lai do tra loi kem.

#### 7.4.3 Thu tu uu tien
- Card den han truoc.
- Card qua han lau hon uu tien hon.
- Card hoc lai uu tien cao.
- Card moi gioi han theo cai dat user.

#### 7.4.4 Vong doi 1 the
1. Tao moi.
2. Di qua giai doan hoc dau.
3. Neu nho on dinh thi vao Review.
4. Neu quen thi quay lai Learning/Relearning.
5. Neu nguoi dung tam dung thi Suspended.

### 7.5 Module FSRS don gian

#### 7.5.1 Muc tieu
- Ap dung nguyen ly FSRS nhung o muc don gian, de trien khai va de bao tri.
- Van phan tach duoc:
- do kho
- do on dinh
- kha nang nho
- lich on tiep theo

#### 7.5.2 Truong du lieu toi thieu cho moi card
- due_at
- last_reviewed_at
- stability
- difficulty
- elapsed_days
- scheduled_days
- reps
- lapses
- state
- last_rating

#### 7.5.3 4 muc danh gia
- Again: quen hoac nho sai, can hoc lai som.
- Hard: nho kho, can on lai som hon.
- Good: nho duoc, lich on theo chuan.
- Easy: nho rat tot, gian cach on dai hon.

#### 7.5.4 Logic xu ly don gian de xuat
- Card moi:
- Again -> on lai trong vai phut/hoac cung ngay.
- Hard -> on lai ngan.
- Good -> sang ngay tiep theo hoac theo moc ngan.
- Easy -> gian cach dai hon card Good.
- Card dang review:
- Tang stability neu Good/Easy.
- Giam stability neu Again.
- Tang difficulty nhe neu Again/Hard lap lai.
- Giam difficulty nhe neu Good/Easy on dinh.

#### 7.5.5 Cong thuc don gian hoa
- Khong can giong 100% FSRS day du.
- Muc tieu la co 1 bo tham so de tinh `next_interval_days`.
- Vi du huong tinh:
- Again -> interval = 0 hoac 1 ngay tuy state.
- Hard -> interval = max(1, scheduled_days * 1.2)
- Good -> interval = max(2, scheduled_days * 2.0)
- Easy -> interval = max(4, scheduled_days * 3.0)
- Sau do dieu chinh theo stability/difficulty.

#### 7.5.6 Yeu cau mo rong
- Sau nay co the thay bang FSRS chuan hon ma khong pha vo du lieu cu.
- Can luu lich su rating moi lan review.

### 7.6 Module nhap dap an va kiem tra dung sai

#### 7.6.1 Muc tieu
- Tang tinh chu dong thay vi chi lat the.
- Kiem tra xem nguoi hoc co thuc su nho cach viet khong.

#### 7.6.2 Cach nhap
- O input van ban.
- Nut xoa nhanh.
- Nut xem dap an.
- Nut nghe TTS.

#### 7.6.3 Cach cham dung sai
- So sanh sau khi chuan hoa:
- lower case
- bo khoang trang thua
- bo ky tu HTML
- chuyen `&nbsp;` ve khoang trang
- co the bo qua dau cau co ban
- Co che do:
- Exact match
- Lenient match

#### 7.6.4 Ket qua cham
- Dung hoan toan.
- Dung mot phan/gan dung.
- Sai.

#### 7.6.5 Quy tac danh gia
- He thong khong thay the quyen danh gia cuoi cung cua nguoi hoc.
- Ket qua cham chi la goi y.
- Nguoi hoc van bam Again/Hard/Good/Easy.

### 7.7 Module TTS

#### 7.7.1 Muc tieu
- Doc noi dung tieng Anh khi card khong co audio.

#### 7.7.2 Kich ban su dung
- Tu dong doc khi mo mat sau hoac mat truoc.
- Bam nut loa de nghe lai.
- Chon voice nam/nu.
- Chon toc do doc.

#### 7.7.3 Nguon TTS
- Giai doan dau co the uu tien Web Speech API tren frontend neu trinh duyet ho tro.
- Phuong an nang cao:
- Tao audio qua dich vu backend/TTS service.
- Cache file audio len Cloudflare.

#### 7.7.4 Quy tac fallback
- Neu browser khong ho tro TTS, an tinh nang hoac hien thong bao.
- Neu van ban qua dai, cat theo cau.

### 7.8 Module thong ke va streak

#### 7.8.1 Streak
- Streak hien tai.
- Streak dai nhat.
- Ngay hoc gan nhat.
- So luong card toi thieu de tinh la "da hoc trong ngay".

#### 7.8.2 Thong ke co ban
- Tong so card.
- So card moi.
- So card dang hoc.
- So card den han.
- So luot review hom nay.
- Ty le danh gia Again/Hard/Good/Easy.
- So ngay hoc lien tiep.

#### 7.8.3 Bieu do
- Bieu do review 7 ngay.
- Bieu do review 30 ngay.
- So card dung/sai theo ngay.

### 7.9 Module sync da thiet bi

#### 7.9.1 Muc tieu
- Su dung tren iPad, iPhone, may tinh voi cung 1 tai khoan.

#### 7.9.2 Pham vi
- Dong bo card.
- Dong bo lich hoc.
- Dong bo lich su review.
- Dong bo cai dat nguoi dung.

#### 7.9.3 Nguyen tac
- Backend la nguon su that.
- Moi review tao 1 ban ghi lich su co timestamp.
- Co co che conflict resolution co ban:
- Ban ghi moi nhat thang.
- Hoac uu tien server timestamp.

### 7.10 Module notification

#### 7.10.1 Muc tieu
- Nhac nguoi dung quay lai hoc dung gio.

#### 7.10.2 Kieu thong bao
- Nhac hoc hang ngay.
- Nhac khi co nhieu card den han.
- Nhac sap mat streak.

#### 7.10.3 Cau hinh
- Bat/tat thong bao.
- Gio nhac hoc.
- So lan nhac toi da/ngay.

### 7.11 Module handwriting recognition

#### 7.11.1 Muc tieu
- Phu hop cho ngon ngu can viet tay, vi du Han tu, chu Nhat, tieng Trung.

#### 7.11.2 Pham vi du kien
- Khung viet tay.
- Luu net ve.
- Gui len dich vu nhan dien.
- So sanh ket qua voi dap an mong doi.

#### 7.11.3 Ghi chu
- Tinh nang nay de o giai doan sau.
- Khong dua vao MVP.

## 8. Yeu cau giao dien

### 8.1 Nguyen tac responsive
- Mobile first.
- Ho tro iPhone doc.
- Ho tro iPad doc.
- Ho tro iPad ngang.
- Ho tro desktop.

### 8.2 Kich thuoc man hinh uu tien
- Mobile: 375px tro len.
- Tablet: 768px tro len.
- Desktop: 1280px tro len.

### 8.3 Cac man hinh chinh
- Dang nhap/Dang ky.
- Dashboard.
- Danh sach deck.
- Chi tiet deck.
- Tao/Sua card.
- Import txt.
- Phien hoc.
- Ket qua hoc.
- Thong ke.
- Cai dat.

### 8.4 Yeu cau UI cho phien hoc
- Khu vuc hien mat truoc ro rang, de doc.
- Nut nghe TTS de bam.
- O input noi bat khi can nhap dap an.
- Nut lat the de thay dap an.
- 4 nut Again/Hard/Good/Easy de bam duoc tot tren mobile.
- Co che do full-screen gan toi da tren tablet.

### 8.5 Yeu cau UX cho tablet
- iPad ngang:
- Co the hien them thong tin phu ben canh.
- iPad doc:
- Uu tien the hoc o giua.
- Swipe hoac nut bam deu su dung duoc.

## 9. Yeu cau phi chuc nang

### 9.1 Hieu nang
- Thoi gian tai dashboard < 2 giay trong dieu kien thong thuong.
- Chuyen card trong phien hoc phai gan nhu tuc thi.
- Import file 1.000 dong khong qua muc cham khong chap nhan duoc.

### 9.2 Bao mat
- Ma hoa password.
- Xac thuc token an toan.
- Kiem tra quyen truy cap deck/card theo user.
- Validate file upload.
- Gioi han kich thuoc file import.

### 9.3 Kha nang mo rong
- Thiet ke API de sau nay them mobile app native neu can.
- Tach module scheduling de de nang cap FSRS.
- Tach module media/TTS de de doi nha cung cap.

### 9.4 Tin cay du lieu
- Luu log import.
- Luu log review.
- Co soft delete cho card/deck neu can.
- Co migration ro rang.

### 9.5 Kha dung
- Giao dien de hoc ngay ma khong can training nhieu.
- Nut bam va font chu du lon tren tablet/mobile.
- Mau sac va trang thai de nhan biet.

## 10. Mo hinh du lieu de xuat

### 10.1 Bang users
- id
- name
- email
- password
- timezone
- locale
- preferred_tts_voice
- daily_goal
- review_reminder_time
- created_at
- updated_at

### 10.2 Bang decks
- id
- user_id
- name
- description
- color
- is_archived
- created_at
- updated_at

### 10.3 Bang notes
- id
- user_id
- deck_id
- front_text
- back_text
- note_text
- source_type
- source_file_name
- source_raw_line
- created_at
- updated_at

### 10.4 Bang cards
- id
- note_id
- user_id
- deck_id
- state
- due_at
- last_reviewed_at
- stability
- difficulty
- elapsed_days
- scheduled_days
- reps
- lapses
- last_rating
- is_suspended
- created_at
- updated_at

### 10.5 Bang review_logs
- id
- user_id
- card_id
- rating
- typed_answer
- judged_result
- reviewed_at
- previous_state
- next_state
- previous_due_at
- next_due_at
- previous_stability
- next_stability
- previous_difficulty
- next_difficulty

### 10.6 Bang import_jobs
- id
- user_id
- deck_id
- file_name
- file_path
- status
- total_rows
- success_rows
- failed_rows
- started_at
- finished_at
- error_summary

### 10.7 Bang import_job_rows
- id
- import_job_id
- row_number
- raw_content
- parsed_front
- parsed_back
- parsed_tags
- status
- error_message

### 10.8 Bang streaks hoac hoc theo ngay
- id
- user_id
- studied_on
- review_count
- achieved_goal

### 10.9 Bang media_assets
- id
- user_id
- note_id
- provider
- file_name
- file_path
- mime_type
- size
- metadata_json

## 11. Thiet ke API de xuat

### 11.1 Auth
- `POST /api/auth/register`
- `POST /api/auth/login`
- `POST /api/auth/logout`
- `POST /api/auth/forgot-password`
- `POST /api/auth/reset-password`
- `GET /api/me`

### 11.2 Deck
- `GET /api/decks`
- `POST /api/decks`
- `GET /api/decks/{id}`
- `PUT /api/decks/{id}`
- `DELETE /api/decks/{id}`

### 11.3 Card/Note
- `GET /api/decks/{id}/cards`
- `POST /api/cards`
- `GET /api/cards/{id}`
- `PUT /api/cards/{id}`
- `DELETE /api/cards/{id}`

### 11.4 Import
- `POST /api/imports/txt/preview`
- `POST /api/imports/txt/confirm`
- `GET /api/imports/{id}`
- `GET /api/imports/{id}/rows`

### 11.5 Study
- `GET /api/study/session`
- `POST /api/study/{cardId}/check-answer`
- `POST /api/study/{cardId}/rate`
- `POST /api/study/{cardId}/tts`

### 11.6 Statistic
- `GET /api/stats/dashboard`
- `GET /api/stats/reviews`
- `GET /api/stats/streak`

### 11.7 Setting
- `GET /api/settings`
- `PUT /api/settings`

## 12. Quy tac nghiep vu chi tiet

### 12.1 Quy tac tao phien hoc
- Moi phien hoc lay toi da N card moi theo cai dat user.
- Card due duoc uu tien hon card moi.
- Neu khong con due card thi moi lay card moi.

### 12.2 Quy tac ghi nhan hoc trong ngay
- Chi can hoan thanh so luot review toi thieu la duoc tinh hoc trong ngay.
- Muc toi thieu co the la 1, 5 hoac 10 review tuy cai dat.

### 12.3 Quy tac duplicate khi import
- Trung theo front + back sau khi normalize.
- Cho phep user chon:
- Skip
- Replace
- Duplicate

### 12.4 Quy tac xoa du lieu
- Deck xoa se xoa mem.
- Card xoa mem.
- Review_logs khong nen xoa de bao toan lich su.

### 12.5 Quy tac TTS
- Uu tien doc truong tieng Anh.
- Khong doc token `[sound:...]`.
- Loai bo HTML truoc khi doc.

## 13. Thiet ke xu ly import txt

### 13.1 Buoc xu ly
1. Upload file txt.
2. Luu file tam.
3. Doc tung dong.
4. Tach header config.
5. Xac dinh separator.
6. Parse tung row.
7. Normalize noi dung.
8. Preview.
9. User xac nhan.
10. Ghi vao notes/cards.
11. Ghi log import.

### 13.2 Chuan hoa noi dung
- Giu lai HTML goc neu `#html:true`.
- Tao ban plain text de tim kiem/TTS/check answer.
- Tach audio token.
- Tach tags neu co.

### 13.3 Validation tung dong
- Bat buoc co front.
- Bat buoc co back.
- Chieu dai khong vuot muc.
- Khong chua du lieu nguy hiem neu render HTML.

## 14. FSRS don gian - yeu cau trien khai

### 14.1 Muc tieu ky thuat
- Dong goi thanh service rieng, vi du `StudySchedulerService`.
- Dau vao:
- card state hien tai
- lich su gan nhat
- rating moi
- thoi diem review
- Dau ra:
- state moi
- due_at moi
- stability moi
- difficulty moi
- scheduled_days moi

### 14.2 Yeu cau kiem thu
- Card moi bam Again phai quay lai som hon Good.
- Card bam Easy phai co due date xa hon Good.
- Card review bi Again phai tang lapses.
- Review log phai luu day du truoc va sau.

## 15. Test case nghiep vu can co

### 15.1 Auth
- Dang ky hop le.
- Dang ky trung email.
- Dang nhap sai mat khau.

### 15.2 Import
- Import file dung format.
- Import file co html.
- Import file co audio token.
- Import file thieu cot.
- Import file separator sai.
- Import file trung du lieu.

### 15.3 Study
- Bat dau phien hoc khi co due card.
- Bat dau phien hoc khi chi co new card.
- Check answer dung.
- Check answer gan dung.
- Check answer sai.
- Rate Again/Hard/Good/Easy.

### 15.4 Streak
- Hoc lien tiep 3 ngay.
- Bo qua 1 ngay va reset streak theo quy tac.

### 15.5 TTS
- Trinh duyet ho tro voice.
- Trinh duyet khong ho tro voice.

## 16. Lo trinh thuc hien de xuat

### Giai doan 1: Requirement va foundation
- Chot requirement.
- Chot schema database.
- Chot wireframe.
- Tao auth, deck, card CRUD.

### Giai doan 2: Import va hoc co ban
- Import txt Anki.
- Tao phien hoc.
- Hien thi card.
- 4 nut danh gia.
- Luu review_logs.

### Giai doan 3: FSRS don gian va check answer
- Trien khai scheduler service.
- Them input dap an.
- Them logic so sanh dung/sai.
- Dashboard thong ke co ban.

### Giai doan 4: TTS va toi uu responsive
- Them TTS.
- Toi uu iPad ngang/doc.
- Toi uu mobile interaction.

### Giai doan 5: Nang cao
- Sync da thiet bi.
- Notification.
- Cloudflare media.
- Handwriting recognition.

## 17. Rui ro va luu y

### 17.1 Rui ro ky thuat
- FSRS neu lam qua don gian co the cho lich on chua toi uu.
- Import txt Anki trong thuc te co nhieu bien the.
- TTS tren browser phu thuoc tung nen tang.
- iOS web app co mot so gioi han voi audio va notification.

### 17.2 Huong giam thieu
- Viet parser linh hoat va co preview.
- Luu raw line de debug.
- TTS co fallback.
- Scheduler tach rieng de nang cap sau.

## 18. Dinh huong mo rong sau MVP
- Them CSV import.
- Them speech-to-text de luyen noi.
- Them che do quiz.
- Them shared deck.
- Them phan tich tu kho, tu hay sai.
- Them offline-first/PWA day du.

## 19. Ket luan
- Day la ung dung hoc tu vung lay y tuong tu Anki, nhung duoc toi uu cho trai nghiem responsive va quy trinh hoc nhanh.
- Trong giai doan dau, can tap trung vao 4 khoi chinh:
- Import txt Anki
- Hoc/on bang FSRS don gian
- Check answer + TTS
- Responsive UI cho mobile/tablet/desktop
- Neu hoan thanh tot MVP nay, du an da co nen tang rat tot de mo rong thanh mot he thong hoc ngon ngu day du hon.
