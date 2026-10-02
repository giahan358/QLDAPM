# UNIBOOK
# MÔ TẢ LUỒNG MUA HÀNG VÀ XỬ LÝ ĐƠN HÀNG
Từ giỏ hàng đến thanh toán, giao nhận, hủy – hoàn tiền và đánh giá sản phẩm

Phạm vi: hệ thống UniBook hiện tại, gồm thanh toán khi nhận hàng (COD) và VNPAY Sandbox.
Ngày lập: 14/09/2026.
Đối tượng sử dụng: người xây dựng hệ thống, người quản trị và người đọc báo cáo nghiệp vụ.

## Nội dung tài liệu
1. Phạm vi và các nguyên tắc chung
2. Thêm sản phẩm vào giỏ và chuẩn bị đặt hàng
3. Luồng thanh toán khi nhận hàng – COD
4. Luồng thanh toán trực tuyến – VNPAY
5. Quản lý giao hàng, tồn kho và đồng bộ trạng thái
6. Hủy đơn và xử lý hoàn tiền
7. Nhận hàng và đánh giá sản phẩm
8. Bảng trạng thái và so sánh hai phương thức thanh toán
9. Ví dụ minh họa và các trường hợp ngoại lệ
10. Các bảng dữ liệu và thành phần xử lý chính

Tài liệu mô tả hành vi đang được triển khai, không mặc định rằng những chức năng dự kiến trong các yêu cầu ban đầu đều đã tồn tại. “Hóa đơn” trong tài liệu là bản ghi đơn bán hàng tại bảng hoadon của UniBook, không đồng nghĩa với hóa đơn điện tử thuế.

---PAGE---
## 1. Phạm vi và các nguyên tắc chung
### 1.1. Các bên tham gia
- Khách hàng: lựa chọn sách, quản lý giỏ, cung cấp địa chỉ, đặt hàng, thanh toán, theo dõi đơn, yêu cầu hủy và đánh giá sản phẩm đã nhận.
- UniBook: kiểm tra dữ liệu, tính tiền, lưu đơn và giao dịch, xác minh thanh toán, cập nhật giỏ, tồn kho và quyền đánh giá.
- Quản trị viên/nhân viên: xem đơn, chuyển trạng thái giao hàng, xét yêu cầu hủy, theo dõi hoàn tiền và xem đánh giá liên quan đến hóa đơn.
- VNPAY và ngân hàng: tiếp nhận thanh toán trực tuyến, cung cấp kết quả giao dịch và xử lý yêu cầu hoàn tiền trong môi trường Sandbox.
- Bộ phận giao hàng/chăm sóc khách hàng: thực hiện giao nhận và hỗ trợ các tình huống sau khi khách đã nhận hàng. Hệ thống hiện không tự tích hợp trạng thái từ một hãng vận chuyển.

### 1.2. Những nguyên tắc quan trọng
1) Giỏ hàng chưa phải là hóa đơn. Thêm sách vào giỏ không đồng nghĩa với thanh toán thành công hoặc xuất kho.
2) Giá và tổng tiền được kiểm tra từ dữ liệu phía máy chủ khi tạo đơn; không tin số tiền do trình duyệt tự gửi lên.
3) COD tạo hóa đơn ngay khi đặt hàng hợp lệ. VNPAY chỉ tạo hóa đơn khi máy chủ xác nhận giao dịch thanh toán thành công.
4) Tồn kho vật lý chỉ bị trừ khi đơn chuyển sang “Đang giao”. Không trừ kho ngay lúc thêm vào giỏ, tạo đơn hay trả tiền qua VNPAY.
5) “Đã hủy đơn” không có nghĩa là “Đã hoàn tiền”. Hoàn tiền phải có kết quả xác minh riêng.
6) Khách chỉ được đánh giá sản phẩm đã mua trong đơn đã nhận, trong thời hạn 10 ngày kể từ thời điểm nhận hàng được lưu.

### 1.3. Sơ đồ tổng quát
Khách chọn sách → Thêm vào giỏ → Kiểm tra số lượng và địa chỉ → Chọn phương thức thanh toán.
Nhánh COD: Đặt hàng hợp lệ → Tạo hóa đơn chờ xác nhận, chưa thanh toán → Giao hàng và trừ kho → Nhận hàng, ghi nhận đã thanh toán → Đánh giá.
Nhánh VNPAY: Tạo đơn thanh toán và mã giao dịch → Chuyển sang VNPAY → Xác minh kết quả → Thành công thì tạo hóa đơn đã thanh toán → Giao hàng và trừ kho → Nhận hàng → Đánh giá.
Nhánh ngoại lệ: Không thanh toán/thất bại → Giữ sản phẩm trong giỏ, chưa phát hành hóa đơn → Theo dõi xác nhận hoặc thanh toán lại khi đủ điều kiện.
Nhánh hủy: Đang chờ thì hủy trực tiếp; đang giao thì chờ admin xét duyệt; đã nhận thì liên hệ chăm sóc khách hàng.

