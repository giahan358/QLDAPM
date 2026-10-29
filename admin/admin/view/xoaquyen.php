<?php
include_once '../../model/connectDB.php';
include_once '../../model/phanquyen.php';

$idquyen = isset($_GET['idquyen']) ? $_GET['idquyen'] : null;

if($idquyen){
    $result = delDataPhanQuyenById($idquyen);
    if($result){;
        header("Location: index.php?pg=phanquyen");
    } else {
        echo "Lỗi xóa nhóm quyền";
    }
} else {
    echo "Không tìm thấy idquyen";
}
exit();
?>