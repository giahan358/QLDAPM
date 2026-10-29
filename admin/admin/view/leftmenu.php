<?php
include_once '../../model/connectDB.php';
include_once '../../model/phanquyen.php';

$quyen = isset($_SESSION['quyen']) ? $_SESSION['quyen'] : '';
$ds_quyen = getQuyenByName($quyen);

if (!$ds_quyen) {
    echo "Không tìm thấy quyền với tên '$quyen'.";
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trang Admin</title>

    <link rel="stylesheet" href="../view/layout/css/admin.css">
    <link rel="stylesheet" href="../view/layout/css/table.css">
    <link rel="stylesheet" href="../view/layout/css/phanTrang.css">
    <link rel="stylesheet" href="../view/layout/css/tabs.css">

    <script src="../view/layout/js/leftmenu.js"></script>
</head>

<body>
    <div class="flexbox">
        <!--Menu-->
        <div id="leftmenu">
            <div class="logo"><img src="../view/layout/logo-icon/logonew.png"></div>
            <ul>
                <?php if ((bool) $ds_quyen['QLCuaHang']) : ?>
                <li><img src="../view/layout/logo-icon/shop-icon.png"><a href="../controller/index.php?pg=cuahang">Cửa hàng</a></li>
                <?php endif; ?>

                <?php if ((bool) $ds_quyen['QLSanPham']) : ?>
                <li><img src="../view/layout/logo-icon/book-icon.png"><a href="../controller/index.php?pg=sanpham">Sản phẩm</a></li>
                <?php endif; ?>

                <?php if ((bool) $ds_quyen['QLDanhMuc']) : ?>
                <li><img src="../view/layout/logo-icon/danhmuc-icon.png"><a href="../controller/index.php?pg=danhmuc">Danh mục</a></li>
                <?php endif; ?>

                <?php if ((bool) $ds_quyen['QLNhanVien']) : ?>
                <li><img src="../view/layout/logo-icon/khachhang-icon.png"><a href="../controller/index.php?pg=nhanvien">Nhân viên</a></li>
                <?php endif; ?>

                <?php if ((bool) $ds_quyen['QLKhachHang']) : ?>
                <li><img src="../view/layout/logo-icon/khachhang-icon.png"><a href="../controller/index.php?pg=khachhang">Khách hàng</a></li>
                <?php endif; ?>

                <?php if ((bool) $ds_quyen['QLNhaCungCap']) : ?>
                <li><img src="../view/layout/logo-icon/nhacungcap-icon.png"><a href="../controller/index.php?pg=nhacungcap">Nhà cung cấp</a></li>
                <?php endif; ?>

                <?php if ((bool) $ds_quyen['QLDonHang']) : ?>
                <li><img src="../view/layout/logo-icon/donhang-icon.png"><a href="../controller/index.php?pg=hoadon">Đơn hàng</a></li>
                <?php endif; ?>

                <?php if ((bool) $ds_quyen['QLPhieuNhap']) : ?>
                <li><img src="../view/layout/logo-icon/donhang-icon.png"><a href="../controller/index.php?pg=phieunhap">Phiếu nhập</a></li>
                <?php endif; ?>

                <?php if ((bool) $ds_quyen['QLThongke']) : ?>
                <li><img src="../view/layout/logo-icon/thongke-icon.png"><a href="../controller/index.php?pg=thongke">Thống kê</a></li>
                <?php endif; ?>

                <?php if ((bool) $ds_quyen['QLTaiKhoan']) : ?>
                <li><img src="../view/layout/logo-icon/taikhoan-icon.png"><a href="../controller/index.php?pg=taikhoan">Tài khoản</a></li>
                <?php endif; ?>

                <?php if ((bool) $ds_quyen['QLPhanQuyen']) : ?>
                <li><img src="../view/layout/logo-icon/taikhoan-icon.png"><a href="../controller/index.php?pg=phanquyen">Phân quyền</a></li>
                <?php endif; ?>

                <li><img src="../view/layout/logo-icon/logout-icon.png"><a href="../controller/index.php?pg=dangxuat">Đăng xuất</a></li>
            </ul>
        </div>






