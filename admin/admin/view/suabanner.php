<?php 
    include_once '../../model/connectDB.php';
    include_once '../../model/banner.php';

    $bannerId = isset($_GET['idbanner']) ? $_GET['idbanner'] : (isset($_POST['idbanner']) ? $_POST['idbanner'] : null);

    if ($bannerId) {
        $banner = getBannerById($bannerId);

        if (!$banner) {
            echo "<script>alert('Không tìm thấy banner!'); window.location.href = '../controller/index.php?pg=cuahang&tabId=tab15';</script>";
            exit;
        }

        $bannerImage = $banner['hinhanh'];
        $bannerDescription = $banner['mota'];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $bannerDescription = $_POST['description'];

            if (isset($_FILES['bannerImage']) && $_FILES['bannerImage']['error'] == 0) {
                $fileName = basename($_FILES['bannerImage']['name']);
                $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

                $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                if (!in_array($fileExtension, $allowedExtensions)) {
                    echo "<script>alert('Chỉ cho phép các file ảnh (jpg, jpeg, png, gif, webp).');</script>";
                    exit;
                }

                $uploadDir = '../../img/ANH_BANNER_MOI/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

                $imagePath = 'img/ANH_BANNER_MOI/' . $fileName;

                if (!move_uploaded_file($_FILES['bannerImage']['tmp_name'], $uploadDir . $fileName)) {
                    echo "<script>alert('Có lỗi xảy ra khi tải ảnh lên.');</script>";
                    exit;
                }
            } else {
                $imagePath = $banner['hinhanh'];
            }

            if (updateBanner($bannerId, $imagePath, $bannerDescription)) {
                echo "<script>alert('Cập nhật banner thành công!'); window.location.href = '../controller/index.php?pg=cuahang&tabId=tab15';</script>";
            } else {
                echo "<script>alert('Cập nhật banner thất bại! Vui lòng thử lại.');</script>";
            }
        }

    } else {
        echo "<script>alert('Không tìm thấy banner!'); window.location.href = '../controller/index.php?pg=cuahang&tabId=tab15';</script>";
    }
?>

    <link rel="stylesheet" href="../view/layout/css/form.css">

    <div class="container">
        <form id="editBannerForm" enctype="multipart/form-data" action="../controller/index.php?pg=suabanner" method="post">
            <button type="button" class="close-button" onclick="location.href='../controller/index.php?pg=cuahang&tabId=tab15'">X</button>
            <h2>Sửa Banner</h2>

            <input type="hidden" name="idbanner" value="<?= $bannerId ?>">

            <div class="row">
                <label for="bannerImage"><b>Hình ảnh:</b></label>
                <input type="file" id="bannerImage" name="bannerImage" accept="image/*">
                <div class="image-preview-container">
                    <img id="previewImage" src="../../<?= $bannerImage ?>?v=<?= time() ?>" style="display:block;" />
                </div>
            </div>

            <label for="description"><b>Mô tả:</b></label>
            <textarea id="description" name="description"><?= htmlspecialchars($bannerDescription) ?></textarea>

            <button type="submit" class="btn">Cập nhật</button>
        </form>
    </div>
</div>

<script>
    document.getElementById("bannerImage").addEventListener("change", function(event) {
    const imagePreview = document.getElementById("previewImage");
    const file = event.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            imagePreview.src = e.target.result;
            imagePreview.style.display = "block";
        };
        reader.readAsDataURL(file);
    }
});
</script>
</body>
</html>
