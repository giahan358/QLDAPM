<?php
include_once '../../model/connectDB.php';
include_once '../../model/taikhoan.php';
include_once '../../model/nhanvien.php';

$idnhanvien = isset($_GET['idnhanvien']) ? $_GET['idnhanvien']: null;
if($idnhanvien){
    $result = delTaiKhoanByID($idnhanvien);
    if($result){
        delNhanVienById($idnhanvien);
        header("Location: index.php?pg=taikhoan");

    }else{
        echo "Lỗi cập nhật tài khoản";
    }
}else{
    echo "Không tìm thấy tài khoản";
}

?>