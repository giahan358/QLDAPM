<?php 
    include_once '../../model/connectDB.php';
    include_once '../../model/tacgia.php';
    include_once '../../model/nhaxuatban.php';
    include_once '../../model/danhmuc.php';
    include_once '../../model/sanpham.php';

    $listTacGia = getComboboxTacGia();
    $listNXB = getComboboxNXB();
    $listCTDanhMuc = getComboboxCTDanhMuc();

    $tacGiaValue = ''; 
    $nxbValue = '';          
    $ctdanhMucValue = '';   
    $tenSach = ''; 
    $moTa = '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Lấy giá trị từ POST
        $tacGiaValue = $_POST['idTacGia']; 
        $nxbValue = $_POST['idNXB'];         
        $ctdanhMucValue = $_POST['idctDanhMuc'];         
    
        // Tách chuỗi chỉ lấy ID
        $idTacGia = explode('-', $tacGiaValue)[0]; 
        $idNXB = explode('-', $nxbValue)[0];  
        $idctDanhMuc = explode('-', $ctdanhMucValue)[0];      
    
        $tenSach = $_POST['productName'];
        $moTa = $_POST['description'];

        $result = checkTrungTenSach($tenSach);

        if($result){
            echo "<script>alert('Tên sách đã tồn tại! Vui lòng nhập lại.');</script>";
        } else {
            // Kiểm tra nếu có file ảnh được upload
            if (isset($_FILES['productImage']) && $_FILES['productImage']['error'] == 0) {
                $fileName = basename($_FILES['productImage']['name']);
                $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

                // Kiểm tra phần mở rộng của file
                $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];

                if (!in_array($fileExtension, $allowedExtensions)) {
                    echo "Chỉ cho phép các file ảnh (jpg, jpeg, png, gif).";
                    exit;
                }

                // Tạo đường dẫn thư mục cố định cho ảnh sách
                $uploadDir = '../../img/ANH_SACH_MOI/';

                // Kiểm tra xem thư mục đã tồn tại chưa, nếu chưa thì tạo mới
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true); // Tạo thư mục mới nếu chưa có
                }

                $imagePath = 'img/ANH_SACH_MOI/' . $fileName;

                // Di chuyển file ảnh từ thư mục tạm vào thư mục đích
                if (move_uploaded_file($_FILES['productImage']['tmp_name'], $uploadDir . $fileName)) {
                    echo "Ảnh đã được tải lên thành công!";
                } else {
                    echo "Có lỗi xảy ra khi tải ảnh lên.";
                    exit;
                }
            } else {
                $imagePath = 'img/ANH_SACH_MOI/default-book.jpg'; 
            }
        
            if (themSach($tenSach, $idTacGia, $idNXB, $idctDanhMuc, $moTa, $imagePath)) {
                echo "<script>alert('Thêm sản phẩm thành công!'); window.location.href = '../controller/index.php?pg=sanpham&tabId=tab1';</script>";
            } else {
                echo "<script>alert('Thêm sản phẩm thất bại! Vui lòng thử lại.'); window.location.href = '../controller/index.php?pg=themsach';</script>";
            }
        }
    }    
?> 

        <div class="container">
            <form id="addProductForm" enctype="multipart/form-data" action="../controller/index.php?pg=themsach" method="post">
                <button type="button" class="close-button" onclick="location.href='../controller/index.php?pg=sanpham&tabId=tab1'">X</button>
                <h2>Thêm Sách</h2>
                <div class="row">
                    <div class="custom-file">
                        <label for="hinhAnh"><b>Hình ảnh:</b></label>
                        <label for="productImage">Chọn ảnh</label>
                        <span class="file-name" id="fileName">default-book.jpg</span>
                        <input type="file" id="productImage" name="productImage" accept="image/*">
                    </div>
                    <div class="image-preview-container">
                        <img src="../../img/ANH_SACH_MOI/default-book.jpg" id="previewImage" style="display:block;" />
                    </div>
                </div>

                <label for="productName"><b>Tên sách:</b></label>
                <input type="text" id="productName" name="productName" placeholder="Nhập tên sản phẩm" required
                    value="<?php echo htmlspecialchars($tenSach); ?>">
        
                <label for="tacgia"><b>Tác giả:</b></label>
                <select name="idTacGia" required>
                    <option value="">-- Chọn tác giả --</option>
                    <?php foreach ($listTacGia as $tg): ?>
                        <option value="<?php echo $tg['idtacgia']; ?>" <?php if ($tacGiaValue == $tg['idtacgia']) echo 'selected'; ?>>
                            <?php echo htmlspecialchars($tg['idtacgia'] . ' - ' . $tg['tentacgia']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <label for="nhaxuatban"><b>Nhà xuất bản:</b></label>
                <select name="idNXB" required>
                    <option value="">-- Chọn nhà xuất bản --</option>
                    <?php foreach ($listNXB as $nxb): ?>
                        <option value="<?php echo $nxb['idnhaxuatban']; ?>" <?php if ($nxbValue == $nxb['idnhaxuatban']) echo 'selected'; ?>>
                            <?php echo htmlspecialchars($nxb['idnhaxuatban'] . ' - ' . $nxb['tennxb']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <label for="ctdanhmuc"><b>Danh mục:</b></label>
                <select name="idctDanhMuc" required>
                    <option value="">-- Chọn danh mục --</option>
                    <?php foreach ($listCTDanhMuc as $ctdanhMuc): ?>
                        <option value="<?php echo $ctdanhMuc['idchitietdanhmuc']; ?>" <?php if ($ctdanhMucValue == $ctdanhMuc['idchitietdanhmuc']) echo 'selected'; ?>> 
                            <?php echo htmlspecialchars($ctdanhMuc['idchitietdanhmuc'] . ' - ' . $ctdanhMuc['tenchitietdanhmuc'] . 
                                ' -------------------- ' . $ctdanhMuc['iddanhmuc'] . ' - ' . $ctdanhMuc['tendanhmuc']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
        
                <div class="row">
                    <label for="price"><b>Giá:</b></label>
                    <input type="text" id="price" name="price" value="0đ" disabled>
                  
                    <label for="quantity"><b>Số lượng:</b></label>
                    <input type="text" id="quantity" name="quantity" value="0" disabled>
                </div>
        
                <label for="description"><b>Mô tả:</b></label>
                <textarea id="description" name="description" placeholder="Nhập mô tả sản phẩm" required><?php echo htmlspecialchars($moTa); ?></textarea>

                <label for="trangThai"><b>Trạng thái</b></label>
                <select name="trangThai" disabled>
                    <option value="hetHang">Hết hàng</option>
                </select>
        
                <button type="submit" class="btn">Thêm</button>
            </form>
        </div>        
    </div>

    <link rel="stylesheet" href="../view/layout/css/form.css">
    <script src="../view/layout/js/xemHinhAnh.js"></script>
</body>

</html>
