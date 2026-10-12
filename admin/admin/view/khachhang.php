
        <!-- Main Content -->
        <div class="right-content">
            <div class="tabs">
                <button class="tab-button active" data-tab="tab7">Khách hàng</button>
                <button class="tab-button" data-tab="tab8">Tài khoản khách hàng</button>
            </div>
            <div id="tab7" class="tab-content active">
                <h2>Danh sách Khách hàng</h2>
                <div class="actions">
                    <input type="text" id="searchKH" name="search" placeholder="Tìm kiếm...">
                    <button onclick="location.href='../controller/index.php?pg=themkhachhang';">Thêm</button>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Họ tên</th>
                            <th>Email</th>
                            <th>Số điện thoại</th>
                            <th>Trạng thái</th>
                            <th>Sửa</th>
                            <th>Xóa</th>
                        </tr>
                    </thead>
                    <tbody id="customer-table-body">
                        
                    </tbody>
                </table>

                <!-- Phân trang -->
                <div class="phanTrang" id="pagination">
                    
                </div>
            </div>

            <div id="tab8" class="tab-content">
                <h2>Tài khoản Khách hàng</h2>
                <div class="actions">
                    <input type="text" id="searchTKKH" name="search" placeholder="Tìm kiếm...">
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>Mã khách hàng</th>
                            <th>Tên đăng nhập</th>
                            <th>Trạng thái</th>
                            <th>Xóa</th>
                        </tr>
                    </thead>
                    <tbody id="accountCustomer-table-body">
                        
                    </tbody>
                </table>

                <!-- Phân trang -->
                <div class="phanTrang" id="pagination_accountCustomer">
                    
                </div>
            </div>
        </div>
    </div>
    <script src="../view/layout/js/khachhang.js"></script>
    <script src="../view/layout/js/taikhoankhachhang.js"></script>
    <script>
    // document.querySelectorAll(".tab-button").forEach(button => {
    //     button.addEventListener("click", function () {
    //         const tabId = this.getAttribute("data-tab");
    //         document.querySelectorAll(".tab-content").forEach(tab => tab.classList.remove("active"));
    //         document.getElementById(tabId).classList.add("active");
    //         if (tabId === "tab7") loadCustomer(1);
    //         if (tabId === "tab8") loadTaiKhoanKhachHang(1);

    //     });
    // });
    // // tab mặc định
    // loadCustomer(1);

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

            const newUrl = `${window.location.pathname}?pg=khachhang&tabId=${tabId}`;
            history.replaceState(null, "", newUrl);

            // Gọi hàm tương ứng
            if (tabId === "tab7") loadCustomer(1);
            if (tabId === "tab8") loadTaiKhoanKhachHang(1);
        });
    });

    // Nếu có tabId từ URL, tự động click nút tương ứng
    if (tabIdFromUrl) {
        const button = document.querySelector(`.tab-button[data-tab="${tabIdFromUrl}"]`);
        if (button) button.click();
    } else {
        // Mặc định là tab khách hàng
        document.querySelector(`.tab-button[data-tab="tab7"]`).click();
    }
</script>

</body>

</html>
