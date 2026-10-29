
<!-- Main Content -->
<div class="right-content">
        <div class="tabs">
            <button class="tab-button active" data-tab="tab1">Sách</button>
            <!-- <button class="tab-button" data-tab="tab15">Thể loại</button> -->
            <button class="tab-button" data-tab="tab3">Tác giả</button>
            <button class="tab-button" data-tab="tab4">Nhà xuất bản</button>
            <!-- <button class="tab-button" data-tab="tab5">Hình ảnh sách</button> -->
        </div>

        <div id="tab1" class="tab-content active">
            <h2>Danh sách Sản phẩm</h2>
            <div class="actions">
                <input type="text" id="searchSP" name="search" placeholder="Nhập id hoặc tên để tìm kiếm...">
                <button onclick="location.href='../controller/index.php?pg=themsach';">Thêm</button>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>Hình ảnh</th>
                        <th>ID</th>
                        <th>Tên sách</th>
                        <th>Tác giả</th>
                        <th>Nhà xuất bản</th>
                        <th>Danh mục</th>
                        <th>Giá</th>
                        <th>Số lượng</th>
                        <th>Mô tả</th>
                        <th>Đánh giá</th>
                        <th>Trạng thái</th>
                        <th>Sửa</th>
                        <th>Xóa</th>
                    </tr>
                </thead>
                <tbody id="product-table-body">
                    <!-- Dữ liệu sẽ được tải động qua JavaScript -->
                </tbody>
            </table>

            <!-- Phân trang -->
            <div class="phanTrang" id="pagination">
                <!-- Nút phân trang sẽ được tạo động qua JavaScript -->
            </div>
        </div>

    <!-- <script src="../view/layout/js/sanpham.js"></script> -->


            <!-- <div id="tab15" class="tab-content">
                <h2>Danh sách Thể loại</h2>
                <div class="actions">
                    <input type="text" id="searchTL" name="search" placeholder="Nhập id hoặc tên để tìm kiếm...">
                    <button onclick="location.href='../controller/index.php?pg=themtheloai';">Thêm</button>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Tên thể loại</th>
                            <th>Trạng thái</th>
                            <th>Sửa</th>
                            <th>Xóa</th>
                        </tr>
                    </thead>
                    <tbody id="theloai-table-body">
                    </tbody>
                </table> -->

                <!-- Phân trang -->
                <!-- <div class="phanTrang" id="pagination-theloai">
                </div>
            </div> -->

            <div id="tab3" class="tab-content">
                <h2>Danh sách Tác giả</h2>
                <div class="actions">
                    <input type="text" id="searchTG" name="search" placeholder="Nhập id hoặc tên để tìm kiếm...">
                    <button onclick="location.href='../controller/index.php?pg=themtacgia';">Thêm</button>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Tên tác giả</th>
                            <th>Tiểu sử</th>
                            <th>Trạng thái</th>
                            <th>Sửa</th>
                            <th>Xóa</th>
                        </tr>
                    </thead>
                    <tbody id="author-table-body">
                        
                    </tbody>
                </table>

                <!-- Phân trang -->
                <div class="phanTrang" id="pagination_author">
                </div>
            </div>

            <div id="tab4" class="tab-content">
                <h2>Danh sách Nhà xuất bản</h2>
                <div class="actions">
                    <input type="text" id="searchNXB" name="search" placeholder="Tìm kiếm...">
                    <button onclick="location.href='../controller/index.php?pg=themnhaxuatban';">Thêm</button>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Tên nhà xuất bản</th>
                            <th>Email</th>
                            <th>Số điện thoại</th>
                            <th>Địa chỉ</th>
                            <th>Trạng thái</th>
                            <th>Sửa</th>
                            <th>Xóa</th>
                        </tr>
                    </thead>
                    <tbody id="publisher-table-body">
                    </tbody>
                </table>

                <!-- Phân trang -->
                <div class="phanTrang" id="pagination_publisher">
                </div>
            </div>
