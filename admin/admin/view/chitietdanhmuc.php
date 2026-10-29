              
        <!-- Main Content -->
        <div class="right-content">
            <div id="tab14" class="tab-content active">
                <h2 id="title-danhmuc"></h2>
                <div class="actions">
                    <input type="text" id="search-ctdm" name="search" placeholder="Tìm kiếm...">
                    <button onclick="location.href='../controller/index.php?pg=themctdanhmuc&iddanhmuc=<?php echo htmlspecialchars($_GET['iddanhmuc']); ?>';">Thêm</button>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Danh mục</th>
                            <th>Tên chi tiết danh mục</th>
                        </tr>
                    </thead>
                    <tbody id="ctdm-table-body">
                       
                    </tbody>
                </table>

                <!-- Phân trang -->
                <div class="phanTrang" id="pagination">

                </div>
            </div>
        </div>
    </div>

    <script src="../view/layout/js/ctdanhmuc.js"></script>
</body>

</html>