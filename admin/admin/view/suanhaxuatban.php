<?php
include_once '../../model/connectDB.php';
include_once '../../model/nhaxuatban.php';

$idnhaxuatban = isset($_GET['idnhaxuatban']) ? $_GET['idnhaxuatban'] : (isset($_POST['idnhaxuatban']) ? $_POST['idnhaxuatban']: null);


if($idnhaxuatban){
    $nhaxuatban = getDataNhaXuaBanTheoId($idnhaxuatban);
    if($_SERVER['REQUEST_METHOD'] == 'POST'){
        $idnhaxuatban = $_POST['idnhaxuatban'];
        $tennxb = $_POST['publisherName'];
        $email = $_POST['email'];
        $sodienthoai = $_POST['phoneNumber'];
        $diachi = $_POST['address'];
        $trangthai = $_POST['trangthai'];
        if(updateNhaXuatBanFull($idnhaxuatban, $tennxb, $email, $sodienthoai, $diachi, $trangthai)){
            echo "<script>
            alert('Cập nhật thông tin nhà xuất bản thành công!');
            window.location.href = '../controller/index.php?pg=sanpham&tabId=tab4';
            </script>";
        }else{
            echo "<script>alert('Lỗi cập nhật nhà xuất bản!')</script>";
        }

    }

} else {
    echo "Không tìm thấy nhà xuất bản";
}

?>

        <div class="container">
            <form action="../controller/index.php?pg=suanhaxuatban" method="post">
                <h2>Sửa Nhà xuất bản</h2>
                <button type="button" class="close-button" onclick="location.href='../controller/index.php?pg=sanpham&tabId=tab4'">X</button>
                <label for="publisherId"><b>ID</b></label>
                <input type="text" name="idnhaxuatban" value="<?=$nhaxuatban['idnhaxuatban']?>" readonly>

                <label for="publisherName"><b>Tên nhà xuất bản</b></label>
                <input type="text" name="publisherName" value="<?=$nhaxuatban['tennxb']?>" required>

                <label for="email"><b>Email</b></label>
                <input type="email" name="email" value="<?=$nhaxuatban['email']?>" required>

                <label for="phoneNumber"><b>Số điện thoại</b></label>
                <input type="text" name="phoneNumber" value="<?=$nhaxuatban['sodienthoai']?>" required>

                <label for="address"><b>Địa chỉ</b></label>
                <input type="text" name="address" value="<?=$nhaxuatban['diachi']?>" required>

                <label for="status"><b>Trạng thái</b></label>
                <select name="trangthai" required>
                    <option value="1" <?=$nhaxuatban['trangthai'] ==1 ? 'selected' : ''?>>Hoạt động</option>
                    <option value="0" <?=$nhaxuatban['trangthai'] ==0 ? 'selected' : ''?>>Tạm khóa</option>
                </select>

                <button type="submit" class="btn">Cập nhật</button>
            </form>
        </div>
    </div>

    <link rel="stylesheet" href="../view/layout/css/form.css">
</body>
</html>