<!-- 
            <div id="tab5" class="tab-content">
                <h2>Danh sách Hình ảnh</h2>
                <div class="actions">
                    <input type="text" placeholder="Tìm kiếm...">
                    <button onclick="location.href='../controller/index.php?pg=themhinhanhsach';">Thêm</button>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Sản phẩm</th>
                            <th>Đường dẫn ảnh</th>
                            <th>Mô tả</th>
                            <th>Trạng thái</th>
                            <th>Sửa</th>
                            <th>Xóa</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>1</td>
                            <td>ABC</td>
                            <td>ABC</td>
                            <td>ABD</td>
                            <td>Hoạt động</td>
                            <td>
                                <a href="#">
                                    <img src="../view/layout/logo-icon/update-icon.png" alt="Sửa" style="width:20px; height:20px;">
                                </a>
                            </td>
                            <td>
                                <a href="#">
                                    <img src="../view/layout/logo-icon/delete-icon.png" alt="Xóa" style="width:20px; height:20px;">
                                </a>
                            </td>
                        </tr>
                    </tbody>
                </table> -->

                <!-- Phân trang -->
                <!-- <div class="phanTrang">
                    <button>1</button>
                    <button>2</button>
                </div>
            </div> -->
        </div>
    </div>
</body>

<?php include __DIR__.'/product-reviews-dialog.php'; ?>
<script src="../view/layout/js/sanpham.js?v=reviews-2"></script>
<script src="../view/layout/js/theloai.js"></script>
<script src="../view/layout/js/tacgia.js"></script>
<script src="../view/layout/js/nhaxuatban.js"></script>
<script>
    // document.querySelectorAll(".tab-button").forEach(button => {
    //     button.addEventListener("click", function () {
    //         const tabId = this.getAttribute("data-tab");
    //         document.querySelectorAll(".tab-content").forEach(tab => tab.classList.remove("active"));
    //         document.getElementById(tabId).classList.add("active");
    //         if (tabId === "tab1") loadProducts(1);
    //         if (tabId === "tab15") loadTheLoai(1);
    //         if (tabId === "tab3") loadAuthor(1);
    //         if (tabId === "tab4") loadPublisher(1);

    //     });
    // });
    // // tab mặc định
    // loadProducts(1);

    // Lấy tabId từ URL (nếu có)
    const urlParams = new URLSearchParams(window.location.search);
    const tabIdFromUrl = urlParams.get("tabId");

    // Gán sự kiện click cho các nút tab
    document.querySelectorAll(".tab-button").forEach(button => {
        button.addEventListener("click", function () {
            const tabId = this.getAttribute("data-tab");

            // Bỏ class active khỏi tất cả tab-content
            document.querySelectorAll(".tab-content").forEach(tab => tab.classList.remove("active"));
            // Hiện tab-content tương ứng
            document.getElementById(tabId).classList.add("active");

            // Bỏ class active khỏi tất cả nút tab
            document.querySelectorAll(".tab-button").forEach(btn => btn.classList.remove("active"));
            // Thêm class active cho nút được click
            this.classList.add("active");

            const newUrl = `${window.location.pathname}?pg=sanpham&tabId=${tabId}`;
            history.replaceState(null, "", newUrl);

            // Gọi hàm tương ứng
            if (tabId === "tab1") loadProducts(1);
            if (tabId === "tab15") loadTheLoai(1);
            if (tabId === "tab3") loadAuthor(1);
            if (tabId === "tab4") loadPublisher(1);
        });
    });

    // Nếu có tabId từ URL, tự động click nút tương ứng
    if (tabIdFromUrl) {
        const button = document.querySelector(`.tab-button[data-tab="${tabIdFromUrl}"]`);
        if (button) button.click();
    } else {
        // Mặc định là tab sản phẩm
        document.querySelector(`.tab-button[data-tab="tab1"]`).click();
    }
</script>

</html>
