<?php
include_once '../../model/connectDB.php';
include_once '../../model/nhaxuatban.php';

$idnhaxuatban = isset($_GET['idnhaxuatban'])? $_GET['idnhaxuatban']:null;
if($idnhaxuatban){
    $result = delDataNhaXuatBanById($idnhaxuatban);
    if($result){
        header("Location: index.php?pg=sanpham&tabId=tab4");
    }else{
        echo "Error update table ";
    }

}else{
    echo "Not found idnhaxuatban";
}
?>
