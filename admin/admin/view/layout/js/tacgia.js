function loadAuthor(page) {
    fetch(`../controller/pagTacGia.php?page=${page}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            const tbody = document.getElementById("author-table-body");
            tbody.innerHTML = ''; // Xóa dữ liệu cũ

            // Thêm dữ liệu mới vào bảng
            data.data.forEach(author => {
                const row = `
                    <tr>
                        <td>${author.idtacgia}</td>
                        <td>${author.tentacgia}</td>
                        <td>${author.tieusu}</td>
                        <td>${author.trangthai == 1 ? "Hoạt động" : "Tạm khóa"}</td>
                        <td>
                            <a href="../controller/index.php?pg=suatacgia&idtacgia=${author.idtacgia}">
                                <img src="../view/layout/logo-icon/update-icon.png" alt="Sửa" style="width:20px; height:20px;">
                            </a>
                        </td>
                        <td>
                            <a href="../controller/index.php?pg=xoatacgia&idtacgia=${author.idtacgia}" onclick="return confirm('Bạn có chắc muốn xóa tác giả ${author.tentacgia} không?')">
                                <img src="../view/layout/logo-icon/delete-icon.png" alt="Xóa" style="width:20px; height:20px;">
                            </a>
                        </td>
                    </tr>
                `;
                tbody.innerHTML += row;
            });

            // Cập nhật phân trang
            const pagination = document.getElementById("pagination_author");
            pagination.innerHTML = "";
            for (let i = 1; i <= data.totalPages; i++) {
                pagination.innerHTML += `
                    <button data-page="${i}" ${i === data.currentPage ? 'class="active"' : ''}>${i}</button>
                `;
            }

            // Gán sự kiện click cho các nút phân trang
            document.querySelectorAll("#pagination_author button").forEach(button => {
                button.addEventListener("click", function () {
                    const page = parseInt(this.getAttribute("data-page"));
                    loadAuthor(page);
                });
            });
        })
        .catch(error => console.error('Error fetching data:', error));
}

function searchTacGia(keyword, page = 1) {
    fetch(`../controller/pagTacGia.php?search=${encodeURIComponent(keyword)}&page=${page}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            const tbody = document.getElementById("author-table-body");
            tbody.innerHTML = '';

            if (data.data.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="6" style="text-align:center;">Không tìm thấy tác giả phù hợp!</td>
                    </tr>
                `;
                document.getElementById("pagination_author").innerHTML = '';
                return;
            }

            data.data.forEach(author => {
                const row = `
                    <tr>
                        <td>${author.idtacgia}</td>
                        <td>${author.tentacgia}</td>
                        <td>${author.tieusu}</td>
                        <td>${author.trangthai == 1 ? "Hoạt động" : "Tạm khóa"}</td>
                        <td>
                            <a href="../controller/index.php?pg=suatacgia&idtacgia=${author.idtacgia}">
                                <img src="../view/layout/logo-icon/update-icon.png" alt="Sửa" style="width:20px; height:20px;">
                            </a>
                        </td>
                        <td>
                            <a href="../controller/index.php?pg=xoatacgia&idtacgia=${author.idtacgia}" onclick="return confirm('Bạn có chắc muốn xóa tác giả ${author.tentacgia} không?')">
                                <img src="../view/layout/logo-icon/delete-icon.png" alt="Xóa" style="width:20px; height:20px;">
                            </a>
                        </td>
                    </tr>
                `;
                tbody.innerHTML += row;
            });

            // Cập nhật phân trang
            const pagination = document.getElementById("pagination_author");
            pagination.innerHTML = "";
            for (let i = 1; i <= data.totalPages; i++) {
                pagination.innerHTML += `
                    <button data-page="${i}" ${i === data.currentPage ? 'class="active"' : ''}>${i}</button>
                `;
            }

            // Gán sự kiện click cho các nút phân trang
            document.querySelectorAll("#pagination_author button").forEach(button => {
                button.addEventListener("click", function () {
                    const page = parseInt(this.getAttribute("data-page"));
                    searchTacGia(keyword, page); // ← gọi lại chính xác
                });
            });
        })
        .catch(error => console.error('Error fetching data:', error));
}

// Tìm kiếm tác giả
document.getElementById("searchTG").addEventListener("input", function () {
    const keyword = this.value.trim();
    if (keyword !== "") {
        searchTacGia(keyword);
    } else {
        loadAuthor(1);
    }
});