## 2. Thêm sản phẩm vào giỏ và chuẩn bị đặt hàng
### 2.1. Chọn sản phẩm
Khách truy cập danh sách sách hoặc trang chi tiết, xem tên sách, giá, mô tả và thông tin liên quan. Khách có thể xem các đánh giá đã được gửi trước đó để tham khảo trước khi mua.
Khách chọn số lượng rồi bấm thêm vào giỏ. Giỏ lưu sản phẩm và số lượng gắn với khách hàng. Khách có thể tăng, giảm số lượng hoặc xóa sản phẩm. Tại bước đặt hàng, hệ thống yêu cầu đăng nhập để xác định chủ sở hữu đơn và địa chỉ nhận hàng.

### 2.2. Kiểm tra giỏ hàng
Trước khi tạo đơn, máy chủ kiểm tra giỏ không rỗng, số lượng hợp lệ, sách còn được kinh doanh, giá bán hợp lệ và lượng đặt không vượt tồn kho tại thời điểm kiểm tra. Nếu không đạt, việc đặt hàng bị từ chối và không tạo hóa đơn dở dang.
Trong phiên bản hiện tại, nội dung đơn được lấy từ giỏ hàng của khách tại thời điểm đặt. Hệ thống lưu bản chụp các sản phẩm, số lượng và giá để sử dụng cho phiên thanh toán đó.
Kiểm tra tồn kho ở đây chưa phải là giữ chỗ hoặc trừ kho. Vì vậy, khi chuẩn bị giao, hệ thống vẫn phải kiểm tra lại lượng tồn thực tế.

### 2.3. Địa chỉ và phương thức thanh toán
Khách chọn địa chỉ đã lưu hoặc tạo địa chỉ mới với thông tin người nhận, số điện thoại và địa chỉ giao hàng. Máy chủ kiểm tra địa chỉ được chọn thuộc tài khoản đang đăng nhập.
Khách kiểm tra tổng tiền và chọn một trong hai phương thức: COD hoặc VNPAY. Phương thức chuyển khoản trực tuyến thủ công trước đây không còn là lựa chọn đặt hàng mới.
Yêu cầu đặt hàng có mã chống gửi trùng và được kiểm tra phiên đăng nhập/mã CSRF. Việc bấm nút nhiều lần không được phép tạo nhiều hóa đơn cho cùng một yêu cầu.

---PAGE---
## 3. Luồng thanh toán khi nhận hàng – COD
### 3.1. Đặt hàng
1) Khách chọn “Thanh toán khi nhận hàng” và xác nhận đặt hàng.
2) Máy chủ kiểm tra tài khoản, địa chỉ, giỏ hàng, số lượng, giá và tồn kho.
3) Hệ thống tạo bản ghi hoadon và các dòng chitiethoadon trong cùng một giao dịch database.
4) Đơn có trạng thái giao hàng “Chờ xác nhận” (trangthai = 0), thanh toán PENDING – chưa thanh toán, và chưa trừ kho.
5) Hệ thống loại bỏ phần sản phẩm đã đặt khỏi giỏ. Nếu phát sinh lỗi trong quá trình lưu đơn, các thay đổi trong giao dịch database được hoàn tác.
6) Khách thấy hộp thông báo đặt hàng thành công, có mã hóa đơn, thông tin người nhận, sản phẩm và tổng tiền. Thông báo này xác nhận đã đặt hàng, không khẳng định đã trả tiền.

### 3.2. Xử lý đơn ở admin
Hóa đơn xuất hiện trong bảng đơn hàng chung. Nhân viên kiểm tra nội dung đơn và địa chỉ nhận hàng. Khi quyết định giao, nhân viên chuyển từ “Chờ xác nhận” sang “Đang giao”.
Hệ thống kiểm tra tồn kho và trừ số lượng của từng sách đúng một lần. Nếu một sản phẩm không đủ tồn, toàn bộ thao tác chuyển sang giao hàng được hoàn tác; đơn không được chuyển một phần và kho không bị trừ dở dang.
Trong khi đang giao, COD vẫn được ghi nhận là chưa thanh toán vì chưa có xác nhận khách đã nhận và trả tiền.

### 3.3. Nhận hàng và ghi nhận thanh toán
Khách nhận hàng, trả tiền cho bên giao hàng rồi bấm “Đã nhận hàng”. Nhân viên có quyền cũng có thể cập nhật đơn sang trạng thái đã nhận.
Hệ thống chuyển trangthai sang 2, lưu received_at và chuyển trạng thái thanh toán COD từ PENDING sang PAID. Giao diện thể hiện “Đã nhận hàng” và “Đã thanh toán”.
Nhận hàng không trừ kho lần nữa vì kho đã được trừ ở bước đang giao. Xác nhận lặp lại không thay đổi mốc received_at, tránh làm kéo dài thời hạn đánh giá.
Đây là quy ước nghiệp vụ hiện tại: xác nhận nhận hàng đồng thời được hiểu là COD đã thu tiền. Hệ thống chưa có một bước đối soát tiền thu hộ riêng từ đơn vị vận chuyển.

