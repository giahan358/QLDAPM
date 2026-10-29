<?php
include_once '../../model/connectDB.php';
include_once '../../model/cuahang.php';

$idthongtin = isset($_GET['idthongtin']) ? $_GET['idthongtin'] : (isset($_POST['idthongtin']) ? $_POST['idthongtin'] : null);

if ($idthongtin) {
    $cuahang = getCuaHangTheoId($idthongtin); 

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $diachi = $_POST['diachi'];
        $sodienthoai = $_POST['sodienthoai'];
        $email = $_POST['email'];
        $facebook = $_POST['facebook'];
        $tiktok = $_POST['tiktok'];

        if (updateCuaHang($idthongtin, $diachi, $sodienthoai, $email, $facebook, $tiktok)) {
            echo "<script>
                alert('Cập nhật thông tin cửa hàng thành công!');
                window.location.href = '../controller/index.php?pg=cuahang&tabId=tab1';
            </script>";
        } else {
            echo "<script>alert('Lỗi cập nhật thông tin cửa hàng!')</script>";
        }
    }
} else {
    echo "Không tìm thấy thông tin cửa hàng.";
}
?>

    <div class="container">
        <form enctype="multipart/form-data" action="../controller/index.php?pg=suacuahang" method="post">
            <h2>Sửa Thông tin Cửa hàng</h2>
            <button type="button" class="close-button" onclick="location.href='../controller/index.php?pg=cuahang&tabId=tab1'">X</button>

            <input type="hidden" name="idthongtin" value="<?= $cuahang['idthongtin'] ?>">

            <label><b>Địa chỉ</b></label>
            <input type="text" name="diachi" value="<?= htmlspecialchars($cuahang['diachi']) ?>">

            <label><b>Số điện thoại</b></label>
            <input type="text" name="sodienthoai" value="<?= htmlspecialchars($cuahang['sodienthoai']) ?>">

            <label><b>Email</b></label>
            <input type="email" name="email" value="<?= htmlspecialchars($cuahang['email']) ?>">

            <label><b>Facebook</b></label>
            <input type="text" name="facebook" value="<?= htmlspecialchars($cuahang['facebook']) ?>">

            <label><b>TikTok</b></label>
            <input type="text" name="tiktok" value="<?= htmlspecialchars($cuahang['tiktok']) ?>">

            <button type="submit" class="btn">Cập nhật</button>
        </form>
    </div>
</div>

<link rel="stylesheet" href="../view/layout/css/form.css">

</body>
</html>
