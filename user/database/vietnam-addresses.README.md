Danh mục địa chỉ giao hàng được tải ngày 2026-09-13 từ
https://provinces.open-api.vn/api/v2/?depth=2 (Vietnam Provinces Open API).
Snapshot gồm 34 tỉnh/thành và 3.321 phường/xã/đặc khu, cấu trúc hai cấp sau
sáp nhập 2025. Không phải dịch vụ của cơ quan nhà nước.

Website phục vụ snapshot nội bộ qua address-catalog.php; không gọi API ngoài
mỗi lần khách mở form. Khi cập nhật dữ liệu, lưu nguyên byte UTF-8 (không để HTTP
client tự giải mã bằng Latin-1), kiểm tra mã không trùng và danh sách phường/xã
trực thuộc mỗi tỉnh. Địa chỉ cũ được giữ nguyên khi sửa thông tin
người nhận; chỉ dùng danh mục hiện hành khi khách chọn lại địa giới.
