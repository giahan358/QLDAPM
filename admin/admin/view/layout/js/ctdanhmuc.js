function loadPage(page) {
    let idDanhMuc = new URLSearchParams(window.location.search).get("iddanhmuc"); // Lấy idDanhMuc từ URL
    fetch(`../controller/pagCTDanhMuc.php?page=${page}&iddanhmuc=${idDanhMuc}`)
        .then(response => response.json())
        .then(data => {
            const tbody = document.getElementById("ctdm-table-body");
            tbody.replaceChildren();
            
            // Lặp qua dữ liệu chi tiết danh mục
            data.data.forEach(ctdm => {
                const row = `
                <tr>
                    <td>${ctdm.idchitietdanhmuc}</td>
                    <td>${ctdm.iddanhmuc} - ${data.tenDanhMuc}</td>  <!-- Hiển thị idDanhMuc và tên danh mục -->
                    <td>${ctdm.tenchitietdanhmuc}</td>
                </tr>
                `;
                tbody.innerHTML += row;
            });

            // Cập nhật phân trang
            const pagination = document.getElementById("pagination");
            pagination.innerHTML = "";
            for (let i = 1; i <= data.totalPages; i++) {
                pagination.innerHTML += `
                <button data-page="${i}" ${i == data.currentPage ? 'class="active"' : ''}>${i}</button>
                `;
            }

            // Gắn sự kiện click cho các nút phân trang
            document.querySelectorAll("#pagination button").forEach(button => {
                button.addEventListener("click", function () {
                    const page = parseInt(this.getAttribute("data-page"));
                    loadPage(page);
                });
            });
        })
        .catch(error => console.error('Error fetching data: ', error));
}

function updateTitleDanhMuc(idDanhMuc) {
    let titleElement = document.getElementById("title-danhmuc");

    if (!idDanhMuc) {
        titleElement.textContent = "Chi tiết Danh mục: Tất cả";
    } else {
        fetch(`../controller/pagCTDanhMuc.php?iddanhmuc=${idDanhMuc}`)
            .then(response => response.json())
            .then(data => {
                titleElement.textContent = `Chi tiết Danh mục: ${data.tenDanhMuc || 'Không xác định'}`;
            })
            .catch(error => {
                console.error("Lỗi khi tải danh mục: ", error);
                titleElement.textContent = "Chi tiết Danh mục: Không xác định";
            });
    }
}

function searchCTDM(keyword, page = 1) {
    let idDanhMuc = new URLSearchParams(window.location.search).get("iddanhmuc");

    fetch(`../controller/pagCTDanhMuc.php?iddanhmuc=${idDanhMuc}&search=${encodeURIComponent(keyword)}&page=${page}`)
        .then(response => response.json())
        .then(data => {
            const tbody = document.getElementById("ctdm-table-body");
            tbody.replaceChildren();

            if (data.data && data.data.length > 0) {
                data.data.forEach(ctdm => {
                    const row = `
                    <tr>
                        <td>${ctdm.idchitietdanhmuc}</td>
                        <td>${ctdm.iddanhmuc} - ${data.tenDanhMuc}</td>
                        <td>${ctdm.tenchitietdanhmuc}</td>
                    </tr>
                    `;
                    tbody.innerHTML += row;
                });
            } else {
                tbody.innerHTML = `<tr><td colspan="3" style="text-align: center;">Không tìm thấy chi tiết danh mục phù hợp!</td></tr>`;
            }

            // Cập nhật phân trang
            const pagination = document.getElementById("pagination");
            pagination.innerHTML = "";
            for (let i = 1; i <= data.totalPages; i++) {
                pagination.innerHTML += `
                <button data-page="${i}" ${i == data.currentPage ? 'class="active"' : ''}>${i}</button>
                `;
            }

            // Gắn sự kiện click cho các nút phân trang trong tìm kiếm
            document.querySelectorAll("#pagination button").forEach(button => {
                button.addEventListener("click", function () {
                    const newPage = parseInt(this.getAttribute("data-page"));
                    searchCTDM(keyword, newPage);
                });
            });
        })
        .catch(error => {
            console.error('Lỗi khi tìm kiếm: ', error);
            document.getElementById("ctdm-table-body").innerHTML = `<tr><td colspan="3">Đã xảy ra lỗi khi tìm kiếm.</td></tr>`;
        });
}

//tải lại trang đầu tiên khi trang được tải
document.addEventListener("DOMContentLoaded", function(){
    loadPage(1);
    let idDanhMuc = new URLSearchParams(window.location.search).get("iddanhmuc");
    updateTitleDanhMuc(idDanhMuc);

    // Tìm kiếm chi tiết danh mục
    document.getElementById("search-ctdm").addEventListener("input", function () {
        const keyword = this.value.trim();
        if (keyword !== "") {
            searchCTDM(keyword);
        } else {
            loadPage(1);
        }
    });
});
