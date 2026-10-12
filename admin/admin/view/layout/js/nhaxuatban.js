function loadPublisher(page) {
    fetch(`../controller/pagNhaXuatBan.php?page=${page}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            const tbody = document.getElementById("publisher-table-body");
            tbody.innerHTML = ''; // Xóa dữ liệu cũ

            // Thêm dữ liệu mới vào bảng
            data.data.forEach(publisher => {
                const row = `
                    <tr>
                        <td>${publisher.idnhaxuatban}</td>
                        <td>${publisher.tennxb}</td>
                        <td>${publisher.email}</td>
                        <td>${publisher.sodienthoai}</td>
                        <td>${publisher.diachi}</td>
                        <td>${publisher.trangthai == 1 ? "Hoạt động" : "Tạm khóa"}</td>
                        <td>
                            <a href="../controller/index.php?pg=suanhaxuatban&idnhaxuatban=${publisher.idnhaxuatban}">
                                <img src="../view/layout/logo-icon/update-icon.png" alt="Sửa" style="width:20px; height:20px;">
                            </a>
                        </td>
                        <td>
                            <a href="../controller/index.php?pg=xoanhaxuatban&idnhaxuatban=${publisher.idnhaxuatban}" onclick="return confirm('Bạn có chắc muốn xóa nhà xuất bản ${publisher.tennxb} không?')">
                                <img src="../view/layout/logo-icon/delete-icon.png" alt="Xóa" style="width:20px; height:20px;">
                            </a>
                        </td>
                    </tr>
                `;
                tbody.innerHTML += row;
            });

            // Cập nhật phân trang
            const pagination = document.getElementById("pagination_publisher");
            pagination.innerHTML = "";
            for (let i = 1; i <= data.totalPages; i++) {
                pagination.innerHTML += `
                    <button data-page="${i}" ${i === data.currentPage ? 'class="active"' : ''}>${i}</button>
                `;
            }

            // Gán sự kiện click cho các nút phân trang
            document.querySelectorAll("#pagination_publisher button").forEach(button => {
                button.addEventListener("click", function () {
                    const page = parseInt(this.getAttribute("data-page"));
                    loadPublisher(page);
                });
            });
        })
        .catch(error => console.error('Error fetching data:', error));
}

function searchNXB(keyword, page = 1) {
    fetch(`../controller/pagNhaXuatBan.php?search=${encodeURIComponent(keyword)}&page=${page}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            const tbody = document.getElementById("publisher-table-body");
            tbody.innerHTML = '';

            if (data.data.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="8" style="text-align:center;">Không tìm thấy nhà xuất bản phù hợp!</td>
                    </tr>
                `;
                document.getElementById("pagination_publisher").innerHTML = '';
                return;
            }

            data.data.forEach(publisher => {
                const row = `
                    <tr>
                        <td>${publisher.idnhaxuatban}</td>
                        <td>${publisher.tennxb}</td>
                        <td>${publisher.email}</td>
                        <td>${publisher.sodienthoai}</td>
                        <td>${publisher.diachi}</td>
                        <td>${publisher.trangthai == 1 ? "Hoạt động" : "Tạm khóa"}</td>
                        <td>
                            <a href="../controller/index.php?pg=suanhaxuatban&idnhaxuatban=${publisher.idnhaxuatban}">
                                <img src="../view/layout/logo-icon/update-icon.png" alt="Sửa" style="width:20px; height:20px;">
                            </a>
                        </td>
                        <td>
                            <a href="../controller/index.php?pg=xoanhaxuatban&idnhaxuatban=${publisher.idnhaxuatban}" onclick="return confirm('Bạn có chắc muốn xóa nhà xuất bản ${publisher.tennxb} không?')">
                                <img src="../view/layout/logo-icon/delete-icon.png" alt="Xóa" style="width:20px; height:20px;">
                            </a>
                        </td>
                    </tr>
                `;
                tbody.innerHTML += row;
            });

            // Cập nhật phân trang
            const pagination = document.getElementById("pagination_publisher");
            pagination.innerHTML = "";
            for (let i = 1; i <= data.totalPages; i++) {
                pagination.innerHTML += `
                    <button data-page="${i}" ${i === data.currentPage ? 'class="active"' : ''}>${i}</button>
                `;
            }

            // Gán sự kiện click cho các nút phân trang
            document.querySelectorAll("#pagination_publisher button").forEach(button => {
                button.addEventListener("click", function () {
                    const newPage = parseInt(this.getAttribute("data-page"));
                    searchPublisher(keyword, newPage);
                });
            });
        })
        .catch(error => console.error('Error fetching data:', error));
}

function checkFormNXB(){
    let emailNXB = document.querySelector('input[name="email"]').value.trim();
    let sdtNXB = document.querySelector('input[name="phoneNumber"]').value.trim();

    let emailPattern = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
    if (!emailPattern.test(emailNXB)) {
        alert("Email không hợp lệ! Vui lòng nhập theo cú pháp 'abc@gmail.com'.");
        return false;
    }

    let phonePattern = /^[0-9]{10,11}$/;
    if (!phonePattern.test(sdtNXB)) {
        alert("Số điện thoại không hợp lệ! Vui lòng nhập 10 hoặc 11 số.");
        return false;
    }

    // Nếu tất cả đều hợp lệ, cho phép submit form
    return true;
}

document.addEventListener("DOMContentLoaded", function(){
    loadPublisher(1);
    // searchNhanVien();
    const form = document.querySelector("form");
    form.addEventListener("submit", function(event) {
        if (!checkFormNXB()) {
            event.preventDefault();  // Ngừng gửi form
        }
        
    });
});

// Tìm kiếm nhà xuất bản
document.getElementById("searchNXB").addEventListener("input", function () {
    const keyword = this.value.trim();
    if (keyword !== "") {
        searchNXB(keyword);
    } else {
        loadPublisher(1);
    }
});
