<?php
include_once '../../model/connectDB.php';
include_once '../../model/taikhoankhachhang.php';
include_once '../../model/khachhang.php';


$idkhachhang = isset($_GET['idkhachhang'])? $_GET['idkhachhang']:null;
if($idkhachhang){
    $result = delDataTaiKhoanKhachHangById($idkhachhang);
    if($result){
        delDataKhachHangById($idkhachhang);
        header("Location: index.php?pg=khachhang&tabId=tab8");
    }else{
        echo "Error update table ";
    }

}else{
    echo "Not found idkhachhang";
}
?>
