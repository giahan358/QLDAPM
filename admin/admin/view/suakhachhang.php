<?php
include_once '../../model/connectDB.php';
include_once '../../model/taikhoankhachhang.php';
include_once '../../model/khachhang.php';

$idkhachhang = isset($_GET['idkhachhang']) ? $_GET['idkhachhang'] : (isset($_POST['idkhachhang']) ? $_POST['idkhachhang']: null);


if($idkhachhang){
    $customer = getDataKhachHangTheoId($idkhachhang);

    if($_SERVER['REQUEST_METHOD'] == 'POST'){
        $idkhachhang = $_POST['idkhachhang'];
        $ten = $_POST['ten'];
        $email = $_POST['email'];
        $sodienthoai = $_POST['phoneNumber'];
        $trangthai = $_POST['trangthai'];
        
        if(updateKhachHangFull($idkhachhang, $ten, $email, $sodienthoai, $trangthai)){
            updateDataTaiKhoanKhachHangById($idkhachhang);
            echo "
            <script>
                alert('Cập nhật khách hàng thành công');
                window.location.href='../controller/index.php?pg=khachhang&tabId=tab7';
            
            </script>";
        }else{
            echo "<script>alert('Cập nhật khách hàng thất bại');</script>";
        }
}
}else {
    echo "Không tìm thấy khách hàng";
}

?>

        <div class="container">
            
            <form action="../controller/index.php?pg=suakhachhang" method="post">
                <h2>Sửa Khách hàng</h2>
                <button type="button" class="close-button" onclick="location.href='../controller/index.php?pg=khachhang&tabId=tab7'">X</button>
                <label for="customerId"><b>ID</b></label>
                <input type="text" name="idkhachhang" value="<?=$customer['idkhachhang']?>" readonly>

                <label for="customerName"><b>Họ tên</b></label>
                <input type="text" name="ten" value="<?=$customer['ten']?>" required>

                <label for="email"><b>Email</b></label>
                <input type="email" name="email" value="<?=$customer['email']?>" required>

                <label for="phoneNumber"><b>Số điện thoại</b></label>
                <input type="text" name="phoneNumber" value="<?=$customer['sodienthoai']?>" required>

                <label for="status"><b>Trạng thái</b></label>
                <select name="trangthai" >
                    <option value="1" <?=$customer['trangthai'] ==1 ? 'selected' : ''?>>Hoạt động</option>
                    <option value="0" <?=$customer['trangthai'] ==0 ? 'selected' : ''?>>Tạm khóa</option>
                </select>

                <button type="submit" class="btn">Cập nhật</button>
            </form>
        </div>
    </div>

    <link rel="stylesheet" href="../view/layout/css/form.css">
</body>
</html>
