<?php
    include_once '../../model/connectDB.php';
    include_once '../../model/cuahang.php';

    $BannerData = getBanner();
    $CuaHangData = getCuaHang();
?>
        <!-- Main Content -->
        <div class="right-content">
            <div class="tabs" name="activeTab">
                <button class="tab-button active" data-tab="tab1">Thông tin Cửa hàng & Chuyển khoản</button>
                <button class="tab-button" data-tab="tab15">Banner</button>
            </div>

            <div id="tab1" class="tab-content active">
                <h2>Thông tin Cửa hàng</h2>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Địa chỉ</th>
                            <th>Số điện thoại</th>
                            <th>Email</th>
                            <th>Facebook</th>
                            <th>TikTok</th>
                            <th>Sửa</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($CuaHangData as $cuaHang): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($cuaHang['idthongtin']); ?></td>
                                <td><?php echo htmlspecialchars($cuaHang['diachi']); ?></td>
                                <td><?php echo htmlspecialchars($cuaHang['sodienthoai']); ?></td>
                                <td><?php echo htmlspecialchars($cuaHang['email']); ?></td>
                                <td><?php echo htmlspecialchars($cuaHang['facebook']); ?></td>
                                <td><?php echo htmlspecialchars($cuaHang['tiktok']); ?></td>
                                <td>
                                    <a href="../controller/index.php?pg=suacuahang&idthongtin=<?php echo htmlspecialchars($cuaHang['idthongtin']);?>">
                                        <img src="../view/layout/logo-icon/update-icon.png" alt="Sửa" style="width:20px; height:20px;">
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <!-- <h2 style="margin-top: 100px;">Thông tin Chuyển khoản</h2>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Tên ngân hàng</th>
                            <th>Số tài khoản</th>
                            <th>Tên chủ tài khoản</th>
                            <th>QR</th>
                            <th>Sửa</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($CuaHangData as $cuaHang): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($cuaHang['idthongtin']); ?></td>
                                <td><?php echo htmlspecialchars($cuaHang['tenNH']); ?></td>
                                <td><?php echo htmlspecialchars($cuaHang['stk']); ?></td>
                                <td><?php echo htmlspecialchars($cuaHang['tenChuTK']); ?></td>
                                <td>
                                    <img src="../../<?php echo htmlspecialchars($cuaHang['anhQrCk']); ?>" 
                                        alt="QR" style="width: 100px; height: auto;">
                                </td>
                                <td>
                                    <a href="../controller/index.php?pg=suachuyenkhoan&idthongtin=<?php echo htmlspecialchars($cuaHang['idthongtin']);?>">
                                        <img src="../view/layout/logo-icon/update-icon.png" alt="Sửa" style="width:20px; height:20px;">
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table> -->
            </div>

            <div id="tab15" class="tab-content">
                <h2>Danh sách Banner</h2>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Hình ảnh</th>
                            <th>Mô tả</th>
                            <th>Sửa</th>
                            <th>Xóa</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($BannerData as $banner): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($banner['idbanner']); ?></td>
                                <td>
                                    <img src="../../<?php echo htmlspecialchars($banner['hinhanh']); ?>" 
                                        alt="Banner" style="width: 100px; height: auto;">
                                </td>
                                <td><?php echo htmlspecialchars($banner['mota']); ?></td>
                                <td>
                                    <a href="../controller/index.php?pg=suabanner&idbanner=<?php echo htmlspecialchars($banner['idbanner']); ?>">
                                        <img src="../view/layout/logo-icon/update-icon.png" alt="Sửa" style="width:20px; height:20px;">
                                    </a>
                                </td>
                                <td>
                                    <a href="../controller/index.php?pg=xoabanner&idbanner=<?php echo htmlspecialchars($banner['idbanner']); ?>" 
                                    onclick="return confirm('Bạn có chắc chắn muốn xóa không?')">
                                        <img src="../view/layout/logo-icon/delete-icon.png" alt="Xóa" style="width:20px; height:20px;">
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</body>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        const tabButtons = document.querySelectorAll(".tab-button");
        const tabContents = document.querySelectorAll(".tab-content");

        // Hàm chuyển tab
        function showTab(tabId) {
            tabButtons.forEach(button => {
                if (button.getAttribute("data-tab") === tabId) {
                    button.classList.add("active");
                } else {
                    button.classList.remove("active");
                }
            });

            tabContents.forEach(content => {
                if (content.id === tabId) {
                    content.classList.add("active");
                } else {
                    content.classList.remove("active");
                }
            });
        }

        // Bắt sự kiện click tab
        tabButtons.forEach(button => {
            button.addEventListener("click", function () {
                const tabId = this.getAttribute("data-tab");
                const newUrl = new URL(window.location.href);
                newUrl.searchParams.set("tabId", tabId);
                history.replaceState(null, "", newUrl.toString());
                showTab(tabId);
            });
        });

        // Lấy tab từ URL, nếu không có thì gán mặc định
        const urlParams = new URLSearchParams(window.location.search);
        let tabFromUrl = urlParams.get("tabId");

        if (!tabFromUrl) {
            // Nếu không có tabId, set URL lại và chọn mặc định tab1
            const newUrl = new URL(window.location.href);
            newUrl.searchParams.set("tabId", "tab1");
            history.replaceState(null, "", newUrl.toString());
            tabFromUrl = "tab1";
        }

        showTab(tabFromUrl);
    });
</script>

</html>
