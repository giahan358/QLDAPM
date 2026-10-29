<?php
function getDataTacGia($page, $limit){
    $conn = connectdb();
    if($conn){
        //tính trang bắt đầu phân trang
        $offset = ($page -1)*$limit;
        //đếm 
        $sqlCount = "SELECT count(*) AS total FROM tacgia";
        $sttm = $conn->prepare($sqlCount);
        $sttm->execute();
        $totalRecords = $sttm->fetch(PDO::FETCH_ASSOC)['total'];
        $totalPages = ceil($totalRecords/$limit);

        //load data
        $sql = "SELECT * FROM tacgia LIMIT :limit OFFSET :offset";
        $sttm = $conn->prepare($sql);
        $sttm->bindValue(':limit', $limit, PDO::PARAM_INT);
        $sttm->bindValue(':offset', $offset, PDO::PARAM_INT);
        $sttm-> execute();
        $data= $sttm->fetchAll(PDO::FETCH_ASSOC);

        return[
            'data'=>$data,
            'totalPages'=>$totalPages,
            'currentPage'=>$page

        ];

    }
    return[
        'data'=>[],
        'totalPages'=>[],
        'currentPage'=>1

    ];

}

function delDataTacGiaById($idtacgia){
    $conn = connectdb();
    if($conn){
        try{
            $sql = "UPDATE tacgia SET trangthai = 0 WHERE idtacgia = :idtacgia";
            $sttm = $conn ->prepare($sql);
            $sttm->bindValue(":idtacgia", $idtacgia, PDO::PARAM_INT);
            $sttm->execute();

            // Tự động chuyển các sách của tác giả này sang tạm ngưng
            $sqlSach = "UPDATE sach SET trangthai = 0 WHERE idtacgia = :idtacgia";
            $stmtSach = $conn->prepare($sqlSach);
            $stmtSach->bindValue(':idtacgia', $idtacgia, PDO::PARAM_INT);
            $stmtSach->execute();

            return true;
        }catch(PDOException $e){
            error_log("Lỗi khi cập nhật trạng thái tác giả!".$e->getMessage());
        }
    }
    return false;
}

function getComboboxTacGia() {
    $conn = connectdb();
    if ($conn) {
        try {
            $stmt = $conn->prepare("SELECT idtacgia, tentacgia FROM tacgia WHERE trangthai = 1");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC); 
        } catch (PDOException $e) {
            error_log("Lỗi khi lấy danh sách tác giả: " . $e->getMessage());
        }
    }
    return false;
}

