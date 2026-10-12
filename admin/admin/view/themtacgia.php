<?php
    include_once '../../model/connectDB.php';
    include_once '../../model/tacgia.php';

    $tenTacGia = '';
    $tieuSu = '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {  
        $tenTacGia = $_POST['authorName'];
        $tieuSu = $_POST['bio'];
    
        $result = checkTrungTenTacGia($tenTacGia);

        if($result){
            echo "<script>alert('Tên tác giả đã tồn tại! Vui lòng nhập lại.');</script>";
        } else {
            if (themTacGia($tenTacGia, $tieuSu)) {
                echo "<script>alert('Thêm tác giả thành công!'); window.location.href = '../controller/index.php?pg=sanpham&tabId=tab3';</script>";
            } else {
                echo "<script>alert('Thêm tác giả thất bại! Vui lòng thử lại.');</script>";
            }
        }
    }
?>

        <div class="container">
            <form action="../controller/index.php?pg=themtacgia" method="post">    
                <button type="button" class="close-button" onclick="location.href='../controller/index.php?pg=sanpham&tabId=tab3'">X</button>
                <h2>Thêm Tác giả</h2>
                <label for="authorName"><b>Tên tác giả</b></label>
                <input type="text" placeholder="Nhập tên tác giả" name="authorName" required
                    value="<?php echo htmlspecialchars($tenTacGia); ?>">
    
                <label for="bio"><b>Tiểu sử</b></label>
                <textarea placeholder="Nhập tiểu sử" name="bio" required><?php echo htmlspecialchars($tieuSu); ?></textarea>
    
                <label for="status"><b>Trạng thái</b></label>
                <select name="statusTG" disabled>
                    <option value="1">Hoạt động</option>
                    <option value="0">Không hoạt động</option>
                </select>
    
                <button type="submit" class="btn">Thêm</button>
            </form>
        </div>
    </div>

    <link rel="stylesheet" href="../view/layout/css/form.css">
</body>
</html>
