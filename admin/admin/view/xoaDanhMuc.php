<?php
include_once '../../model/connectDB.php';
include_once '../../model/danhmuc.php';

$iddanhmuc = isset($_GET['iddanhmuc'])? $_GET['iddanhmuc']:null;
if($iddanhmuc){
    $result = delDanhMucById($iddanhmuc);
    if($result){
        header("Location: index.php?pg=danhmuc");
    }else{
        echo "Error update table of category";
    }

}else{
    echo "Not found iddanhmuc";
}
?>