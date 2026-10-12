<?php
include_once '../../model/connectDB.php';
include_once '../../model/nhacungcap.php';

$idnhacungcap = isset($_GET['idnhacungcap']) ? $_GET['idnhacungcap'] : null;

if($idnhacungcap){
    $result = delNhaCungCapById($idnhacungcap);
    if($result){
        header("Location: index.php?pg=nhacungcap");
    }else{
        echo "Lỗi xóa nhà cung cấp";
    }
}else{
    echo "Không tìm thấy mã nahf cung cấp";
}
exit();

?>