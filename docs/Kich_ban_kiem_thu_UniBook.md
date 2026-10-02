# UNIBOOK
# KỊCH BẢN KIỂM THỬ MUA HÀNG
Từ giỏ hàng đến thanh toán, giao nhận, hủy/hoàn tiền và đánh giá sản phẩm
Ngày dự kiến thực hiện: 15/09/2026. Phạm vi: bản UniBook hiện tại, COD và VNPAY Sandbox.
Đây là kịch bản kiểm thử thủ công để thành viên nhóm thao tác và ghi nhận kết quả; không phải chương trình tự động chạy hoặc kết quả đã kiểm thử.

## 1. Chuẩn bị trước buổi test
- Người điều phối ghi phiên bản mã nguồn, giờ bắt đầu, địa chỉ website đang dùng và người phụ trách xử lý lỗi.
- Khởi động Apache/MySQL trên XAMPP. Kiểm tra ngrok đang chuyển tiếp đúng website; giữ máy chủ và ngrok hoạt động xuyên suốt bài test VNPAY.
- Khách dùng địa chỉ public đang hoạt động hoặc localhost theo phân công. Admin chỉ thao tác trực tiếp trên máy chủ bằng địa chỉ localhost; truy cập admin qua public phải bị chặn.
- Chuẩn bị hai tài khoản khách A và B, một tài khoản nhân viên có quyền quản lý đơn hàng, hai địa chỉ nhận hàng hợp lệ của A và một địa chỉ của B.
- Chọn hai sách đang kinh doanh, gọi là S1 và S2. Ghi mã sách, giá hiện tại và tồn kho ban đầu K1, K2. Dành riêng sách hoặc một khung giờ cho nhóm test tồn kho để không bị người khác mua làm sai số.
- Chuẩn bị ảnh JPG, PNG hoặc WebP hợp lệ nhỏ hơn 2 MB; thêm ảnh quá 2 MB, tệp PDF hoặc tệp giả ảnh và bốn ảnh để kiểm tra giới hạn.
- Dùng hai trình duyệt hoặc hai hồ sơ riêng cho khách và admin. Để cửa sổ khách đang hiển thị khi kiểm tra tự đồng bộ; không dùng chung phiên đăng nhập của A và B.
- Thanh toán chỉ thực hiện ở VNPAY Sandbox. Dùng bộ thẻ thử do nhóm đã xác nhận hoạt động với Sandbox; không nhập thẻ thật. Không ghi mật khẩu, OTP cá nhân hoặc HashSecret vào báo cáo.
- Sao lưu DB trước buổi test. Không xóa hoadon/chitiethoadon, giao dịch hay session trong lúc đang theo dõi thanh toán: thao tác đó phá liên kết và làm kết quả test không còn đáng tin.

### 1.1. Phân công đề xuất
| Vai trò | Nhiệm vụ |
|---|---|
| Tester khách | Thao tác giỏ, đặt hàng, thanh toán, hủy và đánh giá; ghi ảnh/video |
| Tester admin | Chuyển trạng thái, xét duyệt hủy và theo dõi hoàn tiền trên localhost |
| Người kiểm tra dữ liệu | Đọc bản ghi và số lượng tồn kho; đối chiếu mã đơn/mã giao dịch |
| Người tổng hợp | Ghi PASS/FAIL/BLOCKED, lập lỗi và theo dõi kiểm thử lại |

### 1.2. Dữ liệu phải ghi cho mỗi lần chạy
| Trường | Giá trị điền khi chạy |
|---|---|
| Người test / ngày giờ / phiên bản | ........................................................ |
| Thiết bị / trình duyệt / URL | ........................................................ |
| Khách / mã S1, S2 / giá / tồn đầu | ........................................................ |
| Mã đơn / mã phiên thanh toán / vnp_TxnRef | ........................................................ |
| Tổng tiền / phương thức / ảnh bằng chứng | ........................................................ |