### 3.4. Hủy đơn COD
- Đơn chờ xác nhận: khách nhập lý do và xác nhận hủy; đơn bị hủy ngay, không cần hoàn tiền và không trừ kho.
- Đơn đang giao: khách gửi yêu cầu hủy; admin phải duyệt hoặc từ chối. Nếu duyệt, đơn bị hủy và lượng kho đã trừ được cộng lại một lần.
- Đơn đã nhận: không còn nút yêu cầu hủy trực tuyến. Khách cần liên hệ chăm sóc khách hàng nếu cần hỗ trợ đổi trả hoặc có vấn đề phát sinh.
- Đơn COD đã hủy trước khi thu tiền được hiển thị “Đã hủy — không thu tiền”; không được đánh dấu PAID chỉ để làm mất dòng “Chưa thanh toán”.

## 4. Luồng thanh toán trực tuyến – VNPAY
### 4.1. Khởi tạo giao dịch
1) Khách chọn VNPAY rồi xác nhận đặt hàng.
2) Máy chủ kiểm tra dữ liệu như luồng COD, sau đó lưu phiên đặt hàng tại checkout_sessions.
3) Hệ thống lưu lần thanh toán tại checkout_payment_attempts. Mỗi lần thanh toán mới có vnp_TxnRef riêng và số thứ tự lần thử.
4) Tổng tiền gửi VNPAY bằng số tiền tính theo đồng Việt Nam nhân 100. Các mốc thời gian dùng GMT+7; URL hiện được tạo với thời hạn thanh toán 15 phút.
5) Máy chủ sắp xếp và ký tham số bằng HMACSHA512 với cấu hình phía máy chủ, rồi trả URL để trình duyệt chuyển sang VNPAY Sandbox.
6) Ở thời điểm này chưa tạo hoadon/chitiethoadon, chưa xóa giỏ và chưa trừ tồn kho.

### 4.2. Ý nghĩa mã TT và hóa đơn
Admin có thể thấy dòng đơn thanh toán mang mã TT-… trong cùng bảng đơn hàng. Đây là phiên đặt hàng chưa phát hành hóa đơn, không phải đơn bán hàng đã thanh toán thành công.
Dòng này có thể hiển thị VNPAY, chưa thanh toán và “Chưa phát hành hóa đơn”. Khi thanh toán được xác nhận thành công, hệ thống liên kết phiên đặt hàng với hóa đơn thật; bảng chung hiển thị hóa đơn mà không tạo thêm dòng trùng cho cùng phiên đã hoàn tất.

### 4.3. Thao tác tại VNPAY
Khách thực hiện thanh toán theo hướng dẫn của VNPAY, nhập thông tin phương thức thanh toán và xác thực giao dịch khi được yêu cầu. Khách cũng có thể bấm hủy, đóng trình duyệt, để hết thời gian hoặc gặp lỗi xác thực/số dư.
Môi trường hiện tại là Sandbox, phục vụ thử nghiệm tích hợp. Tài liệu không khẳng định đây là giao dịch thu tiền thực tế ở môi trường production.

### 4.4. Xác nhận kết quả: Return, IPN và đối soát
Return URL là trang khách quay về sau VNPAY. Hệ thống kiểm tra chữ ký để hiển thị kết quả, nhưng bản thân endpoint Return không trực tiếp cập nhật đơn, giỏ hay kho.
IPN là thông báo máy chủ VNPAY gửi tới máy chủ UniBook. Trước khi cập nhật, UniBook kiểm tra chữ ký, mã đơn vị kết nối, mã giao dịch và số tiền so với database. Điều kiện thanh toán thành công là vnp_ResponseCode = 00 và vnp_TransactionStatus = 00.
Nếu IPN chưa đến hoặc bị gián đoạn, trang kết quả gọi một endpoint POST đối soát riêng. Endpoint này chỉ dùng thông tin Return đã ký để nhận diện giao dịch; sau đó máy chủ gọi API truy vấn VNPAY và xác minh phản hồi mới. Không sử dụng kết quả do trình duyệt tự khai báo để đánh dấu đã thanh toán.
Truy vấn thành công không đồng nghĩa với thanh toán thành công: hệ thống còn kiểm tra loại giao dịch, trạng thái giao dịch, chữ ký và số tiền. Trạng thái chưa rõ phải tiếp tục chờ hoặc đối soát, không tự đoán là thành công/thất bại.

