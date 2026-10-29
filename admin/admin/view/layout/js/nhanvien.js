function loadPage(page){
    fetch(`../controller/pagNhanVien.php?page=${page}`)
    .then(response =>{
        if(!response.ok){
            throw new Error('Network respone was not ok')
        }
        return response.json();
    })
    .then(data =>{
        const tbody = document.getElementById('employee-table-body');
        tbody.replaceChildren();
        //Thêm dữ liệu vào bảng
        data.data.forEach(employee => {
            const row = `
            <tr>
                <td>${employee.idnhanvien}</td>
                <td>${employee.ten}</td>
                <td>${employee.email}</td>
                <td>${employee.sodienthoai}</td>
                <td>${employee.chucvu}</td>
                <td>${employee.trangthai ==1 ? "Đang hoạt động" : "Tạm khóa"}</td>
                <td>
                    <a href="../controller/index.php?pg=themtaikhoan&idnhanvien=${employee.idnhanvien}" ${employee.hasAccount ? 'style="pointer-events: none; opacity: 0.5;"' : ''}>
                        <img src="../view/layout/logo-icon/add-icon.png" alt="Thêm tài khoản" style="width:20px; height:20px;">
                    </a>
                </td>
                <td>
                    <a href="../controller/index.php?pg=suanhanvien&idnhanvien=${employee.idnhanvien}">
                        <img src="../view/layout/logo-icon/update-icon.png" alt="Sửa" style="width:20px; height:20px;">
                    </a>
                </td>
                <td>
                    <a href="../controller/index.php?pg=xoanhanvien&idnhanvien=${employee.idnhanvien}" onclick="return confirm('Bạn có chắc muốn xóa nhân viên ${employee.ten} không?')">
                        <img src="../view/layout/logo-icon/delete-icon.png" alt="Xóa" style="width:20px; height:20px;">
                    </a>
                </td>
            </tr>
            `;
            tbody.innerHTML += row;
        });

        //cập nhật phân trang
        const pagination = document.getElementById('pagination');
        pagination.replaceChildren();
        for(let i =1; i<= data.totalPages; i++){
            pagination.innerHTML += `
                <button data-page="${i}" ${i == data.currentPage ? 'class= "active"' : ''}> ${i}
                </button>
            `
        }
        //gắn sụ kiện click cho các nút phân trang
        document.querySelectorAll('#pagination button').forEach(button =>{
            button.addEventListener("click", function(){
                const page = parseInt(this.getAttribute("data-page"));
                loadPage(page);
            });
        });
    })
    .catch(error => console.error("Error fetching data: ", error));
}

function checkFormNV() {
    // Lấy giá trị các trường trong form
    let emailNV = document.querySelector('input[name="emailNV"]').value.trim();
    let phoneNum = document.querySelector('input[name="phoneNum"]').value.trim();

    // Kiểm tra email
    let emailPattern = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
    if (!emailPattern.test(emailNV)) {
        alert("Email không hợp lệ! Vui lòng nhập theo cú pháp 'abc@gmail.com'.");
        return false;
    }

    // Kiểm tra số điện thoại (Chỉ kiểm tra nếu cần định dạng cụ thể)
    let phonePattern = /^[0-9]{10}$/;
    if (!phonePattern.test(phoneNum)) {
        alert("Số điện thoại không hợp lệ! Vui lòng nhập 10 số.");
        return false;
    }

    // Nếu tất cả đều hợp lệ, cho phép submit form
    return true;
}

function searchNhanVien(keyword, page = 1) {
    fetch(`../controller/pagNhanVien.php?search=${encodeURIComponent(keyword)}&page=${page}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            const tbody = document.getElementById("employee-table-body");
            tbody.innerHTML = '';

            if (data.data.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="9" style="text-align:center;">Không tìm thấy nhân viên phù hợp!</td>
                    </tr>
                `;
                document.getElementById("pagination").innerHTML = '';
                return;
            }

            data.data.forEach(employee => {
                const row = `
                    <tr>
                        <td>${employee.idnhanvien}</td>
                        <td>${employee.ten}</td>
                        <td>${employee.email}</td>
                        <td>${employee.sodienthoai}</td>
                        <td>${employee.chucvu}</td>
                        <td>${employee.trangthai == 1 ? "Đang hoạt động" : "Tạm khóa"}</td>
                        <td>
                            <a href="../controller/index.php?pg=themtaikhoan&idnhanvien=${employee.idnhanvien}" ${employee.hasAccount ? 'style="pointer-events: none; opacity: 0.5;"' : ''}>
                                <img src="../view/layout/logo-icon/add-icon.png" alt="Thêm tài khoản" style="width:20px; height:20px;">
                            </a>
                        </td>
                        <td>
                            <a href="../controller/index.php?pg=suanhanvien&idnhanvien=${employee.idnhanvien}">
                                <img src="../view/layout/logo-icon/update-icon.png" alt="Sửa" style="width:20px; height:20px;">
                            </a>
                        </td>
                        <td>
                            <a href="../controller/index.php?pg=xoanhanvien&idnhanvien=${employee.idnhanvien}" onclick="return confirm('Bạn có chắc muốn xóa nhân viên ${employee.ten} không?')">
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
                    searchNhanVien(keyword, page);  // Gọi lại hàm với keyword cũ
                });
            });
        })
        .catch(error => console.error('Error fetching employee data:', error));
}

document.addEventListener("DOMContentLoaded", function(){
    loadPage(1);
    // searchNhanVien();
    const form = document.querySelector("form");
    form.addEventListener("submit", function(event) {
        if (!checkFormNV()) {
            event.preventDefault();  // Ngừng gửi form
        }
    });
});

// Tìm kiếm nhân viên
document.getElementById("search-nv").addEventListener("input", function () {
    const keyword = this.value.trim();
    if (keyword !== "") {
        searchNhanVien(keyword);
    } else {
        loadPage(1);
    }
});


