<?php
function getAllSanPhamTrongGioHangID($idKhachHang) {
    $conn = connectdb();
    try {
        $sql = "SELECT * FROM giohang WHERE idkhachhang = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$idKhachHang]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Lỗi khi lấy giỏ hàng: " . $e->getMessage());
        return [];
    }
}

function countSanPhamTrongGioHang($idKhachHang) {
    $sanPhamList = getAllSanPhamTrongGioHangID($idKhachHang);
    try {
        return count($sanPhamList);
    } catch (Exception $e) {
        error_log("Lỗi khi đếm số lượng sản phẩm: " . $e->getMessage());
        return 0;
    }
}

function addSanPhamVaoGioHang($idKhachHang, $idSach, $soLuong) {
    $conn = connectdb();
    try {
        $sqlCheck = "SELECT idgiohang, soluong FROM giohang WHERE idkhachhang = ? AND idsach = ? AND trangthai = 1";
        $stmtCheck = $conn->prepare($sqlCheck);
        $stmtCheck->execute([$idKhachHang, $idSach]);
        $existingItem = $stmtCheck->fetch(PDO::FETCH_ASSOC);

        error_log("addSanPhamVaoGioHang: idKhachHang=$idKhachHang, idSach=$idSach, soLuong=$soLuong, existingItem=" . json_encode($existingItem));

        if ($existingItem) {
            // Nếu sản phẩm đã có, cập nhật số lượng
            $newSoLuong = $existingItem['soluong'] + $soLuong;
            $sqlUpdate = "UPDATE giohang SET soluong = ? WHERE idgiohang = ?";
            $stmtUpdate = $conn->prepare($sqlUpdate);
            $success = $stmtUpdate->execute([$newSoLuong, $existingItem['idgiohang']]);
            error_log("Update giohang: idgiohang={$existingItem['idgiohang']}, newSoLuong=$newSoLuong, success=" . ($success ? 'true' : 'false'));
            return $success;
        } else {
            // Nếu sản phẩm chưa có, thêm mới
            $sqlInsert = "INSERT INTO giohang (idkhachhang, idsach, soluong, trangthai) VALUES (?, ?, ?, 1)";
            $stmtInsert = $conn->prepare($sqlInsert);
            $success = $stmtInsert->execute([$idKhachHang, $idSach, $soLuong]);
            error_log("Insert giohang: idKhachHang=$idKhachHang, idSach=$idSach, soLuong=$soLuong, success=" . ($success ? 'true' : 'false'));
            return $success;
        }
    } catch (PDOException $e) {
        error_log("Lỗi khi thêm sản phẩm vào giỏ hàng: " . $e->getMessage());
        return false;
    }
}
function deleteSanPhamKhoiGioHang($idGioHang) {
    $conn = connectdb();
    try {
        $sql = "DELETE FROM giohang WHERE idgiohang = ?";
        $stmt = $conn->prepare($sql);
        return $stmt->execute([$idGioHang]);
    } catch (PDOException $e) {
        error_log("Lỗi khi xóa sản phẩm khỏi giỏ hàng: " . $e->getMessage());
        return false;
    }
}

function updateSoLuongSanPham($idGioHang, $soLuongMoi) {
    $conn = connectdb();
    try {
        $sql = "UPDATE giohang SET soluong = ? WHERE idgiohang = ?";
        $stmt = $conn->prepare($sql);
        return $stmt->execute([$soLuongMoi, $idGioHang]);
    } catch (PDOException $e) {
        error_log("Lỗi khi cập nhật số lượng: " . $e->getMessage());
        return false;
    }
}

function checkSoLuongSanPhamTrongGioVoiTonKho($idSach, $soLuongYeuCau) {
    $conn = connectdb();
    try {
        $sql = "SELECT sltonkho FROM sach WHERE idsach = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$idSach]);
        $sanPham = $stmt->fetch(PDO::FETCH_ASSOC);
        return $sanPham && $sanPham['sltonkho'] >= $soLuongYeuCau;
    } catch (PDOException $e) {
        error_log("Lỗi khi kiểm tra số lượng tồn kho: " . $e->getMessage());
        return false;
    }
}

function deleteAllSanPhamKhoiGioHangByIdKhachHang($idKhachHang) {
    $conn = connectdb();
    try {
        $sql = "DELETE FROM giohang WHERE idkhachhang = ?";
        $stmt = $conn->prepare($sql);
        return $stmt->execute([$idKhachHang]);
    } catch (PDOException $e) {
        error_log("Lỗi khi xóa tất cả sản phẩm trong giỏ hàng: " . $e->getMessage());
        return false;
    }
}

?>