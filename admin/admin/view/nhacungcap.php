
        <!-- Main Content -->
        <div class="right-content">
            <div class="tab-content active">
                <h2>Danh sách Nhà cung cấp</h2>
                <div class="actions">
                    <input type="text" id="searchNCC" name="search" placeholder="Tìm kiếm...">
                    <button onclick="location.href='../controller/index.php?pg=themnhacungcap';">Thêm</button>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Tên nhà cung cấp</th>
                            <th>Email</th>
                            <th>Số điện thoại</th>
                            <th>Địa chỉ</th>
                            <th>Trạng thái</th>
                            <th>Sửa</th>
                            <th>Xóa</th>
                        </tr>
                    </thead>
                    <tbody id="provider-table-body">
                       
                    </tbody>
                </table>
                <!-- Phân trang -->
                <div class="phanTrang" id="pagination">
                    
                </div>
            </div>
        </div>
    </div>
    <script src="../view/layout/js/nhacungcap.js"></script>
</body>
</html>
