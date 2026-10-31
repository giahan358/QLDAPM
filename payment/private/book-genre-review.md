# Rà soát thể loại sách — 14/09/2026

Đã rà soát 52 bản ghi trong `webbansach.sach`; đổi `idtheloai` cho 20 sách, giữ nguyên 32 bản ghi (gồm 2 bản ghi chưa xác định được tác phẩm). Không thay đổi tác giả, mô tả, giá, tồn kho, danh mục hoặc dữ liệu đơn hàng. Script đối chiếu tất cả cột sách trước và sau trong transaction.

Do mỗi sách hiện chỉ có một `idtheloai`, đây là lựa chọn thể loại chính phục vụ tra cứu, không phải danh sách đầy đủ các thể loại của tác phẩm. Các tiểu thuyết hiện thực/tâm lý, tác phẩm hiện thực huyền ảo của Murakami vẫn có thể nằm trong nhóm Tiểu thuyết. Tiểu thuyết có bối cảnh lịch sử không tự động chuyển thành sách Lịch sử. Không phân loại theo tác giả đang gán trong DB vì nhiều bản ghi tác giả có dấu hiệu nhập sai.

## Các thay đổi

| ID sách | Sách | Thể loại mới (ID) |
|---|---|---|
| 2 | Rừng Na Uy | Lãng mạn (4) |
| 4 | Chí Phèo | Truyện ngắn (11) |
| 10 | Góc sân và khoảng trời | Thơ (12) |
| 15, 16 | Cô gái đến từ hôm qua; Mắt biếc | Lãng mạn (4) |
| 21, 23 | Tiếng chim hót trong bụi mận gai; Cuốn theo chiều gió | Lãng mạn (4) |
| 24–30 | Bảy tập Harry Potter | Kỳ ảo (13) |
| 35, 36 | Đồi gió hú; Jane Eyre | Lãng mạn (4) |
| 44 | Trại súc vật | Tiểu thuyết (1) |
| 46, 47 | Chúa tể những chiếc nhẫn; Hobbit | Kỳ ảo (13) |
| 51 | Điều kỳ diệu (Wonder, theo mô tả Auggie Pullman) | Thiếu nhi (9) |

Ba thể loại mới là Truyện ngắn, Thơ và Kỳ ảo. Góc sân và khoảng trời vẫn là thơ cho thiếu nhi; ở trường thể loại duy nhất đã ưu tiên hình thức thơ. Nhóm Lãng mạn được sử dụng theo nghĩa rộng là văn học tình cảm, gồm cả tình yêu bi kịch/Gothic.

## Nguồn đối chiếu

- [NXB Trẻ về Harry Potter](https://www.nxbtre.com.vn/diem-tin/harry-potter-bo-tui-va-hanh-trinh-dua-bo-truyen-phu-thuy-ve-viet-nam-114994.html): cơ sở chuyển các tập phép thuật sang Kỳ ảo.
- [Penguin Random House — The Lord of the Rings](https://www.penguinrandomhouse.com/series/LOR/the-lord-of-the-rings/): fantasy.
- [HarperCollins/HarperReach — The Hobbit](https://www.harperreach.com/products/the-hobbit-j-r-r-tolkien-9780261103344/): phiêu lưu fantasy.
- [Penguin Random House — Animal Farm](https://assets.penguinrandomhouse.com/book-resumes/OrwellGeorge_AnimalFarm.pdf): ngụ ngôn châm biếm chính trị, đưa vào nhóm tiểu thuyết thay cho khoa học viễn tưởng.
- [Penguin Random House — Wonder](https://penguinrandomhouselibrary.com/book/?isbn=9780375969027): văn học cho độc giả middle-grade.
- [NXB Trẻ — Mắt biếc](https://www.nxbtre.com.vn/sach/mat-biec-146386.html): nội dung tình cảm của tác phẩm.
- [Nghiên cứu về Góc sân và khoảng trời, Đại học Thủ Dầu Một](https://khcn.tdmu.edu.vn/Data/files/HNKH%202023%20Tap%202.pdf): tập thơ Trần Đăng Khoa.

## Bản ghi cần bổ sung thông tin

- ID 39: “Những kẻ si tinh”, tác giả trong DB là Agatha Christie nhưng mô tả là Những người khốn khổ của Victor Hugo. Có nhiều tác phẩm tên gần giống “Những kẻ si tình”; cần ảnh bìa/ISBN để xác định. Giữ Tiểu thuyết (1).
- ID 59: “đ”, mô tả “sdsd”; chưa xác định được tác phẩm. Giữ NULL.

## Sao lưu và khôi phục

Thư mục `backups/genres-20260914-085023-69ae54/` chứa `before.json` (dữ liệu sách và thể loại trước sửa), `changes.json` (20 thay đổi và lý do) và `restore.sql` (khôi phục có điều kiện). Thư mục này nằm trong vùng private không phục vụ qua web.

Script CLI `reclassify-books.php` mặc định chỉ xem trước, cần `--apply` mới cập nhật. Chạy lại sau khi đã áp dụng không đổi thêm dữ liệu. Không nhập lại toàn bộ bản dump SQL cũ để tránh ghi đè dữ liệu bán hàng hiện tại.