function themTacGia($tenTacGia, $tieuSu) {
    $conn = connectdb(); // Kết nối cơ sở dữ liệu
    if ($conn) {
        try {
            // Chuẩn bị câu lệnh SQL để thêm dữ liệu
            $stmt = $conn->prepare("
                INSERT INTO tacgia (tentacgia, tieusu, trangthai) 
                VALUES (:tenTacGia, :tieuSu, 1)
            ");

            // Gán giá trị cho các tham số
            $stmt->bindParam(':tenTacGia', $tenTacGia, PDO::PARAM_STR);
            $stmt->bindParam(':tieuSu', $tieuSu, PDO::PARAM_STR);

            // Thực thi truy vấn
            if ($stmt->execute()) {
                return true; // Thêm thành công
            }
        } catch (PDOException $e) {
            // Ghi log lỗi (nếu xảy ra)
            error_log("Lỗi khi thêm tác giả: " . $e->getMessage());
        }
    }
    return false; // Thêm thất bại
}

function getTenTacGiaTheoId($idtacgia){
    $conn = connectdb();
    if($conn){
        try{
            $sql = "SELECT tentacgia FROM tacgia WHERE idtacgia = :idtacgia";
            $sttm = $conn->prepare($sql);
            $sttm->bindValue('idtacgia', $idtacgia, PDO::PARAM_INT);
            $sttm->execute();
            $tentacgia = $sttm->fetch(PDO::FETCH_ASSOC)['tentacgia'];
            return $tentacgia;
            
        }catch(PDOException $e){
            error_log(("Lỗi khi gọi tên tác giả theo id ".$e->getMessage()));
        }
    }
    return null;
}

function getDataTacGiaTheoId($idtacgia){
    $conn = connectdb();
    if($conn){
        try{
            $sql = "SELECT * from tacgia WHERE idtacgia = :idtacgia ";
            $sttm =$conn->prepare($sql);
            $sttm->bindValue(':idtacgia', $idtacgia, PDO::PARAM_INT);
            $sttm->execute()>0;
            $success = $sttm->rowCount()>0;
            if($success){
                return $sttm->fetch(PDO::FETCH_ASSOC);
            }else{
                return null;
            }

        }catch(PDOException $e){
            error_log("Không tìm thấy thông tin cụ thể của tác giả!", $e->getMessage());
        }
    }
    return null;
}

function updateTacgiaById($idtacgia, $trangthai){
    $conn = connectdb();
    if($conn){
        try{
            $sql = "UPDATE tacgia SET trangthai= :trangthai WHERE idtacgia=:idtacgia";
            $sttm =$conn->prepare($sql);
            $sttm->bindValue(':trangthai', $trangthai, PDO::PARAM_INT);
            $sttm->bindValue(':idtacgia', $idtacgia, PDO::PARAM_INT);
            if ($sttm->execute()) {
                if ($trangthai == 0) {
                    // Khóa tác giả -> Tạm ngưng tất cả sách của tác giả này
                    $sqlSach = "UPDATE sach SET trangthai = 0 WHERE idtacgia = :idtacgia";
                    $stmtSach = $conn->prepare($sqlSach);
                    $stmtSach->bindValue(':idtacgia', $idtacgia, PDO::PARAM_INT);
                    $stmtSach->execute();
                } else {
                    // Mở khóa tác giả -> Kích hoạt lại sách nếu Danh mục và NXB của sách đó cũng đang hoạt động (trangthai = 1)
                    $sqlSach = "UPDATE sach s
                                JOIN chitietdanhmuc ctdm ON s.idctdanhmuc = ctdm.idchitietdanhmuc
                                JOIN danhmuc dm ON ctdm.iddanhmuc = dm.iddanhmuc
                                JOIN nhaxuatban nxb ON s.idnhaxuatban = nxb.idnhaxuatban
                                SET s.trangthai = 1
                                WHERE s.idtacgia = :idtacgia 
                                  AND dm.trangthai = 1 
                                  AND nxb.trangthai = 1";
                    $stmtSach = $conn->prepare($sqlSach);
                    $stmtSach->bindValue(':idtacgia', $idtacgia, PDO::PARAM_INT);
                    $stmtSach->execute();
                }
                return true; 
            }

        }catch(PDOException $e){
            error_log("Lỗi khi cập nhật tác giả: ".$e->getMessage());
            return false;
        }
    }
    return false;
}

function checkTrungTenTacGia($tenTacGia) {
    $conn = connectdb(); // Kết nối CSDL
    $sql = "SELECT COUNT(*) FROM tacgia WHERE tentacgia = :tenTacGia";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':tenTacGia', $tenTacGia, PDO::PARAM_STR);
    $stmt->execute();
    return $stmt->fetchColumn() > 0; // true nếu đã tồn tại
}

function searchTacGia($keyword, $page, $limit) {
    $offset = ($page - 1) * $limit;
    $conn = connectdb();
    $keyword = "%" . $keyword . "%";

    // Đếm tổng kết quả khớp
    $countSql = "SELECT COUNT(*) FROM tacgia WHERE tentacgia LIKE :keyword OR idtacgia LIKE :keyword";
    $stmt = $conn->prepare($countSql);
    $stmt->bindValue(':keyword', $keyword);
    $stmt->execute();
    $total = $stmt->fetchColumn();

    $totalPages = ceil($total / $limit);

    $sql = "SELECT * FROM tacgia WHERE tentacgia LIKE :keyword OR idtacgia LIKE :keyword 
            LIMIT :limit OFFSET :offset";
    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':keyword', $keyword);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return [
        'data' => $data,
        'totalPages' => $totalPages,
        'currentPage' => $page 
    ];
}

function getDataChiTietTacGiaTheoId($idtacgia){
    $conn = connectdb();
    if($conn){
        try{
            $sql = "SELECT * FROM tacgia WHERE idtacgia = :idtacgia";
            $sttm = $conn->prepare($sql);
            $sttm->bindValue('idtacgia', $idtacgia, PDO::PARAM_INT);
            $sttm->execute();
            return $sttm->fetchAll(PDO::FETCH_ASSOC);
            
        }catch(PDOException $e){
            error_log(("Lỗi khi gọi tên tác giả theo id ".$e->getMessage()));
        }
    }
    return null;
}
?>