### 4.5. Khi xác nhận thanh toán thành công
Hệ thống tạo hoadon và chitiethoadon, đặt thanh toán thành PAID và giao hàng ở “Chờ xác nhận”. Phiên đặt hàng được gắn invoice_id tương ứng.
Các số lượng đã mua được trừ khỏi giỏ; phần sản phẩm hoặc số lượng khách thêm mới trong lúc đang thanh toán được giữ lại theo các dòng giỏ đã lưu trong phiên. Tồn kho vật lý chưa bị trừ.
Việc tạo hóa đơn, lưu chi tiết, cập nhật thanh toán và giỏ được thực hiện theo giao dịch database để tránh thiếu dữ liệu giữa chừng. Thông báo IPN hoặc kết quả đối soát lặp lại không tạo thêm hóa đơn hay xóa giỏ thêm lần nữa.
Nếu không đủ điều kiện nghiệp vụ để phát hành hóa đơn dù đã có kết quả trả tiền, giao dịch có thể chuyển sang REVIEW để kiểm tra; không giả vờ báo khách chưa trả tiền và không tự yêu cầu trả tiền lần nữa.

### 4.6. Hủy thanh toán, thất bại và thanh toán lại
Hủy tại VNPAY là hủy một lần thanh toán. Khi chưa có hóa đơn, đây không phải thao tác hủy hóa đơn đã phát hành. Sản phẩm vẫn ở giỏ và chưa bị trừ kho.
Nếu máy chủ đã xác nhận lần thanh toán thất bại, phiên chuyển FAILED. Khách có thể bấm “Thanh toán lại” trong lịch sử mua hàng. Hệ thống kiểm tra quyền sở hữu, tồn kho, trạng thái phiên và giới hạn số lần thử.
Thanh toán lại giữ nguyên phiên đặt hàng, sản phẩm và số tiền đã lưu, đồng thời tạo mã vnp_TxnRef mới. Mỗi phiên được thử lần đầu và tối đa 5 lần thanh toán lại, tổng cộng tối đa 6 lần được ghi nhận.
Nếu phiên vẫn PENDING và URL chưa hết hạn, thao tác tiếp tục thanh toán có thể đưa khách trở lại URL hiện có; đó là tiếp tục lần đang chờ, không phải tạo một lần thử mới sau FAILED. Nếu URL đã hết hạn hoặc trạng thái cần kiểm tra, hệ thống yêu cầu xác minh trước khi mở lần thanh toán mới.
Hệ thống hạn chế tạo thêm phiên thanh toán khác khi khách còn giao dịch VNPAY chưa rõ kết quả, nhằm tránh trả tiền trùng. Khách đã thấy bị trừ tiền nên chờ xác nhận, không liên tục tạo giao dịch mới.

### 4.7. Tình huống kết quả về chậm
Ví dụ: khách hủy ở lần vào VNPAY đầu tiên, hệ thống đối soát nhưng chưa nhận được kết quả cuối cùng; sau đó khách tiếp tục thanh toán thành công. Nếu đang trong thời gian giới hạn truy vấn, hóa đơn có thể chưa xuất hiện ngay.
Phiên bản hiện tại có cơ chế kiểm tra lại: trang Return tiếp tục đọc trạng thái và thực hiện đối soát khi đến thời điểm được phép; trang lịch sử mua hàng cũng tự đối soát những phiên PENDING khi trang đang hoạt động. Khoảng chờ truy vấn API được quản lý theo từng giao dịch, thường là 5 phút.
Tự đọc trạng thái mỗi 5 giây không có nghĩa là gọi API VNPAY mỗi 5 giây. Máy chủ giới hạn các truy vấn thực tế và chỉ xác nhận khi có phản hồi hợp lệ.
Cơ chế tự đối soát bổ sung này phụ thuộc vào trang đang mở/hoạt động; không phải một tác vụ nền được bảo đảm chạy mãi khi tất cả trình duyệt đã đóng. IPN là kênh thông báo độc lập, và nhân viên có thể đối soát khi cần.

---PAGE---
## 5. Quản lý giao hàng, tồn kho và đồng bộ trạng thái
### 5.1. Các bước chuyển giao hàng
Chờ xác nhận (0) → Đang giao (1) → Đã nhận hàng (2).
Chờ xác nhận (0) → Đã hủy (3), nếu có yêu cầu hủy hợp lệ.
Đang giao (1) → Đã hủy (3), sau khi admin duyệt yêu cầu hủy hoặc thực hiện thao tác hủy được phép.
Đã nhận và đã hủy là trạng thái kết thúc đối với luồng chuyển trạng thái thông thường. Không cho tùy ý đưa đơn đã nhận quay về đang giao để xử lý lại kho.

### 5.2. Quy tắc kho
| Thời điểm | Thay đổi tồn kho |
|---|---|
| Thêm vào giỏ, tạo đơn COD, mở VNPAY | Không trừ kho |
| VNPAY đã thanh toán | Chưa trừ kho |
| Admin chuyển sang Đang giao | Trừ đúng số lượng của đơn, một lần |
| Khách hoặc admin xác nhận Đã nhận | Không trừ thêm |
| Hủy đơn chưa từng trừ kho | Không cộng kho |
| Duyệt hủy đơn đã trừ kho | Cộng lại đúng lượng đã trừ, một lần |

