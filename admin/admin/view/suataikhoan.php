<?php
include_once '../../model/connectDB.php';
include_once '../../model/taikhoan.php';
include_once '../../model/nhanvien.php';

$idnhanvien = isset($_GET['idnhanvien']) ? $_GET['idnhanvien'] : (isset($_POST['idnhanvien']) ? $_POST['idnhanvien']: null);

if($idnhanvien){
    $account = getDataTaiKhoanTheoId($idnhanvien);
    $nhanvien = getDataNhanVienTheoId($idnhanvien);

    if($_SERVER['REQUEST_METHOD'] == 'POST'){
        $idnhanvien = $_POST['idnhanvien'];
        $username = $_POST['username'];
        $password = $_POST['password'];
        $trangthai = $_POST['trangthai'];
        echo "<script>console.log('ID Nhân viên: " . $idnhanvien . "');</script>";
        echo "<script>console.log('username: " . $username . "');</script>";
        echo "<script>console.log('password: " . $password . "');</script>";
        echo "<script>console.log('trangthai: " . $trangthai . "');</script>";

        if(updateTaiKhoanById($idnhanvien, $username, $password, $trangthai)){
            updateNhanVienById($idnhanvien, $trangthai);
            echo "<script>alert('Cập nhật tài khoản nhân viên thành công!'); window.location.href = '../controller/index.php?pg=taikhoan';</script>";
        }else{
            echo "<script>alert('Lỗi cập nhật tài khoản nhân viên!')</script>";
        }
    }

} else {
    echo "Không tìm thấy tài khoản nhân viên";
}

?>

        <div class="container">
            
         <form id="editProductForm" enctype="multipart/form-data" action="../controller/index.php?pg=suataikhoan" method="post">
                <h2>Sửa Tài khoản</h2>
                <button type="button" class="close-button" onclick="location.href='../controller/index.php?pg=taikhoan'">X</button>
                <label for="accountId"><b>ID</b></label>
                <input type="text" name="idnhanvien" value="<?=$account['idnhanvien']?>" readonly>

                <label for="employeeName"><b>Nhân viên</b></label>
                <input type="text" name="employeeName" value="<?=$nhanvien['ten']?>" <?=$account['idnhanvien'] == $nhanvien['idnhanvien']?> readonly >

                <label for="username"><b>Tên đăng nhập</b></label>
                <input type="text" name="username"  value="<?=$account['TaiKhoan']?>" readonly>

                <label for="password"><b>Mật khẩu</b></label>
                <input type="password" name="password"  value="<?=$account['MatKhau']?>" readonly>

                <label for="role"><b>Quyền</b></label>
                <input type="text" name="role" value="<?=$account['Quyen']?>" readonly>

                <label for="status"><b>Trạng thái</b></label>
                <select name="trangthai" required>
                    <option value="1" <?=$account['TrangThai'] ==1 ? 'selected' : ''?>>Hoạt động</option>
                    <option value="0" <?=$account['TrangThai'] ==0 ? 'selected' : ''?>>Tạm khóa</option>
                </select>

                <button type="submit" class="btn">Lưu</button>
            </form>
        </div>
    </div>

    <link rel="stylesheet" href="../view/layout/css/form.css">
</body>
</html>
