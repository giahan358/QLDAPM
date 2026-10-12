<?php   
    include_once '../../model/connectDB.php';
    include_once '../../model/khachhang.php';

    $customerName = '';
    $emailKH = '';
    $phoneKH = '';

    if($_SERVER["REQUEST_METHOD"] == "POST"){
        $customerName = $_POST['customerName'];
        $emailKH = $_POST['emailKH'];
        $phoneKH = $_POST['phoneKH'];

        $result = checkTrungEmailVaSDTKH($emailKH, $phoneKH);

        if($result['emailKH']){
            echo "<script>alert('Email khách hàng đã tồn tại! Vui lòng nhập lại.');</script>";
        } elseif ($result['sdtKH']) {
            echo "<script>alert('Số điện thoại khách hàng đã tồn tại! Vui lòng nhập lại.');</script>";
        } else {
            if(themKhachHang($customerName, $emailKH, $phoneKH)){
                echo "<script>alert('Thêm khách hàng thành công!'); window.location.href='../controller/index.php?pg=khachhang&tabId=tab7';</script>";
            }
            else{
                echo "<script>alert('Thêm khách hàng thất bại! Vui lòng thử lại.');</script>";
            }
        }
    }
?>

        <div class="container">
            <form action="../controller/index.php?pg=themkhachhang" method="post">
                <button type="button" class="close-button" onclick="location.href='../controller/index.php?pg=khachhang&tabId=tab7'">X</button>
                <h2>Thêm khách hàng</h2>
                <label for="customerName"><b>Họ tên</b></label>
                <input type="text" placeholder="Nhập họ tên" name="customerName" required
                    value="<?php echo htmlspecialchars($customerName);?>">

                <label for="email"><b>Email</b></label>
                <input type="email" placeholder="Nhập email" name="emailKH" required 
                    value="<?php echo htmlspecialchars($emailKH);?>">

                <label for="phoneNumber"><b>Số điện thoại</b></label>
                <input type="text" placeholder="Nhập số điện thoại" name="phoneKH" required
                    value="<?php echo htmlspecialchars($phoneKH);?>">

                <label for="status"><b>Trạng thái</b></label>
                <select name="statusKH" disabled>
                    <option value="active">Hoạt động</option>
                    <option value="inactive">Không hoạt động</option>
                </select>

                <button type="submit" class="btn">Thêm</button>
            </form>
        </div>
    </div>

    <link rel="stylesheet" href="../view/layout/css/form.css">
    <script src="../view/layout/js/khachhang.js"></script>
</body>
</html>
