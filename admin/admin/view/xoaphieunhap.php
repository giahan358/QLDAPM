<?php
include_once '../../model/connectDB.php';
include_once '../../model/phieunhap.php';

$idphieunhap = isset($_GET['idphieunhap']) ? $_GET['idphieunhap']:null;

if($idphieunhap){
    $result = delPhieuNhapByID($idphieunhap);
    if($result){
        header("Location: index.php?pg=phieunhap");
    }else{
        echo "Lỗi khi cập nhập bảng phiếu nhập ";
    }

}else{
    echo "Không tìm thấy mã phiếu nhập! ";
}
?>