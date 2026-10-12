<?php
include_once '../../model/connectDB.php';
include_once '../../model/danhmuc.php';

$iddanhmuc = isset($_GET['iddanhmuc']) ? $_GET['iddanhmuc'] : (isset($_POST['iddanhmuc']) ? $_POST['iddanhmuc']: null);


if($iddanhmuc){
    $category = getDataDanhMucTheoId($iddanhmuc);

    if($_SERVER['REQUEST_METHOD'] == 'POST'){
        $iddanhmuc = $_POST['iddanhmuc'];
        $trangthai = $_POST['trangthai'];
        if(updateDanhMucById($iddanhmuc, $trangthai)){
            echo "
            <script>
                alert('Cập nhật danh mục thành công');
                window.location.href='../controller/index.php?pg=danhmuc';
            
            </script>";
        }else{
            echo "<script>alert('Cập nhật danh mục thất bại');</script>";
        }
    }
    
} else {
    echo "Không tìm thấy thông tin danh mục!";
}
?>



        <div class="container">
            <form action="../controller/index.php?pg=suadanhmuc" method="post">
                <h2>Sửa Danh mục</h2>
                <button type="button" class="close-button" onclick="location.href='../controller/index.php?pg=danhmuc'">X</button>
                <label for="categoryId"><b>ID</b></label>
                <input type="text" name="iddanhmuc" value="<?=$category['iddanhmuc']?>" readonly>
    
                <label for="categoryName"><b>Tên Danh mục</b></label>
                <input type="text" name="tendanhmuc" value="<?=$category['tendanhmuc']?>" readonly>

                <label for="status"><b>Trạng thái</b></label>
                <select name="trangthai" >
                    <option value="1" <?=$category['trangthai'] ==1 ? 'selected' : ''?>>Hoạt động</option>
                    <option value="0" <?=$category['trangthai'] ==0 ? 'selected' : ''?>>Tạm khóa</option>
                </select>
    
                <button type="submit" class="btn">Cập nhật</button>
            </form>
        </div>
    </div>

    <link rel="stylesheet" href="../view/layout/css/form.css">
</body>

</html>