### 1.3. Quy ước kết quả
PASS: toàn bộ kết quả mong đợi đạt. FAIL: có kết quả sai và tái hiện được. BLOCKED: chưa thể kết luận vì thiếu dữ liệu, ngrok, Sandbox hoặc quyền truy cập. Không đánh dấu PASS chỉ vì thấy trang “thành công”: phải kiểm tra giỏ, đơn của khách, đơn admin và dữ liệu liên quan.
Mỗi test ghi theo mẫu: Kết quả thực tế: …; PASS/FAIL/BLOCKED: …; Mã lỗi/bằng chứng: …; Người test/giờ: … . Có thể nhập trực tiếp dưới từng test trong Word.
Các test thay đổi trạng thái cần đơn riêng, trừ chuỗi được chỉ định. Không dùng lại đơn đã nhận hoặc đã hủy để bắt đầu nhánh khác.

---PAGE---
## 2. Luồng chuẩn cần hiểu trước khi test
COD: Giỏ → Đặt hàng → Hóa đơn đang chờ/chưa thanh toán, xóa phần hàng đã đặt khỏi giỏ → Admin chuyển đang giao, trừ kho → Xác nhận đã nhận, ghi đã thanh toán → Đánh giá trong 10 ngày.
VNPAY: Giỏ → Tạo phiên thanh toán → Sang Sandbox → Máy chủ xác minh thanh toán → Tạo hóa đơn đang chờ/đã thanh toán và cập nhật giỏ → Admin chuyển đang giao, trừ kho → Xác nhận đã nhận → Đánh giá trong 10 ngày.
Hủy: Đang chờ thì hủy trực tiếp; đang giao phải có admin xét duyệt; đã nhận không còn yêu cầu hủy trên website. Đơn VNPAY đã trả tiền cần theo dõi hoàn tiền riêng với trạng thái hủy đơn.
VNPAY chưa thanh toán có thể xuất hiện dưới dạng bản ghi thanh toán chưa phát hành hóa đơn trong danh sách admin. Đây chưa phải hóa đơn của khách. Khi trả tiền thành công phải liên kết đúng phiên với hóa đơn được phát hành, không để bản ghi treo mãi.

## 3. Giỏ hàng và đặt hàng
### TC01 — Thêm, sửa, xóa giỏ hàng [P0]
Điều kiện: A đăng nhập; giỏ trống; S1/S2 đủ tồn.
1) Vào chi tiết S1, chọn số lượng 2, thêm vào giỏ. Kiểm tra giỏ và tổng tiền.
2) Thêm S2 từ khu vực sản phẩm liên quan; thử nút mua và thêm giỏ ở đây.
3) Tăng/giảm số lượng S1; xóa S2; tải lại trang rồi mở lại giỏ.
Mong đợi: thao tác hoạt động; số lượng và tổng tiền khớp giá máy chủ; dữ liệu giỏ được lưu; chưa có hóa đơn và chưa trừ kho.
Ghi nhận: ........................................................................................

### TC02 — Giỏ không hợp lệ và địa chỉ [P1]
Điều kiện: A có giỏ; biết tồn hiện tại của S1.
1) Thử đặt khi giỏ rỗng; thử số lượng 0, âm hoặc lớn hơn tồn qua giao diện nếu giao diện cho nhập.
2) Tạo địa chỉ mới, bỏ trống lần lượt tên/số điện thoại/địa chỉ; sau đó nhập hợp lệ và lưu.
3) Chọn địa chỉ vừa tạo để đặt hàng; kiểm tra tiếng Việt, tỉnh/thành và thông tin người nhận ở bước xác nhận.
Mong đợi: dữ liệu không hợp lệ bị từ chối, không sinh hóa đơn dở dang; địa chỉ hợp lệ được lưu/chọn đúng. Không hiển thị chữ lỗi hoặc hộp lệch che nút.
Ghi nhận: ........................................................................................

### TC03 — Bấm đặt hàng nhiều lần [P0]
Điều kiện: giỏ hợp lệ; dùng một yêu cầu đặt COD để dễ đối chiếu.
1) Bấm đặt hàng liên tiếp nhanh hoặc nhấp đúp. Nếu cần, người kỹ thuật gửi lại chính yêu cầu vừa tạo, giữ nguyên mã chống trùng.
2) Kiểm tra danh sách đơn và dữ liệu theo khách/giờ tạo.
Mong đợi: chỉ một hóa đơn cho cùng yêu cầu; chi tiết không nhân đôi; tổng tiền và phần giỏ bị xóa đúng một lần. Một lần mua mới với mã yêu cầu mới không thuộc test chống trùng này.
Ghi nhận: ........................................................................................

