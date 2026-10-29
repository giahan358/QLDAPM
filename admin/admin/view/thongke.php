<?php
    include_once '../../model/connectDB.php';
    include_once '../../model/hoadon.php';
    $maxKhachHang = getSoLuongKhachMuaHang();
?>
<link rel="stylesheet" href="../view/layout/css/thongke.css?v=classic-4">
<link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<div class="container my-4 main-container">
    <h2 class="text-center">Thống Kê</h2>
    <form action="../controller/index.php?pg=thongke" method="POST" id="thongke-form">
        <div class="form-group">
            <label for="start-date">Ngày Bắt Đầu</label>
            <input type="date" class="form-control" id="start-date" name="start-date" required>
            <label for="end-date">Ngày Kết Thúc</label>
            <input type="date" class="form-control" id="end-date" name="end-date" required>
            <label for="top-kh" class="mt-2">Số lượng khách hàng cần liệt kê</label>
            <input type="number" class="form-control" id="top-kh" name="top-kh" min="1" max="<?=max(1,$maxKhachHang)?>" value="<?=max(1,$maxKhachHang)?>" required>
            <label for="sort-order" class="mt-2">Sắp xếp theo tổng mua</label>
            <select class="form-control" id="sort-order" name="sort-order" required>
                <option value="DESC">Giảm dần</option>
                <option value="ASC">Tăng dần</option>
            </select>
            <button type="submit" class="btn btn-primary" id="btn-thongke"><i class="fas fa-chart-bar mr-1"></i> Thống Kê</button>
            <button type="button" class="btn btn-success" id="btn-export-excel"><i class="fas fa-file-excel mr-1"></i> Xuất Excel</button>
        </div>
    </form>
    <div class="chart-container"><canvas id="myChart"></canvas></div>
    <div class="table-responsive my-4 table-scroll">
        <table class="table table-bordered fixed-header" id="table-thongke">
            <thead class="text-primary"><tr><th>Khách Hàng</th><th>Đơn Hàng</th><th>Tổng Đơn Hàng</th><th>Tổng Mua</th></tr></thead>
            <tbody id="result-table"></tbody>
        </table>
    </div>
</div>
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="../view/layout/js/xlsx.full.min.js"></script>
<script>
if (typeof XLSX === 'undefined') {
    document.write('<script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"><\/script>');
}
</script>
<script src="../view/layout/js/thongke.js?v=classic-4"></script>
</body>
</html>
