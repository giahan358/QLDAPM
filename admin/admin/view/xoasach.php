<?php
include_once '../../model/connectDB.php';
include_once '../../model/sanpham.php';

$idsach = isset($_GET['idsach']) ? $_GET['idsach'] : null;

if ($idsach) {
    $product = getDataSanPhamTheoId($idsach);
    $isInHoaDon = kiemTraIdSachTrongChiTietHoaDon($idsach);

    if ($product['trangthai'] == 1) {
        $result = delSanPhamByID($idsach); 
        if ($result) {
            echo "<script>
                alert('Đã chuyển trạng thái sản phẩm sang tạm ngưng!');
                window.location.href = '../controller/index.php?pg=sanpham&tabId=tab1';
            </script>";
        } else {
            echo "<script>alert('Lỗi khi cập nhật trạng thái sản phẩm!');</script>";
        }
    } else if ($product['trangthai'] == 0 && $product['sltonkho'] == 0 && !$isInHoaDon) {
        // Nếu trạng thái là 0 và tồn kho = 0 và không có trong hóa đơn, xóa hẳn
        if (delSanPhamTheoIdComplete($idsach)) {
            echo "<script>
                alert('Xóa sản phẩm thành công!');
                window.location.href = '../controller/index.php?pg=sanpham&tabId=tab1';
            </script>";
        } else {
            echo "<script>alert('Lỗi khi xóa sản phẩm!');</script>";
        }
    } else if ($product['trangthai'] == 0 && $product['sltonkho'] > 0) {
        // Đang tạm ngưng mà còn tồn kho
        echo "<script>
            alert('Không thể xóa vì sản phẩm đang tạm ngưng và còn tồn kho!');
            window.location.href = '../controller/index.php?pg=sanpham&tabId=tab1';
        </script>";
    } else {
        echo "<script>alert('Không thể xóa sản phẩm trong tình trạng hiện tại!');</script>";
    }
} else {
    echo "<script>alert('Không tìm thấy sản phẩm!');</script>";
}
exit();
?>
