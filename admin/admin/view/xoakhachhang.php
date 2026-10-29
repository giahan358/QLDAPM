<?php
include_once '../../model/connectDB.php';
include_once '../../model/taikhoankhachhang.php';
include_once '../../model/khachhang.php';

$idkhachhang = isset($_GET['idkhachhang']) ? $_GET['idkhachhang'] : null;

if($idkhachhang){
    $result = delDataKhachHangById($idkhachhang);
    if($result){
        delDataTaiKhoanKhachHangById($idkhachhang);
        header("Location: index.php?pg=khachhang&tabId=tab7");
    } else {
        echo "Lỗi xóa khách hàng";
    }
} else {
    echo "Không tìm thấy id khách hàng";
}
exit();
?>
