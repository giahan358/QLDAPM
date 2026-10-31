<?php
function getDataThongTinNhanHangByID($iddiachi) {
    $conn = connectdb();
    if ($conn) {
        try {
            $sql = "SELECT * FROM thongtinnhanhang WHERE iddiachi = :iddiachi";
            $stmt = $conn->prepare($sql);
            $stmt->bindValue(':iddiachi', $iddiachi, PDO::PARAM_INT);
            $stmt->execute();
            $success = $stmt->rowCount() > 0;
            if ($success) {
                return $stmt->fetch(PDO::FETCH_ASSOC);
            } else {
                return null;
            }
        } catch (PDOException $e) {
            error_log("Không tìm thấy thông tin cụ thể của thông tin nhận hàng: " . $e->getMessage());
            return null;
        }
    }
    return null;
}

function getDataByIdKhachHang($idkhachhang) {
    $conn = connectdb();
    if ($conn) {
        try {
            $sql = "SELECT * FROM thongtinnhanhang WHERE idkhachhang = :idkhachhang";
            $stmt = $conn->prepare($sql);
            $stmt->bindValue(':idkhachhang', $idkhachhang, PDO::PARAM_INT);
            $stmt->execute();
            $thongTinList = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $thongTinList;
        } catch (PDOException $e) {
            error_log("Không tìm thấy thông tin cụ thể của thông tin nhận hàng: " . $e->getMessage());
            return [];
        }
    }
    return [];
}

function addThongTinNhanHang($idkhachhang, $thanhpho, $huyen, $xa, $diachi_chitiet, $hotenNgNhan, $sdtNgNhan, $emailNgNhan) {
    $conn = connectdb();
    if ($conn) {
        try {
            $sql = "INSERT INTO thongtinnhanhang (idkhachhang, thanhpho, huyen, xa, diachi_chitiet, hotenNgNhan, sdtNgNhan, emailNgNhan) 
                    VALUES (:idkhachhang, :thanhpho, :huyen, :xa, :diachi_chitiet, :hotenNgNhan, :sdtNgNhan, :emailNgNhan)";
            $stmt = $conn->prepare($sql);
            $stmt->bindValue(':idkhachhang', $idkhachhang, PDO::PARAM_INT);
            $stmt->bindValue(':thanhpho', $thanhpho, PDO::PARAM_STR);
            $stmt->bindValue(':huyen', $huyen, PDO::PARAM_STR);
            $stmt->bindValue(':xa', $xa, PDO::PARAM_STR);
            $stmt->bindValue(':diachi_chitiet', $diachi_chitiet, PDO::PARAM_STR);
            $stmt->bindValue(':hotenNgNhan', $hotenNgNhan, PDO::PARAM_STR);
            $stmt->bindValue(':sdtNgNhan', $sdtNgNhan, PDO::PARAM_STR);
            $stmt->bindValue(':emailNgNhan', $emailNgNhan, PDO::PARAM_STR);

            $result = $stmt->execute();
            return $result ? $conn->lastInsertId() : false;
        } catch (PDOException $e) {
            error_log("Lỗi khi thêm thông tin nhận hàng: " . $e->getMessage());
            return false;
        }
    }
    return false;
}

function deleteThongTinNhanHangByIdDiaChi($iddiachi) {
    $conn = connectdb();
    if ($conn) {
        try {
            $sql = "DELETE FROM thongtinnhanhang WHERE iddiachi = :iddiachi";
            $stmt = $conn->prepare($sql);
            $stmt->bindValue(':iddiachi', $iddiachi, PDO::PARAM_INT);
            $result = $stmt->execute();
            return $result ? $stmt->rowCount() : false;
        } catch (PDOException $e) {
            error_log("Lỗi khi xóa thông tin nhận hàng: " . $e->getMessage());
            return false;
        }
    }
    return false;
}
function updateThongTinNhanHangByIdDiaChi($iddiachi, $thanhpho, $huyen, $xa, $diachi_chitiet, $hotenNgNhan, $sdtNgNhan, $emailNgNhan, $trangthai) {
    $conn = connectdb();
    if ($conn) {
        try {
            $sql = "UPDATE thongtinnhanhang 
                    SET thanhpho = :thanhpho, huyen = :huyen, xa = :xa, diachi_chitiet = :diachi_chitiet, 
                        hotenNgNhan = :hotenNgNhan, sdtNgNhan = :sdtNgNhan, emailNgNhan = :emailNgNhan, trangthai = :trangthai
                    WHERE iddiachi = :iddiachi";
            $stmt = $conn->prepare($sql);
            $stmt->bindValue(':iddiachi', $iddiachi, PDO::PARAM_INT);
            $stmt->bindValue(':thanhpho', $thanhpho, PDO::PARAM_STR);
            $stmt->bindValue(':huyen', $huyen, PDO::PARAM_STR);
            $stmt->bindValue(':xa', $xa, PDO::PARAM_STR);
            $stmt->bindValue(':diachi_chitiet', $diachi_chitiet, PDO::PARAM_STR);
            $stmt->bindValue(':hotenNgNhan', $hotenNgNhan, PDO::PARAM_STR);
            $stmt->bindValue(':sdtNgNhan', $sdtNgNhan, PDO::PARAM_STR);
            $stmt->bindValue(':emailNgNhan', $emailNgNhan, PDO::PARAM_STR);
            $stmt->bindValue(':trangthai', $trangthai, PDO::PARAM_INT);

            $result = $stmt->execute();
            return $result ? $stmt->rowCount() : false;
        } catch (PDOException $e) {
            error_log("Lỗi khi cập nhật thông tin nhận hàng: " . $e->getMessage());
            return false;
        }
    }
    return false;
}
?>