Cờ stock_deducted giúp phân biệt đơn đã tác động vào kho hay chưa. Những thao tác lặp lại không được cộng/trừ lặp. Khi đang xét hủy, hệ thống chặn tiếp tục chuyển trạng thái nhận hàng cho đến khi yêu cầu được xử lý.
Trong thực tế, duyệt hủy đơn đang giao cần gắn với việc nhân viên xác nhận có thể thu hồi hàng. Hệ thống hiện cộng kho khi duyệt hủy, chưa có bước nhập hàng hoàn độc lập từ hãng vận chuyển.

### 5.3. Đồng bộ giao diện
Trang đơn hàng của khách đọc lại trạng thái khoảng 5 giây một lần khi đang hiển thị. Khi khách quay lại tab hoặc có mạng trở lại, trang kiểm tra ngay. Nếu dữ liệu không đổi, bảng được giữ nguyên để tránh nhấp nháy.
Khi tab bị ẩn hoặc mất mạng, việc đọc định kỳ được tạm dừng. Dữ liệu không bị xóa chỉ vì một lỗi kết nối tạm thời. Nếu phiên đăng nhập hết hạn, trang yêu cầu đăng nhập lại.
Vì cơ chế là đọc định kỳ, cập nhật từ admin sang user không phải tức thì tuyệt đối; thường xuất hiện trong chu kỳ kiểm tra kế tiếp, cộng thêm thời gian mạng và máy chủ.

## 6. Hủy đơn và xử lý hoàn tiền
### 6.1. Đơn đang chờ xác nhận
Khách mở lịch sử đơn hàng, bấm “Hủy đơn”, nhập lý do và xác nhận trong hộp giữa màn hình. Máy chủ kiểm tra khách có sở hữu đơn, đơn còn được hủy và yêu cầu chưa được xử lý trước đó.
Nếu hợp lệ, yêu cầu được chấp nhận ngay, đơn chuyển sang đã hủy. Với COD chưa thu tiền, quá trình kết thúc mà không cần hoàn tiền. Với VNPAY đã trả tiền, hệ thống tạo yêu cầu hoàn toàn bộ số tiền của đơn.

### 6.2. Đơn đang giao cần xét duyệt
1) Khách bấm “Yêu cầu hủy”, nhập lý do. Đơn vẫn là đang giao; yêu cầu hủy mang trạng thái chờ duyệt.
2) Admin thấy chữ đỏ gạch chân “Đang chờ xét duyệt hủy” trong chính dòng đơn hàng, không có bảng hủy riêng.
3) Admin bấm vào chữ đó để mở hộp xem lý do khách gửi, nhập lý do phản hồi và chọn “Duyệt hủy” hoặc “Từ chối hủy”.
4) Nếu duyệt, đơn chuyển đã hủy; kho được hoàn lại nếu trước đó đã trừ; VNPAY đã trả tiền sẽ phát sinh yêu cầu hoàn tiền.
5) Nếu từ chối, đơn vẫn đang giao, kho không thay đổi và không thực hiện hoàn tiền. Lý do quyết định được lưu để khách theo dõi.
Mỗi đơn hiện có một bản ghi yêu cầu hủy. Nếu đã bị từ chối, giao diện không tự mở thêm yêu cầu hủy lần hai; khách có thể liên hệ chăm sóc khách hàng để trao đổi.

### 6.3. Các bước hoàn tiền VNPAY
Hệ thống tìm đúng giao dịch đã thanh toán thành công gắn với hóa đơn, kiểm tra số tiền và tạo order_refunds. Mỗi yêu cầu có mã riêng; không dùng một sự kiện nhấp nút để hoàn tiền nhiều lần.
Yêu cầu bắt đầu ở QUEUED. Trước khi gửi mạng, hệ thống chuyển sang SENDING và lưu nội dung yêu cầu. Sau đó máy chủ gọi API refund với loại hoàn toàn phần 02.
Phản hồi phải được xác minh chữ ký, mã giao dịch, loại hoàn tiền và số tiền. Khi có kết quả thành công hợp lệ, order_refunds chuyển SUCCEEDED và trạng thái thanh toán hóa đơn chuyển REFUNDED. Trạng thái giao hàng vẫn là đã hủy.
Nếu VNPAY/ngân hàng đang xử lý, yêu cầu ở PROCESSING. Nếu kết quả chưa rõ do lỗi mạng hoặc hết thời gian chờ, giữ UNKNOWN để đối soát; không tự gửi một lệnh hoàn tiền mới vì lệnh trước có thể đã được tiếp nhận.
Admin bấm “Kiểm tra hoàn tiền” sẽ thấy hộp thông báo giữa màn hình, trước tiên hiện đang kiểm tra rồi hiện thông báo kết quả hoặc lỗi. Trạng thái tiền của đơn được cập nhật trong bảng theo dữ liệu lưu; thông báo thành công của một yêu cầu kiểm tra không tự nó có nghĩa là tiền đã hoàn xong.
Luồng hủy hiện tại triển khai hoàn toàn phần. Không mô tả chức năng hoàn một phần hoặc thời hạn cứng 7 ngày như một tính năng đã áp dụng cho luồng này.