---PAGE---
## 4. COD — chuỗi thành công từ đầu đến cuối
### TC04 — Đặt COD thành công [P0]
Điều kiện: ghi K1/K2; giỏ A gồm 2 S1 và 1 S2; địa chỉ hợp lệ.
1) Chọn “Thanh toán khi nhận hàng”, xác nhận một lần.
2) Đóng hộp thông báo; vào đơn hàng của khách và danh sách admin; ghi mã đơn C1.
3) Đối chiếu tổng tiền với 2 × giá S1 + giá S2 và chi tiết từng dòng.
Mong đợi: thông báo nằm trên các khối khác và đóng được; C1 ở trạng thái đang chờ, COD/chưa thanh toán; giỏ đã bỏ hàng thuộc đơn; tồn vẫn K1/K2; chỉ một hóa đơn với chi tiết đúng khách và địa chỉ.
Ghi nhận: ........................................................................................

### TC05 — Chuyển đang giao và đồng bộ [P0]
Điều kiện: dùng C1 của TC04; để trang đơn của khách mở và đang hiển thị.
1) Admin chuyển C1 từ đang chờ sang đang giao.
2) Không reload trang khách; quan sát khoảng 5–15 giây trên mạng ổn định.
3) Đọc tồn kho; tải lại admin hoặc thử gửi lại cùng chuyển trạng thái nếu người kỹ thuật hỗ trợ.
Mong đợi: khách tự thấy đang giao; tồn S1 = K1−2, S2 = K2−1; COD vẫn chưa thanh toán; không trừ thêm khi đọc lại hoặc xử lý lặp. Nếu không đồng bộ, ghi thời gian chờ và trạng thái request, không tự kết luận do Sandbox.
Ghi nhận: ........................................................................................

### TC06 — Xác nhận nhận hàng COD [P0]
Điều kiện: C1 đang giao; mô phỏng đã giao hàng và thu tiền.
1) Khách bấm xác nhận đã nhận và xác nhận hộp thoại.
2) Mở lại chi tiết ở khách/admin; ghi thời điểm nhận.
3) Thử nhấp lặp hoặc reload, kiểm tra lại tồn và thời điểm nhận.
Mong đợi: đơn đã nhận; COD hiển thị đã thanh toán ở cả hai phía; không trừ kho lần hai; thời điểm nhận không bị kéo dài do thao tác lặp. Không còn nút/yêu cầu hủy; xuất hiện quyền đánh giá. Giữ C1 cho TC20–TC23.
Ghi nhận: ........................................................................................

## 5. VNPAY — thanh toán, thất bại và thanh toán lại
### TC07 — Thanh toán Sandbox thành công [P0]
Điều kiện: giỏ mới và tồn đủ; ngrok/IPN hoạt động; ghi tồn trước đặt.
1) Chọn VNPAY, xác nhận, ghi mã phiên và vnp_TxnRef nếu có trong URL hoặc công cụ theo dõi nội bộ.
2) Khi còn ở Sandbox và chưa trả tiền, kiểm tra admin: bản ghi phải là giao dịch chưa phát hành hóa đơn; giỏ vẫn còn; tồn chưa giảm.
3) Hoàn tất thanh toán bằng dữ liệu thử hợp lệ. Quay về UniBook và giữ trang mở chờ xác nhận.
4) Kiểm tra khách, admin, giỏ và DB; ghi mã hóa đơn V1.
Mong đợi: sau xác minh thành công, đúng một V1 có đủ chi tiết, đang chờ/đã thanh toán VNPAY; xuất hiện bên khách; bản ghi thanh toán liên kết V1; phần giỏ đã đặt được xóa; tồn chưa giảm. Không coi thông báo ở Sandbox là đủ bằng chứng UniBook đã ghi nhận.
Ghi nhận: ........................................................................................

### TC08 — Giao và nhận đơn VNPAY [P0]
Điều kiện: V1 đã thanh toán từ TC07.
1) Admin chuyển đang giao, kiểm tra đồng bộ khách và tồn theo số lượng V1.
2) Khách xác nhận đã nhận; kiểm tra quyền đánh giá.
Mong đợi: trừ kho một lần tại đang giao; khi nhận vẫn đã thanh toán, không yêu cầu thu COD; không trừ kho lần hai; không còn hủy đơn; đánh giá được mở trong thời hạn.
Ghi nhận: ........................................................................................

