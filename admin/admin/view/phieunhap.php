<?php
include_once '../../model/connectDB.php';
include_once '../../model/phieunhap.php';
?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
.btn-export-excel {
    background-color: #28a745 !important;
    color: white !important;
    border: none !important;
    border-radius: 4px !important;
    padding: 9px 16px !important;
    font-weight: bold !important;
    cursor: pointer !important;
    display: inline-flex !important;
    align-items: center !important;
    gap: 6px !important;
    transition: all 0.2s ease !important;
    width: auto !important;
    min-width: 125px !important;
}
.btn-export-excel:hover {
    background-color: #218838 !important;
    box-shadow: 0 2px 6px rgba(40,167,69,0.3) !important;
}
.btn-export-excel:active {
    transform: scale(0.98);
}
.btn-export-excel:disabled {
    opacity: 0.65;
    cursor: not-allowed !important;
}
.icon-export-single {
    color: #28a745;
    font-size: 20px;
    cursor: pointer;
    transition: transform 0.2s ease;
    display: inline-block;
}
.icon-export-single:hover {
    transform: scale(1.3);
    color: #218838;
}
.filter {
    align-items: center;
}
.filter button#filterBtn {
    width: auto !important;
    min-width: 70px !important;
    padding: 9px 16px !important;
}
</style>

        <!-- Main Content -->
        <div class="right-content">
            <div class="tab-content active">
                <h2>Danh sách Phiếu nhập</h2>
                <div class="actions">
                    <input type="text" id="searchPN" name="search" placeholder="Nhập mã phiếu nhập để tìm kiếm...">
                    <button onclick="location.href='../controller/index.php?pg=themphieunhap';">Thêm</button>
                </div>
                <!-- Bộ lọc -->
                <div class="filter">
                    <div class="filter-item">
                        <label for="startDate">Từ ngày:</label>
                        <input type="date" id="start" name="start" placeholder="Từ ngày">
                    </div>
                    <div class="filter-item">
                        <label for="endDate">Đến ngày:</label>
                        <input type="date" id="end" name="end" placeholder="Đến ngày">
                    </div>
                    <button id="filterBtn" onclick="applyFiltersPN()">Lọc</button>
                    <button id="btnExportAllPN" type="button" class="btn-export-excel" onclick="exportDanhSachPhieuNhap()" title="Xuất danh sách phiếu nhập ra file Excel">
                        <i class="fas fa-file-excel"></i> Xuất Excel
                    </button>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nhà cung cấp</th>
                            <th>Nhân viên</th>
                            <th>Ngày nhập</th>
                            <th>Tổng tiền</th>
                            <th>Trạng thái</th>
                            <th>Xem chi tiết</th>
                            <th>Xuất Excel</th>
                            <th>Xóa</th>
                        </tr>
                    </thead>
                    <tbody id="receipt-table-body">
                        
                    </tbody>
                </table>

                <!-- Phân trang -->
                <div class="phanTrang" id="pagination">
                </div>
            </div>
        </div>
    </div>
    <script src="../view/layout/js/xlsx.full.min.js"></script>
    <script>
    if (typeof XLSX === 'undefined') {
        document.write('<script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"><\/script>');
    }
    </script>
    <script src="../view/layout/js/phieunhap.js?v=2.1"></script>
</body>

</html>
