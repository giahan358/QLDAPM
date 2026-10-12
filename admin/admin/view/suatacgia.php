<?php
include_once '../../model/connectDB.php';
include_once '../../model/tacgia.php';

$idtacgia = isset($_GET['idtacgia']) ? $_GET['idtacgia'] : (isset($_POST['idtacgia']) ? $_POST['idtacgia']: null);


if($idtacgia){
    $tacgia = getDataTacGiaTheoId($idtacgia);
    if($_SERVER['REQUEST_METHOD'] == 'POST'){
        $idtacgia = $_POST['idtacgia'];
        $trangthai = $_POST['trangthai'];
        if(updateTacgiaById($idtacgia, $trangthai)){
            echo "<script>
            alert('Cập nhật thông tin tác giả thành công!');
            window.location.href = '../controller/index.php?pg=sanpham&tabId=tab3';
            </script>";
        }else{
            echo "<script>alert('Lỗi cập nhật tác giả!')</script>";
        }

    }

} else {
    echo "Không tìm thấy tác giả";
}

?>
        <div class="container">
            
            <form action="../controller/index.php?pg=suatacgia" method="post">
                <h2>Sửa Tác giả</h2>
                <button type="button" class="close-button" onclick="location.href='../controller/index.php?pg=sanpham&tabId=tab3'">X</button>
                <label for="authorId"><b>ID</b></label>
                <input type="text" name="idtacgia" value="<?=$tacgia['idtacgia']?>" readonly>
    
                <label for="authorName"><b>Tên tác giả</b></label>
                <input type="text" name="authorName" value="<?=$tacgia['tentacgia']?>" readonly>
    
                <label for="bio"><b>Tiểu sử</b></label>
                <textarea name="bio" readonly><?=$tacgia['tieusu']?></textarea>
    
                <label for="status"><b>Trạng thái</b></label>
                <select name="trangthai" required>
                    <option value="1" <?=$tacgia['trangthai'] ==1 ? 'selected' : ''?>>Hoạt động</option>
                    <option value="0" <?=$tacgia['trangthai'] ==0 ? 'selected' : ''?>>Tạm khóa</option>
                </select>
    
                <button type="submit" class="btn">Cập nhật</button>
            </form>
        </div>
    </div>

    <link rel="stylesheet" href="../view/layout/css/form.css">
</body>
</html>