### TC09 — Hủy trên trang VNPAY rồi thanh toán lại [P0, hồi quy lỗi cũ]
Điều kiện: tạo phiên VNPAY mới, ghi mã phiên, tổng tiền, giỏ và tồn.
1) Sang Sandbox, chọn hủy/không hoàn tất thanh toán và quay lại UniBook.
2) Kiểm tra trạng thái trả về, danh sách giao dịch/đơn admin và giỏ.
3) Khi hệ thống xác nhận FAILED và cho thanh toán lại, bấm thanh toán lại đúng phiên đó; ghi mã tham chiếu mới rồi hoàn tất thành công.
4) Chờ máy chủ xác minh; kiểm tra đầy đủ hóa đơn khách/admin, chi tiết và giỏ.
Mong đợi: lần không trả tiền không phát hành hóa đơn; giỏ giữ nguyên; không trừ kho. Retry từ FAILED dùng mã tham chiếu mới nhưng cùng phiên/nội dung đơn. Thành công sau đó phát hành đúng một hóa đơn đã thanh toán, xóa phần giỏ tương ứng và hiện bên khách; không còn treo “Chưa phát hành hóa đơn”.
Lưu ý: nếu đang PENDING thì chưa được coi là thất bại; tiếp tục URL còn hạn có thể dùng lại mã hiện tại. Không yêu cầu mã mới cho tình huống chỉ tiếp tục giao dịch PENDING.
Ghi nhận: ........................................................................................

### TC10 — Bỏ dở, đóng tab, hết hạn [P1]
1) Tạo phiên mới rồi đóng tab VNPAY trước thanh toán; mở lại lịch sử bằng cùng tài khoản.
2) Với phiên khác, để URL hết hạn khoảng 15 phút rồi thử tiếp tục; ghi thời gian tạo/thời gian thử.
3) Theo dõi kết quả đối soát; chỉ thử lại khi hệ thống cho phép.
Mong đợi: chưa có bằng chứng trả tiền thì không phát hành hóa đơn, không xóa giỏ và không trừ kho. PENDING/không xác định phải được phân biệt với FAILED; URL hết hạn không được coi là thanh toán thành công. Không sinh thêm lần thu tiền trong lúc giao dịch cũ chưa rõ kết quả.
Ghi nhận: ........................................................................................

### TC11 — Xác nhận chậm và phục hồi khi mở lại trang [P0]
Điều kiện: phiên Sandbox có thanh toán thành công nhưng ứng dụng đang chờ xác nhận; nếu không tái hiện được độ trễ, ghi BLOCKED cho nhánh này.
1) Giữ trang kết quả mở, ghi thời gian thanh toán và thời gian ghi nhận hóa đơn.
2) Quan sát đối soát tự động; lần truy vấn bổ sung có thể phải chờ khoảng 5 phút do giới hạn gọi lại.
3) Thử chuyển tab rồi quay lại hoặc đóng trang kết quả và mở lịch sử; không tạo đơn mới để chữa lỗi.
Mong đợi: trang hiển thị đang chờ khi chưa rõ; tiếp tục kiểm tra khi trang hoạt động; khi nhận kết quả hợp lệ thì tự cập nhật đơn/giỏ. Không thông báo thành công từ dữ liệu chưa xác minh. Nếu chưa giải quyết sau 10–15 phút, ghi BLOCKED nếu dịch vụ không trả kết quả, hoặc FAIL nếu log có xác nhận thành công mà dữ liệu vẫn không cập nhật.
Ghi nhận: ........................................................................................

### TC12 — Giới hạn retry và bảo toàn giỏ [P1]
1) Dùng một phiên thử riêng; làm thất bại có xác nhận rồi retry cho đến tối đa 5 lần retry ngoài lần đầu.
2) Kiểm tra lịch sử attempt có các mã khác nhau; thử vượt giới hạn.
3) Với phiên khác, sau khi mở Sandbox, thêm số lượng/sản phẩm mới vào giỏ bằng tab khác, rồi hoàn tất thanh toán phiên ban đầu.
Mong đợi: vượt giới hạn bị chặn; không nhân đôi hóa đơn. Thanh toán dùng nội dung phiên ban đầu; hàng thêm sau đó không bị xóa nhầm. Các lần thử và kết quả được lưu để đối chiếu.
Ghi nhận: ........................................................................................

