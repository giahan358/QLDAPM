<?php
    include_once '../../model/connectDB.php';
    include_once '../../model/danhmuc.php';

    $tenDM = '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {  
        $tenDM = $_POST['categoryName'];
    
        $result = checkTrungTenDanhMuc($tenDM);
    
        if($result){
            echo "<script>alert('Tên danh mục đã tồn tại! Vui lòng nhập lại.');</script>";
        } else {
            if (themDanhMuc($tenDM)) {
                echo "<script>alert('Thêm danh mục thành công!'); window.location.href = '../controller/index.php?pg=danhmuc';</script>";
            } else {
                echo "<script>alert('Thêm danh mục thất bại! Vui lòng thử lại.');</script>";
            }
        }
    }
?>

        <div class="container">
            <form action="../controller/index.php?pg=themdanhmuc" method="post">    
                <button type="button" class="close-button" onclick="location.href='../controller/index.php?pg=danhmuc'">X</button>
                <h2>Thêm Danh mục</h2>
                <label for="categoryName"><b>Tên Danh mục</b></label>
                <input type="text" placeholder="Nhập tên danh mục" name="categoryName" required
                    value="<?php echo htmlspecialchars($tenDM);?>">
    
                <label for="status"><b>Trạng thái</b></label>
                <select name="statusDM" disabled>
                    <option value="1">Hoạt động</option>
                    <option value="0">Ngưng hoạt động</option>
                </select>
    
                <button type="submit" class="btn">Thêm</button>
            </form>
        </div>
    </div>

    <link rel="stylesheet" href="../view/layout/css/form.css">
</body>

</html>