### 6.4. Đơn đã nhận
Hệ thống không hiển thị yêu cầu hủy như một thao tác còn dùng được ở đơn đã nhận. Khách liên hệ chăm sóc khách hàng nếu muốn đổi trả, phản ánh giao thiếu hoặc sản phẩm có vấn đề. Đây là quy trình hỗ trợ riêng, không phải tự động hủy và hoàn tiền từ nút trong hóa đơn.

---PAGE---
## 7. Nhận hàng và đánh giá sản phẩm
### 7.1. Mở quyền đánh giá
Khi đơn được xác nhận đã nhận, hệ thống lưu received_at. Với thao tác xác nhận nhận hàng của khách, giao diện mở hộp đánh giá sản phẩm; khách cũng có thể mở lại từ nút “Đánh giá sản phẩm” trong lịch sử đơn khi còn đủ điều kiện.
Quyền đánh giá tồn tại từ received_at đến trước received_at + 10 ngày. Thời điểm kiểm tra lấy từ máy chủ theo múi giờ Việt Nam, không lấy thời gian tự khai báo từ trình duyệt. Đúng mốc kết thúc 10 ngày thì không còn được gửi đánh giá.

### 7.2. Nội dung đánh giá
Khách chọn sản phẩm trong hóa đơn, chọn từ 1 đến 5 sao, nhập bình luận và có thể đính kèm ảnh sản phẩm đã nhận.
Bình luận phải có nội dung và tối đa 2.000 ký tự. Mỗi đánh giá được gửi tối đa 3 ảnh JPG, PNG hoặc WebP; mỗi ảnh không quá 2 MB và không quá 12 megapixel. Máy chủ kiểm tra nội dung ảnh thực tế, không chỉ dựa vào tên đuôi file.

### 7.3. Kiểm tra và lưu
Máy chủ xác nhận người gửi là chủ hóa đơn, hóa đơn đã nhận hàng, vẫn còn thời hạn và sản phẩm thực sự có trong chi tiết đơn. Hệ thống chặn đánh giá trùng cho cùng một sản phẩm trong cùng một hóa đơn.
Đánh giá lưu tại product_reviews, gắn với hóa đơn, khách hàng, sách, số sao, bình luận và thời gian tạo. Các ảnh được lưu với tên do hệ thống tạo và liên kết tại product_review_images.
Một hóa đơn mua nhiều cuốn của cùng một đầu sách vẫn chỉ tạo một đánh giá cho đầu sách đó trong hóa đơn. Nếu khách mua lại trong một hóa đơn khác và nhận hàng, quyền đánh giá được kiểm tra theo hóa đơn mới.

### 7.4. Sau khi gửi đánh giá
- Nếu đơn có một sản phẩm và đã đánh giá, nút “Đánh giá sản phẩm” biến mất, thay bằng “Đã đánh giá”.
- Nếu đơn có nhiều sản phẩm và mới đánh giá một phần, nút còn xuất hiện để khách đánh giá các sản phẩm chưa gửi nhận xét.
- Khi đã đánh giá tất cả sản phẩm, nút biến mất trên hóa đơn đó.
- Nếu hết thời hạn mà còn sản phẩm chưa đánh giá, nút không còn được sử dụng và giao diện thể hiện đã hết hạn đánh giá.
- Việc đánh giá không thay đổi số tiền thanh toán, trạng thái giao hàng hay tồn kho.

### 7.5. Xem đánh giá
Trang chi tiết sách hiển thị điểm trung bình, số lượt, các đánh giá gồm sao, bình luận, thông tin người đánh giá, thời gian và ảnh nếu có. Danh sách được phân trang, không tải vô hạn toàn bộ ảnh cùng lúc.
Trong admin, cột “Đánh giá” ở bảng sản phẩm thể hiện số lượt và điểm trung bình. Bấm vào mở hộp có thể cuộn để xem, tải thêm đánh giá và bấm “Xem hóa đơn #…” để mở đúng hóa đơn tạo ra đánh giá đó. Không có bảng đánh giá riêng trong trang danh sách đơn hàng.

## 8. Bảng trạng thái và so sánh hai phương thức
### 8.1. Các nhóm trạng thái độc lập
| Nhóm | Giá trị chính | Ý nghĩa |
|---|---|---|
| Giao hàng – hoadon.trangthai | 0 / 1 / 2 / 3 | Chờ xác nhận / Đang giao / Đã nhận / Đã hủy |
| Thanh toán hóa đơn | PENDING / PAID / REFUNDED | Chưa trả / Đã trả / Đã hoàn tiền |
| Phiên VNPAY | PENDING / PAID / FAILED / REVIEW | Đang chờ / Thành công / Thất bại / Cần kiểm tra |
| Yêu cầu hủy | PENDING / APPROVED / REJECTED | Chờ duyệt / Đã duyệt / Từ chối |
| Cờ hủy trên hóa đơn | NONE / REQUESTED / APPROVED / REJECTED | Không có / Đang yêu cầu / Đã duyệt / Từ chối |
| Hoàn tiền | QUEUED / SENDING / PROCESSING | Chờ gửi / Đang gửi / Đang xử lý |
| Kết quả hoàn tiền | SUCCEEDED / REJECTED / UNKNOWN / REVIEW | Thành công / Từ chối / Chưa rõ / Cần kiểm tra |