---PAGE---
## 6. Hủy đơn và hoàn tiền
### TC13 — Hủy COD đang chờ [P0]
1) Tạo đơn COD riêng C2, chưa chuyển giao; ghi tồn. Khách chọn hủy, nhập lý do và xác nhận.
2) Kiểm tra hai phía và dữ liệu.
Mong đợi: hủy trực tiếp không cần admin duyệt; lưu lý do; không trừ/hoàn kho vì chưa xuất; không phát sinh hoàn tiền COD; hiển thị đã hủy/không thu tiền, không hiển thị như đã thu rồi hoàn.
Ghi nhận: ........................................................................................

### TC14 — Hủy VNPAY đã trả tiền, đang chờ [P0]
1) Tạo và trả tiền thành công một đơn V2; chưa giao. Khách hủy và nhập lý do.
2) Theo dõi trạng thái hủy và hoàn tiền ở admin; dùng “Kiểm tra hoàn tiền” khi cần.
3) Ghi mã yêu cầu hoàn, số tiền và kết quả xác minh.
Mong đợi: đơn bị hủy; tạo yêu cầu hoàn toàn bộ số đã thanh toán; tồn không đổi. Hoàn tiền đang chờ/chưa rõ không được ghi REFUNDED. Chỉ chuyển đã hoàn khi có xác nhận thành công; không gửi hai lệnh hoàn vì nhấp lặp.
Ghi nhận: ........................................................................................

### TC15 — Đang giao, admin từ chối hủy [P0]
Điều kiện: một đơn COD hoặc VNPAY đang giao; ghi tồn sau xuất.
1) Khách yêu cầu hủy với lý do. Admin mở thông báo xét hủy màu đỏ/gạch chân ngay trong danh sách đơn.
2) Kiểm tra hộp giữa màn hình, lý do khách, nhập lý do từ chối và xác nhận.
3) Khách quan sát cập nhật; tiếp tục giao và nhận đơn nếu phù hợp.
Mong đợi: lúc chờ duyệt đơn không tự hủy, chưa hoàn kho/tiền; không cho xác nhận nhận hàng khi đang xét hủy. Từ chối giữ đang giao, lưu phản hồi, tồn không đổi và không hoàn tiền. Phiên bản hiện tại không tạo yêu cầu hủy mới sau yêu cầu đã bị từ chối; hỗ trợ tiếp qua chăm sóc khách hàng.
Ghi nhận: ........................................................................................

### TC16 — Đang giao, admin duyệt hủy [P0]
Điều kiện: thực hiện riêng cho một đơn COD và một đơn VNPAY đã trả tiền, cả hai đã bị trừ kho.
1) Khách yêu cầu hủy; admin mở hộp, đọc lý do, nhập phản hồi và duyệt.
2) Kiểm tra tồn; thử thao tác duyệt lại hoặc tải lại trang.
3) Với VNPAY theo dõi hoàn tiền; với COD kiểm tra không tạo hoàn tiền.
Mong đợi: đơn hủy, lưu người duyệt và phản hồi; hoàn kho đúng số đã xuất một lần. VNPAY có yêu cầu hoàn toàn phần, COD không thu tiền. Duyệt lặp không cộng kho hoặc hoàn tiền thêm.
Lưu ý vận hành: ứng dụng hoàn kho khi duyệt hủy; người test mô phỏng hàng đã được thu hồi/được phép hoàn kho, không nhầm đây là xác nhận từ hãng vận chuyển.
Ghi nhận: ........................................................................................

### TC17 — Hoàn tiền chưa rõ/lỗi và hộp kết quả [P1]
Điều kiện: có yêu cầu hoàn đang xử lý hoặc dùng môi trường test kỹ thuật có mô phỏng lỗi; không ngắt kết nối giữa chừng trên đơn người dùng thật.
1) Bấm kiểm tra hoàn tiền; quan sát tải, kết quả, đóng hộp và bấm lại trong thời gian hạn chế truy vấn.
2) Người kỹ thuật mô phỏng timeout/kết quả không xác định nếu Sandbox không phát sinh tự nhiên.
Mong đợi: hộp nằm giữa màn hình, có trạng thái tải và thông báo rõ; kết quả không chen trực tiếp vào bảng. Timeout giữ trạng thái cần đối soát, không tự gửi lại lệnh hoàn có nguy cơ trùng; giới hạn truy vấn được xử lý. Chưa xác nhận thì chưa ghi đã hoàn tiền.
Ghi nhận: ........................................................................................

