// Gọi hàm khi trang được load
document.addEventListener("DOMContentLoaded", function () {
    loadProducts(1); // Trang đầu tiên
});

// Hàm hiển thị danh sách sản phẩm
function loadProducts(page) {
    fetch(`../controller/pagSanPhamPN.php?page=${page}`)
        .then(response => response.json())
        .then(data => {
            const tbody = document.getElementById("product-table-body");
            tbody.innerHTML = '';

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
                        <td>${product.sltonkho == 0 ? "Hết hàng" : (product.trangthai == 1 ? "Còn hàng" : "Tạm khóa")}</td>
                        <td>
                            <button class="choose-btn" data-product='${JSON.stringify(product)}' style="background:none; border:none; cursor:pointer;">
                                <img src="../view/layout/logo-icon/add-icon.png" alt="Chọn" style="width:20px;">
                            </button>
                        </td>
                    </tr>
                `;
                tbody.innerHTML += row;
            });

            addChooseEvent(); // Gắn sự kiện click chọn sản phẩm
        })
        .catch(error => console.error('Lỗi tải sản phẩm:', error));
}

// Hàm gắn sự kiện cho nút chọn sản phẩm
function addChooseEvent() {
    document.querySelectorAll(".choose-btn").forEach(button => {
        button.addEventListener("click", function () {
            const product = JSON.parse(this.getAttribute("data-product"));
            fillFormWithProduct(product);
        });
    });
}

// Hàm đẩy dữ liệu vào form
function fillFormWithProduct(product) {
    document.getElementById("IDSach").value = product.idsach || '';
    document.getElementById("tenSach").value = product.tensach || '';
    document.getElementById("tacGia").value = product.idtacgia || '';
    document.getElementById("nxb").value = product.idnhaxuatban || '';
    document.getElementById("danhMuc").value = product.idctdanhmuc || '';
}