Trạng thái FAILED của một lần thanh toán không có nghĩa hóa đơn đã hủy: lần thử đó có thể chưa từng tạo hóa đơn. Tương tự, PAID có thể đi cùng chờ xác nhận hoặc đang giao đối với VNPAY.

### 8.2. So sánh COD và VNPAY
| Nội dung | COD | VNPAY |
|---|---|---|
| Khi tạo hóa đơn | Sau khi đặt hàng hợp lệ | Sau khi máy chủ xác nhận đã thanh toán |
| Khi xóa phần đã mua khỏi giỏ | Khi tạo hóa đơn COD | Khi xác nhận thanh toán và tạo hóa đơn |
| Khi ghi nhận PAID | Xác nhận đã nhận hàng | Xác minh IPN hoặc truy vấn thành công |
| Khi trừ tồn kho | Chuyển sang Đang giao | Chuyển sang Đang giao |
| Nếu chưa trả tiền | Hóa đơn chờ thu COD | Phiên thanh toán chưa phát hành hóa đơn |
| Nếu hủy trước khi thu tiền | Không thu, không hoàn tiền | Giữ giỏ; chưa có hóa đơn nếu chưa thanh toán |
| Hủy sau khi đã trả VNPAY | Không áp dụng | Tạo và theo dõi hoàn tiền riêng |
| Điều kiện đánh giá | Đã nhận, còn hạn, chưa đánh giá sản phẩm | Giống COD |

---PAGE---
## 9. Ví dụ minh họa và ngoại lệ
### 9.1. Ví dụ COD hoàn tất bình thường
Khách đặt 2 cuốn sách tổng 200.000đ. Hệ thống tạo hóa đơn chờ xác nhận, chưa thu tiền, xóa phần sách đã đặt khỏi giỏ nhưng chưa trừ kho. Admin chuyển sang đang giao: kho giảm 2 cuốn. Khách nhận hàng và trả 200.000đ, xác nhận đã nhận: đơn được ghi PAID, lưu thời điểm nhận, kho không giảm thêm. Khách đánh giá trong 10 ngày; khi đánh giá đủ các đầu sách, nút đánh giá trên đơn biến mất.

### 9.2. Ví dụ VNPAY hủy rồi thanh toán lại
Khách chọn VNPAY cho đơn 110.000đ. Hệ thống tạo TT-… và lần giao dịch đầu, gửi vnp_Amount = 11.000.000. Khách hủy ở cổng: giỏ vẫn giữ nguyên và chưa có hóa đơn. Nếu hệ thống đã xác nhận FAILED, khách thanh toán lại bằng mã giao dịch mới trên cùng phiên đặt hàng.
Khi giao dịch thành công được xác minh, hệ thống mới tạo hóa đơn 110.000đ, thanh toán PAID, chờ xác nhận và xóa số lượng đã mua khỏi giỏ. Nếu kết quả chưa kịp đồng bộ, trang tiếp tục đối soát trong giới hạn cho phép; không yêu cầu khách trả thêm tiền chỉ vì hóa đơn chưa xuất hiện ngay.

### 9.3. Ví dụ đang giao rồi yêu cầu hủy
Kho đã giảm theo đơn. Khách gửi lý do muốn hủy nhưng đơn vẫn đang giao. Admin mở hộp xét duyệt: nếu từ chối thì kho giữ nguyên và giao tiếp; nếu đồng ý thì chuyển đã hủy và cộng kho lại một lần. Nếu đó là VNPAY đã trả tiền, hệ thống tiếp tục quy trình hoàn tiền và chỉ ghi REFUNDED sau khi xác minh hoàn thành.