### TC18 — Không được hủy sau nhận [P0]
1) Mở đơn đã nhận ở khách và admin; tìm các nút/yêu cầu hủy cũ.
2) Người kỹ thuật thử gửi lại yêu cầu hủy cũ đối với đúng đơn đã nhận.
Mong đợi: không còn hành động hủy tự phục vụ; máy chủ từ chối yêu cầu không hợp lệ; trạng thái, kho và tiền không đổi. Khách cần liên hệ bộ phận chăm sóc khách hàng.
Ghi nhận: ........................................................................................

### TC19 — Không đủ tồn khi chuẩn bị giao [P1]
Điều kiện: người phụ trách chuẩn bị dữ liệu test riêng có tồn thấp hơn số lượng đơn, không sửa dữ liệu đang được nhóm khác sử dụng.
1) Admin thử chuyển đơn đang chờ sang đang giao.
2) Đối chiếu tất cả sách trong đơn và trạng thái đơn.
Mong đợi: từ chối giao khi thiếu hàng; không trừ một phần tồn rồi để lỗi ở sản phẩm sau; trạng thái không chuyển dở dang. Đơn VNPAY đã trả tiền vẫn cần xử lý nghiệp vụ thiếu hàng, không tự mất ghi nhận thanh toán.
Ghi nhận: ........................................................................................

---PAGE---
## 7. Đánh giá sản phẩm
### TC20 — Đánh giá một phần đơn nhiều sách [P0]
Điều kiện: C1 đã nhận, có S1/S2, chưa đánh giá, trong 10 ngày.
1) Mở hộp đánh giá, chọn S1, chọn 5 sao, nhập bình luận có dấu, đính kèm một ảnh hợp lệ rồi gửi.
2) Mở lại đơn; kiểm tra S1 đã đánh giá và S2 chưa đánh giá.
Mong đợi: hộp giữa màn hình, thao tác được trên desktop/điện thoại; lưu đúng sao, chữ và ảnh cho S1/C1. Nút đánh giá vẫn còn vì S2 chưa được đánh giá; không cho tạo đánh giá S1 lần hai trong C1.
Ghi nhận: ........................................................................................

### TC21 — Đánh giá hết đơn, ẩn nút [P0]
1) Tiếp tục C1, gửi đánh giá S2 không kèm ảnh.
2) Đóng hộp, quan sát danh sách đơn rồi reload/đăng nhập lại.
Mong đợi: ảnh là tùy chọn; sau khi mọi sản phẩm trong C1 đã có đánh giá, không còn nút đánh giá ở hóa đơn này; có thể hiển thị đã đánh giá. Mua nhiều cuốn cùng một mã sách trong một hóa đơn không yêu cầu đánh giá từng cuốn.
Ghi nhận: ........................................................................................

### TC22 — Xem đánh giá ở trang sản phẩm và admin [P0]
1) Mở chi tiết S1 với tài khoản khác hoặc không đăng nhập; xem đánh giá TC20, số sao trung bình, số lượt và ảnh.
2) Admin vào danh sách sản phẩm, mở cột đánh giá S1; cuộn/phân trang nếu có nhiều đánh giá.
3) Bấm liên kết hóa đơn của đánh giá vừa tạo.
Mong đợi: sao, bình luận tiếng Việt và ảnh khớp; không lẫn đánh giá S2; số liệu tổng hợp hợp lý. Hộp admin cuộn được và liên kết mở đúng C1, không phải đơn khác của A.
Ghi nhận: ........................................................................................

### TC23 — Dữ liệu đánh giá không hợp lệ [P1]
Điều kiện: đơn đã nhận khác, còn sản phẩm chưa đánh giá và trong hạn.
1) Thử bỏ sao, bỏ bình luận hoặc nhập toàn khoảng trắng; thử bình luận trên 2.000 ký tự.
2) Thử bốn ảnh, ảnh trên 2 MB, PDF đổi đuôi JPG và ảnh trên 12 megapixel.
3) Sau các lần bị từ chối, gửi bình luận 1–2.000 ký tự, 1–5 sao và tối đa ba ảnh JPG/PNG/WebP đúng giới hạn.
Mong đợi: dữ liệu sai bị từ chối rõ ràng, không lưu đánh giá/ảnh dở dang; dữ liệu hợp lệ lưu được đúng một lần. Nếu giao diện chặn trước khi gửi, ghi rõ; phần kiểm tra phía máy chủ do người kỹ thuật thực hiện riêng.
Ghi nhận: ........................................................................................

