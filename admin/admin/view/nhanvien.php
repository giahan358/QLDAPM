
        <!-- Main Content -->
        <div class="right-content">
            <div class="tab-content active">
                <h2>Danh sách Nhân viên</h2>
                <div class="actions">
                    <input type="text" id="search-nv" name="search" placeholder="Tìm kiếm...">
                    <button onclick="location.href='../controller/index.php?pg=themnhanvien';">Thêm</button>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Họ tên</th>
                            <th>Email</th>
                            <th>Số điện thoại</th>
                            <th>Nhóm quyền</th>
                            <th>Trạng thái</th>
                            <th>Cấp tài khoản</th>
                            <th>Sửa</th>
                            <th>Xóa</th>
                        </tr>
                    </thead>
                    <tbody id="employee-table-body">
                       
                    </tbody>
                </table>

                <!-- Phân trang -->
                <div class="phanTrang" id="pagination">
                   
                </div>
            </div>
        </div>
    </div>
    <script src="../view/layout/js/nhanvien.js"></script>
</body>

</html>
