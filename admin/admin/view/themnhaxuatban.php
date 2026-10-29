<?php
    include_once '../../model/connectDB.php';
    include_once '../../model/nhaxuatban.php';

    $tenNXB = '';
    $email = '';
    $sdt = '';
    $diachi = '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {  
        $tenNXB = $_POST['publisherName'];
        $email = $_POST['email'];
        $sdt = $_POST['phoneNumber'];
        $diachi = $_POST['address'];
    
        $result = checkTrungTenAndEmailAndSDTAndDiaChi($tenNXB, $email, $sdt, $diachi);
    
        if ($result['tenNXB']) {
            echo "<script>alert('Tên nhà xuất bản đã tồn tại! Vui lòng nhập lại.');</script>";
        } elseif ($result['emailNXB']) {
            echo "<script>alert('Email đã tồn tại! Vui lòng nhập lại.');</script>";
        } elseif ($result['SDTNXB']) {
            echo "<script>alert('Số điện thoại đã tồn tại! Vui lòng nhập lại.');</script>";
        } else {
            // Nếu không có trùng lặp, thêm nhà xuất bản mới
            if (themNXB($tenNXB, $email, $sdt, $diachi)) {
                echo "<script>alert('Thêm nhà xuất bản thành công!'); window.location.href = '../controller/index.php?pg=sanpham&tabId=tab4';</script>";
            } else {
                echo "<script>alert('Thêm nhà xuất bản thất bại! Vui lòng thử lại.');</script>";
            }
        }
    }    
?>

        <div class="container">
            <form action="../controller/index.php?pg=themnhaxuatban" method="post">
                <button type="button" class="close-button" onclick="location.href='../controller/index.php?pg=sanpham&tabId=tab4'">X</button>
                <h2>Thêm Nhà xuất bản</h2>
                <label for="publisherName"><b>Tên nhà xuất bản</b></label>
                <input type="text" placeholder="Nhập tên nhà xuất bản" name="publisherName" required
                    value="<?php echo htmlspecialchars($tenNXB); ?>">

                <label for="email"><b>Email</b></label>
                <input type="email" placeholder="Nhập email" name="email" required value="<?php echo htmlspecialchars($email); ?>">

                <label for="phoneNumber"><b>Số điện thoại</b></label>
                <input type="text" placeholder="Nhập số điện thoại" name="phoneNumber" required
                    value="<?php echo htmlspecialchars($sdt); ?>">

                <label for="address"><b>Địa chỉ</b></label>
                <input type="text" placeholder="Nhập địa chỉ" name="address" required  
                    value="<?php echo htmlspecialchars($diachi); ?>">

                <label for="status"><b>Trạng thái</b></label>
                <select name="statusNXB" disabled>
                    <option value="1">Hoạt động</option>
                    <option value="0">Không hoạt động</option>
                </select>

                <button type="submit" class="btn">Thêm</button>
            </form>
        </div>
    </div>

    <link rel="stylesheet" href="../view/layout/css/form.css">
    <script src="../view/layout/js/nhaxuatban.js"></script>
</body>
</html>
