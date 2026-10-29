<?php
include_once '../../model/connectDB.php';
include_once '../../model/thongtinnhanhang.php';

?>

        <!-- Main Content -->
        <div class="right-content">
            <div class="tab-content active">
                <h2>Danh sách Đơn hàng</h2>
                <?php include __DIR__.'/after-sales-panel.php'; ?>
                <div class="actions">
                    <input type="text" id="searchHD" name="search" placeholder="Tìm mã hóa đơn hoặc mã TT-...">
                </div>
                <!-- Bộ lọc -->
                <div class="filter">
                    <select id="filterStatus" name="status">
                        <option value="" selected>Tất cả trạng thái</option>
                        <option value="0">Chờ xác nhận</option>
                        <option value="1">Đang giao</option>
                        <option value="2">Đã giao thành công</option>
                        <option value="3">Đã hủy</option>
                        <option value="unpaid">VNPAY chưa hoàn tất</option>
                    </select>
                    <div class="filter-item">
                        <label for="startDate">Từ ngày:</label>
                        <input type="date" id="startDate" name="startDate" placeholder="Từ ngày">
                    </div>
                    <div class="filter-item">
                        <label for="endDate">Đến ngày:</label>
                        <input type="date" id="endDate" name="endDate" placeholder="Đến ngày">
                    </div>
                    <input style="width: 225px;" type="text" list="cityList" id="filterCity" name="city" placeholder="--- Chọn hoặc nhập TP/Tỉnh ---">
                    <datalist id="cityList">
                    <!-- JS sẽ tự động thêm các <option> tại đây -->
                    </datalist>
                    <button id="filterButton" onclick="applyFiltersHD()">Lọc</button>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Khách hàng</th>
                            <th>Địa chỉ</th>
                            <th>Nhân viên</th>
                            <th>Phương thức thanh toán</th>
                            <th>Ngày xuất</th>
                            <th>Tổng tiền</th>
                            <th>Trạng thái</th>
                            <th>Xét duyệt</th>
                        </tr>
                    </thead>
                    <tbody id="invoice-table-body">
                       
                    </tbody>
                </table>

                <!-- Phân trang -->
                <div class="phanTrang" id="pagination_invoice">
                </div>
            </div>
        </div>
    </div>

    <script src="../view/layout/js/hoadon.js?v=refund-dialog-8"></script>
</body>

</html>
