
    <!-- Main Content -->
        <div class="right-content">
            <div id="tab14" class="tab-content active">
                <h2>Danh sách Danh mục</h2>
                <div class="actions">
                    <input type="text" id="searchDM" name="search" placeholder="Nhập id hoặc tên để tìm kiếm...">
                    <button onclick="location.href='../controller/index.php?pg=themdanhmuc';">Thêm</button>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Tên Danh mục</th>
                            <th>Trạng thái</th>
                            <th>Xem và Thêm chi tiết</th>
                            <th>Sửa</th>
                            <th>Xóa</th>
                        </tr>
                    </thead>
                    <tbody id="category-table-body">
                        
                    </tbody>
                </table>

                <!-- Phân trang -->
                <div class="phanTrang" id="pagination">

                </div>
            </div>
        </div>
    </div>
    <script src="../view/layout/js/danhmuc.js"></script>
</body>

</html>
