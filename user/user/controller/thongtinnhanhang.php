<?php
require_once "../../model/connectDB.php";
require_once "../../model/thongtinnhanhang.php";
require_once __DIR__ . '/../../model/address_catalog.php';

header('Content-Type: application/json');

class ThongTinNhanHangController {
    public function handleRequest() {
        $action = isset($_GET['action']) ? $_GET['action'] : 'hienThi';
        $idKhachHang = isset($_GET['idKhachHang']) ? $_GET['idKhachHang'] : null;

        switch ($action) {
            case 'hienThi':
                echo $this->hienThiThongTinNhanHang($idKhachHang);
                break;
            case 'setDefault':
                $idDiaChi = isset($_GET['idDiaChi']) ? $_GET['idDiaChi'] : null;
                echo $this->setDefaultAddress($idDiaChi, $idKhachHang);
                break;
            case 'update':
                echo $this->updateAddress();
                break;
            case 'delete':
                $idDiaChi = isset($_GET['idDiaChi']) ? $_GET['idDiaChi'] : null;
                echo $this->deleteAddress($idDiaChi);
                break;
            case 'add':
                echo $this->addAddress();
                break;
            default:
                echo json_encode(['status' => 'error', 'message' => 'Action không hợp lệ']);
        }
    }

    private function hienThiThongTinNhanHang($idKhachHang) {
        if ($idKhachHang === null) {
            return json_encode(['status' => 'error', 'message' => 'idKhachHang is required']);
        }
    
        $thongTinList = getDataByIdKhachHang($idKhachHang);
    
        $result = [
            'status' => 'success',
            'data' => []
        ];
    
        if ($thongTinList && !empty($thongTinList)) {
            foreach ($thongTinList as $item) {
                $result['data'][] = [
                    'iddiachi' => $item['iddiachi'],
                    'idkhachhang' => $item['idkhachhang'],
                    'thanhpho' => $item['thanhpho'],
                    'huyen' => $item['huyen'],
                    'xa' => $item['xa'],
                    'diachi_chitiet' => $item['diachi_chitiet'],
                    'hotenngnhan' => $item['hotenNgNhan'],
                    'sdtngnhan' => $item['sdtNgNhan'],
                    'emailngnhan' => $item['emailNgNhan'],
                    'trangthai' => $item['trangthai']
                ];
            }
        } else {
            $result['status'] = 'error';
            $result['message'] = 'Không tìm thấy thông tin';
        }
    
        return json_encode($result);
    }

    private function setDefaultAddress($idDiaChi, $idKhachHang) {
        if ($idDiaChi === null || $idKhachHang === null) {
            return json_encode(['status' => 'error', 'message' => 'Thiếu thông tin bắt buộc']);
        }

        $thongTinList = getDataByIdKhachHang($idKhachHang);

        if ($thongTinList) {
            foreach ($thongTinList as $item) {
                updateThongTinNhanHangByIdDiaChi($item['iddiachi'], $item['thanhpho'], $item['huyen'], $item['xa'], $item['diachi_chitiet'], $item['hotenNgNhan'], $item['sdtNgNhan'], $item['emailNgNhan'], 0);
            }
            $currentAddress = getDataThongTinNhanHangByID($idDiaChi);
            if ($currentAddress) {
                $result = updateThongTinNhanHangByIdDiaChi($idDiaChi, $currentAddress['thanhpho'], $currentAddress['huyen'], $currentAddress['xa'], $currentAddress['diachi_chitiet'], $currentAddress['hotenNgNhan'], $currentAddress['sdtNgNhan'], $currentAddress['emailNgNhan'], 1);
                if ($result) {
                    return json_encode(['status' => 'success', 'message' => 'Cập nhật mặc định thành công']);
                }
            }
        }
        return json_encode(['status' => 'error', 'message' => 'Cập nhật mặc định thất bại']);
    }

