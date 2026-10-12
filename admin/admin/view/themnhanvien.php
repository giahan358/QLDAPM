<?php
include_once '../../model/connectdb.php';
include_once '../../model/nhanvien.php';
include_once '../../model/phanquyen.php';

$listQuyen = getComboboxQuyen();
$employeeName = '';
$emailNV = '';
$phoneNum = '';
$chucVu = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Lấy dữ liệu từ form
    $employeeName = $_POST['employeeName'];
    $emailNV = $_POST['emailNV'];
    $phoneNum = $_POST['phoneNum'];
    $chucVu = $_POST['chucVu'];

    $result = checkTrungEmailVaSDT($emailNV, $phoneNum);

    if ($result['emailNV']) {
        echo "<script>alert('Email nhân viên đã tồn tại! Vui lòng nhập lại.');</script>";
    } elseif ($result['sdtNV']) {
        echo "<script>alert('Số điện thoại nhân viên đã tồn tại! Vui lòng nhập lại.');</script>";
    } else {
        if (themNhanVien($employeeName, $emailNV, $phoneNum, $chucVu)) {
            echo "<script>alert('Thêm nhân viên thành công!'); window.location.href = '../controller/index.php?pg=nhanvien';</script>";
        } else {
            echo "<script>alert('Thêm nhân viên thất bại! Vui lòng thử lại.');</script>";
        }
    }
}
?>
        <div class="container">
            <form action="../controller/index.php?pg=themnhanvien" method="POST">
                <button type="button" class="close-button" onclick="location.href='../controller/index.php?pg=nhanvien'">X</button>
                <h2>Thêm Nhân viên</h2>
                <label for="employeeName"><b>Họ tên</b></label>
                <input type="text" placeholder="Nhập họ tên" name="employeeName" required
                    value="<?php echo htmlspecialchars($employeeName);?>">

                <label for="email"><b>Email</b></label>
                <input type="email" placeholder="Nhập email" name="emailNV" required 
                    value="<?php echo htmlspecialchars($emailNV);?>">

                <label for="phoneNumber"><b>Số điện thoại</b></label>
                <input type="text" placeholder="Nhập số điện thoại" name="phoneNum" required 
                    value="<?php echo htmlspecialchars($phoneNum);?>">

                <label for="chucVu"><b>Nhóm quyền</b></label>
                <select name="chucVu" required>
                    <option value="">-- Chọn nhóm quyền --</option>
                    <?php foreach ($listQuyen as $q): ?>
                        <option value="<?php echo $q['Quyen']; ?>" <?php if ($chucVu == $q['Quyen']) echo 'selected'; ?>>
                            <?php echo htmlspecialchars($q['Quyen']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <label for="status"><b>Trạng thái</b></label>
                <select name="statusNV" disabled>
                    <option value="active">Hoạt động</option>
                    <option value="inactive">Không hoạt động</option>
                </select>

                <button type="submit" class="btn">Thêm</button>
            </form>
        </div>
    </div>

    <link rel="stylesheet" href="../view/layout/css/form.css">
    <script src="../view/layout/js/nhanvien.js"></script>
</body>

</html>
