<?php
// function getDataPhanQuyen($page, $limit){
//     $conn = connectdb();
//     if($conn){
//         //tính trang bắt đầu phân trang
//         $offset = ($page -1)*$limit;
//         //đếm 
//         $sqlCount = "SELECT count(*) AS total FROM nhomquyen";
//         $sttm = $conn->prepare($sqlCount);
//         $sttm->execute();
//         $totalRecords = $sttm->fetch(PDO::FETCH_ASSOC)['total'];
//         $totalPages = ceil($totalRecords/$limit);

//         //load data
//         $sql = "SELECT * FROM nhomquyen LIMIT :limit OFFSET :offset";
//         $sttm = $conn->prepare($sql);
//         $sttm->bindValue(':limit', $limit, PDO::PARAM_INT);
//         $sttm->bindValue(':offset', $offset, PDO::PARAM_INT);
//         $sttm-> execute();
//         $data= $sttm->fetchAll(PDO::FETCH_ASSOC);

//         return[
//             'data'=>$data,
//             'totalPages'=>$totalPages,
//             'currentPage'=>$page
//         ];

//     }
//     return[
//         'data'=>[],
//         'totalPages'=>[],
//         'currentPage'=>1

//     ];

// }

// function delDataPhanQuyenById($idquyen){
//     $conn = connectdb();
//     if($conn){
//         try{
//             $sql = "UPDATE nhomquyen SET trangthai = 0 WHERE idquyen = :idquyen";
//             $sttm = $conn ->prepare($sql);
//             $sttm->bindValue(":idquyen", $idquyen, PDO::PARAM_INT);
//             $sttm->execute();
//             $result = $sttm->rowCount();
//             if($result >0){
//                 return true;
//             }

//         }catch(PDOException $e){
//             error_log("Lỗi khi cập nhật trạng thái nhóm quyền!".$e->getMessage());
//         }
//     }
//     return false;
// }

function getComboboxQuyen() {
    $conn = connectdb();
    if ($conn) {
        try {
            $stmt = $conn->prepare("SELECT Quyen FROM phanquyen");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC); // Trả về danh sách toàn bộ quyền
        } catch (PDOException $e) {
            error_log("Lỗi khi lấy danh sách quyền: " . $e->getMessage());
        }
    }
    return false;
}

function getDataQuyen() {
    $conn = connectdb();
    $sql = "SELECT * FROM phanquyen";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return $result;

    $conn = null;
}

function updateQuyen($quyen, $quyenData) {
    $conn = connectDB();
    $sql = "UPDATE phanquyen SET 
        QLCuaHang = :QLCuaHang, QLSanPham = :QLSanPham, QLDanhMuc = :QLDanhMuc, QLNhanVien = :QLNhanVien, 
        QLKhachHang = :QLKhachHang, QLNhaCungCap = :QLNhaCungCap, QLDonHang = :QLDonHang,
        QLPhieuNhap = :QLPhieuNhap, QLThongke = :QLThongke, QLTaiKhoan = :QLTaiKhoan, QLPhanQuyen = :QLPhanQuyen
        WHERE Quyen = :Quyen";

    // Merging data for query execution
    $stmt = $conn->prepare($sql);
    $stmt->execute(array_merge($quyenData, ['Quyen' => $quyen]));
}

function addQuyen($tenQuyen, $quyen) {
    $conn = connectDB();
    $sql = "INSERT INTO phanquyen 
        (Quyen, QLCuaHang, QLSanPham, QLDanhMuc, QLNhanVien, QLKhachHang, QLNhaCungCap, QLDonHang, QLPhieuNhap, QLThongke, QLTaiKhoan, QLPhanQuyen) 
        VALUES (:Quyen, :QLCuaHang, :QLSanPham, :QLDanhMuc, :QLNhanVien, :QLKhachHang, :QLNhaCungCap, :QLDonHang, :QLPhieuNhap, :QLThongke, :QLTaiKhoan, :QLPhanQuyen)";
    $stmt = $conn->prepare($sql);
    $stmt->execute(array_merge(['Quyen' => $tenQuyen], $quyen));
}

function searchPhanQuyen($keyword) {
    $conn = connectdb();
    $keyword = "%" . $keyword . "%";

    $sql = "SELECT * FROM phanquyen 
            WHERE Quyen LIKE :keyword";
    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':keyword', $keyword);
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return $data;
}

function getQuyenByName($tenQuyen) {
    $conn = connectDB();
    $stmt = $conn->prepare("SELECT * FROM phanquyen WHERE Quyen = :quyen");
    $stmt->execute(['quyen' => $tenQuyen]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$result) {
        return false;
    }

    // Chuyển đổi các giá trị int (0 hoặc 1) thành boolean
    foreach ($result as $key => $value) {
        if (in_array($key, ['QLCuaHang', 'QLSanPham', 'QLDanhMuc', 'QLNhanVien', 'QLKhachHang', 'QLNhaCungCap', 'QLDonHang', 'QLPhieuNhap', 'QLThongke', 'QLTaiKhoan', 'QLPhanQuyen'])) {
            $result[$key] = (bool) $value;
        }
    }

    return $result;
}

function deleteQuyen($tenQuyen) {
    $conn = connectDB();
    $stmt = $conn->prepare("DELETE FROM phanquyen WHERE Quyen = :quyen");
    $stmt->execute(['quyen' => $tenQuyen]);
}
?>