### TC24 — Thời hạn 10 ngày và quyền đánh giá [P0]
Điều kiện: người phụ trách tạo dữ liệu trên DB test/khôi phục bản sao: đơn chưa nhận; đơn vừa nhận; đơn nhận cách đây 9 ngày 23 giờ 59 phút; đơn đúng 10 ngày; đơn trên 10 ngày. Ghi thời gian máy chủ và dữ liệu được dùng; không đổi đồng hồ hệ thống máy đang thanh toán.
1) Mở từng đơn bằng đúng khách mua, kiểm tra quyền và thử gửi đánh giá.
2) Dùng B thử đánh giá đơn của A; thử mã sách không có trong đơn; thử gửi lại cùng đánh giá hai lần.
Mong đợi: chỉ đúng người mua, đúng sản phẩm đã nhận, chưa đánh giá và thời gian nhỏ hơn 10 ngày mới gửi được. Đúng mốc 10 ngày trở đi bị chặn cả ở máy chủ; đơn chưa nhận/đã hủy không được đánh giá. Không có bản ghi trùng cùng hóa đơn và sách.
Ghi nhận: ........................................................................................

---PAGE---
## 8. Kiểm thử kỹ thuật và giao diện bổ sung
### TC25 — IPN trùng lặp, sai checksum, sai số tiền [P0, người kỹ thuật]
Điều kiện: môi trường test cô lập; dùng công cụ gửi callback/fixture của dự án. Không đưa HashSecret vào Word hoặc nhóm chat.
1) Gửi lại callback hợp lệ đã xử lý: đối chiếu số hóa đơn, số chi tiết, giỏ và tồn trước/sau.
2) Thay một tham số nhưng giữ chữ ký cũ: phải bị từ chối checksum (97), dữ liệu không đổi.
3) Dùng fixture được ký hợp lệ nhưng sai số tiền so với DB: phải trả 04; mã tham chiếu không tồn tại: 01. Các ca này cần người có công cụ ký trong môi trường test, sửa query thủ công không kiểm tra được nhánh sau checksum.
4) Gọi Return hợp lệ hoặc sửa Return để trông giống thành công; theo dõi DB và log nguồn xác nhận.
Mong đợi: callback trùng đã xác nhận trả 02, không xử lý tiền/giỏ lần hai. Return chỉ xác minh/hiển thị; cập nhật thanh toán phải dựa IPN hoặc kết quả truy vấn máy chủ đã xác minh. Trong test Return cần cô lập các luồng xác nhận khác để tránh kết luận nhầm.
Ghi nhận: ........................................................................................

### TC26 — Phân quyền và admin public [P0]
1) Mở admin bằng URL public/ngrok; thử cùng trang bằng localhost với tài khoản đúng quyền.
2) Dùng B thử xem chi tiết, hủy, xác nhận nhận, retry hoặc kiểm tra giao dịch của A bằng mã đã biết.
3) Người kỹ thuật thử yêu cầu thay đổi thiếu/sai CSRF.
Mong đợi: admin public bị chặn; localhost vẫn cần đăng nhập/quyền. Khách khác không đọc/sửa đơn hoặc thanh toán của A; yêu cầu không hợp lệ không làm đổi DB.
Ghi nhận: ........................................................................................

### TC27 — Responsive và các hộp thoại [P1]
1) Chạy lại thao tác chính ở chiều rộng 375/390 px và desktop khoảng 1366 px: giỏ, tạo địa chỉ, đặt COD, đánh giá, hủy, xét hủy, kiểm tra hoàn tiền.
2) Cuộn nội dung dài; mở bàn phím điện thoại ở ô nhập; thử đóng hộp và tiếp tục thao tác.
Mong đợi: không mất chữ “Số lượng”, không tràn ngang hoặc lỗi phông tiếng Việt; hộp nằm giữa vùng nhìn và phía trên nền, nội dung dài cuộn được; nút xác nhận/đóng không bị che. Giả lập trình duyệt cần bổ sung một lần trên iPhone thật nếu có.
Ghi nhận: ........................................................................................

