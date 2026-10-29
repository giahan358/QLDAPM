function loadProducts(page) {
    fetch(`../controller/pagination.php?page=${page}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            const tbody = document.getElementById("product-table-body");
            tbody.innerHTML = ''; // Xóa dữ liệu cũ

            // Thêm dữ liệu mới vào bảng
            data.data.forEach(product => {
                const row = `
                    <tr>
                        <td><img src="../../${product.anhbia}" width="50" alt="Ảnh bìa"></td>
                        <td>${product.idsach}</td>
                        <td>${product.tensach}</td>
                        <td>${product.idtacgia}</td>
                        <td>${product.idnhaxuatban}</td>
                        <td>${product.idctdanhmuc}</td>
                        <td>${Number(product.gia).toLocaleString('vi-VN')}đ</td>
                        <td>${product.sltonkho}</td>
                        <td>${product.mota}</td>
                        <td><button type="button" data-product-reviews="${Number(product.idsach)}">${Number(product.review_count)||0} đánh giá${product.review_average ? " · "+Number(product.review_average)+" ★" : ""}</button></td>
                        <td>${product.sltonkho == 0 ? "Hết hàng" : (product.trangthai == 1 ? "Còn hàng" : "Tạm ngưng")}</td>
                        <td>
                            <a href="../controller/index.php?pg=suasach&idsach=${product.idsach}">
                                <img src="../view/layout/logo-icon/update-icon.png" alt="Sửa" style="width:20px; height:20px;">
                            </a>
                        </td>
                        <td>
                            <a href="../controller/index.php?pg=xoasach&idsach=${product.idsach}" onclick="return confirm('Bạn có chắc muốn xóa sách ${product.tensach} không?')">
                                <img src="../view/layout/logo-icon/delete-icon.png" alt="Xóa" style="width:20px; height:20px;">
                            </a>
                        </td>
                    </tr>
                `;
                tbody.innerHTML += row;
            });

            // Cập nhật phân trang
            const pagination = document.getElementById("pagination");
            pagination.innerHTML = "";
            for (let i = 1; i <= data.totalPages; i++) {
                pagination.innerHTML += `
                    <button data-page="${i}" ${i === data.currentPage ? 'class="active"' : ''}>${i}</button>
                `;
            }

            // Gán sự kiện click cho các nút phân trang
            document.querySelectorAll("#pagination button").forEach(button => {
                button.addEventListener("click", function () {
                    const page = parseInt(this.getAttribute("data-page"));
                    loadProducts(page);
                });
            });
        })
        .catch(error => console.error('Error fetching data:', error));
}

function searchProducts(keyword, page = 1) {
    fetch(`../controller/pagination.php?search=${encodeURIComponent(keyword)}&page=${page}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            const tbody = document.getElementById("product-table-body");
            tbody.innerHTML = ''; // Xóa dữ liệu cũ

            if (data.data.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="13" style="text-align:center;">Không tìm thấy sản phẩm phù hợp!</td>
                    </tr>
                `;

                // Xóa phân trang nếu không có kết quả
                document.getElementById("pagination").innerHTML = "";
                return;
            }

            // Thêm dữ liệu mới vào bảng
            data.data.forEach(product => {
                const row = `
                    <tr>
                        <td><img src="../../${product.anhbia}" width="50" alt="Ảnh bìa"></td>
                        <td>${product.idsach}</td>
                        <td>${product.tensach}</td>
                        <td>${product.idtacgia}</td>
                        <td>${product.idnhaxuatban}</td>
                        <td>${product.idctdanhmuc}</td>
                        <td>${Number(product.gia).toLocaleString('vi-VN')}đ</td>
                        <td>${product.sltonkho}</td>
                        <td>${product.mota}</td>
                        <td><button type="button" data-product-reviews="${Number(product.idsach)}">${Number(product.review_count)||0} đánh giá${product.review_average ? " · "+Number(product.review_average)+" ★" : ""}</button></td>
                        <td>${product.sltonkho == 0 ? "Hết hàng" : (product.trangthai == 1 ? "Còn hàng" : "Tạm ngưng")}</td>
                        <td>
                            <a href="../controller/index.php?pg=suasach&idsach=${product.idsach}">
                                <img src="../view/layout/logo-icon/update-icon.png" alt="Sửa" style="width:20px; height:20px;">
                            </a>
                        </td>
                        <td>
                            <a href="../controller/index.php?pg=xoasach&idsach=${product.idsach}" onclick="return confirm('Bạn có chắc muốn xóa sách ${product.tensach} không?')">
                                <img src="../view/layout/logo-icon/delete-icon.png" alt="Xóa" style="width:20px; height:20px;">
                            </a>
                        </td>
                    </tr>
                `;
                tbody.innerHTML += row;
            });

            // Cập nhật phân trang
            const pagination = document.getElementById("pagination");
            pagination.innerHTML = "";
            for (let i = 1; i <= data.totalPages; i++) {
                pagination.innerHTML += `
                    <button data-page="${i}" ${i === data.currentPage ? 'class="active"' : ''}>${i}</button>
                `;
            }

            // Gán sự kiện click cho các nút phân trang
            document.querySelectorAll("#pagination button").forEach(button => {
                button.addEventListener("click", function () {
                    const page = parseInt(this.getAttribute("data-page"));
                    searchProducts(keyword, page); // gọi lại tìm kiếm cho trang mới
                });
            });
        })
        .catch(error => console.error('Lỗi khi tìm kiếm:', error));
}

// Tìm kiếm sản phẩm
document.getElementById("searchSP").addEventListener("input", function () {
    const keyword = this.value.trim();
    if (keyword !== "") {
        searchProducts(keyword);
    } else {
        loadProducts(1);
    }
});
