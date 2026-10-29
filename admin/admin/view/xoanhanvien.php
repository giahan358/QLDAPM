<?php
include_once '../../model/connectDB.php';
include_once '../../model/taikhoan.php';
include_once '../../model/nhanvien.php';

$idnhanvien = isset($_GET['idnhanvien'])? $_GET['idnhanvien']:null;

if($idnhanvien){
    $result = delNhanVienById($idnhanvien);
    if($result){
        delTaiKhoanByID($idnhanvien);
        header("Location: index.php?pg=nhanvien");
    }else{
        echo "Lỗi xóa nhân viên";
    }
}else{
    echo "Không tìm thấy mã nhân viên";
}
exit();
?>
