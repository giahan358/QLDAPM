<?php
include_once '../../model/connectdb.php';
include_once '../../model/nhacungcap.php';

$supplierName = '';
$email = '';
$phoneNumber = '';
$address = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Lấy dữ liệu từ form
    $supplierName = $_POST['supplierName'];
    $email = $_POST['email'];
    $phoneNumber = $_POST['phoneNumber'];
    $address = $_POST['address'];

    $result = checkTrungTenVaEmailVaSdtNCC($supplierName, $email, $phoneNumber);

    if ($result['tenNCC']) {
        echo "<script>alert('Tên nhà cung cấp đã tồn tại! Vui lòng nhập lại.');</script>";
    } elseif ($result['emailNCC']) {
        echo "<script>alert('Email nhà cung cấp đã tồn tại! Vui lòng nhập lại.');</script>";
    } elseif ($result['sdtNCC']) {
        echo "<script>alert('Số điện thoại nhà cung cấp đã tồn tại! Vui lòng nhập lại.');</script>";
    } else {
        // Gọi hàm thêm nhà cung cấp
        if (themNCC($supplierName, $email, $phoneNumber, $address)) {
            echo "<script>alert('Thêm nhà cung cấp thành công!'); window.location.href = '../controller/index.php?pg=nhacungcap';</script>";
        } else {
            echo "<script>alert('Thêm nhà cung cấp thất bại! Vui lòng thử lại.');</script>";
        }
    }
}
?>
        <div class="container">
            <form action="../controller/index.php?pg=themnhacungcap" method="post">
                <button type="button" class="close-button" onclick="location.href='../controller/index.php?pg=nhacungcap'">X</button>
                <h2>Thêm Nhà cung cấp</h2>
                <label for="supplierName"><b>Tên nhà cung cấp</b></label>
                <input type="text" placeholder="Nhập tên nhà cung cấp" name="supplierName" required
                    value="<?php echo htmlspecialchars($supplierName); ?>">

                <label for="email"><b>Email</b></label>
                <input type="email" placeholder="Nhập email" name="email" required 
                    value="<?php echo htmlspecialchars($email); ?>">

                <label for="phoneNumber"><b>Số điện thoại</b></label>
                <input type="text" placeholder="Nhập số điện thoại" name="phoneNumber" required
                    value="<?php echo htmlspecialchars($phoneNumber); ?>">

                <label for="address"><b>Địa chỉ</b></label>
                <input type="text" placeholder="Nhập địa chỉ" name="address" required 
                    value="<?php echo htmlspecialchars($address); ?>">

                <label for="status"><b>Trạng thái</b></label>
                <select name="status" disabled>
                    <option value="active">Hoạt động</option>
                    <option value="inactive">Không hoạt động</option>
                </select>

                <button type="submit" class="btn">Thêm</button>
            </form>
        </div>
    </div>

    <link rel="stylesheet" href="../view/layout/css/form.css">
    <script src="../view/layout/js/nhacungcap.js"></script>
</body>
</html>
