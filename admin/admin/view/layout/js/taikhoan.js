function loadPage(page){
    fetch(`../controller/pagTaiKhoan.php?page=${page}`)
        .then(response => {
            if(!response.ok){
                throw new Error("NetWork was not ok");
            }
            return response.json();
        })
        .then(data =>{
            const tbody = document.getElementById("account-table-body");
            tbody.replaceChildren();

            //thêm dữ liệu mới vào bảng
            data.data.forEach(account=>{
                const row = `
                    <tr>
                        <td>${account.idnhanvien}</td>
                        <td>${account.TaiKhoan}</td>
                        <td>${account.Quyen}</td>
                        <td>${account.TrangThai == 1 ? "Đang hoạt động":"Tạm khóa"}</td>
                        <td>
                            <a href="../controller/index.php?pg=suataikhoan&idnhanvien=${account.idnhanvien}">
                                <img src="../view/layout/logo-icon/update-icon.png" alt="Sửa" style="width:20px; height:20px;">
                            </a>
                        </td>
                        <td>
                            <a href="../controller/index.php?pg=xoataikhoan&idnhanvien=${account.idnhanvien}" onclick="return confirm('Bạn có chắc muốn xóa tài khoản của nhân viên ${account.idnhanvien}')">
                                <img src="../view/layout/logo-icon/delete-icon.png" alt="Xóa" style="width:20px; height:20px;">
                            </a>
                        </td>
                    </tr>
                `;
                tbody.innerHTML+= row;
            });
            //cập nhật phân trang
            const pagination = document.getElementById('pagination');
            pagination.replaceChildren();
            for (let i = 1; i <= data.totalPages; i++) {
                pagination.innerHTML +=
                `
                    <button data-page = "${i}" ${i == data.currentPage ? 'class = "active"' : ''}>${i}</button>
                `;
            }

            //xử lý click cho các phân trang
            document.querySelectorAll("#pagination button").forEach(button =>{
                button.addEventListener('click', function(){
                    const page = parseInt(this.getAttribute("data-page"));
                    loadPage(page);
                })

            });

        })

}

function checkFormTaiKhoan(event){
    let tenDN = document.querySelector('input[name="tenDangNhap"]').value.trim();
    let matKhau = document.querySelector('input[name="matKhau"]').value.trim();

    if(tenDN.length < 5){
        alert("Tên đăng nhập phải có ít nhất 5 ký tự!");
        return false;
    }

    if(matKhau.length < 6){
        alert("Mật khẩu phải có ít nhất 6 ký tự!");
        return false;
    }

    // Gửi request kiểm tra tài khoản trùng
    fetch('../controller/pagCheckTKTrung.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: 'tenDangNhap=' + encodeURIComponent(tenDN)
    })
    .then(response => response.text())
    .then(result => {
        if (result === "exists") {
            alert("Tên đăng nhập đã tồn tại. Vui lòng nhập tên khác.");
            return false;
        } else {
            // Nếu không trùng, gửi form
            event.target.submit();
        }
    })
    .catch(err => {
        alert("Có lỗi xảy ra khi kiểm tra tài khoản.");
        console.error(err);
        return false;
    });

    return false;  // Ngừng gửi form ngay lập tức khi không gửi đi trong vòng gọi fetch
}

function searchTaiKhoanNhanVien(keyword, page=1){
    fetch(`../controller/pagTaiKhoan.php?search=${encodeURIComponent(keyword)}&page=${page}`)
        .then(response => {
            if(!response.ok){
                throw new Error("NetWork was not ok");
            }
            return response.json();
        })
        .then(data =>{
            const tbody = document.getElementById("account-table-body");
            tbody.replaceChildren();

            if(data.data.length === 0){
                tbody.innerHTML = `
                    <tr>
                        <td colspan="6" style="text-align:center;">Không tìm thấy tài khoản nhân viên phù hợp!</td>
                    </tr>
                `;
    
                // Xóa phân trang nếu không có kết quả
                document.getElementById("pagination").innerHTML = "";
                return;
            }

            //thêm dữ liệu mới vào bảng
            data.data.forEach(account=>{
                const row = `
                    <tr>
                        <td>${account.idnhanvien}</td>
                        <td>${account.TaiKhoan}</td>
                        <td>${account.Quyen}</td>
                        <td>${account.TrangThai == 1 ? "Đang hoạt động":"Tạm khóa"}</td>
                        <td>
                            <a href="../controller/index.php?pg=suataikhoan&idnhanvien=${account.idnhanvien}">
                                <img src="../view/layout/logo-icon/update-icon.png" alt="Sửa" style="width:20px; height:20px;">
                            </a>
                        </td>
                        <td>
                            <a href="../controller/index.php?pg=xoataikhoan&idnhanvien=${account.idnhanvien}" onclick="return confirm('Bạn có chắc muốn xóa tài khoản của nhân viên ${account.idnhanvien}')">
                                <img src="../view/layout/logo-icon/delete-icon.png" alt="Xóa" style="width:20px; height:20px;">
                            </a>
                        </td>
                    </tr>
                `;
                tbody.innerHTML+= row;
            });
            //cập nhật phân trang
            const pagination = document.getElementById('pagination');
            pagination.replaceChildren();
            for (let i = 1; i <= data.totalPages; i++) {
                pagination.innerHTML +=
                `
                    <button data-page = "${i}" ${i == data.currentPage ? 'class = "active"' : ''}>${i}</button>
                `;
            }

            //xử lý click cho các phân trang
            document.querySelectorAll("#pagination button").forEach(button =>{
                button.addEventListener('click', function(){
                    const page = parseInt(this.getAttribute("data-page"));
                    searchTaiKhoanNhanVien(keyword, page);
                })

            });

        })

}

//gọi hàm khi trang được tải
document.addEventListener("DOMContentLoaded", function(){
    loadPage(1);
    const form = document.querySelector("form");
    form.addEventListener("submit", function(event) {
        if (!checkFormTaiKhoan(event)) {
            event.preventDefault();  
        }
    });
});

document.getElementById("searchTKNV").addEventListener("input", function(){
    const keyword = this.value.trim();
    if(keyword !== ""){
        searchTaiKhoanNhanVien(keyword);
    } else {
        loadPage(1);
    }
});