### 9.4. Các trường hợp cần chú ý
- Bấm đặt hàng hoặc callback trùng: sử dụng mã yêu cầu, mã giao dịch và khóa/giao dịch database để tránh tạo trùng hóa đơn.
- Khách sửa giỏ trong lúc ở VNPAY: hóa đơn dựa trên nội dung phiên đã lưu; không tự đổi số tiền giao dịch đang thực hiện theo giỏ mới.
- Sách hết hàng sau lúc thanh toán: việc thanh toán đã nhận không bị phủ nhận; bước chuyển sang giao kiểm tra tồn kho và có thể bị chặn để nhân viên xử lý.
- Chữ ký sai hoặc số tiền không khớp: không cập nhật thành công vào hóa đơn.
- Chỉ nhận được kết quả truy vấn “thành công”: vẫn phải kiểm tra trạng thái thanh toán; thành công của API và thành công của giao dịch là hai việc khác nhau.
- Hủy và nhận hàng đồng thời: máy chủ kiểm tra trạng thái hiện tại và khóa đơn khi xử lý; không chỉ dựa vào nút đang hiển thị trên màn hình.
- Mất kết nối lúc hoàn tiền: không tự gửi lại lệnh hoàn mà chưa biết kết quả trước, tránh hoàn hai lần.
- Hết 10 ngày hoặc không phải chủ hóa đơn: máy chủ từ chối gửi đánh giá, kể cả khi khách tự gọi API.
- Xóa bản ghi giao dịch/hóa đơn thủ công không xóa lịch sử tại VNPAY và có thể phá liên kết đối soát; quy trình đúng là cập nhật trạng thái và giữ lịch sử.

## 10. Dữ liệu và thành phần xử lý chính
### 10.1. Các bảng dữ liệu
| Bảng | Vai trò trong luồng |
|---|---|
| giohang | Sản phẩm và số lượng khách đang giữ trong giỏ |
| sach | Giá, trạng thái kinh doanh và tồn kho |
| thongtinnhanhang | Địa chỉ nhận hàng của khách |
| checkout_sessions | Nội dung phiên đặt hàng và liên kết hóa đơn nếu đã phát hành |
| checkout_payment_attempts | Các lần thanh toán VNPAY, mã tham chiếu, kết quả và dữ liệu đối soát |
| hoadon / chitiethoadon | Đơn bán hàng, người mua, tổng tiền và các dòng sản phẩm |
| order_cancel_requests | Lý do hủy, trạng thái xét duyệt, nhân viên và phản hồi |
| order_refunds | Yêu cầu hoàn tiền và kết quả xử lý |
| product_reviews / product_review_images | Đánh giá gắn với sản phẩm, hóa đơn và ảnh |

### 10.2. Các file tham chiếu trong mã nguồn
- user/user/controller/giohang.php: thao tác giỏ hàng.
- user/user/view/thanh-toan.php và user/user/controller/thanhtoan.php: giao diện đặt hàng và tiếp nhận yêu cầu COD/VNPAY, thanh toán lại, kiểm tra giao dịch.
- payment/private/StoreCheckout.php: kiểm tra đơn, tạo phiên, tạo hóa đơn, xử lý IPN và cập nhật giỏ.
- payment/private/Gateway.php: tạo chữ ký, xác minh và gọi API VNPAY.
- payment/private/StoreReconciler.php, payment/reconcile-payment.php và payment/return-status.js: đối soát bổ sung và theo dõi kết quả Return.
- payment/private/OrderReceipt.php: chuyển trạng thái giao hàng, trừ/hoàn kho và ghi nhận nhận hàng/COD.
- payment/private/AfterSales.php: yêu cầu hủy, quyết định của admin và hoàn tiền.
- admin/admin/view/after-sales-panel.php và admin/admin/view/layout/js/hoadon.js: hộp xét duyệt hủy, hộp kết quả kiểm tra hoàn tiền và danh sách đơn hàng.
- user/user/view/lichsu-muahang.php và user/user/view/resources/js/order-sync.js: lịch sử đơn và đồng bộ trạng thái định kỳ.
- payment/private/Reviews.php, user/user/controller/after-sales.php: điều kiện và lưu đánh giá.
- admin/admin/controller/product-reviews.php: xem đánh giá sản phẩm và liên kết hóa đơn trong admin.

### 10.3. Giới hạn phạm vi
Tài liệu không mô tả như đã triển khai các tính năng sau: tích hợp hãng vận chuyển tự động, đối soát tiền thu hộ COD riêng, giữ chỗ tồn kho khi mở thanh toán, hoàn tiền từng phần trong luồng hủy, tác vụ đối soát chạy nền liên tục, hoặc quy trình đổi trả tự phục vụ sau nhận hàng.
Việc quản trị hiện chỉ cho truy cập trực tiếp từ máy localhost. Cổng public/ngrok phục vụ khách hàng và các endpoint thanh toán; không dùng để truy cập trang admin.

### 10.4. Nguồn lập tài liệu
Nguồn chính là các file mã nguồn nêu trên tại thời điểm 14/09/2026 và các quy tắc đã được triển khai trong phiên bản hiện tại.
Tài liệu tham khảo giao thức VNPAY:
- Thanh toán PAY: https://sandbox.vnpayment.vn/apis/docs/thanh-toan-pay/pay.html
- Truy vấn/hoàn tiền: https://sandbox.vnpayment.vn/apis/docs/truy-van-hoan-tien/querydr&refund.html
Các nguồn giao thức không thay thế cho việc xác định tính năng thực sự đang bật trong UniBook. Khi thay đổi mã nguồn hoặc chính sách kinh doanh, cần cập nhật lại tài liệu này.
