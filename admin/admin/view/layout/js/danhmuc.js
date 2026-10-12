function loadPage(page){
    fetch(`../controller/pagDanhmuc.php?page=${page}`)
    .then(response =>{
        if(!response.ok){
            throw new Error("Network response was not ok");

        }
        return response.json();
    })
    .then(data =>{
        const tbody = document.getElementById("category-table-body");
        tbody.replaceChildren();
        //thêm lại dữ liệu mới 
        data.data.forEach(category=>{
            const row = `
            <tr>
                <td>${category.iddanhmuc}</td>
                <td>${category.tendanhmuc}</td>
                <td>${category.trangthai == 1 ? "Hoạt động" : "Tạm khóa"}</td>
                <td>
                    <a href="../controller/index.php?pg=chitietdanhmuc&iddanhmuc=${category.iddanhmuc}">
                        <img src="../view/layout/logo-icon/detail-icon.png" alt="Xem chi tiết" style="width:20px; height:20px;">
                    </a>
                </td>
                <td>
                    <a href="../controller/index.php?pg=suadanhmuc&iddanhmuc=${category.iddanhmuc}">
                        <img src="../view/layout/logo-icon/update-icon.png" alt="Sửa" style="width:20px; height:20px;">

                    </a>
                </td>
                <td>
                    <a href="../controller/index.php?pg=xoadanhmuc&iddanhmuc=${category.iddanhmuc}" onclick="return confirm('Bạn có chắc muốn xóa danh mục ${category.tendanhmuc} không?')">
                        <img src="../view/layout/logo-icon/delete-icon.png" alt="Xóa" style="width:20px; height:20px;">
                    </a>
                </td>
            </tr>
            `;
            tbody.innerHTML += row;
        });

        //cập nhật phân trang
        const pagination = document.getElementById("pagination");
        pagination.innerHTML="";
        for(let i =1; i<= data.totalPages; i++){
            pagination.innerHTML += `
            <button data-page="${i}" ${i == data.currentPage ? 'class="active"' : ''}>${i}</button>
            `
        }

        //gắn sự kiện click cho các nút phân trang
        document.querySelectorAll("#pagination button").forEach(button =>{
            button.addEventListener("click", function(){
                const page = parseInt(this.getAttribute("data-page"));
                loadPage(page);
            });
        });
    })
    .catch(error =>console.error('Error fetching data: ', error));
}

function searchDanhMuc(keyword, page = 1) {
    fetch(`../controller/pagDanhmuc.php?search=${encodeURIComponent(keyword)}&page=${page}`)
        .then(response => {
            if (!response.ok) {
                throw new Error("Network response was not ok");
            }
            return response.json();
        })
        .then(data => {
            const tbody = document.getElementById("category-table-body");
            tbody.replaceChildren();

            if (data.data.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="6" style="text-align: center;">Không tìm thấy danh mục phù hợp!</td>
                    </tr>
                `;
                document.getElementById("pagination").innerHTML = "";
                return;
            }

            data.data.forEach(category => {
                const row = `
                <tr>
                    <td>${category.iddanhmuc}</td>
                    <td>${category.tendanhmuc}</td>
                    <td>${category.trangthai == 1 ? "Hoạt động" : "Tạm khóa"}</td>
                    <td>
                        <a href="../controller/index.php?pg=chitietdanhmuc&iddanhmuc=${category.iddanhmuc}">
                            <img src="../view/layout/logo-icon/detail-icon.png" alt="Xem chi tiết" style="width:20px; height:20px;">
                        </a>
                    </td>
                    <td>
                        <a href="../controller/index.php?pg=suadanhmuc&iddanhmuc=${category.iddanhmuc}">
                            <img src="../view/layout/logo-icon/update-icon.png" alt="Sửa" style="width:20px; height:20px;">
                        </a>
                    </td>
                    <td>
                        <a href="../controller/index.php?pg=xoadanhmuc&iddanhmuc=${category.iddanhmuc}" onclick="return confirm('Bạn có chắc muốn xóa danh mục ${category.tendanhmuc} không?')">
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
                    <button data-page="${i}" ${i == data.currentPage ? 'class="active"' : ''}>${i}</button>
                `;
            }

            // Gắn sự kiện click cho các nút phân trang
            document.querySelectorAll("#pagination button").forEach(button => {
                button.addEventListener("click", function () {
                    const page = parseInt(this.getAttribute("data-page"));
                    searchDanhMuc(keyword, page);
                });
            });
        })
        .catch(error => console.error("Error fetching search data:", error));
}

//tải lại trang đầu tiên khi trang được tải
document.addEventListener("DOMContentLoaded", function(){
    loadPage(1);

    // Tìm kiếm danh mục
    document.getElementById("searchDM").addEventListener("input", function () {
        const keyword = this.value.trim();
        if (keyword !== "") {
            searchDanhMuc(keyword);
        } else {
            loadPage(1);
        }
    });
});
