<?php
function getDataTaiKhoan($page, $limit){
    $conn = connectdb();
    if($conn){
        //trang bắt đầu phân trang
        $offset = ($page -1)*$limit;
        //tinh tong tai khoản
        $sqlCount = "SELECT count(*) AS total from taikhoan_nhanvien";
        $sttm = $conn -> prepare($sqlCount);
        $sttm -> execute();
        $totalRecords = $sttm -> fetch(PDO::FETCH_ASSOC)['total'];
        $totalPages = ceil($totalRecords/$limit);
        //load data
        $sql = "SELECT * FROM taikhoan_nhanvien LIMIT :limit OFFSET :offset";
        $sttm = $conn->prepare($sql);
        $sttm->bindValue(':limit', $limit, PDO::PARAM_INT);
        $sttm->bindValue(':offset', $offset, PDO::PARAM_INT);
        $sttm->execute();
        $data = $sttm->fetchAll(PDO::FETCH_ASSOC);
        return [
            'data'=>$data,
            'currentPage'=>$page,
            'totalPages'=>$totalPages
        ];

    }
    return [
        'data'=>[],
        'currentPage'=>1,
        'totalPages'=>[]
    ];

}

function delTaiKhoanByID($idnhanvien){
    $conn = connectdb();
    if($conn){
        $sql = 'UPDATE taikhoan_nhanvien SET TrangThai = 0 WHERE idnhanvien = :idnhanvien';
        $sttm = $conn->prepare(query: $sql);
        $sttm->bindValue('idnhanvien', $idnhanvien, PDO::PARAM_INT);
        $sttm->execute();
        $result = $sttm ->rowCount();
        if($result > 0){
            return true;
        }

    }
    return false;
}

function themTaiKhoanNV($idNhanVien, $taiKhoan, $matKhau, $quyen) {
    $conn = connectdb(); // Kết nối cơ sở dữ liệu
    if ($conn) {
        try {
            // Chuẩn bị câu lệnh SQL để thêm dữ liệu
            $stmt = $conn->prepare("
                INSERT INTO taikhoan_nhanvien (TaiKhoan, MatKhau, Quyen, TrangThai) 
                VALUES (:taiKhoan, :matKhau, :quyen, 1)
            ");

            // Gán giá trị cho các tham số
            $stmt->bindParam(':taiKhoan', $taiKhoan, PDO::PARAM_STR);
            $stmt->bindParam(':matKhau', $matKhau, PDO::PARAM_STR); // Mã hóa mật khẩu trước khi gọi hàm
            $stmt->bindParam(':quyen', $quyen, PDO::PARAM_STR);

            // Thực thi truy vấn
            if ($stmt->execute()) {
                return true; // Thêm thành công
            }
        } catch (PDOException $e) {
            // Ghi log lỗi (nếu xảy ra)
            error_log("Lỗi khi thêm tài khoản: " . $e->getMessage());
        }
    }
    return false; // Thêm thất bại
}

function getDataTaiKhoanTheoId($idnhanvien){
    $conn = connectdb();
    if($conn){
        try{
            $sql = "SELECT * from taikhoan_nhanvien WHERE idnhanvien = :idnhanvien ";
            $sttm =$conn->prepare($sql);
            $sttm->bindValue(':idnhanvien', $idnhanvien, PDO::PARAM_INT);
            $sttm->execute()>0;
            $success = $sttm->rowCount()>0;
            if($success){
                return $sttm->fetch(PDO::FETCH_ASSOC);
            }else{
                return null;
            }

        }catch(PDOException $e){
            error_log("Không tìm thấy thông tin cụ thể của taikhoan!", $e->getMessage());
        }
    }
    return null;
}

function updateTaiKhoanById($idnhanvien, $username, $password, $trangthai){
    $conn = connectdb();
    if($conn){
        try{
            $sql = "UPDATE taikhoan_nhanvien SET 
            TaiKhoan= :username, MatKhau= :password,
            TrangThai= :trangthai WHERE idnhanvien=:idnhanvien";
            $sttm =$conn->prepare($sql);
            $sttm->bindValue(':username', $username, PDO::PARAM_STR);
            $sttm->bindValue(':password', $password, PDO::PARAM_STR);
            $sttm->bindValue(':trangthai', $trangthai, PDO::PARAM_INT);
            $sttm->bindValue(':idnhanvien', $idnhanvien, PDO::PARAM_INT);
            if ($sttm->execute()) {
                return true; 
            }

        }catch(PDOException $e){
            error_log("Lỗi khi cập nhật tài khoản cho nhân viên: ".$e->getMessage());
            return false;
        }
    }
    return false;
}

function kiemTraTenDangNhapTonTai($taiKhoan) {
    $conn = connectdb();
    $stmt = $conn->prepare("SELECT COUNT(*) FROM taikhoan_nhanvien WHERE TaiKhoan = :taiKhoan");
    $stmt->bindParam(':taiKhoan', $taiKhoan);
    $stmt->execute();
    return $stmt->fetchColumn() > 0; // Trả về true nếu đã tồn tại
}

function kiemTraNhanVienDaCoTaiKhoan($idNhanVien) {
    $conn = connectDB();
    $sql = "SELECT * FROM taikhoan_nhanvien WHERE idnhanvien = :idNhanVien";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':idNhanVien', $idNhanVien);
    $stmt->execute();
    
    return $stmt->fetch(PDO::FETCH_ASSOC) ? true : false;
}

function updateTaiKhoanByIdChoTrangThai($idnhanvien, $trangthai){
    $conn = connectdb();
    if($conn){
        try{
            $sql = "UPDATE taikhoan_nhanvien SET 
            trangthai= :trangthai WHERE idnhanvien=:idnhanvien";
            $sttm =$conn->prepare($sql);
            $sttm->bindValue(':trangthai', $trangthai, PDO::PARAM_INT);
            $sttm->bindValue(':idnhanvien', $idnhanvien, PDO::PARAM_INT);
            $sttm->execute();
            if ($sttm->rowCount()>0) {
                return true; 
            }
        }catch(PDOException $e){
            error_log("Lỗi khi cập nhật tài khoản cho nhân viên: ".$e->getMessage());
            return false;
        }
    }
    return false;
}

function searchTaiKhoanNV($keyword, $page, $limit) {
    $offset = ($page - 1) * $limit;
    $conn = connectdb();
    $keyword = "%" . $keyword . "%";

    // Đếm tổng kết quả khớp
    $countSql = "SELECT COUNT(*) FROM taikhoan_nhanvien WHERE TaiKhoan LIKE :keyword OR Quyen LIKE :keyword
                    OR idnhanvien LIKE :keyword";
    $stmt = $conn->prepare($countSql);
    $stmt->bindValue(':keyword', $keyword);
    $stmt->execute();
    $total = $stmt->fetchColumn();

    $totalPages = ceil($total / $limit);

    $sql = "SELECT * FROM taikhoan_nhanvien WHERE TaiKhoan LIKE :keyword OR Quyen LIKE :keyword
                    OR idnhanvien LIKE :keyword LIMIT :limit OFFSET :offset";
    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':keyword', $keyword);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return [
        'data' => $data,
        'currentPage' => $page,
        'totalPages' => $totalPages
    ];
}
?>
