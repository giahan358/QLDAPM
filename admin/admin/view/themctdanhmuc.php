<?php
    include_once '../../model/connectDB.php';
    include_once '../../model/ctdanhmuc.php';
    include_once '../../model/danhmuc.php';

    // Lấy danh mục theo id
    if (isset($_GET['iddanhmuc']) && is_numeric($_GET['iddanhmuc'])) {
        $idDM = $_GET['iddanhmuc'];
        $danhMuc = getDanhMucById($idDM);
    } else {
        echo "<script>alert('ID danh mục không hợp lệ!'); window.location.href = '../controller/index.php?pg=danhmuc';</script>";
        exit();
    }    

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {  
        $DanhMucValue = $_POST['iddanhmuc_display'];
        $idDM = explode('-', $DanhMucValue)[0];   
        $tenctdm = $_POST['subCategoryName'];

        if (checkTrungTenCTDM($idDM, $tenctdm)) {
            echo "<script>alert('Tên chi tiết danh mục đã tồn tại!'); window.location.href = '../controller/index.php?pg=themctdanhmuc&iddanhmuc=$idDM';</script>";
            exit();
        }
    
        if (themCTDM($idDM, $tenctdm)) {
            echo "<script>alert('Thêm chi tiết danh mục thành công!'); window.location.href = '../controller/index.php?pg=chitietdanhmuc&iddanhmuc=$idDM';</script>";
        } else {
            echo "<script>alert('Thêm chi tiết danh mục thất bại! Vui lòng thử lại.'); window.location.href = '../controller/index.php?pg=themctdanhmuc&iddanhmuc=$idDM';</script>";
        }
    }
?>

    <div class="container">
        <form action="../controller/index.php?pg=themctdanhmuc&iddanhmuc=<?php echo htmlspecialchars($idDM); ?>" method="POST">
            <button type="button" class="close-button" onclick="location.href='../controller/index.php?pg=chitietdanhmuc&iddanhmuc=<?php echo htmlspecialchars($_GET['iddanhmuc']); ?>'">X</button>
            <h2>Thêm Chi tiết Danh mục</h2>
            <label for="iddanhmuc"><b>Danh mục</b></label>
            <!-- Display ID - Tên Danh Mục -->
            <input type="text" name="iddanhmuc_display" 
                value="<?php echo htmlspecialchars($danhMuc['iddanhmuc'] . ' - ' . $danhMuc['tendanhmuc']); ?>" 
                readonly> 

            <label for="subCategoryName"><b>Tên Chi tiết Danh mục</b></label>
            <input type="text" name="subCategoryName" placeholder="Nhập tên chi tiết danh mục" required>

            <label for="statusCTDM"><b>Trạng thái</b></label>
            <select name="statusCTDM" disabled>
                <option value="1">Hoạt động</option>
                <option value="0">Ngưng hoạt động</option>
            </select>

            <button type="submit" class="btn">Thêm</button>
        </form>
    </div>

    <link rel="stylesheet" href="../view/layout/css/form.css">
</body>

</html> 