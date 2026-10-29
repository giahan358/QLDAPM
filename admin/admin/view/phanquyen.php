<?php 
    include_once '../controller/pagPhanQuyen.php';
?>

        <link rel="stylesheet" href="../view/layout/css/phanquyen.css">
        <!-- Bootstrap CSS -->
        <!-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"> -->
        <!-- FontAwesome -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

        <div class="container table-container">
            <!-- FORM THÊM MỚI VAI TRÒ -->
            <form id="formThemVaiTro" method="post" action="">
                <h4>Thêm mới nhóm quyền</h4>
                <div class="mb-3">
                    <label for="ten_quyen">Tên nhóm quyền:</label>
                    <input type="text" name="ten_quyen" id="ten_quyen" required class="form-control">
                </div>
                <button type="submit" name="add_quyen" class="btn btn-success">Thêm nhóm quyền</button>
            </form>
            <hr>

            <h2 class="text-center">Danh sách Quyền</h2>
            <input type="text" id="searchQuyen" name="search" class="form-control w-25" placeholder="Nhập tên quyền để tìm kiếm..."
                onkeyup="searchQuyen()">
            <div class="table-container-custom">
                <table class="table table-striped table-bordered table-head">
                    <thead class="table-primary">
                        <tr>
                            <th>Hành động</th>
                            <th>Tên quyền</th>
                            <th>QL Cửa Hàng</th>
                            <th>QL Sản Phẩm</th>
                            <th>QL Danh Mục</th>
                            <th>QL Nhân Viên</th>
                            <th>QL Khách Hàng</th>
                            <th>QL Nhà Cung Cấp</th>
                            <th>QL Đơn Hàng</th>
                            <th>QL Phiếu Nhập</th>
                            <th>QL Thống Kê</th>
                            <th>QL Tài Khoản</th>
                            <th>QL Phân Quyền</th>
                        </tr>
                    </thead>
                </table>
                <div class="table-body-scroll">
                    <table class="table table-striped table-bordered table-body">
                        <tbody>
                            <?php foreach ($quyenData as $row): ?>
                            <form method="post" action="">
                                <tr>
                                    <td>
                                        <input type="hidden" name="quyen" value="<?= $row['Quyen'] ?>">
                                        <button type="submit" name="update_quyen" class="btn btn-info btn-action">
                                            <img src="../view/layout/logo-icon/update-icon.png" alt="Sửa" style="width:20px; height:20px;">
                                        </button>
                                        <button type="submit" name="delete_quyen" class="btn btn-danger btn-action"
                                            onclick="return confirmDelete('<?php echo htmlspecialchars($row['Quyen']); ?>');">
                                            <img src="../view/layout/logo-icon/delete-icon.png" alt="Xóa" style="width:20px; height:20px;">
                                        </button>
                                    </td>
                                    <td><?= htmlspecialchars($row['Quyen']) ?></td>
                                    <td><input type="checkbox" name="QLCuaHang" class="form-check-input" <?= $row['QLCuaHang'] ? 'checked' : '' ?>></td>
                                    <td><input type="checkbox" name="QLSanPham" class="form-check-input" <?= $row['QLSanPham'] ? 'checked' : '' ?>></td>
                                    <td><input type="checkbox" name="QLDanhMuc" class="form-check-input" <?= $row['QLDanhMuc'] ? 'checked' : '' ?>></td>
                                    <td><input type="checkbox" name="QLNhanVien" class="form-check-input" <?= $row['QLNhanVien'] ? 'checked' : '' ?>></td>
                                    <td><input type="checkbox" name="QLKhachHang" class="form-check-input" <?= $row['QLKhachHang'] ? 'checked' : '' ?>></td>
                                    <td><input type="checkbox" name="QLNhaCungCap" class="form-check-input" <?= $row['QLNhaCungCap'] ? 'checked' : '' ?>></td>
                                    <td><input type="checkbox" name="QLDonHang" class="form-check-input" <?= $row['QLDonHang'] ? 'checked' : '' ?>></td>
                                    <td><input type="checkbox" name="QLPhieuNhap" class="form-check-input" <?= $row['QLPhieuNhap'] ? 'checked' : '' ?>></td>
                                    <td><input type="checkbox" name="QLThongke" class="form-check-input" <?= $row['QLThongke'] ? 'checked' : '' ?>></td>
                                    <td><input type="checkbox" name="QLTaiKhoan" class="form-check-input" <?= $row['QLTaiKhoan'] ? 'checked' : '' ?>></td>
                                    <td><input type="checkbox" name="QLPhanQuyen" class="form-check-input" <?= $row['QLPhanQuyen'] ? 'checked' : '' ?>></td>
                                </tr>
                            </form>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="../view/layout/js/phanquyen.js"></script>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        window.addEventListener('load', () => {
            const headerTable = document.querySelector('.table-head');
            const bodyTable = document.querySelector('.table-body');

            if (!headerTable || !bodyTable) return;

            const headerCols = headerTable.querySelectorAll('thead th');
            const firstBodyRow = bodyTable.querySelector('tbody tr');
            if (!firstBodyRow) return;

            const bodyCols = firstBodyRow.querySelectorAll('td');

            headerCols.forEach((th, index) => {
                const bodyTd = bodyCols[index];
                if (bodyTd) {
                    const width = Math.max(th.offsetWidth, bodyTd.offsetWidth);
                    th.style.width = width + 'px';
                    bodyTd.style.width = width + 'px';
                }
            });
        });

        function confirmDelete(quyenName) {
            return confirm('Bạn có chắc muốn xóa nhóm quyền "' + quyenName + '" không?');
        }
    </script>

</body>

</html>
