<?php
    include_once '../../model/connectDB.php';
    include_once '../../model/taikhoan.php';
    include_once '../../model/phanquyen.php';
    include_once '../../model/nhanvien.php';

    $listNhanVien = getComboboxNhanVien();
    $listQuyen = getComboboxQuyen();

    $nhanVienValue = '';
    $taiKhoan = '';
    $matKhau = '';
    $quyenValue = '';

    if (isset($_GET['idnhanvien']) && is_numeric($_GET['idnhanvien'])) {
        $idNV = $_GET['idnhanvien'];
        $getChucVuvaNhanVien = getNhanVienVaChucVuByIDNV($idNV);
        $nhanVienValue = $getChucVuvaNhanVien['idnhanvien'] . ' - ' . $getChucVuvaNhanVien['ten'];
        $quyenValue = $getChucVuvaNhanVien['chucvu'];
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $nhanVien = $_POST['idNhanVien'];
        $idNhanVien = explode(' - ', $nhanVien); 

        $quyen = $_POST['quyen'];       
        $taiKhoan = $_POST['tenDangNhap'];
        $matKhau = $_POST['matKhau'];

        if (kiemTraNhanVienDaCoTaiKhoan($idNhanVien)) {
            echo "<script>alert('Nhân viên này đã có tài khoản đang hoạt động! Không thể cấp tài khoản mới.');</script>";
        } else {
            if (!kiemTraTenDangNhapTonTai($taiKhoan)) {
                if (themTaiKhoanNV($idNhanVien, $taiKhoan, $matKhau, $quyen)) {
                    echo "<script>alert('Cấp tài khoản thành công!'); window.location.href = '../controller/index.php?pg=taikhoan';</script>";
                } else {
                    echo "<script>alert('Cấp tài khoản thất bại! Vui lòng thử lại.');</script>";
                }
            } else {
                echo "<script>alert('Tên đăng nhập đã tồn tại! Vui lòng nhập tên khác.');</script>";
            }
        }
    }    
?>

        <div class="container">
            <form action="../controller/index.php?pg=themtaikhoan" method="post">
                <button type="button" class="close-button" onclick="location.href='../controller/index.php?pg=nhanvien'">X</button>
                <h2>Cấp Tài khoản</h2>
                <!-- Nhân viên -->
                <label for="idNhanVien"><b>Nhân viên</b></label>
                <input type="text" name="idNhanVien" value="<?php echo htmlspecialchars($nhanVienValue); ?>" readonly>

                <!-- Tên đăng nhập -->
                <label for="tenDangNhap"><b>Tên đăng nhập</b></label>
                <input type="text" name="tenDangNhap" placeholder="Nhập tên đăng nhập" required value="<?php echo htmlspecialchars($taiKhoan); ?>">

                <!-- Mật khẩu -->
                <label for="matKhau"><b>Mật khẩu</b></label>
                <input type="password" name="matKhau" placeholder="Nhập mật khẩu" required value="<?php echo htmlspecialchars($matKhau); ?>">

                <!-- Quyền -->
                <label for="quyen"><b>Nhóm quyền</b></label>
                <input type="text" name="quyen" value="<?php echo htmlspecialchars($quyenValue); ?>" readonly>

                <!-- Trạng thái -->
                <label for="trangThai"><b>Trạng thái</b></label>
                <select name="trangThai" disabled>
                    <option value="1">Hoạt động</option>
                    <option value="0">Ngưng hoạt động</option>
                </select>

                <button type="submit" class="btn">Cấp tài khoản</button>
            </form>
        </div>
    </div>

    <link rel="stylesheet" href="../view/layout/css/form.css">
    <script src="../view/layout/js/taikhoan.js"></script>
</body>
</html>
