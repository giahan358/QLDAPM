<?php
include_once '../../model/connectDB.php';
include_once '../../model/tacgia.php';

$idtacgia = isset($_GET['idtacgia'])? $_GET['idtacgia']:null;
if($idtacgia){
    $result = delDataTacGiaById($idtacgia);
    if($result){
        header("Location: index.php?pg=sanpham&tabId=tab3");
    }else{
        echo "Error update table ";
    }

}else{
    echo "Not found idtacgia";
}
?>
