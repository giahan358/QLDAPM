<?php
include_once '../../model/connectDB.php';
include_once '../../model/nhacungcap.php';

$idnhacungcap = isset($_GET['idnhacungcap']) ? $_GET['idnhacungcap'] : (isset($_POST['idnhacungcap']) ? $_POST['idnhacungcap']: null);


if($idnhacungcap){
    $provider = getDataNhaCungCapTheoId($idnhacungcap);
    if($_SERVER['REQUEST_METHOD'] == 'POST'){
        $idnhacungcap = $_POST['idnhacungcap'];
        $trangthai = $_POST['trangthai'];
        if(updateNhaCungCapById($idnhacungcap, $trangthai)){
            echo "<script>
            alert('Cập nhật thông tin nhà cung cấp thành công!');
            window.location.href = '../controller/index.php?pg=nhacungcap';
            </script>";
        }else{
            echo "<script>alert('Lỗi cập nhật nhà cung cấp!')</script>";
        }

    }

} else {
    echo "Không tìm thấy nhacungcap";
}

?>

        <div class="container">
           
            <form action="../controller/index.php?pg=suanhacungcap" method="post">
                <h2>Sửa Nhà cung cấp</h2>
                <button type="button" class="close-button" onclick="location.href='../controller/index.php?pg=nhacungcap'">X</button>
                <label for="supplierId"><b>ID</b></label>
                <input type="text" name="idnhacungcap" value="<?=$provider['idnhacungcap']?>" readonly>

                <label for="supplierName"><b>Tên nhà cung cấp</b></label>
                <input type="text" name="ten" value="<?=$provider['tenncc']?>" readonly>

                <label for="email"><b>Email</b></label>
                <input type="email"  name="email" value="<?=$provider['email']?>" readonly>

                <label for="phoneNumber"><b>Số điện thoại</b></label>
                <input type="text" name="phoneNumber" value="<?=$provider['sodienthoai']?>" readonly>

                <label for="address"><b>Địa chỉ</b></label>
                <input type="text" name="address" value="<?=$provider['diachi']?>" readonly> 

                <label for="status"><b>Trạng thái</b></label>
                <select name="trangthai" required>
                    <option value="1" <?=$provider['trangthai'] ==1 ? 'selected' : ''?>>Hoạt động</option>
                    <option value="0" <?=$provider['trangthai'] ==0 ? 'selected' : ''?>>Tạm khóa</option>
                </select>

                <button type="submit" class="btn">Lưu</button>
            </form>
        </div>
    </div>

    <link rel="stylesheet" href="../view/layout/css/form.css">
</body>
</html>