## 9. Đối chiếu dữ liệu và tổng hợp
### 9.1. Các mốc cần kiểm tra
| Mốc | Hóa đơn / thanh toán | Giỏ và tồn |
|---|---|---|
| Vừa thêm giỏ | Chưa phát hành hóa đơn | Có hàng trong giỏ; tồn không đổi |
| Đặt COD hợp lệ | Đang chờ; chưa thanh toán | Xóa phần hàng đã đặt; tồn không đổi |
| Mới mở VNPAY / chưa rõ kết quả | Phiên thanh toán, chưa phát hành hóa đơn | Giữ giỏ; tồn không đổi |
| VNPAY xác minh thành công | Một hóa đơn; đã thanh toán | Xóa phần giỏ tương ứng; tồn chưa trừ |
| Chuyển đang giao | Giữ đúng trạng thái thanh toán | Trừ kho đúng một lần |
| Nhận hàng | Đã nhận; COD chuyển đã thanh toán | Không trừ kho thêm |
| Duyệt hủy sau xuất kho | Đã hủy; VNPAY theo dõi hoàn riêng | Hoàn đúng kho đã trừ một lần |
| Hoàn tiền được xác nhận | Thanh toán đã hoàn | Không tác động kho lần nữa |
| Đánh giá hết sản phẩm trong đơn | Lưu đánh giá, ẩn nút đánh giá | Không đổi tiền/kho |

### 9.2. Bảng dữ liệu người kỹ thuật đối chiếu
- giohang, sach: số lượng giỏ và tồn kho trước/sau; kiểm tra theo đúng mã sách.
- checkout_sessions, checkout_payment_attempts: phiên, các lần thử, mã tham chiếu, kết quả xác minh, liên kết hóa đơn.
- hoadon, chitiethoadon: khách, trạng thái giao hàng/thanh toán, tổng tiền, từng sách, stock_deducted và received_at.
- order_cancel_requests, order_refunds: lý do, quyết định, phản hồi, số tiền và kết quả hoàn.
- product_reviews, product_review_images: đúng khách–đơn–sách, số sao, bình luận, ảnh và không trùng.
Chỉ người phụ trách DB thực hiện truy vấn; ưu tiên đọc dữ liệu. Việc chuẩn bị ngày nhận/thiếu tồn cho test biên phải tách dữ liệu và có bản sao lưu, không đưa lệnh xóa DB vào kịch bản chạy thường.

### 9.3. Mẫu báo lỗi
Mã lỗi: UB-… | Test case: TC… | Mức độ: chặn luồng / sai dữ liệu / giao diện.
Môi trường, phiên bản, người test, thời gian: …
Tài khoản test, mã đơn, mã phiên/vnp_TxnRef (nếu có): …
Các bước tái hiện: 1) … 2) … 3) …
Kết quả mong đợi: …
Kết quả thực tế: …
Bằng chứng: ảnh/video, thời gian chờ, mã phản hồi đã che dữ liệu riêng tư; không đính kèm secret hoặc cookie phiên.
Người xử lý / kiểm thử lại / kết quả sau sửa: …

### 9.4. Thứ tự chạy ngày mai
1) Chuẩn bị và TC01–TC06: hoàn tất COD đầu cuối; giữ C1 cho phần đánh giá.
2) TC07–TC12: VNPAY và thanh toán lại; chạy nhánh có độ trễ sớm để có thời gian quan sát.
3) TC13–TC19: dùng đơn riêng cho từng nhánh hủy/hoàn/thiếu tồn.
4) TC20–TC24: đánh giá và các điều kiện biên.
5) TC25–TC27: kỹ thuật, phân quyền và giao diện; có thể phân công người khác chạy trên dữ liệu riêng.
Ưu tiên hoàn thành P0 trước. Ca chờ Sandbox và ca cần fixture được ghi BLOCKED nếu chưa thể kiểm chứng, không bỏ qua mà ghi PASS. Kết thúc buổi test cần tổng hợp số PASS/FAIL/BLOCKED và danh sách lỗi chưa xử lý, đặc biệt các lỗi thu tiền, hóa đơn, giỏ và tồn kho.

Tài liệu này dựa trên luồng hiện được triển khai trong UniBook. Khi thay đổi nghiệp vụ hoặc mã nguồn, người điều phối cập nhật kết quả mong đợi trước khi chạy lại.
