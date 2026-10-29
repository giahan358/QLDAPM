<?php
include_once '../../model/connectDB.php';
include_once '../../model/cuahang.php';

$id = isset($_GET['idthongtin']) ? $_GET['idthongtin'] : (isset($_POST['idthongtin']) ? $_POST['idthongtin'] : null);

if ($id) {
    $cuaHang = getCuaHangTheoId($id);

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $tenNH = $_POST['tenNH'];
        $stk = $_POST['stk'];
        $tenChuTK = $_POST['tenChuTK'];

        // Xử lý ảnh QR
        if (isset($_FILES['anhQrCk']) && $_FILES['anhQrCk']['error'] == 0) {
            $fileName = basename($_FILES['anhQrCk']['name']);
            $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

            if (!in_array($fileExtension, $allowedExtensions)) {
                echo "<script>alert('Chỉ cho phép các file ảnh (jpg, jpeg, png, gif, webp).');</script>";
                exit;
            }

            $uploadDir = '../../img/qrcode/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

            $qrImagePath = 'img/qrcode/' . $fileName;

            if (!move_uploaded_file($_FILES['anhQrCk']['tmp_name'], $uploadDir . $fileName)) {
                echo "<script>alert('Lỗi tải ảnh QR.');</script>";
                exit;
            }
        } else {
            $qrImagePath = $cuaHang['anhQrCk'];
        }

        if (updateChuyenKhoan($id, $tenNH, $stk, $tenChuTK, $qrImagePath)) {
            echo "<script>
                alert('Cập nhật thông tin chuyển khoản thành công!');
                window.location.href = '../controller/index.php?pg=cuahang&tabId=tab1';
            </script>";
        } else {
            echo "<script>alert('Lỗi cập nhật chuyển khoản!')</script>";
        }
    }
} else {
    echo "Không tìm thấy ID thông tin cửa hàng.";
}
?>

<link rel="stylesheet" href="../view/layout/css/form.css">

<div class="container">
    <form enctype="multipart/form-data" action="../controller/index.php?pg=suachuyenkhoan" method="post">
        <h2>Sửa thông tin chuyển khoản</h2>
        <button type="button" class="close-button" onclick="location.href='../controller/index.php?pg=cuahang&tabId=tab1'">X</button>
        <input type="hidden" name="idthongtin" value="<?= $cuaHang['idthongtin'] ?>">

        <label><b>Tên ngân hàng</b></label>
        <input type="text" name="tenNH" value="<?= htmlspecialchars($cuaHang['tenNH']) ?>">

        <label><b>Số tài khoản</b></label>
        <input type="text" name="stk" value="<?= htmlspecialchars($cuaHang['stk']) ?>">

        <label><b>Tên chủ tài khoản</b></label>
        <input type="text" name="tenChuTK" value="<?= htmlspecialchars($cuaHang['tenChuTK']) ?>">

        <label><b>Ảnh QR</b></label>
        <input type="file" id="anhQrCk" name="anhQrCk" accept="image/*">
        <div class="image-preview-container">
            <img id="previewImage" src="../../<?= $cuaHang['anhQrCk'] ?>?v=<?= time() ?>" style="display:block; max-width: 200px;" />
        </div>

        <button type="submit" class="btn">Cập nhật</button>
    </form>
</div>

<script>
document.getElementById("anhQrCk").addEventListener("change", function(event) {
    const imagePreview = document.getElementById("previewImage");
    const file = event.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            imagePreview.src = e.target.result;
            imagePreview.style.display = "block";
        };
        reader.readAsDataURL(file);
    }
});
</script>
</body>
</html>