    private function updateAddress() {
        $data = $_POST;
        if (!isset($data['iddiachi']) || !isset($data['hotenNgNhan']) || !isset($data['sdtNgNhan']) || !isset($data['thanhpho']) || !isset($data['huyen']) || !isset($data['xa']) || !isset($data['diachi_chitiet'])) {
            return json_encode(['status' => 'error', 'message' => 'Thiếu thông tin bắt buộc']);
        }

        $iddiachi = $data['iddiachi'];
        $thanhpho = $data['thanhpho'];
        $huyen = $data['huyen'];
        $xa = $data['xa'];
        $diachi_chitiet = $data['diachi_chitiet'];
        $hotenNgNhan = $data['hotenNgNhan'];
        $sdtNgNhan = $data['sdtNgNhan'];
        $emailNgNhan = isset($data['emailNgNhan']) ? $data['emailNgNhan'] : '';

        if (!preg_match('/^0[0-9]{9}$/', $sdtNgNhan)) {
            return json_encode(['status' => 'error', 'message' => 'Số điện thoại không hợp lệ. Phải bắt đầu bằng 0 và có đúng 10 chữ số.']);
        }

        $currentAddress = getDataThongTinNhanHangByID($iddiachi);
        if (!validShippingRegion($data, $currentAddress ?: null)) return json_encode(['status' => 'error', 'message' => 'Vui lòng chọn tỉnh/thành và phường/xã đúng danh mục.']);
        if ($currentAddress) {
            $result = updateThongTinNhanHangByIdDiaChi($iddiachi, $thanhpho, $huyen, $xa, $diachi_chitiet, $hotenNgNhan, $sdtNgNhan, $emailNgNhan, $currentAddress['trangthai']);
            if ($result) {
                return json_encode(['status' => 'success', 'message' => 'Cập nhật địa chỉ thành công']);
            }
        }
        return json_encode(['status' => 'error', 'message' => 'Cập nhật địa chỉ thất bại']);
    }

    private function deleteAddress($idDiaChi) {
        if ($idDiaChi === null) {
            return json_encode(['status' => 'error', 'message' => 'Thiếu idDiaChi']);
        }

        $result = deleteThongTinNhanHangByIdDiaChi($idDiaChi);
        if ($result) {
            return json_encode(['status' => 'success', 'message' => 'Xóa địa chỉ thành công']);
        }
        return json_encode(['status' => 'error', 'message' => 'Xóa địa chỉ thất bại']);
    }

    private function addAddress() {
        $data = $_POST;
        if (!isset($data['idkhachhang']) || !isset($data['hotenNgNhan']) || !isset($data['sdtNgNhan']) || !isset($data['thanhpho']) || !isset($data['huyen']) || !isset($data['xa']) || !isset($data['diachi_chitiet'])) {
            return json_encode(['status' => 'error', 'message' => 'Thiếu thông tin bắt buộc']);
        }
    
        $idkhachhang = $data['idkhachhang'];
        $thanhpho = $data['thanhpho'];
        $huyen = $data['huyen'];
        $xa = $data['xa'];
        $diachi_chitiet = $data['diachi_chitiet'];
        $hotenNgNhan = $data['hotenNgNhan'];
        $sdtNgNhan = $data['sdtNgNhan'];
        $emailNgNhan = isset($data['emailNgNhan']) ? $data['emailNgNhan'] : '';
    
        // Kiểm tra số điện thoại
        if (!preg_match('/^0[0-9]{9}$/', $sdtNgNhan)) {
            return json_encode(['status' => 'error', 'message' => 'Số điện thoại không hợp lệ. Phải bắt đầu bằng 0 và có đúng 10 chữ số.']);
        }
    
        if (!validShippingRegion($data)) return json_encode(['status' => 'error', 'message' => 'Vui lòng chọn tỉnh/thành và phường/xã đúng danh mục.']);
        $result = addThongTinNhanHang($idkhachhang, $thanhpho, $huyen, $xa, $diachi_chitiet, $hotenNgNhan, $sdtNgNhan, $emailNgNhan);
        if ($result) {
            return json_encode(['status' => 'success', 'message' => 'Thêm địa chỉ thành công']);
        }
        return json_encode(['status' => 'error', 'message' => 'Thêm địa chỉ thất bại']);
    }
}

// Khởi tạo và xử lý
$controller = new ThongTinNhanHangController();
$controller->handleRequest();
?>
